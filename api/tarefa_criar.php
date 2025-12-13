<?php
//atualização
// ARQUIVO: api/tarefa_criar.php
ob_start(); ini_set('display_errors', 0); 
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../includes/funcoes.php'; 
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado() || !in_array($_SESSION[SESSAO_USUARIO_KEY]['papel'], ['DONO', 'LIDER', 'GESTOR'])) {
        throw new Exception("Permissão negada ou sessão expirada.");
    }
    
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    $pdo = conectar_db();
    
    // Coleta e Validação de Dados
    $empresaId = getEmpresaIdLogado($sessao);
    $criadorId = $sessao['id'];
    
    $projetoId = (int)($_POST['projeto_id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $responsavelId = (int)($_POST['responsavel_id'] ?? 0);
    $prioridade = $_POST['prioridade'] ?? 'NORMAL';
    $prazo = !empty($_POST['prazo']) ? $_POST['prazo'] : null;
    $status = $_POST['status'] ?? 'A_FAZER'; 
    
    if (!$projetoId || empty($nome) || !$responsavelId) {
        throw new Exception("Dados obrigatórios incompletos.");
    }
    
    if ($prazo) {
        $prazo = $prazo . ' 23:59:59'; 
    }

    // 1. Processa Checklist (Gera o JSON inicial)
    // CRUCIAL: A função processarChecklist deve estar definida em funcoes.php
    $checklistArray = processarChecklist(0, $_POST, $pdo);
    $checklistJson = empty($checklistArray) ? NULL : json_encode($checklistArray, JSON_UNESCAPED_UNICODE);
    
    // 2. Insert SQL: Inclui apenas a coluna 'checklist'
    $sql = "INSERT INTO tarefa 
                (projeto_id, empresa_id, criador_id, responsavel_id, prioridade, titulo, descricao, prazo, status, checklist, criado_em, atualizado_em)
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $projetoId, $empresaId, $criadorId, $responsavelId, $prioridade, $nome, $descricao, $prazo, $status, $checklistJson
    ]);

    $novaTarefaId = $pdo->lastInsertId(); 

    echo json_encode(['ok' => true, 'mensagem' => 'Tarefa criada com sucesso!', 'id' => $novaTarefaId]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}

ob_end_flush();
?>