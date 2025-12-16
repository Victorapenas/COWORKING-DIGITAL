<?php
// ARQUIVO: public/minhas_tarefas.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';

proteger_pagina();
$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$empresaId = getEmpresaIdLogado($usuario);
$papel = $usuario['papel'];

// Permissão de Gestão
$isManager = in_array($papel, ['DONO', 'LIDER', 'GESTOR']);

$pdo = conectar_db();

// --- 1. Busca TAREFAS DE GESTÃO (Se for manager, busca tudo. Se não, array vazio) ---
$tarefasGestao = [];
if ($isManager) {
    $sqlG = "SELECT t.*, p.nome as projeto_nome, u.nome as responsavel_nome, u.cargo_detalhe as responsavel_cargo, e.nome as equipe_nome
             FROM tarefa t 
             LEFT JOIN projeto p ON t.projeto_id = p.id 
             LEFT JOIN usuario u ON t.responsavel_id = u.id
             LEFT JOIN equipe e ON u.equipe_id = e.id
             WHERE t.empresa_id = ? AND t.status != 'CANCELADA' ORDER BY t.prazo ASC";
    $stmtG = $pdo->prepare($sqlG);
    $stmtG->execute([$empresaId]);
    $tarefasGestao = $stmtG->fetchAll(PDO::FETCH_ASSOC);
}

// --- 2. Busca TAREFAS PESSOAIS (Execução) ---
$sqlP = "SELECT t.*, p.nome as projeto_nome, u.nome as responsavel_nome, u.cargo_detalhe as responsavel_cargo
         FROM tarefa t 
         LEFT JOIN projeto p ON t.projeto_id = p.id
         LEFT JOIN usuario u ON t.responsavel_id = u.id
         WHERE t.responsavel_id = ? AND t.status != 'CANCELADA' ORDER BY t.prazo ASC";
$stmtP = $pdo->prepare($sqlP);
$stmtP->execute([$usuario['id']]);
$tarefasPessoais = $stmtP->fetchAll(PDO::FETCH_ASSOC);

// --- 3. Busca PROJETOS (Para o modal de Nova Atividade Rápida) ---
$listaProjetos = [];
if ($isManager) {
    $stmtProj = $pdo->prepare("SELECT id, nome FROM projeto WHERE empresa_id = ? AND ativo = 1 ORDER BY nome ASC");
    $stmtProj->execute([$empresaId]);
    $listaProjetos = $stmtProj->fetchAll(PDO::FETCH_ASSOC);
}

// Cálculo de KPIs Pessoais
$statsPessoal = ['total'=>0, 'prog'=>0, 'done'=>0, 'late'=>0];
foreach ($tarefasPessoais as $t) {
    $statsPessoal['total']++;
    if($t['status']=='CONCLUIDA') $statsPessoal['done']++;
    elseif($t['status']!='PENDENTE') $statsPessoal['prog']++;
    if($t['status']!='CONCLUIDA' && $t['prazo'] && strtotime($t['prazo']) < time()) $statsPessoal['late']++;
}

// Cálculo de KPIs Gestão
$statsGestao = ['total'=>0, 'prog'=>0, 'done'=>0, 'late'=>0];
foreach ($tarefasGestao as $t) {
    $statsGestao['total']++;
    if($t['status']=='CONCLUIDA') $statsGestao['done']++;
    elseif($t['status']!='PENDENTE') $statsGestao['prog']++;
    if($t['status']!='CONCLUIDA' && $t['prazo'] && strtotime($t['prazo']) < time()) $statsGestao['late']++;
}

