<?php
// ARQUIVO: api/tarefa_excluir.php
ob_start();
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php'; 
require_once __DIR__ . '/../includes/seguranca.php';

$resposta = [];

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    
    // 1. Verificação de Permissão
    if (!in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR'])) {
        throw new Exception("Permissão negada. Apenas DONO, LIDER ou GESTOR podem excluir tarefas.");
    }

    // 2. Coleta e Validação do ID
    $tarefaId = (int)($_POST['id'] ?? 0);
    if (!$tarefaId) throw new Exception("ID da tarefa inválido.");

    $pdo = conectar_db();
    
    // 3. Verificação de Propriedade da Empresa (Segurança)
    $empresaId = getEmpresaIdLogado($sessao);
    $stmt = $pdo->prepare("SELECT id FROM tarefa WHERE id = ? AND empresa_id = ?");
    $stmt->execute([$tarefaId, $empresaId]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Tarefa não encontrada, ou você não tem permissão para excluí-la.");
    }

    // =======================================================================
    // 4. CORREÇÃO DE EXCLUSÃO (LIMPEZA MANUAL DE DEPENDÊNCIAS)
    // Exclui comentários primeiro para evitar bloqueio da Chave Estrangeira.
    // =======================================================================
    $sqlDeleteComentarios = "DELETE FROM comentario_tarefa WHERE tarefa_id = ?";
    $pdo->prepare($sqlDeleteComentarios)->execute([$tarefaId]);

    // ** ATENÇÃO: Se houver outras tabelas que dependam de 'tarefa',
    // ** adicione a exclusão delas aqui (ex: 'arquivos_tarefa', 'log_tarefa').

    // 5. EXCLUSÃO DA TAREFA PRINCIPAL
    $sql = "DELETE FROM tarefa WHERE id = ?";
    $stmtExec = $pdo->prepare($sql);
    $stmtExec->execute([$tarefaId]);

    $resposta = ['ok' => true, 'mensagem' => 'Tarefa excluída permanentemente.'];

} catch (Exception $e) {
    http_response_code(400);
    $resposta = ['ok' => false, 'erro' => $e->getMessage()];
}

ob_end_clean();
echo json_encode($resposta);
?>