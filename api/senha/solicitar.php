<?php
// /api/senha/solicitar.php

require_once __DIR__ . '/../funcoes_api.php';
$dados = get_json_input();

$email = validar_email($dados['email'] ?? '');

if (!$email) {
    responder_erro("E-mail inválido.");
}

try {
    $pdo = conectar_db();

    // 1. Busca o ID do usuário
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $usuario_id = $stmt->fetchColumn();

    if (!$usuario_id) {
        // Para evitar enumeração de usuários, responde 'OK' mesmo que o e-mail não exista
        // Mas para fins acadêmicos e feedback, vamos responder erro se não achar, simplificando o fluxo.
        aplicar_rate_limit();
        responder_erro("E-mail não cadastrado ou inativo.");
    }
    
    // 2. Invalida tokens anteriores para este usuário
    $stmt = $pdo->prepare("UPDATE token_recuperacao_senha SET usado_em = NOW() WHERE usuario_id = ? AND usado_em IS NULL AND expira_em > NOW()");
    $stmt->execute([$usuario_id]);

    // 3. Gera e salva o novo código (4 dígitos)
    $codigo = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $expira_em = date('Y-m-d H:i:s', time() + (CODIGO_EXPIRACAO_MINUTOS * 60));

    $stmt = $pdo->prepare("INSERT INTO token_recuperacao_senha (usuario_id, codigo, expira_em) VALUES (?, ?, ?)");
    $stmt->execute([$usuario_id, $codigo, $expira_em]);

    // 4. Responde com sucesso (e código de debug)
    http_response_code(200);
    echo json_encode([
        'ok' => true, 
        'mensagem' => 'Código gerado e enviado (simulado).',
        'codigo_debug' => $codigo // Apenas para ambiente acadêmico/dev!
    ]);

} catch (Exception $e) {
    responder_erro("Erro interno do servidor: " . $e->getMessage(), 500);
}