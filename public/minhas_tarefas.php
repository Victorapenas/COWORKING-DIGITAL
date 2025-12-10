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

// 1. Busca TAREFAS DE GESTÃO (Se for manager, busca tudo. Se não, array vazio)
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

// 2. Busca TAREFAS PESSOAIS (Sempre busca, para qualquer usuário)
$sqlP = "SELECT t.*, p.nome as projeto_nome, u.nome as responsavel_nome, u.cargo_detalhe as responsavel_cargo
         FROM tarefa t 
         LEFT JOIN projeto p ON t.projeto_id = p.id
         LEFT JOIN usuario u ON t.responsavel_id = u.id
         WHERE t.responsavel_id = ? AND t.status != 'CANCELADA' ORDER BY t.prazo ASC";
$stmtP = $pdo->prepare($sqlP);
$stmtP->execute([$usuario['id']]);
$tarefasPessoais = $stmtP->fetchAll(PDO::FETCH_ASSOC);

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

        /* Estilos dos Cards (Reutilizando e Aprimorando) */
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
                <button class="botao-primario" onclick="window.location.href = 'projetos.php'" style="padding:12px 25px; background: #4318FF;">
                    <?= getIcone('adicionar') ?> Nova Atividade
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

    <?php require_once 'tarefa.php'; ?>
    
    <div id="modalExecucao" class="modal">
        <div class="modal-content" style="width: 700px;">
            <span class="close-btn" onclick="closeModal('modalExecucao')">&times;</span>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 id="execTitulo" style="margin:0; color:#2c3e50;">Detalhes da Atividade</h3>
                <span id="execBadge" class="st-badge">STATUS</span>
            </div>
            <form id="formEntrega" enctype="multipart/form-data">
                <input type="hidden" name="tarefa_id" id="execId">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
                    <div>
                        <h4 style="color:#666; font-size:0.9rem; margin-bottom:10px;">Descrição</h4>
                        <div id="execDesc" style="background:#f8f9fa; padding:15px; border-radius:8px; font-size:0.9rem; color:#444; min-height:100px; margin-bottom:20px; border:1px solid #eee;"></div>
                        <div class="form-group">
                            <label>Progresso Real</label>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <input type="range" name="progresso" id="execProgresso" min="0" max="100" oninput="document.getElementById('lblProg').innerText = this.value + '%'">
                                <span id="lblProg" style="font-weight:bold; color:#0d6efd; width:40px;">0%</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Mover para</label>
                            <select name="status" id="execStatus" class="campo-padrao">
                                <option value="PENDENTE">A Fazer</option>
                                <option value="EM_ANDAMENTO">Em Execução</option>
                                <option value="EM_REVISAO">Revisão</option>
                                <option value="CONCLUIDA">Concluído</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label>Entrega / Arquivos</label>
                        <div class="upload-area" onclick="document.getElementById('fileInput').click()" style="border: 2px dashed #e0e0e0; padding: 20px; text-align: center; border-radius: 10px; cursor: pointer; background: #fafafa; margin-bottom: 15px;">
                            <input type="file" name="arquivo_entrega" id="fileInput" style="display:none" onchange="previewUpload(this)">
                            <div style="font-size:1.5rem; color:#aaa; margin-bottom:5px;"><?= getIcone('pasta') ?></div>
                            <span id="uploadText" style="font-size:0.85rem; color:#666;">Clique para anexar arquivo</span>
                        </div>
                        <div class="form-group">
                            <label>Comentário</label>
                            <textarea name="comentario" rows="3" class="campo-padrao" placeholder="Atualização sobre o andamento..."></textarea>
                        </div>
                        <button type="submit" class="botao-primario" style="width:100%;">Salvar Progresso</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/minhas_tarefas.js"></script>
    <script src="../js/tarefas.js"></script> 
    <script src="../js/projetos.js"></script>
    <script>
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
    </script>
</body>
</html>

<?php
function renderCard($t, $podeEditar) {
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
    
    // Ações: Se pode editar (Gestor) mostra Editar/Excluir. Senão, só "Ver Detalhes".
    $actions = '<button class="btn-act btn-view" onclick=\'abrirModalExecucao('.$json.')\'>Ver Detalhes</button>';
    
    if($podeEditar) {
        $actions .= '
        <button class="btn-act btn-edit" onclick=\'openTarefaModal(null, '.$t['id'].')\'>Editar</button>
        <button class="btn-act btn-del" onclick=\'confirmarExclusaoTarefa('.$t['id'].')\'>Excluir</button>';
    }

    // Grid CSS para os botões se ajustarem
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