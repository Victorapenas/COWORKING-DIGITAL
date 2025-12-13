<?php
// ARQUIVO: api/tarefa_editar.php
ob_start(); ini_set('display_errors', 0); 
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../includes/funcoes.php'; 
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    $pdo = conectar_db();
    
    $tarefaId = (int)($_POST['id'] ?? 0);
    if (!$tarefaId) throw new Exception("ID inválido.");
    
    $nome = trim($_POST['nome'] ?? ''); 
    $descricao = trim($_POST['descricao'] ?? '');
    $responsavelId = (int)($_POST['responsavel_id'] ?? 0);
    $prioridade = $_POST['prioridade'] ?? 'NORMAL';
    $prazo = !empty($_POST['prazo']) ? $_POST['prazo'] : null;
    
    $status = $_POST['status'] ?? 'PENDENTE';
    if ($status === 'A_FAZER') $status = 'PENDENTE';

    if (empty($nome) || !$responsavelId) throw new Exception("Campos obrigatórios vazios.");
    
    if ($prazo) $prazo = $prazo . ' 23:59:59'; 

    $podeEditar = in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR']);
    
    // Checklist
    if (function_exists('processarChecklist')) {
        processarChecklist($tarefaId, $_POST, $pdo); 
    }
    // Checklist
    $checklistArray = processarChecklist(0, $_POST, $pdo);
    $checklistJson = empty($checklistArray) ? NULL : json_encode($checklistArray, JSON_UNESCAPED_UNICODE);

    $sql = "UPDATE tarefa SET 
                responsavel_id=?, prioridade=?, titulo=?, descricao=?, prazo=?, status=?, checklist=?, atualizado_em=NOW()
            WHERE id=?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$responsavelId, $prioridade, $nome, $descricao, $prazo, $status, $checklistJson, $tarefaId]);

    echo json_encode(['ok' => true, 'mensagem' => 'Tarefa atualizada com sucesso!']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
ob_end_flush();
?>