<?php
// /public/setup_admin.php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

$mensagem = '';
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_empresa = trim($_POST['nome_empresa'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $email = validar_email($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($nome_empresa) || empty($nome) || !$email || !validar_senha($senha)) {
        $mensagem = "Erro: Preencha todos os campos. Senha deve ter no mínimo 8 caracteres.";
    } else {
        try {
            $pdo = conectar_db();
            $pdo->beginTransaction();

            // 1. Limpeza para testes (REMOVE usuario antigo se existir)
            $pdo->exec("DELETE FROM usuario WHERE email = '$email'");

            // 2. Garante papéis
            $pdo->exec("INSERT IGNORE INTO papel (id, nome) VALUES (1, 'DONO'), (2, 'GESTOR'), (3, 'FUNCIONARIO')");

            // 3. Cria Empresa
            $stmt = $pdo->prepare("INSERT INTO empresa (nome) VALUES (?)");
            $stmt->execute([$nome_empresa]);
            $empresa_id = $pdo->lastInsertId();

            // 4. Cria Dono (Ativo = 1, Precisa Redefinir = 0)
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuario (empresa_id, papel_id, nome, email, senha_hash, ativo, precisa_redefinir_senha) VALUES (?, 1, ?, ?, ?, 1, 0)");
            $stmt->execute([$empresa_id, $nome, $email, $senha_hash]);

            $pdo->commit();
            $sucesso = true;
            $mensagem = "<div class='status-sucesso' style='padding:15px; border-radius:8px; text-align:left;'>
                <h3>✅ Instalação Concluída!</h3>
                <p>Empresa: <strong>$nome_empresa</strong></p>
                <p>Login: <strong>$email</strong></p>
                <hr>
                <a href='login.php' class='botao-primario' style='display:block; text-align:center; margin-top:10px; text-decoration:none;'>Ir para Login</a>
            </div>";

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $mensagem = "<div class='mensagem-erro'>Erro: " . $e->getMessage() . "</div>";
        }
    }
}

function renderizar_logo_setup() {
    ?>
    <div class="logo">
        <div class="logo-icon">
            <img src = "../css/coworking_digital.svg"/>
        </div>
        <h1>Instalação Coworking</h1>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Setup - Coworking Digital</title>
    <link rel="stylesheet" href="../css/login.css">
    <style>body { justify-content: center; padding: 40px; }</style>
</head>
<body>
    <div class="painel-auth" style="max-width:500px;">
        <div class="card-auth">
            <?php renderizar_logo_setup(); ?>
            
            <?php if (!$sucesso): ?>
                <h2>Configuração Inicial</h2>
                <p class="descricao-pequena">Cadastre sua empresa e o Dono.</p>
                <?= $mensagem ?>

                <form method="POST" class="formulario">
                    <div class="campo-form">
                        <input type="text" name="nome_empresa" placeholder="Nome da Empresa" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                    </div>
                    <div class="campo-form">
                        <input type="text" name="nome" placeholder="Seu Nome (Dono)" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <div class="campo-form">
                        <input type="email" name="email" placeholder="Seu E-mail" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </div>
                    <div class="campo-form">
                        <input type="password" name="senha" placeholder="Sua Senha" required minlength="8">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm-2-9h4V6c0-1.1-.9-2-2-2s-2 .9-2 2v2z"/></svg>
                    </div>
                    <button type="submit" class="botao-primario">Criar Empresa</button>
                </form>
            <?php else: ?>
                <?= $mensagem ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>