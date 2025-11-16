<?php
// /public/painel.php

require_once __DIR__ . '/../includes/seguranca.php';
proteger_pagina(); // Protege: exige que o usuário esteja logado

$usuario = $_SESSION[SESSAO_USUARIO_KEY];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Coworking Digital</title>
    <link rel="stylesheet" href="/css/login.css">
    <style>
        body { background-color: #f0f2f5; display: block; padding: 40px; }
        .painel-content { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .painel-content h1 { color: #1e3c72; margin-bottom: 20px; }
        .painel-content p { margin-bottom: 10px; }
        .painel-content strong { font-weight: 700; }
        .logout-btn { display: block; margin-top: 30px; padding: 10px 20px; background-color: #e74c3c; color: white; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.2s; text-align: center; text-decoration: none; max-width: 150px; }
        .logout-btn:hover { background-color: #c0392b; }
    </style>
</head>
<body>
    <div class="painel-content">
        <h1>🚀 Bem-vindo(a), <?= htmlspecialchars($usuario['nome']) ?>!</h1>
        <p>Você acessou o **Painel do Coworking Digital** (Placeholder).</p>
        <p>Este é o ponto de entrada após a autenticação bem-sucedida.</p>
        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
        
        <h2>Detalhes da Sessão:</h2>
        <p><strong>ID:</strong> <?= htmlspecialchars($usuario['id']) ?></p>
        <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
        <p><strong>Papel:</strong> <span style="color: #6A66FF;"><?= htmlspecialchars($usuario['papel']) ?></span></p>

        <form id="formLogout">
            <button type="submit" class="logout-btn">Sair (Logout)</button>
        </form>
    </div>

    <script>
        document.getElementById('formLogout').addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const resposta = await fetch('/api/logout.php', { method: 'POST' });
                const resultado = await resposta.json();
                if (resultado.ok) {
                    window.location.href = 'login.php';
                } else {
                    alert('Erro ao fazer logout. Tente novamente.');
                }
            } catch (erro) {
                console.error('Erro de rede no logout:', erro);
                alert('Erro de rede. Não foi possível sair.');
            }
        });
    </script>
</body>
</html>