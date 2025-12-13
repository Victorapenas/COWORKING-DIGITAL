<?php
//atualização
// ARQUIVO: public/login.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
proteger_autenticacao();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Coworking Digital</title>
    
    <link rel="stylesheet" href="../css/login.css?v=<?php echo time(); ?>">
    
    <style>
        .logo-box {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 25px;
            width: 100%;
        }
        .logo-box img, 
        .logo-sistema-img {
            max-width: 180px !important; /* !important atropela qualquer regra antiga */
            max-height: 80px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="painel-auth">
            <div class="card-auth">
                <?php renderizar_logo(); ?>
                
                <h2>Acesso Corporativo</h2>
                <p class="descricao-pequena">Entre com sua conta empresarial</p>

                <div id="erroGeral" class="mensagem-erro" style="display:none;"></div>

                <form id="formLogin" class="formulario">
                    <div class="campo-form">
                        <input type="email" id="email" placeholder="E-mail corporativo" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </div>

                    <div class="campo-form">
                        <input type="password" id="senha" placeholder="Senha" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm-2-9h4V6c0-1.1-.9-2-2-2s-2 .9-2 2v2z"/></svg>
                    </div>

                    <div class="opcoes-login">
                        <label>
                            <input type="checkbox" id="lembrar">
                            Lembre de mim
                        </label>
                        <a href="esqueci_senha.php" class="link-auxiliar">Esqueceu sua senha?</a>
                    </div>

                    <button type="submit" id="btnEntrar" class="botao-primario">Entrar</button>
                </form>

                <p class="link-rodape" style="margin-top: 30px; font-size: 0.8rem; color: #aaa;">
                    Sistema de uso exclusivo para colaboradores.
                </p>
            </div>
        </div>
        <?php renderizar_painel_info(); ?>
    </div>
    <script src="../js/login.js?v=<?php echo time(); ?>"></script>
</body>
</html>