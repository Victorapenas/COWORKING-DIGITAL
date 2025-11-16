<?php
// /public/login.php

require_once __DIR__ . '/../includes/seguranca.php';
proteger_autenticacao(); // Redireciona para painel.php se já estiver logado

function renderizar_painel_info() {
    // Ícones retirados das imagens fornecidas, simulados por SVGs
    $icones = [
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 12c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>', // Grupo Pessoas
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V8h12v3z"/></svg>', // Chat
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>', // Alvo/Objetivo
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11 2v4H7c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-4V2h-2zm-1 6h4v4h-4zM6 22H4V4h2v18z"/></svg>' // Raio/Velocidade/Energia
    ];
    ?>
    <div class="painel-info">
        <div class="info-icones">
            <?php foreach ($icones as $icone): ?>
                <div class="info-icone-wrap"><?= $icone ?></div>
            <?php endforeach; ?>
        </div>
        <h2 class="info-titulo">Gerencie seu coworking com muito mais inteligência</h2>
        <p class="info-subtitulo">Conecte sua equipe, organize tarefas e comunique-se melhor</p>
    </div>
    <?php
}

function renderizar_logo() {
    // Logo simulada com SVG para fidelidade visual do ícone
    ?>
    <div class="logo">
        <div class="logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1.8 14.8c-.89-.48-1.57-1.16-2.05-2.05-.7-.7-.78-1.74-.21-2.48l1.63-2.12c.16-.2.4-.33.66-.33h2.64c.26 0 .5.13.66.33l1.63 2.12c.57.74.49 1.78-.21 2.48-.48.89-1.16 1.57-2.05 2.05-.5.28-1.07.42-1.65.42s-1.15-.14-1.65-.42zM12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm.8 4h-1.6c-.22 0-.4.18-.4.4v1.2c0 .22.18.4.4.4h1.6c.22 0 .4-.18.4-.4V8.4c0-.22-.18-.4-.4-.4zM12 18c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/>
                </svg>
        </div>
        <h1>COWORKING</h1>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Coworking Digital</title>
    <link rel="stylesheet" href="../css/login.css">
<script src="../js/login.js"></script>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='%236A66FF' d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z'/></svg>">
</head>
<body>
    <div class="container">
        <div class="painel-auth">
            <div class="card-auth">
                <?php renderizar_logo(); ?>
                <h2>Bem-vindo ao Coworking Digital</h2>
                <p class="descricao-pequena">Acesse sua conta para gerenciar seu coworking</p>

                <div id="erroGeral" class="mensagem-erro" style="display:none;"></div>

                <form id="formLogin" class="formulario">
                    <div class="campo-form">
                        <input type="email" id="email" placeholder="Digite seu endereço de e-mail" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>

                    <div class="campo-form">
                        <input type="password" id="senha" placeholder="Digite sua senha" required minlength="8">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm-2-9h4V6c0-1.1-.9-2-2-2s-2 .9-2 2v2z"/></svg>
                    </div>

                    <div class="opcoes-login">
                        <label>
                            <input type="checkbox" name="lembrar" id="lembrar">
                            Lembre de mim
                        </label>
                        <a href="esqueci_senha.php" class="link-auxiliar">Esqueceu sua senha?</a>
                    </div>

                    <button type="submit" id="btnEntrar" class="botao-primario">Entrar</button>
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
                
                <?php if (PERMITIR_CADASTRO): ?>
                <p class="link-rodape">Não tem uma conta? <a href="registrar.php" class="link-auxiliar">Cadastre-se</a></p>
                <?php endif; ?>
            </div>
        </div>
        <?php renderizar_painel_info(); ?>
    </div>
    <script src="/js/login.js"></script>
</body>
</html>