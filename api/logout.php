<?php
//atualização
// /api/logout.php

// Carrega o segurança para ter acesso à função encerrar_sessao()
require_once __DIR__ . '/../includes/seguranca.php';

// Destrói tudo
encerrar_sessao();

// Retorna JSON para o JavaScript saber que deu certo e redirecionar
header('Content-Type: application/json');
echo json_encode(['ok' => true]);
?>