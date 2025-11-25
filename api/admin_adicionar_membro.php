<?php
// /api/admin_adicionar_membro.php
require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

// 1. Proteção: Só quem tá logado acessa
if (!esta_logado()) {
    responder_erro("Não autorizado.", 401);
}

$sessao = $_SESSION[SESSAO_USUARIO_KEY];
// 2. Proteção: Só DONO ou GESTOR podem cadastrar
if ($sessao['papel'] !== 'DONO' && $sessao['papel'] !== 'GESTOR') {
    responder_erro("Apenas Donos e Gestores podem cadastrar membros.", 403);
}

$dados = get_json_input();
$nome = trim($dados['nome'] ?? '');
$email = validar_email($dados['email'] ?? '');
$papel_escolhido = strtoupper($dados['papel'] ?? 'FUNCIONARIO'); // FUNCIONARIO ou GESTOR
$funcao = trim($dados['funcao'] ?? '');

if (empty($nome) || !$email) {
    responder_erro("Nome e E-mail são obrigatórios.");
}

try {
    $pdo = conectar_db();

    // 3. Pega dados da empresa do usuário logado
    $stmt = $pdo->prepare("SELECT u.empresa_id, e.nome as nome_empresa FROM usuario u JOIN empresa e ON u.empresa_id = e.id WHERE u.id = ?");
    $stmt->execute([$sessao['id']]);
    $info = $stmt->fetch();

    if (!$info) responder_erro("Empresa não encontrada.");

    // 4. GERA SENHA PADRÃO: @NomeEmpresa123
    // Remove espaços e capitaliza: "Minha Empresa" -> "@MinhaEmpresa123"
    $nome_empresa_limpo = str_replace(' ', '', ucwords(strtolower($info['nome_empresa'])));
    $senha_padrao = "@" . $nome_empresa_limpo . "123";
    $senha_hash = password_hash($senha_padrao, PASSWORD_DEFAULT);

    // 5. Define ID do papel
    $papel_id = ($papel_escolhido === 'GESTOR') ? 2 : 3;

    // 6. Insere usuário com FLAG de PRIMEIRO ACESSO (precisa_redefinir_senha = 1)
    $stmt = $pdo->prepare("INSERT INTO usuario (empresa_id, papel_id, nome, email, cargo_detalhe, senha_hash, ativo, precisa_redefinir_senha) VALUES (?, ?, ?, ?, ?, ?, 1, 1)");
    $stmt->execute([$info['empresa_id'], $papel_id, $nome, $email, $funcao, $senha_hash]);

    echo json_encode([
        'ok' => true,
        'mensagem' => 'Cadastrado com sucesso!',
        'senha_gerada' => $senha_padrao // Retorna pro JS mostrar na tela
    ]);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) responder_erro("E-mail já cadastrado.", 409);
    responder_erro("Erro: " . $e->getMessage(), 500);
}
?>