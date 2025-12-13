<?php
// ARQUIVO: api/tarefa_checklist_toggle.php
ob_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    
    $pdo = conectar_db();
    
    // Suporte a FormData (Arquivos) e JSON Raw
    $input = $_POST;
    if (empty($input)) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    $tarefaId = (int)($input['tarefa_id'] ?? 0);
    $itemIndex = (int)($input['index'] ?? -1);
    // 'feito' pode vir como string "true"/"false" do FormData ou booleano do JSON
    $feito = isset($input['feito']) ? filter_var($input['feito'], FILTER_VALIDATE_BOOLEAN) : false;

    if (!$tarefaId || $itemIndex < 0) throw new Exception("Dados inválidos.");

    // Busca Checklist Atual
    $stmt = $pdo->prepare("SELECT checklist, responsavel_id FROM tarefa WHERE id = ?");
    $stmt->execute([$tarefaId]);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarefa) throw new Exception("Tarefa não encontrada.");

    $checklist = json_decode($tarefa['checklist'] ?? '[]', true);
    if (!isset($checklist[$itemIndex])) throw new Exception("Item inexistente.");

    // Atualiza status booleano
    $checklist[$itemIndex]['concluido'] = $feito ? 1 : 0;

    // Processa Upload de Arquivo para este item (Se houver)
    if (isset($_FILES['arquivo_item']) && $_FILES['arquivo_item']['error'] === 0) {
        $uploadDir = __DIR__ . '/../public/uploads/checklist/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['arquivo_item']['name'], PATHINFO_EXTENSION);
        $novoNome = 'chk_' . $tarefaId . '_' . $itemIndex . '_' . time() . '.' . $ext;
        
        if (move_uploaded_file($_FILES['arquivo_item']['tmp_name'], $uploadDir . $novoNome)) {
            // Salva metadados do arquivo no item do checklist
            $checklist[$itemIndex]['arquivo'] = [
                'url' => 'uploads/checklist/' . $novoNome,
                'nome' => $_FILES['arquivo_item']['name'],
                'data' => date('d/m/Y H:i')
            ];
        }
    }

    // Salva no Banco
    $novoJson = json_encode($checklist, JSON_UNESCAPED_UNICODE);
    
    // Recalcula progresso
    $total = count($checklist);
    $concluidos = count(array_filter($checklist, fn($i) => !empty($i['concluido'])));
    $novoProgresso = $total > 0 ? round(($concluidos / $total) * 100) : 0;

    $sql = "UPDATE tarefa SET checklist = ?, progresso = ?, atualizado_em = NOW() WHERE id = ?";
    $pdo->prepare($sql)->execute([$novoJson, $novoProgresso, $tarefaId]);

    echo json_encode([
        'ok' => true, 
        'progresso' => $novoProgresso, 
        'item_atualizado' => $checklist[$itemIndex]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
ob_end_flush();
?>