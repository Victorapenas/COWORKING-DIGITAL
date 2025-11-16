<?php
// /public/verificar_codigo.php

require_once __DIR__ . '/../includes/seguranca.php';
proteger_autenticacao();

// Reutilizando funções do login.php
require_once 'login.php'; 

// Para fins acadêmicos, exibe o código de debug (opcional)
$codigo_debug = $_GET['code'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação - Coworking Digital</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <div class="container">
        <div class="painel-auth">
            <div class="card-auth">
                <?php renderizar_logo(); ?>

                <?php if ($codigo_debug): ?>
                    <div style="background-color: #ffe0b2; padding: 10px; border-radius: 8px; text-align: center; font-weight: 600; color: #e65100;">
                        DEBUG: Código de 4 dígitos: <span><?= htmlspecialchars($codigo_debug) ?></span>
                    </div>
                <?php endif; ?>

                <h2>Verificação</h2>
                <p class="descricao-pequena">Por favor, digite o código de 4 dígitos que você recebeu</p>

                <div id="erroGeral" class="mensagem-erro" style="display:none;"></div>
                
                <div class="input-codigo-group" id="inputCodigoGroup">
                    <input type="number" class="input-codigo" maxlength="1" pattern="\d{1}" inputmode="numeric">
                    <input type="number" class="input-codigo" maxlength="1" pattern="\d{1}" inputmode="numeric">
                    <input type="number" class="input-codigo" maxlength="1" pattern="\d{1}" inputmode="numeric">
                    <input type="number" class="input-codigo" maxlength="1" pattern="\d{1}" inputmode="numeric">
                </div>

                <div class="status-codigo">
                    <span id="timer">00:30</span>
                    <a href="#" id="btnReenviar" class="link-reenvio disabled">Reenvie</a>
                </div>

                <button type="button" id="btnContinuar" class="botao-primario">Continuar</button>

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