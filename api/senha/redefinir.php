<?php
// /api/senha/redefinir.php

require_once __DIR__ . '/../funcoes_api.php';
$dados = get_json_input();

$email = validar_email($dados['email'] ?? '');
$codigo = $dados['codigo'] ?? '';
$nova_senha = $dados['nova_senha'] ?? '';

if (!$email || !validar_codigo_recuperacao($codigo) || !validar_senha($nova_senha)) {
    responder_erro("Dados incompletos ou inválidos.");
}

try {
    $pdo = conectar_db();
    $pdo->beginTransaction();

    // 1. Busca o ID do usuário
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    $usuario_id = $usuario['id'] ?? null;

    if (!$usuario_id) {
        aplicar_rate_limit();
        $pdo->rollBack();
        responder_erro("Usuário não encontrado.", 404);
    }

    // 2. Valida o código (verificação final)
    $stmt = $pdo->prepare("SELECT id FROM token_recuperacao_senha 
                           WHERE usuario_id = ? 
                           AND codigo = ? 
                           AND usado_em IS NULL 
                           AND expira_em > NOW() 
                           FOR UPDATE"); // Bloqueia para evitar concorrência
    $stmt->execute([$usuario_id, $codigo]);
    $token_id = $stmt->fetchColumn();

    if (!$token_id) {
        aplicar_rate_limit();
        $pdo->rollBack();
        responder_erro("Código inválido ou expirado. Comece a recuperação novamente.", 401);
    }

    // 3. Atualiza a senha
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuario SET senha_hash = ? WHERE id = ?");
    $stmt->execute([$senha_hash, $usuario_id]);

    // 4. Invalida o token (MARCA COMO USADO)
    $stmt = $pdo->prepare("UPDATE token_recuperacao_senha SET usado_em = NOW() WHERE id = ?");
    $stmt->execute([$token_id]);

    $pdo->commit();
    http_response_code(200);
    echo json_encode(['ok' => true, 'mensagem' => 'Senha redefinida com sucesso.']);

} catch (Exception $e) {
    $pdo->rollBack();
    responder_erro("Erro interno do servidor: " . $e->getMessage(), 500);
}