<?php
// /public/redefinir_senha.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
proteger_autenticacao();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Senha</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="container">
        <div class="painel-auth">
            <div class="card-auth">
                <?php renderizar_logo(); ?>
                <h2>Criar nova senha</h2>
                <p class="descricao-pequena">Sua nova senha deve ser diferente da anterior.</p>

                <div id="erroGeral" class="mensagem-erro" style="display:none;"></div>

                <form id="formRedefinirSenha" class="formulario">
                    <div class="campo-form">
                        <input type="password" id="nova_senha" placeholder="Nova senha" required>
                        <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2z"/></svg>
                    </div>
                    
                    <div class="campo-form">
                        <input type="password" id="confirma_senha" placeholder="Confirme a senha" required>
                        <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2z"/></svg>
                    </div>
                    
                    <p style="font-size: 0.8rem; color: #888; margin-top:-10px;">Mínimo de 8 caracteres.</p>

                    <button type="submit" id="btnRedefinir" class="botao-primario" style="margin-top:10px;">Redefinir Senha</button>
                </form>

                <a href="login.php" class="link-voltar">← Voltar ao login</a>
            </div>
        </div>
        <?php renderizar_painel_info(); ?>
    </div>
    <script src="../js/recuperar.js"></script>
</body>
</html>