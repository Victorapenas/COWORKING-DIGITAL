<?php
// ARQUIVO: api/tarefa_criar.php
// ATUALIZADO: Correção para salvar Horário exato e tratamento de erros

ob_start(); 
ini_set('display_errors', 0); 
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../includes/funcoes.php'; 
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    $pdo = conectar_db();
    
    $empresaId = getEmpresaIdLogado($sessao);
    $criadorId = $sessao['id'];
    
    $projetoId = (int)($_POST['projeto_id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $responsavelId = (int)($_POST['responsavel_id'] ?? 0);
    $prioridade = $_POST['prioridade'] ?? 'NORMAL';
    
    // --- CORREÇÃO DE DATA E HORÁRIO ---
    $prazo = !empty($_POST['prazo']) ? $_POST['prazo'] : null;
    
    if ($prazo) {
        // O input type="datetime-local" envia o formato "YYYY-MM-DDTHH:MM"
        // Precisamos substituir o "T" por espaço para o formato MySQL "YYYY-MM-DD HH:MM:SS"
        $prazo = str_replace('T', ' ', $prazo);
        
        // Se a string tiver apenas data e hora (16 chars), adiciona os segundos
        if (strlen($prazo) <= 16) {
            $prazo .= ":00";
        }
        // Se por acaso vier só a data (legado), mantém a lógica de fim do dia (opcional)
        elseif (strlen($prazo) <= 10) {
            $prazo .= ' 23:59:59';
        }
    }
    
    // Correção de status
    $status = $_POST['status'] ?? 'PENDENTE'; 
    if ($status === 'A_FAZER') $status = 'PENDENTE';
    
    // Validação
    if (!$projetoId || empty($nome) || !$responsavelId) {
        throw new Exception("Dados obrigatórios incompletos (Projeto, Nome ou Responsável).");
    }

    // Processamento do Checklist
    $checklistArray = processarChecklist(0, $_POST, $pdo);
    $checklistJson = empty($checklistArray) ? NULL : json_encode($checklistArray, JSON_UNESCAPED_UNICODE);
    
    $sql = "INSERT INTO tarefa 
                (projeto_id, empresa_id, criador_id, responsavel_id, prioridade, titulo, descricao, prazo, status, checklist, criado_em, atualizado_em)
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $projetoId, $empresaId, $criadorId, $responsavelId, $prioridade, $nome, $descricao, $prazo, $status, $checklistJson
    ]);

    echo json_encode(['ok' => true, 'mensagem' => 'Tarefa criada com sucesso!', 'id' => $pdo->lastInsertId()]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
ob_end_flush();
?>