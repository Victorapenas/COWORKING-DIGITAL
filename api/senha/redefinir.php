<?php
// /api/senha/redefinir.php

require_once __DIR__ . '/../funcoes_api.php';
$dados = get_json_input();

// Validações de entrada
$email = validar_email($dados['email'] ?? '');
$codigo = $dados['codigo'] ?? '';
$nova_senha = $dados['nova_senha'] ?? '';

if (!$email || !validar_codigo_recuperacao($codigo) || !validar_senha($nova_senha)) {
    responder_erro("Dados inválidos ou senha muito curta.");
}

try {
    $pdo = conectar_db();
    $pdo->beginTransaction();

    // 1. Busca o ID do usuário pelo e-mail
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    $usuario_id = $stmt->fetchColumn();

    // Se o usuário não existir, interrompe (evita erro na próxima query)
    if (!$usuario_id) {
        // Pequeno delay para evitar ataque de enumeração de usuários
        aplicar_rate_limit(); 
        throw new Exception("E-mail não encontrado ou inválido.");
    }

    // 2. Valida Token (verifica se bate com o usuário, código, se não foi usado e validade)
    $stmt = $pdo->prepare("SELECT id FROM token_recuperacao_senha WHERE usuario_id = ? AND codigo = ? AND usado_em IS NULL AND expira_em > NOW()");
    $stmt->execute([$usuario_id, $codigo]);
    $token_id = $stmt->fetchColumn();

    if (!$token_id) {
        $pdo->rollBack();
        responder_erro("Código inválido ou expirado.", 401);
    }

    // 3. Atualiza Senha E REMOVE A FLAG DE PRIMEIRO ACESSO
    // O hash da senha é gerado aqui
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    // IMPORTANTE: Atualiza a senha e define precisa_redefinir_senha = 0
    $stmt = $pdo->prepare("UPDATE usuario SET senha_hash = ?, precisa_redefinir_senha = 0 WHERE id = ?");
    $stmt->execute([$senha_hash, $usuario_id]);

    // 4. Queima o token (marca como usado)
    $stmt = $pdo->prepare("UPDATE token_recuperacao_senha SET usado_em = NOW() WHERE id = ?");
    $stmt->execute([$token_id]);

    $pdo->commit();
    
    // Retorna sucesso
    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    // Se houver transação ativa, desfaz
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Log do erro real no servidor (opcional) e resposta genérica ao usuário
    // error_log($e->getMessage()); 
    responder_erro("Erro ao redefinir senha: " . $e->getMessage(), 500);
}
?>