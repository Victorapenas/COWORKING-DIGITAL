<?php
// ARQUIVO: api/tarefa_buscar.php
ob_start(); ini_set('display_errors', 0); 
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../includes/funcoes.php'; 
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) {
        throw new Exception("Sessão expirada.");
    }
    
    $tarefaId = (int)($_GET['id'] ?? 0);
    if (!$tarefaId) {
        throw new Exception("ID da tarefa ausente.");
    }
    
    $pdo = conectar_db();
    
    // Seleciona os dados necessários para preencher o formulário de edição
    $stmt = $pdo->prepare("SELECT id, projeto_id, responsavel_id, titulo, descricao, prazo, status FROM tarefa WHERE id = ?");
    $stmt->execute([$tarefaId]);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarefa) {
        throw new Exception("Tarefa não encontrada.");
    }
    
    // Formata a data para o formato yyyy-mm-dd do input HTML (importante para o campo 'date')
    if ($tarefa['prazo']) {
        $tarefa['prazo'] = date('Y-m-d', strtotime($tarefa['prazo']));
    }

    echo json_encode(['ok' => true, 'tarefa' => $tarefa]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}

ob_end_flush();
?>