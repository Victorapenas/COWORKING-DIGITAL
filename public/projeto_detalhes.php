<?php
//atualização
// ARQUIVO: public/projeto_detalhes.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';

proteger_pagina();

// 1. Variáveis de Sessão e Empresa
$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$empresaId = getEmpresaIdLogado($usuario);
$id = (int)($_GET['id'] ?? 0);

// 2. Busca e Validação de Dados do Projeto
$proj = getProjetoDetalhe($id);
if (!$proj) {
    die("Projeto não encontrado.");
}

// 3. Permissões
$papel = $usuario['papel'];
$is_socio = in_array($papel, ['DONO', 'LIDER']);
$pode_editar = in_array($papel, ['DONO', 'LIDER', 'GESTOR']);

// 4. Dados Auxiliares
$listaEquipes = listarEquipes($empresaId);
$membrosProjeto = getMembrosDoProjeto($id);

// 5. Cálculo de Prazos
$prazoHtml = '<span style="color:#999">Indefinido</span>';
if ($proj['data_fim']) {
    $hoje = new DateTime();
    $fim = new DateTime($proj['data_fim']);
    $diff = $hoje->diff($fim);

    if ($diff->invert) {
        $prazoHtml = '<span style="color:#2ecc71; font-weight:bold;">' . ($diff->days+1) . ' dia(s) restantes</span>';
    } else {
        $prazoHtml = '<span style="color:#e74c3c; font-weight:bold;">Atrasado ' . ($diff->days+1) . ' dia(s)</span>';
    }
}

