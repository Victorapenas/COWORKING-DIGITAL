<?php
//atualização
// /api/me.php - Retorna os dados da sessão do usuário logado

require_once 'funcoes_api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder_erro("Método não permitido.", 405);
}

if (esta_logado()) {
    http_response_code(200);
    echo json_encode([
        'ok' => true, 
        'usuario' => $_SESSION[SESSAO_USUARIO_KEY]
    ]);
} else {
    responder_erro("Nenhuma sessão ativa.", 401);
}