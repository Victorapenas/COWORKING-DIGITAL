<?php
// ARQUIVO: api/tarefa_checklist_toggle.php
// ATUALIZADO: Salva evidencia_url e evidencia_nome no item

ob_start();
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    $pdo = conectar_db();
    
    $input = $_POST;
    if (empty($input)) $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $tarefaId = (int)($input['tarefa_id'] ?? 0);
    $itemIndex = (int)($input['index'] ?? -1);
    $acao = $input['acao'] ?? 'toggle'; 

    if (!$tarefaId || $itemIndex < 0) throw new Exception("Dados inválidos.");

    $stmt = $pdo->prepare("SELECT checklist FROM tarefa WHERE id = ?");
    $stmt->execute([$tarefaId]);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tarefa) throw new Exception("Tarefa não encontrada.");

    $checklist = json_decode($tarefa['checklist'] ?? '[]', true);
    if (!isset($checklist[$itemIndex])) throw new Exception("Item inexistente.");

    // --- AÇÕES ---

    // 1. Upload de Arquivo
    if ($acao === 'upload' && isset($_FILES['arquivo_item'])) {
        if ($_FILES['arquivo_item']['error'] === 0) {
            $uploadDir = __DIR__ . '/../public/uploads/checklist/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = pathinfo($_FILES['arquivo_item']['name'], PATHINFO_EXTENSION);
            
            // Validação de formato (Opcional, mas recomendada se o front enviar restrições)
            // Aqui confiamos no front por enquanto, ou você pode checar contra $checklist[$itemIndex]['formatos']
            
            $novoNome = 'chk_' . $tarefaId . '_' . $itemIndex . '_' . uniqid() . '.' . $ext;
            
            if (move_uploaded_file($_FILES['arquivo_item']['tmp_name'], $uploadDir . $novoNome)) {
                $checklist[$itemIndex]['evidencia_url'] = 'uploads/checklist/' . $novoNome;
                $checklist[$itemIndex]['evidencia_nome'] = $_FILES['arquivo_item']['name'];
                $checklist[$itemIndex]['concluido'] = 1; 
            } else {
                throw new Exception("Falha ao mover arquivo.");
            }
        }
    }

    // 2. Link
    if ($acao === 'link') {
        $url = trim($input['link_url'] ?? '');
        if($url) {
            $checklist[$itemIndex]['evidencia_url'] = $url;
            $checklist[$itemIndex]['evidencia_nome'] = 'Link Externo';
            $checklist[$itemIndex]['concluido'] = 1;
        }
    }

    // 3. Toggle Simples
    if ($acao === 'toggle') {
        $feito = isset($input['feito']) ? filter_var($input['feito'], FILTER_VALIDATE_BOOLEAN) : false;
        $checklist[$itemIndex]['concluido'] = $feito ? 1 : 0;
    }
    
    // 4. Remover
    if ($acao === 'remover_evidencia') {
        $checklist[$itemIndex]['evidencia_url'] = null;
        $checklist[$itemIndex]['evidencia_nome'] = null;
        $checklist[$itemIndex]['concluido'] = 0;
    }

    // Salva
    $novoJson = json_encode($checklist, JSON_UNESCAPED_UNICODE);
    
    // Recalcula progresso
    $total = count($checklist);
    $concluidos = count(array_filter($checklist, fn($i) => !empty($i['concluido'])));
    $novoProgresso = $total > 0 ? round(($concluidos / $total) * 100) : 0;

    $sql = "UPDATE tarefa SET checklist = ?, progresso = ?, atualizado_em = NOW() WHERE id = ?";
    $pdo->prepare($sql)->execute([$novoJson, $novoProgresso, $tarefaId]);

    echo json_encode(['ok' => true, 'progresso' => $novoProgresso]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
ob_end_flush();
?>