// 6. Dados para Modais JS (JSON)
$projJson = htmlspecialchars(json_encode($proj), ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($proj['nome']) ?></title>
    <link rel="stylesheet" href="../css/painel.css">
    <style>
        /* Estilos específicos da página mantidos */
        .tab-content { display: none; padding: 30px; max-width: 1400px; margin: 0 auto; }
        .tab-content.active { display: block; }
        .grid-overview { display: grid; grid-template-columns: 2.5fr 1fr; gap: 30px; width: 100%; }
        .full-layout { width: 95%; }
        
        .war-room-header { background: white; padding: 25px 30px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .header-title h1 { margin: 0; font-size: 1.8rem; color: #2c3e50; font-weight: 800; }
        .header-meta { display: flex; gap: 20px; color: #666; font-size: 0.9rem; margin-top: 8px; }
        
        .page-tabs { display: flex; gap: 30px; border-bottom: 1px solid #e0e0e0; padding: 0 30px; background: white; }
        .page-tab { padding: 15px 0; cursor: pointer; color: #7f8c8d; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.2s; font-size: 0.95rem; }
        .page-tab:hover { color: #0d6efd; }
        .page-tab.active { color: #0d6efd; border-bottom-color: #0d6efd; }
        
        .kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .kpi-box { background: white; padding: 25px; border-radius: 12px; border: 1px solid #eee; text-align: center; }
        .kpi-box h3 { margin: 0; font-size: 2rem; color: #6A66FF; font-weight: 800; }
        
        .content-box { background: white; border-radius: 16px; border: 1px solid #eee; padding: 30px; margin-bottom: 30px; }
        .box-title { font-size: 1.1rem; font-weight: 700; color: #333; margin-bottom: 25px; border-bottom: 1px solid #f5f5f5; padding-bottom: 15px; display: flex; align-items: center; justify-content: space-between; }
        
        .file-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; }
        .team-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .team-card { background: #f8f9fa; border: 1px solid #eee; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; }
        .team-avatar { width: 50px; height: 50px; border-radius: 50%; background: #e3f2fd; color: #0d6efd; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; flex-shrink: 0; }
        
        .file-card-item { background: white; border: 1px solid #eee; border-radius: 12px; padding: 20px; text-align: center; text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center; position: relative; }
        .file-card-item:hover { border-color: #0d6efd; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .file-icon-circle { font-size: 1.5rem; margin-bottom: 15px; color: #0d6efd; background: #f0f7ff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;}
        
        /* Dropdown Customizado */
        .custom-select-wrapper { position: relative; user-select: none; width: 100%; }
        .custom-select { position: relative; display: flex; flex-direction: column; }
        .custom-select__trigger { position: relative; display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #fff; border: 1px solid #e0e5f2; border-radius: 10px; cursor: pointer; }
        .custom-options { position: absolute; display: block; top: 100%; left: 0; right: 0; border: 1px solid #e0e5f2; border-top: 0; background: #fff; transition: all 0.3s; opacity: 0; visibility: hidden; pointer-events: none; z-index: 100; max-height: 200px; overflow-y: auto; }
        .custom-select.open .custom-options { opacity: 1; visibility: visible; pointer-events: all; top: 105%; }
        .custom-option { padding: 10px 15px; cursor: pointer; transition: all 0.2s; }
        .custom-option:hover { background-color: #f4f7fe; color: #0d6efd; }
        .custom-option.selected { background-color: #f0f7ff; color: #0d6efd; font-weight: 600; }

        /* Badges de Tarefa */
        .st-badge { padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; display: inline-flex; align-items: center; gap: 5px; height: fit-content; }
        .st-badge.a_fazer { background: #f0f4ff; color: #436cf1; }
        .st-badge.em_andamento { background: #fffbe6; color: #ffab00; }
        .st-badge.concluida { background: #d3fbe7; color: #029d5b; }
        .st-badge.cancelada { background: #ffeded; color: #cc0000; }
        .st-badge.prioridade-normal { background: #f0f4ff; color: #436cf1; border: 1px solid #436cf1; }
        .st-badge.prioridade-importante { background: #fffbe6; color: #ffab00; border: 1px solid #ffab00; }
        .st-badge.prioridade-urgente { background: #ffeded; color: #cc0000; border: 1px solid #cc0000; }
        .btn-icone { background: none; border: none; cursor: pointer; color: #3498db; }
        .tarefa-item { display:flex; justify-content:space-between; align-items:center; padding:15px; border-bottom: 1px solid #eee; background: white; margin-bottom: 5px; border-radius: 8px; transition: background 0.2s; }
        .tarefa-item:hover { background: #fcfcfc; }

        /* Estilo do Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); justify-content: center; align-items: center; }
        .modal-content { background-color: #fefefe; margin: auto; padding: 20px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative; }
        .close-btn { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .modal-tabs { display: flex; gap: 15px; border-bottom: 1px solid #eee; margin-bottom: 20px; margin-top: 10px; }
        .modal-tab { padding: 10px 0; cursor: pointer; color: #7f8c8d; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.2s; font-size: 0.9rem; }
        .modal-tab.active { color: #0d6efd; border-bottom-color: #0d6efd; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; color: #555; }
        .form-group input:not([type="checkbox"]), .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #e0e5f2; border-radius: 10px; box-sizing: border-box; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; padding-top: 15px; margin-top: 20px; }
        
        /* Container onde aparecem os arquivos para excluir */
        .arquivos-atuais-container { margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 8px; border: 1px dashed #ddd; }
    </style>
</head>
<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content" style="padding:0; background: #fafafa;">
        
        <div class="war-room-header">
            <div class="header-left">
                <a href="projetos.php" style="color:#999; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; gap:5px; margin-bottom:10px;">
                    <?= getIcone('seta_voltar') ?> Voltar
                </a>
                <div class="header-title">
                    <h1><?= htmlspecialchars($proj['nome']) ?></h1>
                </div>
                <div class="header-meta">
                    <span><?= getIcone('user') ?> <?= htmlspecialchars($proj['cliente_nome'] ?: 'Interno') ?></span>
                    <span><?= getIcone('calendario') ?> Entrega: <?= $prazoHtml ?></span>
                    <span style="padding:4px 10px; background:#e3f2fd; color:#0d6efd; border-radius:20px; font-size:0.75rem; font-weight:800; text-transform:uppercase;"><?= str_replace('_', ' ', $proj['status']) ?></span>
                </div>
            </div>
            <div style="text-align:right; display:flex; align-items:center; gap:20px;">
                <?php if ($is_socio): ?>
                    <button onclick="abrirModalEditarProjeto(<?= $projJson ?>, 'info', 'editar')" class="botao-primario" style="padding: 10px 20px; display:flex; align-items:center; gap:8px;">
                        <?= getIcone('editar') ?> Editar Projeto
                    </button>
                <?php endif; ?>
                
                <div>
                    <div style="font-size:2rem; font-weight:800; color:#333; text-align:right;"><?= $proj['progresso'] ?>%</div>
                    <div style="width:150px; height:6px; background:#eee; border-radius:3px; margin-top:5px; overflow:hidden;">
                        <div style="width:<?= $proj['progresso'] ?>%; background: linear-gradient(90deg, #6A66FF, #0d6efd); height:100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-tabs">
            <div class="page-tab active" onclick="switchPageTab('visao_geral', this)">Visão Geral</div>
            <div class="page-tab" onclick="switchPageTab('equipe', this)">Equipe & Progresso</div>
            <div class="page-tab" onclick="switchPageTab('arquivos', this)">Arquivos & Links</div>
            <?php if ($is_socio): ?>
            <div class="page-tab" onclick="switchPageTab('restrito', this)" style="color:#e74c3c;">Área Restrita</div>
            <?php endif; ?>
        </div>

        <div id="tab-visao_geral" class="tab-content active">
            <div class="grid-overview">
                <div>
                    <div class="kpi-row">
                        <div class="kpi-box"><h3><?= $proj['tarefas_total'] ?></h3><p>Tarefas</p></div>
                        <div class="kpi-box"><h3 style="color:#2ecc71;"><?= $proj['tarefas_feitas'] ?></h3><p>Concluídas</p></div>
                        <div class="kpi-box"><h3 style="color:#e74c3c;"><?= $proj['tarefas_total'] - $proj['tarefas_feitas'] ?></h3><p>Pendentes</p></div>
                        <div class="kpi-box"><h3 style="color:#333;"><?= count($proj['equipes']) ?></h3><p>Equipes</p></div>
                    </div>

                    <div class="content-box">
                        <div class="box-title">
                            <span><?= getIcone('task') ?> Cronograma de Atividades</span>
                            <?php if ($pode_editar): ?>
                                <button class="botao-primario" onclick="openTarefaModal(<?php echo $id; ?>, null);" style="padding: 5px 15px; font-size: 0.85rem;">
                                    <?= getIcone('adicionar'); ?>Nova Tarefa
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="tarefas-lista-container">
                            <?php if (empty($proj['tarefas_lista'])): ?>
                                <p style="color: #7f8c8d; text-align:center; padding:20px;">Nenhuma tarefa cadastrada neste projeto.</p>
                            <?php else: ?>
                                <?php foreach ($proj['tarefas_lista'] as $tarefa): 
                                    $statusClass = strtolower(str_replace('_', '', $tarefa['status']));
                                    $prazo = $tarefa['prazo'] ? date('d/m/Y', strtotime($tarefa['prazo'])) : 'Indefinido';
                                ?>
                                    <div class="tarefa-item">
                                        <div style="flex-grow: 1;">
                                            <div style="font-weight: 600; color: #2c3e50;"><?= htmlspecialchars($tarefa['titulo']); ?></div>
                                            <div style="font-size: 0.85rem; color: #7f8c8d; margin-top: 3px;">
                                                Resp: <?= htmlspecialchars($tarefa['responsavel_nome']); ?> | 
                                                Prazo: <?= $prazo; ?>
                                            </div>
                                        </div>
                                        <div style="min-width: 150px; text-align: right; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                                            <span class="st-badge prioridade-<?= strtolower($tarefa['prioridade']); ?>"><?= htmlspecialchars($tarefa['prioridade']); ?></span>
                                            <span class="st-badge <?= $statusClass; ?>"><?= statusTarefaLabel($tarefa['status']); ?></span>

                                            <?php if ($pode_editar || $tarefa['responsavel_id'] == $usuario['id']): ?>
                                                <button class="btn-icone" title="Editar Tarefa" onclick="openTarefaModal(<?php echo $proj['id']; ?>, <?php echo $tarefa['id']; ?>);">
                                                    <?= getIcone('editar'); ?>
                                                </button>
                                            <?php endif; ?>

                                            <?php 
                                            if (in_array($usuario['papel'], ['DONO', 'LIDER', 'GESTOR'])): 
                                            ?>
                                                <button class="btn-icone btn-icone-perigo" title="Excluir Tarefa" onclick="confirmarExclusaoTarefa(<?php echo $tarefa['id']; ?>)">
                                                    <?= getIcone('lixo'); ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="side-panel">
                    <div class="content-box">
                        <div class="box-title"><?= getIcone('olho') ?> Detalhes</div>
                        <p style="font-size:0.9rem; line-height:1.6; color:#555;"><?= nl2br(htmlspecialchars($proj['descricao'] ?: 'Sem descrição.')) ?></p>
                        <div style="margin-top:20px;">
                            <span style="font-size:0.75rem; color:#999; font-weight:700; display:block; margin-bottom:5px;">GESTOR</span>
                            <div style="display:flex; align-items:center; gap:8px; font-weight:600; color:#333;">
                                <?php $inicialGestor = strtoupper(substr($proj['nome_gestor'] ?? 'U', 0, 1)); ?>
                                <div class="team-avatar" style="width:24px; height:24px; background:#6A66FF; color:white; font-size:0.7rem;"><?= $inicialGestor ?></div>
                                <?= htmlspecialchars($proj['nome_gestor'] ?? 'Não definido') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-equipe" class="tab-content full-layout">
            <div class="content-box">
                <div class="box-title">
                    <span><?= getIcone('users') ?> Membros Envolvidos & Desempenho</span>
                    <?php if ($is_socio): ?>
                        <button class="botao-secundario" onclick="abrirModalEditarProjeto(<?= $projJson ?>)" style="padding:5px 15px; font-size:0.85rem;">+ Adicionar Equipe</button>
                    <?php endif; ?>
                </div>
                <?php if (empty($membrosProjeto)): ?>
                    <p style="text-align:center; color:#999;">Nenhum membro vinculado.</p>
                <?php else: ?>
                    <div class="team-grid">
                        <?php foreach ($membrosProjeto as $mem): 
                            $iniciais = strtoupper(substr($mem['nome'], 0, 2)); 
                        ?>
                            <div class="team-card">
                                <div class="team-avatar"><?= $iniciais ?></div>
                                <div class="team-info">
                                    <h4 style="margin:0; font-size:1rem; color:#333;"><?= htmlspecialchars($mem['nome']) ?></h4>
                                    <p style="margin:2px 0 5px; font-size:0.8rem; color:#888;"><?= htmlspecialchars($mem['cargo_detalhe'] ?: 'Colaborador') ?></p>
                                    <div style="font-size:0.75rem; font-weight:600; color:#6A66FF;">
                                        <?php if ($mem['cargo_detalhe'] != 'CEO'):?>
                                            <?= $mem['tarefas_feitas'] ?> Entregas
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="tab-arquivos" class="tab-content full-layout">
            <div class="content-box">
                <div class="box-title">
                    <span><?= getIcone('pasta') ?> Arquivos e Links Públicos</span>
                    <?php if ($pode_editar): ?>
                        <button class="botao-secundario" onclick="abrirModalEditarProjeto(<?= $projJson ?>, 'anexos', 'adicionar_arquivos')" style="padding:5px 15px; font-size:0.85rem;">+ Adicionar Arquivos</button>
                    <?php endif; ?>
                </div>
                
                <div class="file-grid"> 
                    <?php 
                    $arquivosPublicos = array_filter($proj['links'] ?? [], function($arq) {
                        return ($arq['tipo'] !== 'logo');
                    });
                    ?>
                    <?php if (empty($arquivosPublicos)): ?>
                        <p style="color:#999; grid-column:1/-1; text-align:center; padding: 20px;">Nenhum arquivo público compartilhado.</p>
                    <?php else: foreach ($arquivosPublicos as $arq): 
                        $icone = ($arq['tipo'] === 'link') ? getIcone('link') : getIcone('documento'); 
                        $label = ($arq['tipo']==='link') ? 'Link Externo' : 'Arquivo';
                    ?>
                        <a href="<?= $arq['url'] ?>" target="_blank" class="file-card-item">
                            <div class="file-icon-circle"><?= $icone ?></div>
                            <div style="font-size:0.9rem; font-weight:600; color:#333; margin-bottom:5px; word-break:break-word;"><?= htmlspecialchars($arq['titulo']) ?></div>
                            <span style="font-size:0.75rem; color:#999; text-transform:uppercase;"><?= $label ?></span>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <?php if ($is_socio): ?>
        <div id="tab-restrito" class="tab-content full-layout">
            <div class="content-box" style="border-color:#ffcdd2;">
                <div class="box-title" style="color:#c62828;">
                    <span><?= getIcone('cadeado') ?> Área Restrita (Confidencial)</span>
                    <button class="botao-secundario" onclick="abrirModalEditarProjeto(<?= $projJson ?>, 'privado', 'adicionar_arquivos')" style="padding:5px 15px; font-size:0.85rem; border-color:#ffcdd2; color:#c62828;">+ Adicionar Confidencial</button>
                </div>
                
                <div class="file-grid">
                    <?php if (empty($proj['privados'])): ?>
                        <p style="color:#999; grid-column:1/-1; text-align:center; padding: 20px;">Nenhum documento confidencial.</p>
                    <?php else: foreach ($proj['privados'] as $arq): 
                        $icone = ($arq['tipo'] === 'link') ? getIcone('link') : getIcone('documento'); 
                    ?>
                        <a href="<?= $arq['url'] ?>" target="_blank" class="file-card-item" style="background:#fffafa; border-color:#ffcdd2;">
                            <div class="file-icon-circle" style="background:#ffebee; color:#c62828;"><?= $icone ?></div>
                            <div style="font-size:0.9rem; font-weight:600; color:#333; margin-bottom:5px; word-break:break-word;"><?= htmlspecialchars($arq['titulo']) ?></div>
                            <span style="font-size:0.75rem; color:#c62828; font-weight:bold;">CONFIDENCIAL</span>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php 
    // INCLUSÃO DO MODAL DE TAREFAS
    require_once __DIR__ . '/tarefa.php'; 
    ?>
    
    <div id="modalProjeto" class="modal">
        <div class="modal-content" style="width: 700px;">
            <h3 id="modalTitle">Editar Projeto</h3>
            <div class="modal-tabs">
                <?php if ($is_socio): ?>
                    <div class="modal-tab active" onclick="switchFormTab('info', this)">Informações & Equipe</div>
                <?php endif; ?>
                <div class="modal-tab" onclick="switchFormTab('anexos', this)">Anexos e Links</div>
                <?php if ($is_socio): ?>
                <div class="modal-tab" onclick="switchFormTab('privado', this)" style="color:#6A66FF;"><?= getIcone('cadeado') ?> Área do Sócio</div>
                <?php endif; ?>
            </div>

            <form id="formCriarProjeto" enctype="multipart/form-data">
                <input type="hidden" name="id" id="projId"> 
                <?php if($is_socio): ?>
                    <div id="tab-info" class="tab-panel active">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                            <div class="form-group"><label>Nome do Projeto</label><input type="text" name="nome" id="projNome" required></div>
                            <div class="form-group"><label>Cliente</label><input type="text" name="cliente" id="projCliente"></div>
                            <div class="form-group"><label>Início</label><input type="date" name="data_inicio" id="projInicio"></div>
                            <div class="form-group"><label>Previsão Fim</label><input type="date" name="data_fim" id="projFim"></div>
                        </div>
                        
                        <div class="form-group">
                            <label>Status Atual</label>
                            <select name="status" id="projStatus" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                <option value="PLANEJADO">Planejado</option>
                                <option value="EM_ANDAMENTO">Em Andamento</option>
                                <option value="CONCLUIDO">Concluído</option>
                                <option value="CANCELADO">Cancelado</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Equipes Envolvidas</label>
                            <div class="custom-select-wrapper">
                                <div class="custom-select">
                                    <div class="custom-select__trigger" id="equipeTrigger">
                                        <span id="equipeTriggerText">Selecione as equipes...</span>
                                        <div class="arrow"></div>
                                    </div>
                                    <div class="custom-options">
                                        <?php foreach ($listaEquipes as $eq): ?>
                                            <span class="custom-option" data-value="<?= $eq['id'] ?>">
                                                <?= htmlspecialchars($eq['nome']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div id="hiddenEquipesInputs"></div>
                            </div>
                        </div>

                        <div class="form-group"><label>Descrição</label><textarea name="descricao" id="projDesc" rows="3" style="width:100%; border:1px solid #ddd; border-radius:8px; padding:10px;"></textarea></div>
                    </div>
                <?php endif; ?>

                <div id="tab-anexos" class="tab-panel">
                    <div class="form-group">
                        <label>Adicionar Arquivos Públicos</label>
                        <input type="file" name="docs_publicos[]" multiple class="campo-form" style="padding:10px;">
                        
                        <div id="arquivosAtuaisPublicos" class="arquivos-atuais-container"></div>
                    </div>
                    <div class="form-group">
                        <label>Links Externos (Atuais e Novos)</label>
                        <div id="containerLinksPublicos"></div>
                        <button type="button" onclick="addLinkInput('containerLinksPublicos')" class="btn-add-mini">+ Adicionar Link</button>
                    </div>
                </div>

                <?php if ($is_socio): ?>
                <div id="tab-privado" class="tab-panel">
                    <div class="form-group">
                        <label>Adicionar Documentos Confidenciais</label>
                        <input type="file" name="docs_privados[]" multiple class="campo-form">
                        
                        <div id="arquivosAtuaisPrivados" class="arquivos-atuais-container"></div>
                    </div>
                    <div class="form-group">
                        <label>Links Privados</label>
                        <div id="containerLinksPrivados"></div>
                        <button type="button" onclick="addLinkInput('containerLinksPrivados', true)" class="btn-add-mini">+ Link Privado</button>
                    </div>
                </div>
                <?php endif; ?>

                <div class="modal-footer">
                    <button type="button" class="botao-secundario" onclick="closeModal('modalProjeto')">Cancelar</button>
                    <button type="submit" class="botao-primario">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
                        
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/projetos.js"></script>
    <script src="../js/tarefas.js"></script> 
    <script>
        // Função JS para fechar modals, acessível globalmente pelos scripts externos
        window.closeModal = function(id) {
            const modal = document.getElementById(id);
            if (modal) modal.style.setProperty('display', 'none', 'important');
        };

        // Função JS para trocar as abas principais (Visão Geral, Equipe, etc.)
        function switchPageTab(tabName, btn) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            document.querySelectorAll('.page-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        // Lógica do Dropdown Multi-Select (Replicada e centralizada)
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.querySelector('.custom-select-wrapper');
            if (wrapper) {
                wrapper.addEventListener('click', function() {
                    this.querySelector('.custom-select').classList.toggle('open');
                });
            }

            document.querySelectorAll('.custom-option').forEach(option => {
                option.addEventListener('click', function() {
                    this.classList.toggle('selected');
                    atualizarInputsEquipe();
                });
            });
            
            // Troca de abas dentro do modal (Projeto)
            window.switchFormTab = function(tabName, btn) {
                document.querySelectorAll('#modalProjeto .tab-panel').forEach(c => c.classList.remove('active'));
                document.getElementById('tab-' + tabName).classList.add('active');
                document.querySelectorAll('#modalProjeto .modal-tab').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            };
        });

        // Função global para ser acessada pelo JS externo
        window.atualizarInputsEquipe = function() {
            const selected = document.querySelectorAll('.custom-option.selected');
            const containerHidden = document.getElementById('hiddenEquipesInputs');
            const triggerText = document.getElementById('equipeTriggerText');
            
            if(!containerHidden || !triggerText) return;

            containerHidden.innerHTML = '';
            let names = [];

            selected.forEach(opt => {
                const val = opt.getAttribute('data-value');
                names.push(opt.textContent.trim());
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'equipes[]';
                input.value = val;
                containerHidden.appendChild(input);
            });

            if (names.length > 0) {
                triggerText.textContent = names.join(', ');
                triggerText.style.color = '#333';
                triggerText.style.fontWeight = '600';
            } else {
                triggerText.textContent = 'Selecione as equipes...';
                triggerText.style.color = '#999';
                triggerText.style.fontWeight = '400';
            }
        }

        window.confirmarExclusaoTarefa = async function(id) {
            // Confirmação explícita para exclusão definitiva
            if (!confirm("ATENÇÃO: A exclusão definitiva apagará a tarefa e todos os seus comentários. Tem certeza?")) {
                return;
            }

            if (!id) {
                alert("ID da tarefa não fornecido.");
                return;
            }

            try {
                const resp = await fetch('../api/tarefa_excluir.php', { 
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded' 
                    },
                    body: new URLSearchParams({ // Envia os dados como form-data
                        id: id
                    })
                });

                const json = await resp.json();

                if (json.ok) {
                    alert(json.mensagem);
                    window.location.reload(); // Recarrega a página após o sucesso
                } else {
                    alert('Erro ao excluir: ' + (json.erro || "Erro ao processar a exclusão."));
                }
                
            } catch (err) {
                console.error("Erro na requisição fetch:", err);
                alert("Erro de conexão ou falha ao receber a resposta do servidor. Verifique o console para mais detalhes.");
            }
        }
    </script>
</body>
</html>