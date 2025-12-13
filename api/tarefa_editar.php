<?php
//atualização
// ARQUIVO: api/tarefa_editar.php
ob_start(); ini_set('display_errors', 0); 
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../includes/funcoes.php'; 
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) {
        throw new Exception("Sessão expirada.");
    }
    
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    $pdo = conectar_db();
    
    $tarefaId = (int)($_POST['id'] ?? 0);
    if (!$tarefaId) {
        throw new Exception("ID da tarefa inválido para edição.");
    }
    
    // Coleta dos campos normais
    $nome = trim($_POST['nome'] ?? ''); 
    $descricao = trim($_POST['descricao'] ?? '');
    $responsavelId = (int)($_POST['responsavel_id'] ?? 0);
    $prioridade = $_POST['prioridade'] ?? 'NORMAL';
    $prazo = !empty($_POST['prazo']) ? $_POST['prazo'] : null;
    $status = $_POST['status'] ?? 'A_FAZER';

    if (empty($nome) || !$responsavelId) {
        throw new Exception("Nome e Responsável são obrigatórios.");
    }
    
    if ($prazo) {
        $prazo = $prazo . ' 23:59:59'; 
    }

    // Validação de Permissão (baseada no código existente)
    $podeEditarProjeto = in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR']);
    
    $stmt = $pdo->prepare("SELECT responsavel_id FROM tarefa WHERE id = ?");
    $stmt->execute([$tarefaId]);
    $tarefaAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarefaAtual || (!$podeEditarProjeto && $tarefaAtual['responsavel_id'] != $sessao['id'])) {
        throw new Exception("Permissão negada para editar esta tarefa.");
    }

    // =======================================================================
    // FLUXO DE ATUALIZAÇÃO (Apenas Checklist)
    // =======================================================================

    // 1. Processamento do Checklist
    if (function_exists('processarChecklist')) {
        processarChecklist($tarefaId, $_POST, $pdo); 
    }
    
    // REMOVIDO: Processamento de Exclusão e Upload de Arquivos
    
    // 2. Update SQL dos campos principais (DETALHES)
    $sql = "UPDATE tarefa SET 
                responsavel_id=?, prioridade=?, titulo=?, descricao=?, prazo=?, status=?, atualizado_em=NOW()
            WHERE id=?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $responsavelId, $prioridade, $nome, $descricao, $prazo, $status, $tarefaId
    ]);


    echo json_encode(['ok' => true, 'mensagem' => 'Tarefa atualizada com sucesso!']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}

ob_end_flush();
?>