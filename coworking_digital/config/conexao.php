<?php
// /config/conexao.php
require_once __DIR__ . '/config.php';

function conectar_db() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
        return $pdo;
    } catch (PDOException $e) {
        // Em um ambiente de produção, este erro não deve ser exibido.
        die("Erro de Conexão com o Banco de Dados: " . $e->getMessage());
    }
}