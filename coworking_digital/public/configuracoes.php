<?php
// /public/configuracoes.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';

proteger_pagina();

// Usuário logado (da sessão)
$usuarioSessao = $_SESSION[SESSAO_USUARIO_KEY];
$usuarioId     = (int)$usuarioSessao['id'];
$papel         = $usuarioSessao['papel'] ?? '';

// Busca usuário completo no banco para garantir empresa_id
$usuarioBanco = buscarUsuarioPorId($usuarioId);
if (!$usuarioBanco) {
    // Se por algum motivo não encontrar, para tudo
    die('Usuário não encontrado no banco de dados.');
}

$empresaId = $usuarioBanco['empresa_id'] ?? null;

$mensagemUsuario = '';
$mensagemEmpresa = '';

/**
 * PROCESSA FORMULÁRIOS (POST)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Atualizar dados do usuário
    if (isset($_POST['atualizar_usuario'])) {
        $novoNome  = trim($_POST['nome']  ?? '');
        $novoCargo = trim($_POST['cargo'] ?? '');

        if ($novoNome !== '') {
            $ok = atualizarUsuarioBasico($usuarioId, $novoNome, $novoCargo !== '' ? $novoCargo : null);

            if ($ok) {
                $mensagemUsuario = 'Dados do usuário atualizados com sucesso!';
                // Atualiza sessão para refletir o novo nome no topo/painel
                $_SESSION[SESSAO_USUARIO_KEY]['nome'] = $novoNome;
            } else {
                $mensagemUsuario = 'Erro ao atualizar dados do usuário.';
            }
        } else {
            $mensagemUsuario = 'O nome não pode ficar em branco.';
        }
    }

    // Atualizar dados da empresa (se houver empresa ligada)
    if (isset($_POST['atualizar_empresa']) && $empresaId) {
        $novoNomeEmpresa = trim($_POST['empresa_nome'] ?? '');

        if ($novoNomeEmpresa !== '') {
            $ok = atualizarNomeEmpresa($empresaId, $novoNomeEmpresa);

            if ($ok) {
                $mensagemEmpresa = 'Nome da empresa atualizado com sucesso!';
            } else {
                $mensagemEmpresa = 'Erro ao atualizar o nome da empresa.';
            }
        } else {
            $mensagemEmpresa = 'O nome da empresa não pode ficar em branco.';
        }
    }
}

/**
 * BUSCA DADOS ATUAIS (APÓS POSSÍVEL UPDATE)
 */
$dadosUsuario = buscarUsuarioPorId($usuarioId);               // nome, cargo_detalhe, empresa_id...
$dadosEmpresa = $empresaId ? buscarEmpresaPorId($empresaId) : null;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configurações</title>
    <link rel="stylesheet" href="../css/painel.css">
</head>
<body>

    <div class="sidebar">
        <div class="logo-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="#0d6efd"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm-1 15h2v-6h-2v6zm0-8h2V7h-2v2z"/></svg>
            <h3 style="color: #0d6efd; margin: 0;">Coworking</h3>
        </div>

        <div class="profile-box">
            <div class="avatar"><?= strtoupper(substr($usuarioSessao['nome'], 0, 2)) ?></div>
            <div>
                <strong style="display: block;"><?= htmlspecialchars($usuarioSessao['nome']) ?></strong>
                <small><?= htmlspecialchars($papel) ?></small>
            </div>
        </div>

        <?php renderizar_sidebar(); ?>
    </div>

    <div class="main-content">

        <h2>Configurações</h2>
        <p>Gerencie seus dados e da empresa.</p>

        <!-- MENSAGEM USUÁRIO -->
        <?php if (!empty($mensagemUsuario)): ?>
            <div class="alert success"><?= htmlspecialchars($mensagemUsuario) ?></div>
        <?php endif; ?>

        <!-- FORMULÁRIO DO USUÁRIO -->
        <div class="card">
            <h3>Meus Dados</h3>
            <form method="POST">
                <input type="hidden" name="atualizar_usuario" value="1">

                <label>Nome</label>
                <input
                    type="text"
                    name="nome"
                    value="<?= htmlspecialchars($dadosUsuario['nome'] ?? '') ?>"
                    required
                >

                <label>Cargo / Função</label>
                <input
                    type="text"
                    name="cargo"
                    value="<?= htmlspecialchars($dadosUsuario['cargo_detalhe'] ?? '') ?>"
                >

                <button class="botao-primario" type="submit">Salvar Alterações</button>
            </form>
        </div>

        <!-- MENSAGEM EMPRESA -->
        <?php if (!empty($mensagemEmpresa)): ?>
            <div class="alert success"><?= htmlspecialchars($mensagemEmpresa) ?></div>
        <?php endif; ?>

        <!-- FORMULÁRIO DA EMPRESA -->
        <div class="card">
            <h3>Empresa</h3>

            <?php if ($empresaId && $dadosEmpresa): ?>
                <form method="POST">
                    <input type="hidden" name="atualizar_empresa" value="1">

                    <label>Nome da Empresa</label>
                    <input
                        type="text"
                        name="empresa_nome"
                        value="<?= htmlspecialchars($dadosEmpresa['nome'] ?? '') ?>"
                        required
                    >

                    <button class="botao-primario" type="submit">Salvar Empresa</button>
                </form>
            <?php else: ?>
                <p style="color:#777;">
                    Nenhuma empresa vinculada a este usuário ainda.
                </p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
