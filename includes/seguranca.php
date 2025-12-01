<?php
// /includes/seguranca.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/funcoes.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function esta_logado(): bool {
    return isset($_SESSION[SESSAO_USUARIO_KEY]) && is_array($_SESSION[SESSAO_USUARIO_KEY]);
}

function iniciar_sessao(array $usuario) {
    $_SESSION[SESSAO_USUARIO_KEY] = [
        'id'    => $usuario['id'],
        'nome'  => $usuario['nome'],
        'email' => $usuario['email'],
        'papel' => $usuario['papel'] ?? 'FUNCIONARIO'
    ];
}

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

// Protege páginas internas
function proteger_pagina() {
    if (!esta_logado()) {
        encerrar_sessao(); 
        redirecionar('login.php');
    }
}

// Protege páginas de login (se já logado, manda para DENTRO)
function proteger_autenticacao() {
    if (esta_logado()) {
        // AQUI ESTAVA O ERRO: Mudamos de painel.php para equipes.php
        redirecionar('equipes.php'); 
    }
}
?>