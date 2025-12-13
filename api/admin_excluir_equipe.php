<?php
//atualização
// ARQUIVO: api/admin_excluir_equipe.php
ob_start();
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

$resposta = [];
try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    
    if ($sessao['papel'] !== 'DONO' && $sessao['papel'] !== 'LIDER') throw new Exception("Apenas Liderança pode excluir equipes.");

    $dados = get_json_input();
    $id = (int)($dados['id'] ?? 0);
    // Ignoramos a opção de deletar membros se ela vier, forçamos o movimento para Geral
    // Mas se quiser manter a opção flexível, use: $deletarMembros = !empty($dados['deletar_membros']);
    // Como você pediu: "não é para excluir os membros", vamos forçar false.
    $deletarMembros = false; 

    if (!$id) throw new Exception("ID inválido.");

    $pdo = conectar_db();
    $pdo->beginTransaction();

    // 1. Verifica se a equipe é da empresa e pega o nome
    $stmt = $pdo->prepare("SELECT id, nome FROM equipe WHERE id = ? AND empresa_id = (SELECT empresa_id FROM usuario WHERE id = ?)");
    $stmt->execute([$id, $sessao['id']]);
    $equipe = $stmt->fetch();

    if (!$equipe) throw new Exception("Equipe não encontrada.");

    // 2. PROTEÇÃO: Não excluir equipe Geral
    if (strtolower($equipe['nome']) === 'geral') {
        throw new Exception("A equipe Geral é padrão e não pode ser excluída.");
    }

    // 3. Move os membros para "Geral" (NULL)
    // Isso garante que eles apareçam como "Sem equipe" ou na lista geral
    $stmtUp = $pdo->prepare("UPDATE usuario SET equipe_id = NULL WHERE equipe_id = ?");
    $stmtUp->execute([$id]);

    // 4. Deleta a Equipe
    $stmtDel = $pdo->prepare("DELETE FROM equipe WHERE id = ?");
    $stmtDel->execute([$id]);

    $pdo->commit();
    $resposta = ['ok' => true, 'mensagem' => 'Equipe excluída e membros movidos para Geral.'];

} catch (Exception $e) {
    if(isset($pdo)) $pdo->rollBack();
    http_response_code(400); $resposta = ['ok' => false, 'erro' => $e->getMessage()];
}
ob_end_clean(); echo json_encode($resposta);
?>