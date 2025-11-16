<?php
// /api/login.php

require_once 'funcoes_api.php';
$dados = get_json_input();

$email = validar_email($dados['email'] ?? '');
$senha = $dados['senha'] ?? '';

if (!$email || empty($senha)) {
    responder_erro("E-mail e senha são obrigatórios.");
}

try {
    $pdo = conectar_db();

    // Busca o usuário e a hash da senha
    $stmt = $pdo->prepare("SELECT 
        u.id, u.nome, u.email, u.senha_hash, p.nome AS papel, u.ativo 
        FROM usuario u 
        JOIN papel p ON u.papel_id = p.id
        WHERE u.email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        if ($usuario['ativo'] == 0) {
            aplicar_rate_limit(); // Mesmo que falhe, aplica delay
            responder_erro("Conta desativada. Entre em contato com o suporte.");
        }
        
        // Inicia a sessão
        iniciar_sessao($usuario);
        
        http_response_code(200);
        echo json_encode([
            'ok' => true,
            'usuario' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'papel' => $usuario['papel']
            ]
        ]);
        
    } else {
        aplicar_rate_limit(); // Aplica delay em caso de falha de login
        responder_erro("E-mail ou senha incorretos.");
    }
} catch (Exception $e) {
    responder_erro("Erro interno do servidor: " . $e->getMessage(), 500);
}