<?php
// /includes/seguranca.php

require_once __DIR__ . '/../config/config.php';
// CORREÇÃO: Importar o arquivo onde a função redirecionar() foi criada
require_once __DIR__ . '/funcoes.php'; 

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o usuário está logado.
 * @return bool
 */
function esta_logado(): bool {
    return isset($_SESSION[SESSAO_USUARIO_KEY]) && is_array($_SESSION[SESSAO_USUARIO_KEY]);
}

/**
 * Armazena os dados do usuário na sessão.
 */
function iniciar_sessao(array $usuario) {
    $_SESSION[SESSAO_USUARIO_KEY] = [
        'id'    => $usuario['id'],
        'nome'  => $usuario['nome'],
        'email' => $usuario['email'],
        'papel' => $usuario['papel'] ?? 'FUNCIONARIO'
    ];
}

/**
 * Destrói a sessão do usuário.
 */
function encerrar_sessao() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Middleware: Se NÃO estiver logado, chuta para o login.
 */
function proteger_pagina() {
    if (!esta_logado()) {
        encerrar_sessao(); 
        redirecionar('login.php'); // Agora vai funcionar sem erro fatal
    }
}

/**
 * Middleware: Se JÁ estiver logado, chuta para o painel.
 * (Usado na tela de login para não deixar logar duas vezes)
 */
function proteger_autenticacao() {
    if (esta_logado()) {
        redirecionar('painel.php'); // Agora vai funcionar sem erro fatal
    }
}

if (session_status() === PHP_SESSION_NONE) session_start();

// garante usuário logado
function verificarLogin(){
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /public/login.php');
        exit;
    }
}

// permite apenas DONO(1) ou GESTOR(2)
function verificarAdmin(){
    verificarLogin();
    $papel = $_SESSION['papel_id'] ?? 0;
    if ($papel != 1 && $papel != 2) {
        http_response_code(403);
        echo "Acesso negado.";
        exit;
    }
}

?>