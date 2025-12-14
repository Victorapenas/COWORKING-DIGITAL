<?php
// ARQUIVO: api/tarefa_checklist_toggle.php
// ATUALIZADO: Gerencia upload de arquivos, links e status de itens do checklist

ob_start();
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    $pdo = conectar_db();
    
    // Suporte a JSON no body ou FormData
    $input = $_POST;
    if (empty($input)) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    $tarefaId = (int)($input['tarefa_id'] ?? 0);
    $itemIndex = (int)($input['index'] ?? -1);
    $acao = $input['acao'] ?? 'toggle'; 

    if (!$tarefaId || $itemIndex < 0) throw new Exception("Dados inválidos.");

    // Busca a tarefa e o checklist atual
    $stmt = $pdo->prepare("SELECT checklist FROM tarefa WHERE id = ?");
    $stmt->execute([$tarefaId]);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tarefa) throw new Exception("Tarefa não encontrada.");

    $checklist = json_decode($tarefa['checklist'] ?? '[]', true);
    
    // Verifica se o índice existe (pode ser um array vazio se for o primeiro item e houver erro de sincronia, mas geralmente valida)
    if (!isset($checklist[$itemIndex])) throw new Exception("Item do checklist inexistente.");

    // --- PROCESSAMENTO DAS AÇÕES ---

    // 1. Upload de Arquivo (Evidência)
    if ($acao === 'upload' && isset($_FILES['arquivo_item'])) {
        if ($_FILES['arquivo_item']['error'] === 0) {
            $uploadDir = __DIR__ . '/../public/uploads/checklist/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = pathinfo($_FILES['arquivo_item']['name'], PATHINFO_EXTENSION);
            
            // Gera nome único para evitar sobrescrita
            $novoNome = 'chk_' . $tarefaId . '_' . $itemIndex . '_' . uniqid() . '.' . $ext;
            
            if (move_uploaded_file($_FILES['arquivo_item']['tmp_name'], $uploadDir . $novoNome)) {
                $checklist[$itemIndex]['evidencia_url'] = 'uploads/checklist/' . $novoNome;
                $checklist[$itemIndex]['evidencia_nome'] = $_FILES['arquivo_item']['name'];
                $checklist[$itemIndex]['concluido'] = 1; // Marca automaticamente como feito
            } else {
                throw new Exception("Falha ao mover arquivo para o servidor.");
            }
        } else {
            throw new Exception("Erro no envio do arquivo: Código " . $_FILES['arquivo_item']['error']);
        }
    }

    // 2. Salvar Link Externo
    if ($acao === 'link') {
        $url = trim($input['link_url'] ?? '');
        if($url) {
            $checklist[$itemIndex]['evidencia_url'] = $url;
            $checklist[$itemIndex]['evidencia_nome'] = 'Link Externo';
            $checklist[$itemIndex]['concluido'] = 1; // Marca como feito
        } else {
            throw new Exception("URL inválida.");
        }
    }

    // 3. Toggle Simples (Marcar/Desmarcar manual)
    if ($acao === 'toggle') {
        $feito = isset($input['feito']) ? filter_var($input['feito'], FILTER_VALIDATE_BOOLEAN) : false;
        $checklist[$itemIndex]['concluido'] = $feito ? 1 : 0;
    }
    
    // 4. Remover Evidência (Limpar item)
    if ($acao === 'remover_evidencia') {
        $checklist[$itemIndex]['evidencia_url'] = null;
        $checklist[$itemIndex]['evidencia_nome'] = null;
        $checklist[$itemIndex]['concluido'] = 0; // Define como pendente
    }

    // --- SALVAMENTO E ATUALIZAÇÃO ---

    $novoJson = json_encode($checklist, JSON_UNESCAPED_UNICODE);
    
    // Recalcula o progresso total da tarefa (0 a 100%)
    $totalItens = count($checklist);
    $itensConcluidos = count(array_filter($checklist, fn($i) => !empty($i['concluido'])));
    $novoProgresso = $totalItens > 0 ? round(($itensConcluidos / $totalItens) * 100) : 0;

    // Atualiza no banco
    $sql = "UPDATE tarefa SET checklist = ?, progresso = ?, atualizado_em = NOW() WHERE id = ?";
    $stmtUpdate = $pdo->prepare($sql);
    $stmtUpdate->execute([$novoJson, $novoProgresso, $tarefaId]);

    echo json_encode([
        'ok' => true, 
        'progresso' => $novoProgresso,
        'mensagem' => 'Checklist atualizado com sucesso.'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}

ob_end_flush();
?>