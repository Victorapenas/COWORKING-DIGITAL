<?php
// /public/registrar.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/seguranca.php';

// Se o cadastro não for permitido, redireciona ou exibe erro
if (!PERMITIR_CADASTRO) {
    redirecionar('login.php');
}

proteger_autenticacao(); // Redireciona para painel.php se já estiver logado

// Reutilizando funções do login.php
require_once 'login.php'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Coworking Digital</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <div class="container">
        <div class="painel-auth">
            <div class="card-auth">
                <?php renderizar_logo(); ?>
                <h2>Criar nova conta</h2>
                <p class="descricao-pequena">Leva menos de um minuto</p>

                <div id="erroGeral" class="mensagem-erro" style="display:none;"></div>

                <form id="formRegistro" class="formulario">
                    <div class="campo-form">
                        <input type="text" id="nome" placeholder="Seu nome completo" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 12c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <div class="campo-form">
                        <input type="email" id="email" placeholder="Digite seu endereço de e-mail" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </div>

                    <div class="campo-form">
                        <input type="password" id="senha" placeholder="Crie uma senha segura" required minlength="8">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm-2-9h4V6c0-1.1-.9-2-2-2s-2 .9-2 2v2z"/></svg>
                    </div>

                    <button type="submit" id="btnCadastrar" class="botao-primario" style="margin-top: 15px;">Cadastrar</button>
                </form>

                <div class="separador"><span>ou faça login com</span></div>

                <div class="login-social">
                    <button class="login-social-btn google" aria-label="Login com Google">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22.54 10.36h-1.92v2.88h1.92v2.88h-1.92v2.88H20.62V13.24h-1.92v-2.88h1.92V7.54h1.92v2.82zm-8.88 0h-1.92v2.88h1.92v2.88h-1.92v2.88h1.92V13.24h-1.92v-2.88h1.92V7.54h1.92v2.82zm-8.88 0h-1.92v2.88h1.92v2.88H6.78v2.88H4.86V13.24H2.94v-2.88H4.86V7.54H6.78v2.82z"/></svg>
                    </button>
                    <button class="login-social-btn linkedin" aria-label="Login com LinkedIn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20.447 20.453h-3.64v-5.96h3.64v5.96zM15.111 20.453h-3.64v-10.92h3.64v10.92zM9.775 20.453h-3.64V7.533h3.64v12.92zM4.441 20.453H.8V7.533h3.64v12.92zM22.222 2H1.778C.795 2 0 2.795 0 3.778v16.444C0 21.205.795 22 1.778 22h20.444C23.205 22 24 21.205 24 20.222V3.778C24 2.795 23.205 2 22.222 2z"/></svg>
                    </button>
                </div>
                
                <p class="link-rodape">Tem uma conta? <a href="login.php" class="link-auxiliar">Entrar</a></p>
            </div>
        </div>
        <?php renderizar_painel_info(); ?>
    </div>
    <script src="../js/recuperar.js"></script>
 </body>
</html>