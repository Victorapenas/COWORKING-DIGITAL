<?php
//atualização
// ARQUIVO: api/tarefa_excluir.php
ob_start();
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");

// Corrigido o caminho do require para funcoes.php, que deve conter conectar_db()
require_once __DIR__ . '/../includes/funcoes.php'; 
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
    
    // 3. Busca de Metadados e Verificação de Propriedade
    $empresaId = getEmpresaIdLogado($sessao);
    // Busca a coluna arquivos_tarefa para saber quais arquivos deletar
    $stmt = $pdo->prepare("SELECT id, arquivos_tarefa FROM tarefa WHERE id = ? AND empresa_id = ?");
    $stmt->execute([$tarefaId, $empresaId]);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tarefa) {
        throw new Exception("Tarefa não encontrada, ou você não tem permissão para excluí-la.");
    }

    // 4. LIMPEZA FÍSICA DE ARQUIVOS (IMPORTANTE)
    $arquivos = json_decode($tarefa['arquivos_tarefa'] ?? '[]', true);

    // Exclui arquivos físicos
    foreach ($arquivos as $arquivo) {
        // Constrói o caminho físico completo
        $caminhoFisico = __DIR__ . '/../' . $arquivo['caminho_arquivo']; 
        if (file_exists($caminhoFisico)) {
            // @ para suprimir erros caso a permissão esteja errada
            @unlink($caminhoFisico);
        }
    }
    
    // Remove a pasta de uploads da tarefa (rmdir só funciona se estiver vazia)
    $pastaUpload = __DIR__ . '/../uploads/tarefas/' . $tarefaId;
    if (is_dir($pastaUpload)) {
        @rmdir($pastaUpload); 
    }

    // 5. Exclui comentários
    $sqlDeleteComentarios = "DELETE FROM comentario_tarefa WHERE tarefa_id = ?";
    $pdo->prepare($sqlDeleteComentarios)->execute([$tarefaId]);

    // 6. EXCLUSÃO DA TAREFA PRINCIPAL
    $sql = "DELETE FROM tarefa WHERE id = ?";
    $stmtExec = $pdo->prepare($sql);
    $stmtExec->execute([$tarefaId]);

    $resposta = [
        'ok' => true,
        'mensagem' => "Tarefa excluída com sucesso."
    ];
    
    echo json_encode($resposta);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}

ob_end_flush();
?>