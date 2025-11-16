<?php
// /public/senha_redefinida.php

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
    <title>Sucesso! - Coworking Digital</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <div class="container">
        <div class="painel-auth">
            <div class="card-auth">
                <?php renderizar_logo(); ?>

                <div class="status-icone status-sucesso">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9 16.17l-3.59-3.59L4 14l5 5L20 9l-1.41-1.41z"/></svg>
                </div>
                
                <p class="status-mensagem">Sua senha foi redefinida com sucesso</p>

                <a href="login.php" class="botao-primario">Continuar</a>

                <a href="login.php" class="link-voltar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
                    Voltar ao login
                </a>
            </div>
        </div>
        <?php renderizar_painel_info(); ?>
    </div>
</body>
</html>