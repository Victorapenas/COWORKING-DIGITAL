<?php
// ARQUIVO: api/admin_adicionar_membro.php

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

$resposta = [];

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.", 401);

    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    $papelLogado = $sessao['papel'] ?? '';

    // Permissão básica: Apenas Liderança e Gestores acessam
    if (!in_array($papelLogado, ['DONO', 'GESTOR', 'LIDER'])) {
        throw new Exception("Permissão negada.", 403);
    }

    $dados = get_json_input();
    $nome = trim($dados['nome'] ?? '');
    $email = validar_email($dados['email'] ?? '');
    $papel_escolhido = strtoupper($dados['papel'] ?? 'FUNCIONARIO'); 
    $funcao = trim($dados['funcao'] ?? '');
    $equipe_id = isset($dados['equipe_id']) && $dados['equipe_id'] != "" ? (int)$dados['equipe_id'] : null;

    if (empty($nome) || !$email) throw new Exception("Nome e E-mail são obrigatórios.");

    // --- LÓGICA DE HIERARQUIA ---
    // Definição dos IDs (Baseado no seu banco: 1=DONO, 2=GESTOR, 3=FUNCIONARIO)
    $papel_id = 3; // Padrão Funcionário

    if ($papel_escolhido === 'DONO' || $papel_escolhido === 'LIDER') {
        // SEGURANÇA: Apenas um DONO pode criar outro DONO
        if ($papelLogado !== 'DONO' && $papelLogado !== 'LIDER') {
            throw new Exception("Apenas Sócios/Donos podem adicionar novos Sócios.");
        }
        $papel_id = 1;
        // Sócios geralmente não ficam presos a uma equipe específica (opcional)
        // Mas se quiser vincular, mantenha o $equipe_id.
    } 
    elseif ($papel_escolhido === 'GESTOR') {
        $papel_id = 2;
    } 
    else {
        $papel_id = 3; // Colaborador
    }

    $pdo = conectar_db();

    // Busca dados da empresa
    $stmt = $pdo->prepare("SELECT u.empresa_id, e.nome as nome_empresa FROM usuario u JOIN empresa e ON u.empresa_id = e.id WHERE u.id = ?");
    $stmt->execute([$sessao['id']]);
    $info = $stmt->fetch();

    if (!$info) throw new Exception("Empresa não encontrada.");

    // Verifica e-mail
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) throw new Exception("E-mail já cadastrado.");

    // Senha padrão
    $nome_empresa_limpo = str_replace(' ', '', ucwords(strtolower($info['nome_empresa'])));
    $senha_padrao = "@" . $nome_empresa_limpo . "123";
    $senha_hash = password_hash($senha_padrao, PASSWORD_DEFAULT);

    // Insere
    $sql = "INSERT INTO usuario (empresa_id, papel_id, equipe_id, nome, email, cargo_detalhe, senha_hash, ativo, precisa_redefinir_senha) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$info['empresa_id'], $papel_id, $equipe_id, $nome, $email, $funcao, $senha_hash]);

    $resposta = [
        'ok' => true,
        'mensagem' => 'Membro adicionado com sucesso!',
        'senha_gerada' => $senha_padrao
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