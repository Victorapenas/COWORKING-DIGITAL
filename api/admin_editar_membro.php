<?php
// ARQUIVO: api/admin_editar_membro.php

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

$resposta = [];

try {
    if (!esta_logado()) throw new Exception("Não autorizado.", 401);
    
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    // Apenas liderança pode editar usuários
    if (!in_array($sessao['papel'] ?? '', ['DONO', 'GESTOR', 'LIDER'])) {
        throw new Exception("Permissão negada.", 403);
    }

    $dados = get_json_input();
    $id_usuario = (int)($dados['id'] ?? 0);
    $nome = trim($dados['nome'] ?? '');
    $email = validar_email($dados['email'] ?? '');
    $funcao = trim($dados['funcao'] ?? '');
    $papel_input = strtoupper($dados['papel'] ?? 'FUNCIONARIO');

    if (!$id_usuario || empty($nome) || !$email) {
        throw new Exception("Dados inválidos.");
    }

    $pdo = conectar_db();

    // 1. Busca dados atuais do usuário ALVO para segurança
    $stmtAtual = $pdo->prepare("SELECT papel_id, empresa_id FROM usuario WHERE id = ?");
    $stmtAtual->execute([$id_usuario]);
    $usuarioAtual = $stmtAtual->fetch();

    if (!$usuarioAtual) throw new Exception("Usuário não encontrado.");

    // Verifica se pertence à mesma empresa
    $stmtEmp = $pdo->prepare("SELECT empresa_id FROM usuario WHERE id = ?");
    $stmtEmp->execute([$sessao['id']]);
    $empresaAdmin = $stmtEmp->fetchColumn();

    if ($usuarioAtual['empresa_id'] != $empresaAdmin) throw new Exception("Acesso negado a este usuário.");

    // 2. LÓGICA DE PROTEÇÃO DE PAPEL (CORREÇÃO AQUI)
    $novo_papel_id = 3; // Default Colaborador

    // Se o usuário que está sendo editado JÁ É DONO/LIDER (ID 1), ele PERMANECE DONO.
    // Ninguém (nem ele mesmo) pode rebaixar um Dono por esta tela de edição simples.
    if ($usuarioAtual['papel_id'] == 1) {
        $novo_papel_id = 1; 
    } else {
        // Se não for dono, aplica a lógica normal de escolha (Gestor ou Funcionario)
        $novo_papel_id = ($papel_input === 'GESTOR') ? 2 : 3;
    }

    // 3. Verifica email duplicado (excluindo o próprio usuário)
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id_usuario]);
    if ($stmt->fetch()) throw new Exception("Este e-mail já pertence a outro usuário.");

    // 4. Atualiza
    $stmt = $pdo->prepare("UPDATE usuario SET nome = ?, email = ?, cargo_detalhe = ?, papel_id = ? WHERE id = ?");
    $stmt->execute([$nome, $email, $funcao, $novo_papel_id, $id_usuario]);

    $resposta = ['ok' => true, 'mensagem' => 'Dados atualizados com sucesso!'];

} catch (Exception $e) {
    http_response_code(400);
    $resposta = ['ok' => false, 'erro' => $e->getMessage()];
}

ob_end_clean();
echo json_encode($resposta);
exit;
?>