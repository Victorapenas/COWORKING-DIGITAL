<?php
// /includes/funcoes.php
require_once __DIR__ . '/../config/conexao.php';

/**
 * Normaliza e valida um e-mail.
 * @param string $email
 * @return string|false E-mail normalizado ou false se inválido.
 */
function validar_email(string $email): string|false {
    $email = strtolower(trim($email));
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

/**
 * Valida o formato da senha (mínimo 8 caracteres).
 * @param string $senha
 * @return bool
 */
function validar_senha(string $senha): bool {
    // Pode adicionar regras mais complexas aqui (ex: maiúsculas, números, etc.)
    return strlen($senha) >= 8;
}

/**
 * Valida o formato do código de 4 dígitos.
 * @param string $codigo
 * @return bool
 */
function validar_codigo_recuperacao(string $codigo): bool {
    return is_numeric($codigo) && strlen($codigo) === 4;
}

/**
 * Simples rate limiting para API em caso de falha de login.
 */
function aplicar_rate_limit() {
    usleep(RATE_LIMIT_DELAY_MS * 1000); // Dorme por N milissegundos
}

/**
 * Retorna uma resposta JSON de erro.
 * @param string $mensagem
 * @param int $http_code
 */
function responder_erro(string $mensagem, int $http_code = 400) {
    http_response_code($http_code);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'erro' => $mensagem]);
    exit;
}

/**
 * Redireciona o usuário para uma URL.
 * @param string $url
 */
function redirecionar(string $url) {
    header('Location: ' . $url);
    exit;
}