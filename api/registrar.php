<?php
// /api/registrar.php

require_once 'funcoes_api.php';
require_once __DIR__ . '/../config/config.php'; // Inclui para verificar PERMITIR_CADASTRO

if (!PERMITIR_CADASTRO) {
    responder_erro("O registro de novos usuários não está habilitado.", 403);
}

$dados = get_json_input();

$nome = trim($dados['nome'] ?? '');
$email = validar_email($dados['email'] ?? '');
$senha = $dados['senha'] ?? '';

if (empty($nome) || !$email || !validar_senha($senha)) {
    responder_erro("Dados de registro inválidos (Nome, E-mail ou Senha).");
}

try {
    $pdo = conectar_db();
    
    // Busca o ID do papel padrão (ex: FUNCIONARIO)
    $stmt = $pdo->prepare("SELECT id FROM papel WHERE nome = 'FUNCIONARIO'");
    $stmt->execute();
    $papel_id = $stmt->fetchColumn();

    if (!$papel_id) {
        responder_erro("Erro interno: Papel padrão não configurado.", 500);
    }
    
    // Insere o novo usuário
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuario (papel_id, nome, email, senha_hash) VALUES (?, ?, ?, ?)");
    $stmt->execute([$papel_id, $nome, $email, $senha_hash]);

    http_response_code(201); // Created
    echo json_encode(['ok' => true, 'mensagem' => 'Usuário registrado com sucesso.']);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Duplicação (e-mail)
        responder_erro("O e-mail informado já está em uso.", 409);
    }
    responder_erro("Erro interno do servidor: " . $e->getMessage(), 500);
}