<?php
//atualização
// ARQUIVO: api/perfil_editar.php
ob_start();
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");

    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    $usuarioId = $sessao['id'];
    
    // Lê JSON enviado pelo JS
    $dados = get_json_input();
    
    $nome = trim($dados['nome'] ?? '');
    $email = validar_email($dados['email'] ?? '');
    $novaSenha = $dados['senha'] ?? '';

    if (empty($nome) || !$email) {
        throw new Exception("Nome e E-mail são obrigatórios.");
    }

    $pdo = conectar_db();

    // 1. Verifica duplicidade de e-mail (exceto o próprio)
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ? AND id != ?");
    $stmt->execute([$email, $usuarioId]);
    if ($stmt->fetch()) {
        throw new Exception("Este e-mail já está em uso.");
    }

    // 2. Monta Query
    $params = [$nome, $email];
    $sql = "UPDATE usuario SET nome = ?, email = ?";

    if (!empty($novaSenha)) {
        if (strlen($novaSenha) < 8) {
            throw new Exception("A nova senha deve ter no mínimo 8 caracteres.");
        }
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $sql .= ", senha_hash = ?";
        $params[] = $hash;
    }

    $sql .= " WHERE id = ?";
    $params[] = $usuarioId;

    $stmtUp = $pdo->prepare($sql);
    $stmtUp->execute($params);

    // 3. Atualiza Sessão IMEDIATAMENTE
    $_SESSION[SESSAO_USUARIO_KEY]['nome'] = $nome;
    $_SESSION[SESSAO_USUARIO_KEY]['email'] = $email;

    ob_clean();
    echo json_encode(['ok' => true, 'mensagem' => 'Perfil atualizado!']);

} catch (Exception $e) {
    ob_clean();
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
exit;
?>