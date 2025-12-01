<?php
// ARQUIVO: api/admin_mover_membro.php
ob_start();
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

$resposta = [];

try {
    if (!esta_logado()) throw new Exception("Não autorizado.", 401);
    
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    if (!in_array($sessao['papel'], ['DONO', 'GESTOR', 'LIDER'])) {
        throw new Exception("Permissão negada.", 403);
    }

    $dados = get_json_input();
    $usuario_id = (int)($dados['usuario_id'] ?? 0);
    $equipe_id = (int)($dados['equipe_id'] ?? 0); // Se for 0, remove da equipe

    if (!$usuario_id) throw new Exception("Usuário inválido.");

    $pdo = conectar_db();

    // Verifica se o usuário alvo pertence à mesma empresa
    $stmt = $pdo->prepare("SELECT empresa_id FROM usuario WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $empresaAlvo = $stmt->fetchColumn();

    // Verifica empresa do admin logado
    $stmtEmp = $pdo->prepare("SELECT empresa_id FROM usuario WHERE id = ?");
    $stmtEmp->execute([$sessao['id']]);
    $empresaAdmin = $stmtEmp->fetchColumn();

    if ($empresaAlvo != $empresaAdmin) throw new Exception("Usuário não pertence à sua empresa.");

    // Atualiza
    $equipe_sql = ($equipe_id > 0) ? $equipe_id : null;
    $stmtUp = $pdo->prepare("UPDATE usuario SET equipe_id = ? WHERE id = ?");
    $stmtUp->execute([$equipe_sql, $usuario_id]);

    $resposta = ['ok' => true, 'mensagem' => 'Membro movido com sucesso!'];

} catch (Exception $e) {
    http_response_code(400);
    $resposta = ['ok' => false, 'erro' => $e->getMessage()];
}

ob_end_clean();
echo json_encode($resposta);
?>