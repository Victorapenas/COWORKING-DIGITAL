<?php
//atualização
// ARQUIVO: public/dashboard.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';
proteger_pagina();

$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$papel = $usuario['papel'];
$isColab = ($papel == 'FUNCIONARIO' || $papel == 'COLABORADOR');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Coworking Digital</title>
    <link rel="stylesheet" href="../css/painel.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ESTILOS ESPECÍFICOS DO COLABORADOR */
        .colab-welcome { background: linear-gradient(135deg, var(--primary) 0%, #6A66FF 100%); padding: 30px; border-radius: 20px; color: white; margin-bottom: 30px; box-shadow: 0 10px 25px rgba(13, 110, 253, 0.2); }
        .colab-welcome h1 { margin: 0; font-size: 1.8rem; }
        .colab-welcome p { opacity: 0.9; margin-top: 5px; }
        
        .task-focus-card { background: white; padding: 20px; border-radius: 16px; border-left: 5px solid #ddd; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: 0.3s; margin-bottom: 15px; cursor: pointer; position: relative; }
        .task-focus-card:hover { transform: translateX(5px); }
        .task-focus-card.priority-URGENTE { border-left-color: #ee5d50; }
        .task-focus-card.priority-IMPORTANTE { border-left-color: #ffce20; }
        .task-focus-card.priority-NORMAL { border-left-color: #0d6efd; }
        
        .task-focus-card .play-btn { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; background: #f4f7fe; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); transition: 0.2s; }
        .task-focus-card:hover .play-btn { background: var(--primary); color: white; }

        /* BOTÃO FLUTUANTE (Checklist Rápido) */
        .fab-container { position: fixed; bottom: 30px; right: 30px; z-index: 1000; }
        .fab-main { width: 60px; height: 60px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 20px rgba(13, 110, 253, 0.4); cursor: pointer; transition: 0.3s; font-size: 1.5rem; }
        .fab-main:hover { transform: scale(1.1); background: #0b5ed7; }
        .fab-badge { position: absolute; top: 0; right: 0; background: #ee5d50; color: white; font-size: 0.7rem; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid white; }

        /* MODAL LATERAL DE CHECKLIST */
        .side-modal { position: fixed; top: 0; right: -450px; width: 400px; height: 100vh; background: white; z-index: 2000; box-shadow: -5px 0 30px rgba(0,0,0,0.1); transition: right 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); padding: 30px; overflow-y: auto; display: flex; flex-direction: column; }
        .side-modal.open { right: 0; }
        .side-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 1999; display: none; backdrop-filter: blur(2px); }
        .side-overlay.open { display: block; }
    </style>
</head>
<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content">
        
        <?php if ($isColab): ?>
            <div id="dash-colab">
                <div class="colab-welcome">
                    <h1>Olá, <?= htmlspecialchars($usuario['nome']) ?>! 🚀</h1>
                    <p>Você tem <strong id="countPendentes">0</strong> tarefas prioritárias para hoje.</p>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                    <div>
                        <h3 style="color:#2b3674; margin-bottom: 20px;">Sua Fila de Produção</h3>
                        <div id="listaTarefasFoco">
                            <p style="color:#999;">Carregando suas missões...</p>
                        </div>
                    </div>

                    <div>
                        <div class="side-card" style="background: white; padding: 20px; border-radius: 16px; margin-bottom: 20px;">
                            <h4 style="margin:0 0 15px 0; color:#2b3674;">Meus Projetos Ativos</h4>
                            <div id="listaProjetosSimples"></div>
                        </div>
                        
                        <div class="side-card" style="background: #e3f2fd; padding: 20px; border-radius: 16px;">
                            <h4 style="margin:0 0 10px 0; color:#0d6efd;">Produtividade</h4>
                            <div style="font-size: 2rem; font-weight: 800; color:#0d6efd;" id="prodNumber">0%</div>
                            <small style="color:#6ea8fe;">Tarefas concluídas este mês</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fab-container" onclick="toggleSideChecklist()">
                <div class="fab-main">
                    <?= getIcone('check') ?>
                </div>
                <div class="fab-badge" id="fabCount">0</div>
            </div>

            <div class="side-overlay" onclick="toggleSideChecklist()"></div>
            <div id="sideChecklist" class="side-modal">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h2 style="margin:0; font-size:1.4rem; color:#2b3674;">Foco Atual</h2>
                    <button onclick="toggleSideChecklist()" style="border:none; background:none; font-size:1.5rem; cursor:pointer;">&times;</button>
                </div>
                
                <div id="sideChecklistContent">
                    <p style="color:#999; text-align:center; margin-top:50px;">
                        Selecione uma tarefa na lista para ver o checklist aqui.
                    </p>
                </div>
            </div>

        <?php else: ?>
            
            <div class="dashboard-container">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <div>
                        <h1 style="margin:0; color:#2b3674; font-size:1.8rem;">Visão Geral</h1>
                        <p style="color:#a3aed0; margin-top:5px;">Monitoramento de performance da equipe.</p>
                    </div>
                    <div style="text-align:right; font-size:0.85rem; color:#a3aed0;">
                        <?= date('d/m/Y') ?>
                    </div>
                </div>
                <div id="kpiContainer" class="kpi-grid"></div>
                <div class="main-grid">
                    <div class="left-col">
                        <div class="card-list-header">
                            <span class="card-list-title">Projetos & Atividades</span>
                        </div>
                        <div id="mainListContainer"></div>
                    </div>
                    <div class="right-col">
                        <div class="side-card">
                            <h4 style="margin:0 0 15px 0; color:#2b3674;">Produtividade</h4>
                            <canvas id="prodChart" height="180"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <script src="../js/dashboard.js?v=<?= time() ?>"></script>
    <?php if($isColab): ?>
    <script>
        // Scripts Exclusivos do Colaborador (Inline para garantir carregamento)
        
        // Função para abrir o checklist lateral
        let tarefaFocoAtual = null;

        function carregarChecklistLateral(tarefa) {
            tarefaFocoAtual = tarefa;
            const container = document.getElementById('sideChecklistContent');
            const sideModal = document.getElementById('sideChecklist');
            const overlay = document.querySelector('.side-overlay');
            
            // Abre o modal
            sideModal.classList.add('open');
            overlay.classList.add('open');

            // Renderiza o conteúdo (Título + Checklist)
            let checklistHTML = '';
            let checklist = [];
            try { checklist = JSON.parse(tarefa.checklist || '[]'); } catch(e){}

            if (checklist.length > 0) {
                checklistHTML = '<div style="margin-top:15px;">';
                checklist.forEach((item, idx) => {
                    const checked = (item.concluido == 1) ? 'checked' : '';
                    const style = checked ? 'text-decoration:line-through; color:#aaa;' : '';
                    checklistHTML += `
                        <div class="checklist-item" style="padding:10px; border-bottom:1px solid #eee;">
                            <input type="checkbox" ${checked} onchange="toggleCheckItemSide(${tarefa.id}, ${idx}, this)">
                            <span style="${style}; margin-left:10px;">${item.descricao}</span>
                        </div>`;
                });
                checklistHTML += '</div>';
            } else {
                checklistHTML = '<p style="color:#999; font-style:italic;">Esta tarefa não possui checklist. Use o botão abaixo para concluir.</p>';
            }

            container.innerHTML = `
                <span class="st-badge ${tarefa.status.toLowerCase()}" style="margin-bottom:10px;">${tarefa.status.replace('_',' ')}</span>
                <h3 style="margin:0 0 10px 0; color:#333;">${tarefa.titulo}</h3>
                <p style="font-size:0.9rem; color:#666; background:#f9f9f9; padding:10px; border-radius:8px;">${tarefa.descricao || 'Sem descrição'}</p>
                
                <h4 style="margin-top:20px; border-bottom:1px solid #eee; padding-bottom:5px;">Checklist</h4>
                ${checklistHTML}

                <button class="botao-primario" onclick="window.location.href='minhas_tarefas.php'" style="width:100%; margin-top:30px;">
                    Abrir Detalhes Completos
                </button>
            `;
        }

        function toggleSideChecklist() {
            document.getElementById('sideChecklist').classList.toggle('open');
            document.querySelector('.side-overlay').classList.toggle('open');
        }

        // Função rápida para marcar checklist sem recarregar tudo
        async function toggleCheckItemSide(tarefaId, index, checkbox) {
            const span = checkbox.nextElementSibling;
            span.style.textDecoration = checkbox.checked ? 'line-through' : 'none';
            span.style.color = checkbox.checked ? '#aaa' : '#333';

            try {
                const resp = await fetch('../api/tarefa_checklist_toggle.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ tarefa_id: tarefaId, index: index, feito: checkbox.checked })
                });
                // Opcional: Atualizar barra de progresso visualmente
            } catch(e) { console.error(e); }
        }
    </script>
    <?php endif; ?>
</body>
</html>