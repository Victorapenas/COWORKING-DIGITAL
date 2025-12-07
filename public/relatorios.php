<?php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
proteger_pagina();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatórios de Desempenho</title>
    <link rel="stylesheet" href="../css/painel.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php renderizar_sidebar(); ?>
    <div class="main-content">
        <h1 style="color:#2c3e50; margin-bottom:30px;">Relatórios Gerenciais</h1>
        
        <div class="dashboard-cards">
            <div class="content-box" style="grid-column: span 2; background:white; padding:20px; border-radius:20px;">
                <h4>Tarefas por Membro</h4>
                <canvas id="chartMembros"></canvas>
            </div>
            <div class="content-box" style="grid-column: span 2; background:white; padding:20px; border-radius:20px;">
                <h4>Status dos Projetos</h4>
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const resp = await fetch('../api/relatorios_dados.php');
            const data = await resp.json();
            
            // Gráfico Membros
            new Chart(document.getElementById('chartMembros'), {
                type: 'bar',
                data: {
                    labels: data.membros.map(m => m.nome),
                    datasets: [{
                        label: 'Tarefas Concluídas',
                        data: data.membros.map(m => m.concluidas),
                        backgroundColor: '#05cd99'
                    }]
                }
            });

            // Gráfico Status
            new Chart(document.getElementById('chartStatus'), {
                type: 'doughnut',
                data: {
                    labels: ['Planejado', 'Em Andamento', 'Concluído'],
                    datasets: [{
                        data: [data.status.planejado, data.status.andamento, data.status.concluido],
                        backgroundColor: ['#e0e0e0', '#0d6efd', '#2ecc71']
                    }]
                }
            });
        });
    </script>
</body>
</html>