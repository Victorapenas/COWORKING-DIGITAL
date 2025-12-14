<?php
// ARQUIVO: public/dashboard.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';
proteger_pagina();

$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$papel = $usuario['papel'];
$empresaId = getEmpresaIdLogado($usuario);

// Definição de Papéis
$isColab = ($papel == 'FUNCIONARIO' || $papel == 'COLABORADOR');
$isLider = ($papel == 'DONO' || $papel == 'LIDER');
$isGestor = ($papel == 'GESTOR');

// Busca lista de projetos para o modal de "Tarefa Rápida" (Apenas para Gestão)
$listaProjetos = [];
if (!$isColab) {
    $pdo = conectar_db();
    $stmt = $pdo->prepare("SELECT id, nome FROM projeto WHERE empresa_id = ? AND ativo = 1 ORDER BY nome ASC");
    $stmt->execute([$empresaId]);
    $listaProjetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Coworking Digital</title>
    <link rel="stylesheet" href="../css/painel.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* =============================================================================
           ESTILOS ESPECÍFICOS DO DASHBOARD
           ============================================================================= */
        :root {
            --primary-soft: #e3f2fd;
            --text-dark: #1b2559;
        }

        /* --- LAYOUT GERAL --- */
        .dash-grid-container {
            display: grid;
            grid-template-columns: 2.5fr 1fr;
            grid-template-rows: auto 1fr;
            gap: 25px;
            min-height: 600px;
            margin-top: 20px;
        }

        /* KPIs */
        .kpi-row-full {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        /* KPI Cards Clicáveis */
        .card-info { 
            cursor: pointer; 
            transition: transform 0.2s, box-shadow 0.2s; 
            text-decoration: none; 
            color: inherit; 
        }
        .card-info:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.08); 
        }

        /* Colunas Gestor */
        .dash-col-main { display: flex; flex-direction: column; gap: 25px; overflow: hidden; }
        .dash-col-side { display: flex; flex-direction: column; gap: 25px; }

        /* Card Inbox (Gestor) */
        .inbox-card {
            background: #fff; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            border: 1px solid #eef0f7; display: flex; flex-direction: column; flex: 1; 
            overflow: hidden; position: relative; margin-bottom: 0;
        }
        .inbox-header { padding: 20px 25px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .inbox-body { padding: 0; overflow-y: auto; flex: 1; background: #fcfcfc; max-height: 400px; }
        .inbox-item {
            padding: 18px 25px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between;
            background: white; transition: all 0.2s; cursor: pointer; margin: 0;
        }
        .inbox-item:hover { background: #f8f9fa; }
        .inbox-item.priority { border-left: 4px solid #ee5d50; }

        /* Ações Rápidas */
        .quick-actions-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .qa-btn {
            background: white; border: 1px solid #e0e5f2; border-radius: 16px; padding: 20px 10px;
            text-align: center; cursor: pointer; transition: all 0.2s; display: flex; flex-direction: column;
            align-items: center; gap: 10px; text-decoration: none; color: #2b3674; height: 100%;
        }
        .qa-btn:hover { border-color: #4318FF; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(67, 24, 255, 0.1); background: #f8faff; }
        .qa-icon {
            font-size: 1.4rem; color: #4318FF; background: #eef2ff; width: 45px; height: 45px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 5px;
        }

        /* Widget Minhas Tarefas (Compacto - Gestor) */
        .my-tasks-widget .task-row-mini { 
            padding: 12px 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; 
            justify-content: space-between; font-size: 0.9rem; transition: background 0.2s;
        }
        .my-tasks-widget .task-row-mini:hover { background: #f9f9f9; }
        .my-tasks-widget .task-row-mini:last-child { border-bottom: none; }
        .my-tasks-widget .task-prj { font-size: 0.75rem; color: #999; display: block; margin-top: 2px; }

        /* --- ESTILOS DO COLABORADOR --- */
        .dash-header-colab { margin-bottom: 30px; }
        .dash-header-colab h1 { font-size: 1.8rem; color: var(--text-dark); margin: 0 0 5px 0; }
        .kpi-grid-modern { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .kpi-card-modern { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; }
        .kpi-icon-modern { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .kc-blue { background: #eef2ff; color: #4318FF; }
        .kc-orange { background: #fff8e1; color: #ffab00; }
        .kc-green { background: #e8f5e9; color: #05cd99; }
        .kc-red { background: #ffebee; color: #ee5d50; }
        
        .focus-section { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); border: 1px solid #f0f0f0; display: flex; flex-direction: column; }
        .focus-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f5f5f5; padding-bottom: 10px; }
        .focus-title { font-size: 1.1rem; font-weight: 700; color: var(--text-dark); }

        .task-row { display: flex; align-items: center; padding: 12px; border-bottom: 1px solid #f5f5f5; transition: 0.2s; cursor: pointer; border-radius: 8px; margin-bottom: 5px; }
        .task-row:hover { background-color: #f9f9f9; transform: translateX(3px); }
        .task-icon { width: 35px; height: 35px; border-radius: 50%; background: #f4f7fe; color: #4318FF; display: flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 1rem; flex-shrink: 0; }
        .task-info h4 { margin: 0; color: var(--text-dark); font-size: 0.9rem; font-weight: 600; }
        .task-info span { color: #a3aed0; font-size: 0.75rem; }
        .task-prio { padding: 3px 10px; border-radius: 15px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; margin-left: auto; }
        .tp-alta { background: #ffebee; color: #ee5d50; }
        .tp-media { background: #fff8e1; color: #ffab00; }
        .tp-normal { background: #e3f2fd; color: #4318FF; }

        /* --- AGENDA DO DIA (LINHA DO TEMPO) --- */
        .agenda-container {
            display: flex; flex-direction: column; gap: 10px; 
            max-height: 320px; overflow-y: auto; padding-right: 5px;
        }
        .agenda-container::-webkit-scrollbar { width: 4px; }
        .agenda-container::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

        .agenda-item {
            display: flex; gap: 15px; padding: 12px; border-left: 4px solid #ddd;
            background: #fcfcfc; border-radius: 6px; transition: 0.2s; border: 1px solid #f0f0f0;
        }
        .agenda-item.urgent { border-left-color: #ee5d50; background: #fff5f5; }
        .agenda-item.today { border-left-color: #05cd99; background: #f0fdf4; }
        
        .agenda-time {
            font-weight: 800; font-size: 1rem; color: #333; min-width: 55px;
            text-align: center; display: flex; flex-direction: column; justify-content: center;
            border-right: 1px dashed #eee; padding-right: 10px;
        }
        .agenda-details h4 { margin: 0; font-size: 0.9rem; color: #2b3674; font-weight: 600; }
        .agenda-details span { font-size: 0.75rem; color: #888; display: block; margin-top: 2px; }

        /* --- TIMER FLUTUANTE --- */
        #floatingTimer {
            position: fixed; bottom: 30px; right: 30px;
            background: #2b3674; color: white; padding: 10px 20px;
            border-radius: 50px; box-shadow: 0 10px 30px rgba(43, 54, 116, 0.4);
            display: none; align-items: center; gap: 12px; z-index: 2500;
            cursor: pointer; transition: transform 0.2s; border: 2px solid #4318FF;
        }
        #floatingTimer:hover { transform: scale(1.05); }
        .float-pulsing-dot {
            width: 10px; height: 10px; background-color: #ee5d50; border-radius: 50%;
            animation: pulseRed 1s infinite;
        }
        @keyframes pulseRed {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(238, 93, 80, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(238, 93, 80, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(238, 93, 80, 0); }
        }

        /* --- SIDE MODAL --- */
        .side-modal { position: fixed; top: 0; right: -600px; width: 500px; height: 100vh; background: white; z-index: 2000; box-shadow: -5px 0 30px rgba(0,0,0,0.1); transition: right 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); display: flex; flex-direction: column; }
        .side-modal.open { right: 0; }
        .side-header { padding: 20px 25px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .side-body { padding: 25px; overflow-y: auto; flex: 1; background: #fff; }
        .side-footer { padding: 20px; border-top: 1px solid #eee; background: #fcfcfc; }
        .side-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 1999; display: none; backdrop-filter: blur(2px); }
        .side-overlay.open { display: block; }

        @media (max-width: 992px) {
            .dash-grid-container { grid-template-columns: 1fr; }
            .side-modal { width: 100%; right: -100%; }
        }
    </style>
</head>

<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content">

        <?php if ($isColab): // ================= VISÃO COLABORADOR ================= ?>
            
            <div id="floatingTimer" onclick="maximizarTarefa()">
                <div class="float-pulsing-dot"></div>
                <div style="display:flex; flex-direction:column; line-height: 1.1;">
                    <span id="floatTimerText" style="font-family:'Courier New', monospace; font-weight:800; font-size:1.1rem;">00:00</span>
                    <span style="font-size:0.65rem; opacity:0.8;">CLIQUE P/ VOLTAR</span>
                </div>
                <div style="background:rgba(255,255,255,0.2); padding:6px; border-radius:50%;">
                    <?= getIcone('play') ?>
                </div>
            </div>

            <div class="dash-header-colab">
                <h1 style="display:flex; align-items:center; gap:10px;">
                    Olá, <?= htmlspecialchars(explode(' ', $usuario['nome'])[0]) ?>!
                    <span style="color:#f1c40f;"><?= getIcone('aceno') ?></span>
                </h1>
                <p>Aqui está o panorama da sua operação hoje.</p>
            </div>

            <div class="kpi-grid-modern">
                </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
                
                <div class="focus-section">
                    <div class="focus-header">
                        <div class="focus-title">Minhas Próximas Entregas</div>
                        <div style="display:flex; gap:10px;">
                            <button onclick="carregarDashboard()" style="border:none; background:none; cursor:pointer; color:#999; display:flex; align-items:center;" title="Atualizar">
                                <?= getIcone('refresh') ?>
                            </button>
                        </div>
                    </div>
                    <div id="listaTarefasColab">
                        <p style="text-align:center; color:#999; padding:20px;">Carregando...</p>
                    </div>
                </div>

                <div class="focus-section">
                    <div class="focus-header" style="margin-bottom:15px; border:none; padding:0;">
                        <div class="focus-title" style="display:flex; align-items:center; gap:8px;">
                            <?= getIcone('calendario') ?> Agenda de Hoje
                        </div>
                    </div>
                    <div id="agendaDiaContainer" class="agenda-container">
                        <p style="color:#999; text-align:center; margin-top:20px;">Nenhuma entrega agendada para hoje.</p>
                    </div>
                </div>

            </div>

            <div class="side-overlay" onclick="minimizarTarefa()"></div>
            <div id="painelLateral" class="side-modal">
                <div class="side-header">
                    <h3 style="margin:0; font-size:1.1rem; color:#2b3674;">Detalhes da Tarefa</h3>
                    <div style="display:flex; gap:10px;">
                        <button onclick="minimizarTarefa()" style="border:none; background:none; font-size:1.5rem; cursor:pointer; color:#999; display:flex; align-items:center;" title="Minimizar (Timer continua)">
                            _
                        </button>
                        <button onclick="fecharPainelLateral()" style="border:none; background:none; font-size:1.5rem; cursor:pointer; color:#999; display:flex; align-items:center;" title="Fechar">
                            &times;
                        </button>
                    </div>
                </div>
                <div class="side-body" id="painelLateralBody"></div>
                <div class="side-footer" id="painelLateralFooter"></div>
            </div>

        <?php else: // ================= VISÃO GESTOR E LÍDER ================= ?>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div>
                    <h1 style="margin:0; color:#2b3674; font-size:1.8rem; font-weight:800;">Visão Geral</h1>
                    <p style="color:#a3aed0; margin:0;">
                        Olá, <?= htmlspecialchars(explode(' ', $usuario['nome'])[0]) ?>. 
                        <?= $isLider ? 'Acompanhe a saúde dos projetos.' : 'Gerencie a operação da equipe.' ?>
                    </p>
                </div>
                
                <div style="display:flex; gap:15px; align-items:center;">
                    
                    <span id="liveClock" style="font-weight:600; color:#2b3674; background:#fff; padding:10px 15px; border-radius:12px; border:1px solid #eee; display:none; @media(min-width:768px){display:inline-block;}">--:--</span>

                    <?php if($isLider): ?>
                        <button class="botao-primario" onclick="window.location.href='projetos.php?novo=1'" style="background:#6A66FF; padding: 12px 20px;">
                            <?= getIcone('pasta') ?> Novo Projeto
                        </button>
                    <?php endif; ?>
                    
                    <button class="botao-primario" onclick="abrirModalSelecaoProjeto()" style="padding: 12px 20px;">
                        <?= getIcone('adicionar') ?> Nova Tarefa Rápida
                    </button>
                </div>
            </div>

            <div class="dash-grid-container">
                
                <div class="dash-col-main">
                    
                    <div class="kpi-row-full" id="kpiContainer"></div>

                    <div class="inbox-card">
                        <div class="inbox-header">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="background:#ffebee; color:#d32f2f; padding:8px; border-radius:10px;"><?= getIcone('alerta') ?></div>
                                <div>
                                    <h3 style="margin:0; font-size:1.1rem; color:#2b3674;">Aprovações Pendentes</h3>
                                    <span style="font-size:0.8rem; color:#a3aed0;">Tarefas aguardando sua revisão</span>
                                </div>
                            </div>
                            <span class="st-badge" id="badgePendencias" style="background:#ee5d50; color:white; border-radius:20px; padding:2px 10px;">0</span>
                        </div>
                        <div class="inbox-body" id="listaPendencias">
                            <div style="padding:40px; text-align:center; color:#ccc;">Carregando...</div>
                        </div>
                    </div>

                    <?php if($isLider): ?>
                    <div class="inbox-card" style="border-left: 4px solid #05cd99;">
                        <div class="inbox-header">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="background:#e8f5e9; color:#05cd99; padding:8px; border-radius:10px;"><?= getIcone('check') ?></div>
                                <div>
                                    <h3 style="margin:0; font-size:1.1rem; color:#2b3674;">Entregas Recentes (Auditoria)</h3>
                                    <span style="font-size:0.8rem; color:#a3aed0;">Verifique a qualidade das tarefas finalizadas</span>
                                </div>
                            </div>
                        </div>
                        <div class="inbox-body" id="listaAuditoria">
                            <p style="padding:20px; text-align:center; color:#ccc;">Carregando histórico...</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="dash-col-side">
                    
                    <div class="inbox-card my-tasks-widget">
                        <div class="inbox-header" style="background:#f8faff; padding:15px 20px;">
                            <h4 style="margin:0; font-size:1rem; color:#2b3674;">Minhas Tarefas</h4>
                            <a href="minhas_tarefas.php" style="font-size:0.8rem; text-decoration:none; color:#4318FF;">Ver todas</a>
                        </div>
                        <div class="inbox-body" id="listaMinhasTarefasGestor" style="max-height: 250px;">
                            <p style="padding:20px; text-align:center; color:#999; font-size:0.9rem;">Carregando...</p>
                        </div>
                    </div>

                    <div class="content-box" style="padding:20px; background:white; border-radius:20px; border:1px solid #eef0f7;">
                        <h4 style="margin:0 0 15px 0; color:#2b3674; font-size:1rem;">Acesso Rápido</h4>
                        <div class="quick-actions-grid">
                            <a href="projetos.php" class="qa-btn">
                                <div class="qa-icon"><?= getIcone('pasta') ?></div>
                                <strong>Projetos</strong>
                            </a>
                            <a href="equipes.php" class="qa-btn">
                                <div class="qa-icon" style="color:#05cd99; background:#e8f5e9;"><?= getIcone('users') ?></div>
                                <strong>Equipe</strong>
                            </a>
                            <a href="relatorios.php" class="qa-btn">
                                <div class="qa-icon" style="color:#ee5d50; background:#ffebee;"><?= getIcone('chart') ?></div>
                                <strong>Relatórios</strong>
                            </a>
                            <a href="calendario.php" class="qa-btn">
                                <div class="qa-icon" style="color:#ffab00; background:#fff8e1;"><?= getIcone('calendario') ?></div>
                                <strong>Agenda</strong>
                            </a>
                        </div>
                    </div>
                    
                    <div class="content-box" style="padding:20px; flex:1; min-height:200px; background:white; border-radius:20px; border:1px solid #eef0f7;">
                        <h4 style="margin:0 0 10px 0; color:#2b3674; font-size:1rem;">Produtividade (7d)</h4>
                        <div style="flex:1; position:relative; height:150px;">
                            <canvas id="prodChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div id="modalSelectProj" class="modal">
                <div class="modal-content" style="width: 400px; text-align:center;">
                    <span class="close-btn" onclick="document.getElementById('modalSelectProj').style.display='none'">&times;</span>
                    <h3 style="color:#2b3674; margin-bottom:10px;">Nova Atividade</h3>
                    <p style="color:#666; margin-bottom:25px; font-size:0.9rem;">Selecione o projeto para criar a tarefa:</p>
                    
                    <div style="margin-bottom:25px;">
                        <select id="quickProjectSelect" class="campo-padrao" style="width:100%;">
                            <option value="">-- Selecione o Projeto --</option>
                            <?php foreach($listaProjetos as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button class="botao-primario" onclick="avancarCriacaoTarefa()" style="width:100%;">Continuar</button>
                </div>
            </div>

            <?php require_once 'tarefa.php'; ?>

        <?php endif; ?>

    </div>

    <script src="../js/dashboard.js?v=<?= time() ?>"></script>
    <script src="../js/tarefas.js"></script>
    <script>
        // --- Scripts específicos para a visão de Gestão ---
        
        function abrirModalSelecaoProjeto() {
            document.getElementById('modalSelectProj').style.display = 'flex';
        }

        function avancarCriacaoTarefa() {
            const projId = document.getElementById('quickProjectSelect').value;
            if(!projId) {
                alert("Por favor, selecione um projeto.");
                return;
            }
            // Fecha seleção e abre modal principal
            document.getElementById('modalSelectProj').style.display = 'none';
            
            // Função global definida em tarefas.js
            openTarefaModal(projId, null); 
        }
        
        // Linkando KPIs e Relógio
        document.addEventListener('DOMContentLoaded', () => {
            
            // Relógio
            const elClock = document.getElementById('liveClock');
            if(elClock) {
                setInterval(() => {
                    const now = new Date();
                    elClock.innerText = now.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});
                }, 1000);
            }

            // Tornar KPIs clicáveis (após carregamento do JS principal)
            setTimeout(() => {
                const kpis = document.querySelectorAll('.card-info');
                // Linkando o 1º KPI (Pendências) para scrollar até a lista
                if(kpis[0]) kpis[0].onclick = () => document.getElementById('listaPendencias').scrollIntoView({behavior: 'smooth'});
                // Linkando o 2º KPI (Ativas) para ir a Minhas Tarefas (ou Projetos, conforme preferir)
                if(kpis[1]) kpis[1].onclick = () => window.location.href = 'minhas_tarefas.php';
                // Linkando o 3º KPI (Projetos)
                if(kpis[2]) kpis[2].onclick = () => window.location.href = 'projetos.php';
            }, 800);
        });
    </script>
</body>
</html>