$listaEquipes = listarEquipes($empresaId);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Atividades</title>
    <link rel="stylesheet" href="../css/painel.css">
    <style>
        /* TABS SUPERIORES (TOGGLE) */
        .view-toggle { display: flex; background: #f1f3f9; padding: 5px; border-radius: 12px; width: fit-content; margin-bottom: 25px; }
        .view-btn { padding: 10px 25px; border-radius: 8px; border: none; background: transparent; color: #7f8c8d; font-weight: 600; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; }
        .view-btn.active { background: white; color: #2b3674; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .view-btn:hover:not(.active) { color: #4318FF; }

        /* Estilos dos Cards */
        .filters-wrapper { background: white; padding: 20px; border-radius: 16px; border: 1px solid #f0f0f0; margin-bottom: 25px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .filter-item label { font-size: 0.85rem; font-weight: 700; color: #2b3674; margin-bottom: 8px; display: block; }
        
        .kpi-row-mgmt { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; margin-bottom: 30px; }
        .kpi-card-mgmt { background: white; border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #fff; transition: 0.3s; }
        .kpi-card-mgmt:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        .kpi-val { font-size: 2.2rem; font-weight: 800; margin-bottom: 5px; color: #2b3674; }
        .kpi-lbl { color: #a3aed0; font-size: 0.9rem; font-weight: 500; }
        .k-blue .kpi-val { color: #4318FF; } .k-cyan .kpi-val { color: #05cd99; } .k-purple .kpi-val { color: #4318FF; } .k-red .kpi-val { color: #ee5d50; }

        .tasks-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        
        .mgmt-card { background: white; border-radius: 20px; padding: 25px; border: 1px solid #f4f7fe; box-shadow: 0 2px 10px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between; transition: 0.2s; position: relative; }
        .mgmt-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-color: #eef0f7; }
        .mc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .mc-title { font-size: 1.1rem; font-weight: 700; color: #1b2559; margin-bottom: 5px; line-height: 1.4; }
        .mc-meta { font-size: 0.8rem; color: #a3aed0; }
        .mc-badge { padding: 5px 12px; border-radius: 10px; font-weight: 700; font-size: 0.75rem; }
        .bg-progresso { background: #e3f2fd; color: #0d6efd; }
        .bg-concluido { background: #e8f5e9; color: #05cd99; }
        .bg-pendente { background: #fff8e1; color: #ffa000; }
        .bg-atrasada { background: #ffebee; color: #ee5d50; }
        .mc-user { display: flex; align-items: center; gap: 12px; margin: 20px 0; }
        .mc-avatar { width: 40px; height: 40px; background: #4318FF; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem; }
        .mc-user-info h5 { margin: 0; font-size: 0.95rem; color: #1b2559; }
        .mc-user-info span { font-size: 0.8rem; color: #a3aed0; }
        .mc-prio { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .prio-alta { color: #ee5d50; background: #ffebee; padding: 4px 10px; border-radius: 8px; }
        .prio-media { color: #ffa000; background: #fff8e1; padding: 4px 10px; border-radius: 8px; }
        .prio-baixa { color: #05cd99; background: #e8f5e9; padding: 4px 10px; border-radius: 8px; }
        .mc-prog-bar { height: 6px; width: 100%; background: #eff4fb; border-radius: 10px; overflow: hidden; margin-top: 10px; }
        .mc-prog-fill { height: 100%; border-radius: 10px; background: #4318FF; transition: width 0.5s; }
        .mc-prog-text { font-size: 0.8rem; color: #a3aed0; text-align: right; display: block; margin-top: 5px; font-weight: 600; }
        .mc-actions { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 20px; }
        .btn-act { padding: 8px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.85rem; cursor: pointer; text-align: center; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 5px; }
        .btn-view { background: #f4f7fe; color: #4318FF; } .btn-view:hover { background: #e0e5f2; }
        .btn-edit { background: #f4f7fe; color: #2b3674; } .btn-edit:hover { background: #e0e5f2; }
        .btn-del { background: #ffebee; color: #ee5d50; } .btn-del:hover { background: #ffcdd2; }

        .view-section { display: none; }
        .view-section.active { display: block; }
    </style>
</head>
<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content">
        
        <div style="display:flex; justify-content:space-between; align-items:end; margin-bottom:30px;">
            <div>
                <h1 style="margin:0; font-size:2rem; font-weight:800; color:#2b3674;">Atividades</h1>
                <p style="color:#a3aed0; margin-top:5px;">Centro de controle de tarefas e entregas.</p>
            </div>
            
            <?php if($isManager): ?>
                <button class="botao-primario" onclick="abrirModalSelecaoProjeto()" style="padding:12px 25px; background: #4318FF;">
                    <?= getIcone('adicionar') ?> Nova Tarefa Rápida
                </button>
            <?php endif; ?>
        </div>

        <?php if($isManager): ?>
        <div class="view-toggle">
            <button class="view-btn active" onclick="switchView('gestao', this)">
                <?= getIcone('chart') ?> Visão de Gestão
            </button>
            <button class="view-btn" onclick="switchView('pessoal', this)">
                <?= getIcone('user') ?> Minhas Entregas
            </button>
        </div>
        <?php endif; ?>

        <?php if($isManager): ?>
        <div id="view-gestao" class="view-section active">
            <div class="filters-wrapper">
                <div class="filter-item">
                    <label>Status</label>
                    <select id="filtroStatusG" class="campo-padrao" onchange="filtrarGrid('G')">
                        <option value="">Todos</option>
                        <option value="PENDENTE">A Fazer</option>
                        <option value="EM_ANDAMENTO">Em Progresso</option>
                        <option value="CONCLUIDA">Concluídas</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label>Equipe</label>
                    <select id="filtroEquipeG" class="campo-padrao" onchange="filtrarGrid('G')">
                        <option value="">Todas</option>
                        <?php foreach($listaEquipes as $eq): ?>
                            <option value="<?= htmlspecialchars($eq['nome']) ?>"><?= htmlspecialchars($eq['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label>Prioridade</label>
                    <select id="filtroPrioridadeG" class="campo-padrao" onchange="filtrarGrid('G')">
                        <option value="">Todas</option>
                        <option value="URGENTE">Alta</option>
                        <option value="IMPORTANTE">Média</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label>Busca</label>
                    <input type="text" id="filtroBuscaG" class="campo-padrao" placeholder="Título ou responsável..." onkeyup="filtrarGrid('G')">
                </div>
            </div>

            <div class="kpi-row-mgmt">
                <div class="kpi-card-mgmt k-blue"><div class="kpi-val"><?= $statsGestao['total'] ?></div><div class="kpi-lbl">Total</div></div>
                <div class="kpi-card-mgmt k-purple"><div class="kpi-val"><?= $statsGestao['prog'] ?></div><div class="kpi-lbl">Em Progresso</div></div>
                <div class="kpi-card-mgmt k-cyan"><div class="kpi-val"><?= $statsGestao['done'] ?></div><div class="kpi-lbl">Concluídas</div></div>
                <div class="kpi-card-mgmt k-red"><div class="kpi-val"><?= $statsGestao['late'] ?></div><div class="kpi-lbl">Atrasadas</div></div>
            </div>

            <div class="tasks-grid" id="gridG">
                <?php foreach($tarefasGestao as $t) echo renderCard($t, true); ?>
            </div>
            <?php if(empty($tarefasGestao)) echo '<p style="text-align:center; color:#999; padding:40px;">Nenhuma tarefa encontrada.</p>'; ?>
        </div>
        <?php endif; ?>

        <div id="view-pessoal" class="view-section <?= !$isManager ? 'active' : '' ?>">
            
            <div class="kpi-row-mgmt" style="grid-template-columns: repeat(3, 1fr);">
                <div class="kpi-card-mgmt k-blue"><div class="kpi-val"><?= $statsPessoal['total'] ?></div><div class="kpi-lbl">Minhas Tarefas</div></div>
                <div class="kpi-card-mgmt k-purple"><div class="kpi-val"><?= $statsPessoal['prog'] ?></div><div class="kpi-lbl">Em Execução</div></div>
                <div class="kpi-card-mgmt k-red"><div class="kpi-val"><?= $statsPessoal['late'] ?></div><div class="kpi-lbl">Meus Atrasos</div></div>
            </div>

            <div class="tasks-grid" id="gridP">
                <?php foreach($tarefasPessoais as $t) echo renderCard($t, false); ?>
            </div>
            <?php if(empty($tarefasPessoais)) echo '<p style="text-align:center; color:#999; padding:40px;">Você não tem tarefas pendentes. Bom trabalho! 🎉</p>'; ?>
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
    
    <div id="modalExecucao" class="modal">
        <div class="modal-content" style="width: 900px; max-width:95%; height: 85vh; display:flex; flex-direction:column;">
            
            <div style="flex-shrink:0; border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:15px; display:flex; justify-content:space-between;">
                <div>
                    <h3 id="execTitulo" style="margin:0; color:#2b3674;">Carregando...</h3>
                    <div id="execCronograma" style="margin-top:5px; font-size:0.85rem;"></div>
                </div>
                <button onclick="closeModal('modalExecucao')" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>

            <div class="modal-tabs" style="flex-shrink:0;">
                <div class="modal-tab active" onclick="switchExecTab('execucao', this)">📝 Execução & Checklist</div>
                <div class="modal-tab" onclick="switchExecTab('historico', this)">💬 Histórico & Arquivos</div>
            </div>

            <div style="flex-grow:1; overflow-y:auto; padding-right:5px;">
                
                <div id="tab-execucao" class="tab-exec-panel active">
                    <form id="formEntrega" enctype="multipart/form-data">
                        <input type="hidden" name="tarefa_id" id="execId">
                        
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:25px;">
                            <div>
                                <div style="background:#f8f9fa; padding:15px; border-radius:10px; border:1px solid #eef0f7; margin-bottom:20px;">
                                    <label style="font-size:0.8rem; color:#888;">DESCRIÇÃO</label>
                                    <div id="execDesc" style="color:#333; margin-top:5px; line-height:1.5;"></div>
                                </div>

                                <div id="execChecklistArea">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                        <h4 style="margin:0; color:#2b3674;">Checklist de Entregas</h4>
                                        <span id="execProgressoTexto" style="background:#e3f2fd; color:#0d6efd; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:bold;">0%</span>
                                    </div>
                                    <div id="listaChecklistColab"></div>
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Entrega Geral (Opcional)</label>
                                    <div class="upload-area" onclick="document.getElementById('fileInput').click()" style="border: 2px dashed #ddd; padding: 20px; text-align: center; border-radius: 10px; cursor: pointer; background: #fff;">
                                        <input type="file" name="arquivo_entrega" id="fileInput" style="display:none" onchange="previewUpload(this)">
                                        <div style="font-size:1.5rem; color:#aaa; margin-bottom:5px;"><?= getIcone('upload') ?></div>
                                        <span id="uploadText" style="font-size:0.85rem; color:#666;">Clique para anexar arquivo final</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Comentário / Atualização</label>
                                    <textarea name="comentario" rows="3" class="campo-padrao" placeholder="Descreva o que foi feito..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Novo Status</label>
                                    <select name="status" id="execStatus" class="campo-padrao">
                                        <option value="EM_ANDAMENTO">Em Andamento</option>
                                        <option value="EM_REVISAO">Enviar para Revisão</option>
                                        <option value="CONCLUIDA">Concluir Diretamente</option>
                                    </select>
                                </div>

                                <div id="execFeedbackArea" style="display:none; background:#fff3e0; border-left:4px solid #ff9800; padding:10px; margin-bottom:15px; font-size:0.9rem;">
                                    <strong>⚠️ Feedback:</strong> <span id="execFeedbackText"></span>
                                </div>

                                <button type="submit" class="botao-primario" style="width:100%; height:50px; font-size:1rem;">💾 Salvar Alterações</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="tab-historico" class="tab-exec-panel" style="display:none;">
                    <div id="containerHistorico" style="display:flex; flex-direction:column; gap:15px;">
                        <p style="text-align:center; color:#999;">Carregando histórico...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="../js/minhas_tarefas.js"></script>
    <script src="../js/tarefas.js"></script> 
    <script src="../js/projetos.js"></script>
    <script>
        // Funções para troca de abas e filtro
        function switchView(view, btn) {
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            document.getElementById('view-'+view).classList.add('active');
            document.querySelectorAll('.view-btn').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');
        }

        function filtrarGrid(suffix) {
            const busca = document.getElementById('filtroBusca'+suffix)?.value.toLowerCase() || '';
            const status = document.getElementById('filtroStatus'+suffix)?.value || '';
            const equipe = document.getElementById('filtroEquipe'+suffix)?.value || '';
            const prio = document.getElementById('filtroPrioridade'+suffix)?.value || '';
            const grid = document.getElementById('grid'+suffix);

            if(grid) {
                grid.querySelectorAll('.mgmt-card').forEach(card => {
                    const txt = card.innerText.toLowerCase();
                    const s = card.getAttribute('data-status');
                    const p = card.getAttribute('data-prio');
                    const e = card.getAttribute('data-equipe');

                    let show = true;
                    if(busca && !txt.includes(busca)) show = false;
                    if(status && s !== status) show = false;
                    if(prio && p !== prio) show = false;
                    if(equipe && e !== equipe) show = false;

                    card.style.display = show ? 'flex' : 'none';
                });
            }
        }

        // Scripts para a funcionalidade "Nova Tarefa Rápida"
        function abrirModalSelecaoProjeto() {
            document.getElementById('modalSelectProj').style.display = 'flex';
        }

        function avancarCriacaoTarefa() {
            const projId = document.getElementById('quickProjectSelect').value;
            if(!projId) {
                alert("Por favor, selecione um projeto.");
                return;
            }
            document.getElementById('modalSelectProj').style.display = 'none';
            // Chama função global definida em tarefas.js
            openTarefaModal(projId, null); 
        }

        // Captura clique vindo do Dashboard (URL param)
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const execId = params.get('abrir_execucao');
            if(execId) {
                // Busca detalhes da tarefa para abrir o modal de execução
                fetch(`../api/tarefa_buscar.php?id=${execId}`)
                    .then(r => r.json())
                    .then(data => {
                        if(data.ok && data.tarefa) {
                            // Se encontrar, abre o modal de execução (minhas_tarefas.js)
                            abrirModalExecucao(data.tarefa);
                            // Limpa a URL para não reabrir ao recarregar
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }
                    });
            }
        });
    </script>
</body>
</html>

<?php
function renderCard($t, $podeEditar) {
    // Garante que o objeto PHP vire um JSON válido para o JS
    $json = htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8');
    
    $prazoTxt = $t['prazo'] ? date('d/m/Y', strtotime($t['prazo'])) : 'S/ Data';
    $criadoEm = date('d/m/Y', strtotime($t['criado_em']));
    $isAtrasada = ($t['status']!='CONCLUIDA' && $t['prazo'] && strtotime($t['prazo']) < time());
    
    // Cores
    $statusLabel = 'Pendente'; $statusClass = 'bg-pendente';
    if($t['status']=='EM_ANDAMENTO' || $t['status']=='EM_REVISAO') { $statusLabel = 'Em Progresso'; $statusClass = 'bg-progresso'; }
    if($t['status']=='CONCLUIDA') { $statusLabel = 'Concluída'; $statusClass = 'bg-concluido'; }
    if($isAtrasada) { $statusLabel = 'Atrasada'; $statusClass = 'bg-atrasada'; }

    $prioLabel = 'Baixa'; $prioClass = 'prio-baixa';
    if($t['prioridade']=='URGENTE') { $prioLabel = 'Alta'; $prioClass = 'prio-alta'; }
    if($t['prioridade']=='IMPORTANTE') { $prioLabel = 'Média'; $prioClass = 'prio-media'; }

    $iniciais = strtoupper(substr($t['responsavel_nome'], 0, 2));
    
    // Ações:
    // Para todos (incluindo Gestor quando age como executor): Ver Detalhes (Execução/Checklist)
    $actions = '<button class="btn-act btn-view" onclick=\'abrirModalExecucao('.$json.')\'>Ver Checklist</button>';
    
    // Se for modo gestão, adiciona edição completa
    if($podeEditar) {
        $actions .= '
        <button class="btn-act btn-edit" onclick=\'openTarefaModal(null, '.$t['id'].')\'>Editar</button>
        <button class="btn-act btn-del" onclick=\'confirmarExclusaoTarefa('.$t['id'].')\'>Excluir</button>';
    }

    $gridCols = $podeEditar ? '1fr 1fr 1fr' : '1fr';

    return '
    <div class="mgmt-card" data-status="'.($isAtrasada?'ATRASADA':$t['status']).'" data-prio="'.$t['prioridade'].'" data-equipe="'.htmlspecialchars($t['equipe_nome']??'').'">
        <div class="mc-header">
            <div style="flex:1">
                <div class="mc-title">'.htmlspecialchars($t['titulo']).'</div>
                <div class="mc-meta">Criada: '.$criadoEm.' • Prazo: '.$prazoTxt.'</div>
            </div>
            <span class="mc-badge '.$statusClass.'">'.$statusLabel.'</span>
        </div>
        <div class="mc-user">
            <div class="mc-avatar">'.$iniciais.'</div>
            <div class="mc-user-info">
                <h5>'.htmlspecialchars($t['responsavel_nome']).'</h5>
                <span>'.htmlspecialchars($t['responsavel_cargo'] ?: 'Colaborador').'</span>
            </div>
            <div style="margin-left:auto;"><span class="mc-prio '.$prioClass.'">'.$prioLabel.'</span></div>
        </div>
        <div class="mc-progress-wrap">
            <div class="mc-prog-bar"><div class="mc-prog-fill" style="width:'.$t['progresso'].'%"></div></div>
            <span class="mc-prog-text">'.$t['progresso'].'% concluído</span>
        </div>
        <div class="mc-actions" style="grid-template-columns: '.$gridCols.';">
            '.$actions.'
        </div>
    </div>';
}
?>