<?php
// ARQUIVO: api/admin_criar_equipe.php

// 1. Blindagem (Output Buffering)
ob_start();

// Configurações de erro (Log only)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

$resposta = [];

try {
    if (!esta_logado()) throw new Exception("Sessão expirada. Faça login novamente.", 401);

    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    
    // Permissão: DONO, GESTOR, LIDER
    if (!in_array($sessao['papel'] ?? '', ['DONO', 'GESTOR', 'LIDER'])) {
        throw new Exception("Permissão negada.", 403);
    }

    $dados = get_json_input();
    $nomeEquipe = trim($dados['nome_equipe'] ?? '');

    if (empty($nomeEquipe)) throw new Exception("O nome da equipe é obrigatório.");

    $pdo = conectar_db();
    
    // Busca empresa
    $stmt = $pdo->prepare("SELECT empresa_id FROM usuario WHERE id = ?");
    $stmt->execute([$sessao['id']]);
    $empresaId = $stmt->fetchColumn();

    if (!$empresaId) throw new Exception("Usuário não vinculado a uma empresa.");

    // Verifica duplicidade
    $stmt = $pdo->prepare("SELECT id FROM equipe WHERE nome = ? AND empresa_id = ?");
    $stmt->execute([$nomeEquipe, $empresaId]);
    if ($stmt->fetch()) throw new Exception("Já existe uma equipe com este nome.");

    // Cria
    $stmt = $pdo->prepare("INSERT INTO equipe (empresa_id, nome) VALUES (?, ?)");
    $stmt->execute([$empresaId, $nomeEquipe]);

    $resposta = [
        'ok' => true,
        'mensagem' => 'Equipe criada com sucesso!',
        'id_criado' => $pdo->lastInsertId()
    ];

} catch (Exception $e) {
    $code = $e->getCode();
    if ($code < 100 || $code > 599) $code = 400; 
    http_response_code($code);
    $resposta = ['ok' => false, 'erro' => $e->getMessage()];
}

ob_end_clean();
echo json_encode($resposta);
exit;
?>