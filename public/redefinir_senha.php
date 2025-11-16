<?php
// /public/redefinir_senha.php

require_once __DIR__ . '/../includes/seguranca.php';
proteger_autenticacao();

// Reutilizando funções do login.php
require_once 'login.php'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Coworking Digital</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <div class="container">
        <div class="painel-auth">
            <div class="card-auth">
                <?php renderizar_logo(); ?>
                <h2>Redefinir sua senha</h2>
                <p class="descricao-pequena">Crie uma nova senha segura para sua conta.</p>

                <div id="erroGeral" class="mensagem-erro" style="display:none;"></div>

                <form id="formRedefinirSenha" class="formulario">
                    <div class="campo-nova-senha">
                        <div class="campo-form">
                            <input type="password" id="nova_senha" placeholder="Nova senha" required minlength="8">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                        </div>
                    </div>
                    
                    <div class="campo-nova-senha">
                        <div class="campo-form">
                            <input type="password" id="confirma_senha" placeholder="Confirmar nova senha" required minlength="8">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                        </div>
                    </div>

                    <button type="submit" id="btnRedefinir" class="botao-primario">Redefinir Senha</button>
                </form>

                <a href="login.php" class="link-voltar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
                    Voltar ao login
                </a>
            </div>
        </div>
        <?php renderizar_painel_info(); ?>
    </div>
    <script src="/js/recuperar.js"></script>
</body>
</html>