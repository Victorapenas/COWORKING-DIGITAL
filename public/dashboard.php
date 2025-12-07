<?php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';
proteger_pagina();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Visão Geral</title>
    <link rel="stylesheet" href="../css/painel.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content">
        <div class="topbar">
            <h1 style="font-size:1.8rem; color:#2c3e50;">Olá, <?= htmlspecialchars($_SESSION[SESSAO_USUARIO_KEY]['nome']) ?>! 👋</h1>
            <div class="profile"><div class="avatar-profile"><?= strtoupper(substr($_SESSION[SESSAO_USUARIO_KEY]['nome'], 0, 2)) ?></div></div>
        </div>

        <div class="dashboard-cards" id="kpiContainer">
            <div class="card-info"><p>Carregando...</p></div>
        </div>

        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:30px;">
            <div>
                <div class="content-box" style="background:white; padding:25px; border-radius:20px; box-shadow:0 5px 20px rgba(0,0,0,0.02);">
                    <h3 style="margin-bottom:20px; color:#333;">Ações Rápidas</h3>
                    <div style="display:flex; gap:15px;">
                        <button onclick="window.location.href='projetos.php'" class="botao-primario" style="flex:1; background:#e3f2fd; color:#0d6efd;">+ Novo Projeto</button>
                        <button onclick="window.location.href='equipes.php'" class="botao-primario" style="flex:1; background:#f3e5f5; color:#7b1fa2;">+ Novo Membro</button>
                        <button onclick="window.location.href='emergenciais.php'" class="botao-primario" style="flex:1; background:#ffebee; color:#c62828;">! Emergência</button>
                    </div>
                </div>

                <div class="content-box" style="margin-top:30px; background:white; padding:25px; border-radius:20px;">
                    <h3 style="margin-bottom:20px; color:#333;">Projetos em Andamento</h3>
                    <div id="listaProjetosRecentes">Carregando...</div>
                </div>
            </div>

            <div class="content-box" style="background:white; padding:25px; border-radius:20px;">
                <h3 style="margin-bottom:20px; color:#333;">Produtividade da Semana</h3>
                <canvas id="graficoSemana"></canvas>
                <div id="listaEmergencias" style="margin-top:30px;">
                    </div>
            </div>
        </div>
    </div>
    <script src="../js/dashboard.js"></script>
</body>
</html>