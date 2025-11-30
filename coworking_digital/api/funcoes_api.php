<?php
// /api/funcoes_api.php

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/seguranca.php';

// Configura cabeçalhos CORS e JSON padrão
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Em produção, restrinja o *
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

/**
 * Lê e decodifica o body da requisição POST (JSON).
 * @return array|null Dados decodificados ou null se falhar.
 */
function get_json_input(): array|null {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responder_erro("Método não permitido.", 405);
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        responder_erro("JSON inválido no corpo da requisição.", 400);
    }
    return $data;
}