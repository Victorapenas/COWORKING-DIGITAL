<?php
// ARQUIVO: public/dashboard.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';
proteger_pagina();

$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$empresaId = getEmpresaIdLogado($usuario);

$papel = $usuario['papel'];
$is_socio = in_array($papel, ['DONO', 'LIDER']);
$pode_editar = in_array($papel, ['DONO', 'LIDER', 'GESTOR']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Coworking Digital</title>
    <link rel="stylesheet" href="../css/painel.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* DASHBOARD ESPECÍFICO */
        .dashboard-container { padding-top: 10px; }
        
        /* Ações Rápidas */
        .quick-actions { display: flex; gap: 15px; margin-bottom: 30px; overflow-x: auto; padding-bottom: 5px; }
        .action-btn { 
            background: white; border: 1px solid #eef0f7; padding: 12px 20px; border-radius: 12px; 
            display: flex; align-items: center; gap: 10px; cursor: pointer; transition: 0.2s; 
            color: #555; font-weight: 600; font-size: 0.9rem; white-space: nowrap; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .action-btn:hover { border-color: #6A66FF; color: #6A66FF; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(106, 102, 255, 0.15); }
        .action-btn svg { width: 18px; height: 18px; }

        /* KPIs */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        
        /* Layout Principal */
        .main-grid { display: grid; grid-template-columns: 2.5fr 1fr; gap: 30px; }
        @media (max-width: 1100px) { .main-grid { grid-template-columns: 1fr; } }

        /* Lista de Projetos/Tarefas */
        .card-list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .card-list-title { font-size: 1.1rem; font-weight: 700; color: #2b3674; }
        
        .project-item { 
            background: white; border-radius: 16px; padding: 20px; margin-bottom: 15px;
            border: 1px solid #f0f0f0; transition: 0.2s; cursor: pointer; display: flex; align-items: center; justify-content: space-between;
        }
        .project-item:hover { border-color: #6A66FF; transform:translateX(5px); }
        
        .prog-container { width: 120px; text-align: right; }
        .prog-bar { height: 6px; background: #eee; border-radius: 3px; overflow: hidden; margin-top: 5px; }
        .prog-fill { height: 100%; background: linear-gradient(90deg, #6A66FF, #0d6efd); }

        /* Lateral (Online & Pendências) */
        .side-card { background: white; padding: 20px; border-radius: 16px; border: 1px solid #f0f0f0; margin-bottom: 25px; }
        .online-list { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .online-avatar { 
            width: 40px; height: 40px; border-radius: 50%; background: #e3f2fd; color: #0d6efd; 
            display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;
            position: relative; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .online-dot { width: 10px; height: 10px; background: #05cd99; border: 2px solid white; border-radius: 50%; position: absolute; bottom: 0; right: 0; }

        /* Aprovações */
        .approval-item { padding: 15px; background: #fff8e1; border-radius: 12px; margin-bottom: 10px; border-left: 4px solid #ffab00; }
        .approval-actions { display: flex; gap: 10px; margin-top: 10px; }
        .btn-mini { padding: 5px 10px; border-radius: 6px; font-size: 0.75rem; border: none; cursor: pointer; font-weight: 700; }
        .btn-ok { background: #e8f5e9; color: #2e7d32; }
        .btn-ok:hover { background: #c8e6c9; }
    </style>
</head>
<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content">
        <div class="dashboard-container">
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div>
                    <h1 style="margin:0; color:#2b3674; font-size:1.8rem;">Olá, <span id="userName">...</span> 👋</h1>
                    <p style="color:#a3aed0; margin-top:5px;">Aqui está o panorama da sua operação hoje.</p>
                </div>
                <div style="text-align:right; font-size:0.85rem; color:#a3aed0;">
                    <?= date('d/m/Y') ?>
                </div>
            </div>

            <div id="quickActions" class="quick-actions" style="display:none;">
                <?php if ($is_socio): ?>
                    <div class="action-btn" onclick="openModal()">
                        <?= getIcone('adicionar') ?> Novo Projeto
                    </div>
                <?php endif;?>
                <div class="action-btn" onclick="window.location.href='equipes.php'">
                    <?= getIcone('users') ?> Gerenciar Equipe
                </div>
                <div class="action-btn" onclick="window.location.href='minhas_tarefas.php'">
                    <?= getIcone('task') ?> Criar Tarefa
                </div>
            </div>

            <div id="kpiContainer" class="kpi-grid">
                </div>

            <div class="main-grid">
                <div class="left-col">
                    <div class="card-list-header">
                        <span class="card-list-title" id="mainListTitle">Atividades</span>
                        <button class="btn-round view" onclick="window.location.reload()"><?= getIcone('restaurar') ?></button>
                    </div>
                    <div id="mainListContainer">
                        <p style="color:#999; text-align:center;">Carregando...</p>
                    </div>
                </div>

                <div class="right-col">
                    
                    <div id="approvalSection" style="display:none;">
                        <div class="side-card" style="border-color: #ffab00;">
                            <h4 style="margin:0 0 15px 0; color:#d35400;">⚠️ Aprovações Pendentes</h4>
                            <div id="approvalList"></div>
                        </div>
                    </div>

                    <div id="onlineSection" style="display:none;">
                        <div class="side-card">
                            <h4 style="margin:0; color:#2b3674;">Equipe Online</h4>
                            <div id="onlineList" class="online-list"></div>
                        </div>
                    </div>

                    <div class="side-card">
                        <h4 style="margin:0 0 15px 0; color:#2b3674;">Produtividade (7 dias)</h4>
                        <canvas id="prodChart" height="180"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php require_once 'tarefa.php'; ?>
    <script>
        // Sobrescreve openModal para redirecionar se o modal não existir aqui
        window.openModal = function() { window.location.href = 'projetos.php'; }
    </script>

    <script src="../js/dashboard.js?v=<?= time() ?>"></script>
</body>
</html>