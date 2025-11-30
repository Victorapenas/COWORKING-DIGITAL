<?php
// /api/senha/verificar.php

require_once __DIR__ . '/../funcoes_api.php';
$dados = get_json_input();

$email = validar_email($dados['email'] ?? '');
$codigo = $dados['codigo'] ?? '';

if (!$email || !validar_codigo_recuperacao($codigo)) {
    responder_erro("E-mail ou código de 4 dígitos inválidos.");
}

try {
    $pdo = conectar_db();

    // 1. Busca o ID do usuário
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $usuario_id = $stmt->fetchColumn();

    if (!$usuario_id) {
        aplicar_rate_limit();
        responder_erro("E-mail não cadastrado.", 404);
    }

    // 2. Valida o código
    $stmt = $pdo->prepare("SELECT id FROM token_recuperacao_senha 
                           WHERE usuario_id = ? 
                           AND codigo = ? 
                           AND usado_em IS NULL 
                           AND expira_em > NOW()");
    $stmt->execute([$usuario_id, $codigo]);
    $token = $stmt->fetch();

    if (!$token) {
        aplicar_rate_limit();
        responder_erro("Código incorreto ou expirado.", 401);
    }

    // Código é válido. Não o marca como usado *ainda* para permitir a redefinição.
    http_response_code(200);
    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    responder_erro("Erro interno do servidor: " . $e->getMessage(), 500);
}