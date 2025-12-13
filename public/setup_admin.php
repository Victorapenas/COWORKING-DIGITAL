<?php
//atualização
// /public/setup_admin.php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

$mensagem = '';
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitização e Coleta
    $nome_empresa = trim($_POST['nome_empresa'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $cargo = trim($_POST['cargo'] ?? 'Administrador'); // Novo campo
    $email = validar_email($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';

    // Validações
    if (empty($nome_empresa) || empty($nome) || empty($cargo) || !$email) {
        $mensagem = "<div class='mensagem-erro'>Por favor, preencha todos os campos obrigatórios.</div>";
    } elseif (strlen($senha) < 8) {
        $mensagem = "<div class='mensagem-erro'>A senha deve ter no mínimo 8 caracteres.</div>";
    } elseif ($senha !== $confirma_senha) {
        $mensagem = "<div class='mensagem-erro'>As senhas não coincidem.</div>";
    } else {
        try {
            $pdo = conectar_db();
            $pdo->beginTransaction();

            // 1. Limpeza para testes (REMOVE usuario antigo com este email para evitar duplicidade no setup)
            $pdo->exec("DELETE FROM usuario WHERE email = '$email'");

            // 2. Garante que os papéis existam no banco
            $pdo->exec("INSERT IGNORE INTO papel (id, nome, nivel_hierarquia) VALUES (1, 'LIDER', 100), (2, 'GESTOR', 50), (3, 'COLABORADOR', 10)");

            // 3. Cria a Empresa (Espaço de Trabalho)
            $stmt = $pdo->prepare("INSERT INTO empresa (nome, criado_em) VALUES (?, NOW())");
            $stmt->execute([$nome_empresa]);
            $empresa_id = $pdo->lastInsertId();

            // 4. Cria equipe 'Geral' padrão para não ficar vazio
            $stmtEq = $pdo->prepare("INSERT INTO equipe (empresa_id, nome, criado_em) VALUES (?, 'Geral', NOW())");
            $stmtEq->execute([$empresa_id]);
            $equipe_geral_id = $pdo->lastInsertId();

            // 5. Cria o Usuário LÍDER (Administrador) com o Cargo definido
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Note que incluímos cargo_detalhe e vinculamos à equipe Geral automaticamente
            $sqlUsuario = "INSERT INTO usuario (empresa_id, equipe_id, papel_id, nome, email, cargo_detalhe, senha_hash, ativo, precisa_redefinir_senha, criado_em, atualizado_em) 
                           VALUES (?, ?, 1, ?, ?, ?, ?, 1, 0, NOW(), NOW())";
            
            $stmt = $pdo->prepare($sqlUsuario);
            $stmt->execute([$empresa_id, $equipe_geral_id, $nome, $email, $cargo, $senha_hash]);

            $pdo->commit();
            $sucesso = true;
            
            // Mensagem de Sucesso Visual
            $mensagem = "
            <div class='status-icone status-sucesso' style='margin: 20px auto; width: 60px; height: 60px;'>
                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='#2ecc71'><path d='M9 16.17l-3.59-3.59L4 14l5 5L20 9l-1.41-1.41z'/></svg>
            </div>
            <div style='text-align:center;'>
                <h3 style='color:#2ecc71;'>Ambiente Configurado!</h3>
                <p style='color:#666; margin-bottom:20px;'>A empresa <strong>$nome_empresa</strong> foi criada com sucesso.</p>
                <a href='login.php' class='botao-primario' style='display:inline-block; text-decoration:none; max-width:200px;'>Acessar Painel</a>
            </div>";

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $mensagem = "<div class='mensagem-erro'>Erro ao configurar: " . $e->getMessage() . "</div>";
        }
    }
}

function renderizar_logo_setup() {
    ?>
    <div class="logo">
        <div class="logo-icon">
            <img src="../css/coworking_digital.svg" alt="Logo"/>
        </div>
        <h1 style="font-size:1.2rem; margin-top:10px;">Coworking Digital</h1>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configuração - Coworking Digital</title>
    <link rel="stylesheet" href="../css/login.css">
    <style>
        body { justify-content: center; padding: 40px; background-color: #f8f9fa; }
        .card-auth { max-width: 550px; }
        .grid-duplo { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>
    <div class="painel-auth" style="width: 100%; max-width:600px; flex:none;">
        <div class="card-auth">
            <?php if (!$sucesso): ?>
                <?php renderizar_logo_setup(); ?>
                
                <div style="text-align:center; margin-bottom:20px;">
                    <h2 style="font-size:1.5rem;">Configuração Inicial</h2>
                    <p class="descricao-pequena">Defina os dados da sua organização e do administrador.</p>
                </div>

                <?= $mensagem ?>

                <form method="POST" class="formulario">
                    
                    <label style="font-size:0.85rem; font-weight:bold; color:#555; margin-bottom:-10px;">Dados da Organização</label>
                    <div class="campo-form">
                        <input type="text" name="nome_empresa" placeholder="Nome da Empresa / Espaço" value="<?= htmlspecialchars($_POST['nome_empresa'] ?? '') ?>" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                    </div>

                    <label style="font-size:0.85rem; font-weight:bold; color:#555; margin-top:10px; margin-bottom:-10px;">Perfil do Administrador</label>
                    
                    <div class="campo-form">
                        <input type="text" name="nome" placeholder="Nome Completo" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>

                    <div class="campo-form">
                        <input type="text" name="cargo" placeholder="Cargo (Ex: CEO, Fundador, Diretor)" value="<?= htmlspecialchars($_POST['cargo'] ?? '') ?>" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>
                    </div>

                    <div class="campo-form">
                        <input type="email" name="email" placeholder="E-mail de Acesso" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </div>

                    <div class="grid-duplo">
                        <div class="campo-form">
                            <input type="password" name="senha" placeholder="Senha" required minlength="8">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm-2-9h4V6c0-1.1-.9-2-2-2s-2 .9-2 2v2z"/></svg>
                        </div>
                        <div class="campo-form">
                            <input type="password" name="confirma_senha" placeholder="Confirmar Senha" required minlength="8">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                        </div>
                    </div>

                    <button type="submit" class="botao-primario" style="margin-top:10px;">Finalizar Configuração</button>
                </form>
            <?php else: ?>
                <?= $mensagem ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>