<?php
// ARQUIVO: public/verificar_codigo.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
proteger_autenticacao();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação - Coworking Digital</title>
    <link rel="stylesheet" href="../css/login.css">
    <style>
        .input-codigo { width: 50px; height: 60px; border-radius: 12px; border: 1px solid #ddd; font-size: 24px; text-align: center; font-weight: bold; }
        .status-reenvio { margin-top: 20px; font-size: 0.9rem; color: #666; }
        .link-reenvio { color: #6A66FF; font-weight: bold; text-decoration: none; cursor: pointer; }
        .link-reenvio.disabled { color: #ccc; cursor: not-allowed; pointer-events: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="painel-auth">
            <div class="card-auth">
                <?php renderizar_logo(); ?>

                <h2>Confirme seu acesso</h2>
                
                <p class="descricao-pequena">
                    Enviamos um código de segurança para<br>
                    <strong id="emailNoTexto" style="color:#333">...</strong>
                </p>

                <div id="erroGeral" class="mensagem-erro" style="display:none;"></div>

                <div class="input-codigo-group" style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                    <input type="text" class="input-codigo" maxlength="1" inputmode="numeric">
                    <input type="text" class="input-codigo" maxlength="1" inputmode="numeric">
                    <input type="text" class="input-codigo" maxlength="1" inputmode="numeric">
                    <input type="text" class="input-codigo" maxlength="1" inputmode="numeric">
                </div>

                <p class="status-reenvio">
                    Não recebeu o código? 
                    <a href="#" id="btnReenviar" class="link-reenvio disabled">Reenviar</a>
                    <span id="timer" style="font-weight:bold; margin-left:5px;">00:30</span>
                </p>

                <button type="button" id="btnContinuar" class="botao-primario">Validar Código</button>
                <a href="login.php" class="link-voltar">← Voltar ao login</a>
            </div>
        </div>
        
        <?php renderizar_painel_info(); ?>
    </div>
    
    <script src="../js/recuperar.js"></script>
    <script>
        // Exibe o e-mail de forma bonita
        document.addEventListener("DOMContentLoaded", () => {
            const email = sessionStorage.getItem('recuperacao_email');
            const spanEmail = document.getElementById('emailNoTexto');
            
            if(email) {
                spanEmail.textContent = email;
            } else {
                spanEmail.textContent = "seu e-mail";
            }
        });
    </script>
</body>
</html>