<?php
//atualização
// /public/esqueci_senha.php

require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php'; // Importante: Só UI auxiliar
proteger_autenticacao();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci a Senha - Coworking Digital</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="container">
        <div class="painel-auth">
            <div class="card-auth">
                <?php renderizar_logo(); ?>
                <h2>Esqueceu sua senha</h2>
                <p class="descricao-pequena">Digite seu e-mail. Enviaremos um código de 4 dígitos.</p>

                <div id="erroGeral" class="mensagem-erro" style="display:none;"></div>

                <form id="formEsqueciSenha" class="formulario">
                    <div class="campo-form">
                        <input type="email" id="email" placeholder="Seu e-mail cadastrado" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </div>

                    <button type="submit" id="btnContinuar" class="botao-primario">Continuar</button>
                </form>

                <a href="login.php" class="link-voltar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
                    Voltar ao login
                </a>
            </div>
        </div>
        <?php renderizar_painel_info(); ?>
    </div>
    <script src="../js/recuperar.js"></script>
</body>
</html>