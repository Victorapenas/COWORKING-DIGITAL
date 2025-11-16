<?php
// /includes/seguranca.php

require_once __DIR__ . '/../config/config.php';

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
 * @param array $usuario Dados do usuário (id, nome, email, papel, etc.)
 */
function iniciar_sessao(array $usuario) {
    // Armazena apenas dados essenciais e não sensíveis
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
 * Middleware para proteger páginas que exigem login.
 * Redireciona para o login se não estiver logado.
 */
function proteger_pagina() {
    if (!esta_logado()) {
        encerrar_sessao(); // Garante limpeza
        redirecionar('login.php');
    }
}

/**
 * Middleware para páginas de autenticação.
 * Redireciona para o painel se JÁ estiver logado.
 */
function proteger_autenticacao() {
    if (esta_logado()) {
        redirecionar('painel.php');
    }
}