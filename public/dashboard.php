<?php
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
        /* ESTILOS ESPECÍFICOS DO NOVO DASHBOARD COLABORADOR */
        :root {
            --primary-soft: #e3f2fd;
            --text-dark: #1b2559;
        }

        /* Welcome Banner */
        .dash-header-colab {
            margin-bottom: 30px;
        }

        .dash-header-colab h1 {
            font-size: 1.8rem;
            color: var(--text-dark);
            margin: 0 0 5px 0;
        }

        .dash-header-colab p {
            color: #a3aed0;
            margin: 0;
        }

        /* KPI Cards Modernos */
        .kpi-grid-modern {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .kpi-card-modern {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
            border: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s;
        }

        .kpi-card-modern:hover {
            transform: translateY(-3px);
        }

        .kpi-icon-modern {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-left: 15px;
        }

        .kc-blue {
            background: #eef2ff;
            color: #4318FF;
        }

        .kc-orange {
            background: #fff8e1;
            color: #ffab00;
        }

        .kc-green {
            background: #e8f5e9;
            color: #05cd99;
        }

        .kc-red {
            background: #ffebee;
            color: #ee5d50;
        }

        .kpi-content h3 {
            font-size: 2rem;
            margin: 0;
            color: var(--text-dark);
            font-weight: 700;
        }

        .kpi-content span {
            color: #a3aed0;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .kpi-meta {
            font-size: 0.75rem;
            color: #05cd99;
            margin-top: 5px;
            font-weight: 600;
        }

        /* Lista de Foco (Timeline) */
        .focus-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
            border: 1px solid #f0f0f0;
            margin-bottom: 30px;
        }

        .focus-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .focus-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .task-row {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f5f5f5;
            transition: 0.2s;
            cursor: pointer;
            border-radius: 12px;
        }

        .task-row:hover {
            background-color: #f9f9f9;
        }

        .task-row:last-child {
            border-bottom: none;
        }

        .task-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f4f7fe;
            color: #4318FF;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.1rem;
        }

        .task-info {
            flex: 1;
        }

        .task-info h4 {
            margin: 0;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .task-info span {
            color: #a3aed0;
            font-size: 0.8rem;
        }

        .task-prio {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .tp-alta {
            background: #ffebee;
            color: #ee5d50;
        }

        .tp-media {
            background: #fff8e1;
            color: #ffab00;
        }

        .tp-normal {
            background: #e3f2fd;
            color: #4318FF;
        }

        /* Painel Lateral Deslizante (Checklist) */
        .side-modal {
            position: fixed;
            top: 0;
            right: -500px;
            width: 450px;
            height: 100vh;
            background: white;
            z-index: 2000;
            box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
            transition: right 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            flex-direction: column;
        }

        .side-modal.open {
            right: 0;
        }

        .side-header {
            padding: 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .side-body {
            padding: 25px;
            overflow-y: auto;
            flex: 1;
        }

        .side-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            background: #fcfcfc;
        }

        .side-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.2);
            z-index: 1999;
            display: none;
            backdrop-filter: blur(2px);
        }

        .side-overlay.open {
            display: block;
        }

        /* Checklist Styles */
        .chk-progress-wrap {
            margin-bottom: 25px;
        }

        .chk-prog-bar {
            height: 6px;
            background: #eee;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 8px;
        }

        .chk-prog-fill {
            height: 100%;
            background: #4318FF;
            transition: width 0.3s;
        }

        .chk-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
            transition: 0.2s;
        }

        .chk-item.done {
            opacity: 0.6;
        }

        .chk-checkbox {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            cursor: pointer;
            accent-color: #4318FF;
        }

        .chk-text {
            font-size: 0.9rem;
            color: #333;
            line-height: 1.4;
        }

        .chk-item.done .chk-text {
            text-decoration: line-through;
        }

        /* Botão Flutuante */
        .fab-task {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #4318FF;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 25px rgba(67, 24, 255, 0.4);
            cursor: pointer;
            z-index: 1000;
            transition: transform 0.2s;
        }

        .fab-task:hover {
            transform: scale(1.1);
        }

        .fab-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #ee5d50;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content">

        <?php if ($isColab): ?>
            <div class="dash-header-colab">
                <h1 style="display:flex; align-items:center; gap:10px;">Olá, <?= htmlspecialchars($usuario['nome']) ?>!
                    <span style="color:#f1c40f;"><?= getIcone('aceno') ?></span></h1>
                <p>Aqui está o panorama da sua operação hoje.</p>
            </div>

            <div class="kpi-grid-modern">
                <div class="kpi-card-modern">
                    <div class="kpi-content">
                        <h3 id="kpiPendentes">0</h3>
                        <span>A Fazer</span>
                    </div>
                    <div class="kpi-icon-modern kc-blue"><?= getIcone('task') ?></div>
                </div>
                <div class="kpi-card-modern">
                    <div class="kpi-content">
                        <h3 id="kpiUrgentes">0</h3>
                        <span>Urgentes</span>
                    </div>
                    <div class="kpi-icon-modern kc-red"><?= getIcone('alerta') ?></div>
                </div>
                <div class="kpi-card-modern">
                    <div class="kpi-content">
                        <h3 id="kpiConcluidas">0</h3>
                        <span>Entregues</span>
                        <div class="kpi-meta">+<span id="kpiMetaMes">0</span> este mês</div>
                    </div>
                    <div class="kpi-icon-modern kc-green"><?= getIcone('check') ?></div>
                </div>
                <div class="kpi-card-modern">
                    <div class="kpi-content">
                        <h3 id="kpiProd">0%</h3>
                        <span>Produtividade</span>
                    </div>
                    <div class="kpi-icon-modern kc-orange"><?= getIcone('chart') ?></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">

                <div class="focus-section">
                    <div class="focus-header">
                        <div class="focus-title">Minhas Próximas Entregas</div>
                        <button onclick="carregarDashboard()"
                            style="border:none; background:none; cursor:pointer; color:#999; display:flex; align-items:center;"><?= getIcone('refresh') ?></button>
                    </div>
                    <div id="listaTarefasColab">
                        <p style="text-align:center; color:#999; padding:20px;">Carregando...</p>
                    </div>
                </div>

                <div class="focus-section">
                    <div class="focus-title" style="margin-bottom:20px;">Produtividade (7 dias)</div>
                    <canvas id="chartProdColab" height="250"></canvas>
                </div>

            </div>

            <div class="side-overlay" onclick="fecharPainelLateral()"></div>
            <div id="painelLateral" class="side-modal">
                <div class="side-header">
                    <h3 style="margin:0; font-size:1.1rem; color:#2b3674;">Detalhes da Tarefa</h3>
                    <div style="display:flex; gap:10px;">
                        <button onclick="fecharPainelLateral()"
                            style="border:none; background:none; font-size:1.5rem; cursor:pointer; color:#999; display:flex; align-items:center;"><?= getIcone('close') ?></button>
                    </div>
                </div>
                <div class="side-body" id="painelLateralBody">
                </div>
                <div class="side-footer" id="painelLateralFooter">
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

    <?php if ($isColab): ?>
        <script>
            // --- FUNÇÕES EXCLUSIVAS DO PAINEL LATERAL (COLABORADOR) ---

            let tarefaAtual = null;
            let timerInterval = null;
            let tempoSessao = 0;

            function abrirPainelTarefa(tarefaJson) {
                // Decodifica JSON seguro
                tarefaAtual = typeof tarefaJson === 'string' ? JSON.parse(tarefaJson) : tarefaJson;

                const modal = document.getElementById('painelLateral');
                const overlay = document.querySelector('.side-overlay');
                const body = document.getElementById('painelLateralBody');
                const footer = document.getElementById('painelLateralFooter');

                // 1. Renderiza Cabeçalho e Descrição
                let prazoHtml = tarefaAtual.prazo ? new Date(tarefaAtual.prazo).toLocaleDateString('pt-BR') : 'Sem prazo';
                let prioridadeClass = 'tp-normal';
                if (tarefaAtual.prioridade === 'URGENTE') prioridadeClass = 'tp-alta';
                if (tarefaAtual.prioridade === 'IMPORTANTE') prioridadeClass = 'tp-media';

                let html = `
                <div style="margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                        <span class="task-prio ${prioridadeClass}">${tarefaAtual.prioridade}</span>
                        <span style="font-size:0.8rem; color:#888;">${prazoHtml}</span>
                    </div>
                    <h2 style="margin:0 0 10px 0; color:#2b3674; font-size:1.4rem;">${tarefaAtual.titulo}</h2>
                    <p style="font-size:0.9rem; color:#666; line-height:1.5;">${tarefaAtual.descricao || 'Sem descrição.'}</p>
                </div>
                
                <div style="background:#f9f9f9; padding:15px; border-radius:12px; display:flex; align-items:center; justify-content:space-between; margin-bottom:25px;">
                    <div>
                        <small style="color:#888; font-weight:600;">TEMPO TOTAL</small>
                        <div style="font-size:1.2rem; font-weight:700; color:#333;" id="displayTempoTotal">${formatarMinutos(tarefaAtual.tempo_total_minutos || 0)}</div>
                    </div>
                    </div>
                    <button class="botao-primario" id="btnTimerPanel" onclick="toggleTimerPainel()" style="padding:8px 15px; font-size:0.9rem; display:flex; align-items:center; gap:6px;">
                        ${ICONS.play} Iniciar
                    </button>
                </div>
            `;

                // 2. Renderiza Checklist
                let checklist = [];
                try { checklist = JSON.parse(tarefaAtual.checklist || '[]'); } catch (e) { }

                // Calcula progresso inicial
                let totalItens = checklist.length;
                let feitos = checklist.filter(i => i.concluido == 1).length;
                let pct = totalItens > 0 ? Math.round((feitos / totalItens) * 100) : 0;

                html += `
                <div class="chk-progress-wrap">
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600; color:#2b3674;">
                        <span>Progresso do Checklist</span>
                        <span id="txtProgressoPainel">${pct}%</span>
                    </div>
                    <div class="chk-prog-bar">
                        <div class="chk-prog-fill" id="barProgressoPainel" style="width:${pct}%"></div>
                    </div>
                </div>
                
                <div id="listaChecklistPainel">
            `;

                if (checklist.length > 0) {
                    checklist.forEach((item, idx) => {
                        const checked = item.concluido == 1 ? 'checked' : '';
                        const doneClass = item.concluido == 1 ? 'done' : '';

                        html += `
                        <div class="chk-item ${doneClass}">
                            <input type="checkbox" class="chk-checkbox" ${checked} onchange="toggleCheckItemPainel(${tarefaAtual.id}, ${idx}, this)">
                            <span class="chk-text">${item.descricao}</span>
                        </div>
                    `;
                    });
                } else {
                    html += `<div style="text-align:center; color:#999; font-style:italic;">Sem itens de checklist.</div>`;
                }
                html += `</div>`; // fecha lista

                body.innerHTML = html;

                // 3. Renderiza Footer (Ações)
                footer.innerHTML = `
                <button class="botao-secundario" style="width:100%; margin-bottom:10px;" onclick="window.location.href='minhas_tarefas.php'">
                    Ver Detalhes Completos / Anexar
                </button>
                <button class="botao-primario" style="width:100%; background:#05cd99; display:flex; justify-content:center; align-items:center; gap:8px;" onclick="concluirTarefaPainel(${tarefaAtual.id})">
                    ${ICONS.check} Marcar como Concluída
                </button>
            `;

                modal.classList.add('open');
                overlay.classList.add('open');
            }

            function fecharPainelLateral() {
                document.getElementById('painelLateral').classList.remove('open');
                document.querySelector('.side-overlay').classList.remove('open');
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
            }

            // Lógica do Timer no Painel Lateral
            function toggleTimerPainel() {
                const btn = document.getElementById('btnTimerPanel');
                if (!timerInterval) {
                    // Iniciar
                    btn.innerHTML = ICONS.pause + " Pausar";
                    btn.style.backgroundColor = "#ffce20";
                    btn.style.color = "#333";

                    tempoSessao = 0; // zera contador da sessao
                    timerInterval = setInterval(() => {
                        tempoSessao++;
                        // A cada minuto, atualiza no banco (opcional) ou apenas visualmente
                        // Aqui vamos atualizar visualmente somando ao total original
                        let totalAtual = (parseInt(tarefaAtual.tempo_total_minutos) || 0) + Math.floor(tempoSessao / 60);
                        // document.getElementById('displayTempoTotal').innerText = formatarMinutos(totalAtual);
                    }, 1000);
                } else {
                    // Pausar e Salvar
                    clearInterval(timerInterval);
                    timerInterval = null;
                    btn.innerHTML = ICONS.play + " Continuar";
                    btn.style.backgroundColor = "";
                    btn.style.color = "";

                    // Salva o tempo gasto na sessão
                    let minutosGastos = Math.ceil(tempoSessao / 60);
                    if (minutosGastos > 0) {
                        salvarTempoAPI(tarefaAtual.id, minutosGastos);
                    }
                }
            }

            async function salvarTempoAPI(id, minutos) {
                let totalAntigo = parseInt(tarefaAtual.tempo_total_minutos) || 0;
                let novoTotal = totalAntigo + minutos;

                // Atualiza objeto local
                tarefaAtual.tempo_total_minutos = novoTotal;
                document.getElementById('displayTempoTotal').innerText = formatarMinutos(novoTotal);

                // Envia para API
                const fd = new FormData();
                fd.append('tarefa_id', id);
                fd.append('tempo_gasto', novoTotal); // A API espera o novo total acumulado

                try {
                    await fetch('../api/tarefa_entregar.php', { method: 'POST', body: fd });
                } catch (e) { console.error(e); }
            }

            async function toggleCheckItemPainel(id, index, checkbox) {
                const itemDiv = checkbox.parentElement;
                if (checkbox.checked) itemDiv.classList.add('done');
                else itemDiv.classList.remove('done');

                try {
                    const resp = await fetch('../api/tarefa_checklist_toggle.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ tarefa_id: id, index: index, feito: checkbox.checked })
                    });
                    const json = await resp.json();
                    if (json.ok) {
                        // Atualiza Barra de Progresso
                        document.getElementById('txtProgressoPainel').innerText = json.progresso + '%';
                        document.getElementById('barProgressoPainel').style.width = json.progresso + '%';
                    }
                } catch (e) { console.error(e); }
            }

            async function concluirTarefaPainel(id) {
                if (!confirm("Deseja concluir esta tarefa agora?")) return;
                const fd = new FormData();
                fd.append('tarefa_id', id);
                fd.append('status', 'CONCLUIDA');
                fd.append('progresso', 100);

                try {
                    try {
                        await fetch('../api/tarefa_entregar.php', { method: 'POST', body: fd });
                        alert("Tarefa concluída!");
                        fecharPainelLateral();
                        carregarDashboard(); // Recarrega a lista
                    } catch (e) { alert("Erro ao salvar."); }
                }

                function formatarMinutos(mins) {
                    const h = Math.floor(mins / 60);
                    const m = mins % 60;
                    if (h > 0) return `${h}h ${m}m`;
                    return `${m}m`;
                }
        </script>
    <?php endif; ?>
</body>

</html>