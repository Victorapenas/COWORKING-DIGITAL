<?php
// ARQUIVO: api/projeto_excluir.php
ob_start();
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

$resposta = [];

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    // Permissão
    if (!in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR'])) {
        throw new Exception("Apenas gestores podem gerenciar projetos.");
    }

    $dados = get_json_input();
    $id = (int)($dados['id'] ?? 0);
    $tipo = $dados['tipo'] ?? 'soft'; // 'soft' (arquivar), 'hard' (deletar), 'restore' (restaurar)

    if (!$id) throw new Exception("ID inválido.");

    $pdo = conectar_db();

    // Verifica se projeto existe
    $stmt = $pdo->prepare("SELECT id FROM projeto WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) throw new Exception("Projeto não encontrado.");

    if ($tipo === 'hard') {
        // Exclusão Definitiva (Apenas Dono/Lider)
        if ($sessao['papel'] !== 'DONO' && $sessao['papel'] !== 'LIDER') {
            throw new Exception("Apenas Donos podem excluir definitivamente.");
        }
        // Remove vínculos primeiro (embora o CASCADE no banco devesse cuidar, é bom garantir)
        $pdo->prepare("DELETE FROM projeto_equipe WHERE projeto_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM tarefa WHERE projeto_id = ?")->execute([$id]); // Opcional, dependendo da regra de negócio
        
        $sql = "DELETE FROM projeto WHERE id = ?";
        $msg = "Projeto excluído permanentemente.";
    
    } elseif ($tipo === 'restore') {
        // Restaurar
        $sql = "UPDATE projeto SET ativo = 1 WHERE id = ?";
        $msg = "Projeto restaurado para a lista principal.";
    
    } else {
        // Arquivar (Soft Delete)
        $sql = "UPDATE projeto SET ativo = 0 WHERE id = ?";
        $msg = "Projeto movido para arquivados.";
    }

    $stmtExec = $pdo->prepare($sql);
    $stmtExec->execute([$id]);

    $resposta = ['ok' => true, 'mensagem' => $msg];

} catch (Exception $e) {
    http_response_code(400);
    $resposta = ['ok' => false, 'erro' => $e->getMessage()];
}

ob_end_clean();
echo json_encode($resposta);
?>