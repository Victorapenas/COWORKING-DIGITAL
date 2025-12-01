<?php
// ... INCLUDE DO HEADER E PHP INICIAL IGUAL ...
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';

proteger_pagina();
$id = (int)($_GET['id'] ?? 0);
$proj = getProjetoDetalhe($id);
if(!$proj) die("Projeto não encontrado.");

$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$is_socio = in_array($usuario['papel'], ['DONO', 'LIDER']);
$membrosProjeto = getMembrosDoProjeto($id);

$prazoHtml = '<span style="color:#999">Indefinido</span>';
if ($proj['data_fim']) {
    $hoje = new DateTime();
    $fim = new DateTime($proj['data_fim']);
    $diff = $hoje->diff($fim);
    if ($diff->invert) $prazoHtml = '<span style="color:#e74c3c; font-weight:bold;">Atrasado ' . $diff->days . ' dias</span>';
    else $prazoHtml = '<span style="color:#2ecc71; font-weight:bold;">' . $diff->days . ' dias restantes</span>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($proj['nome']) ?></title>
    <link rel="stylesheet" href="../css/painel.css">
    <style>
        /* ESTILO ESPECÍFICO PARA ESCONDER E MOSTRAR ABAS LIMPAS */
        .tab-content { display: none; padding: 30px; max-width: 1400px; margin: 0 auto; }
        .tab-content.active { display: block; } /* Use block para controle total, grid apenas interno */
        
        /* Layout Grid APENAS para a Visão Geral */
        .grid-overview { display: grid; grid-template-columns: 2.5fr 1fr; gap: 30px; width: 100%; }
        
        /* Layout para as outras abas (Full Width) */
        .full-layout { width: 100%; }

        /* Estilos auxiliares já existentes... */
        .war-room-header { background: white; padding: 25px 30px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .header-title h1 { margin: 0; font-size: 1.8rem; color: #2c3e50; font-weight: 800; }
        .header-meta { display: flex; gap: 20px; color: #666; font-size: 0.9rem; margin-top: 8px; }
        .page-tabs { display: flex; gap: 30px; border-bottom: 1px solid #e0e0e0; padding: 0 30px; background: white; }
        .page-tab { padding: 15px 0; cursor: pointer; color: #7f8c8d; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.2s; font-size: 0.95rem; }
        .page-tab:hover { color: #0d6efd; }
        .page-tab.active { color: #0d6efd; border-bottom-color: #0d6efd; }
        .kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .kpi-box { background: white; padding: 25px; border-radius: 12px; border: 1px solid #eee; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
        .kpi-box h3 { margin: 0; font-size: 2rem; color: #6A66FF; font-weight: 800; }
        .kpi-box p { margin: 5px 0 0 0; font-size: 0.75rem; color: #888; text-transform: uppercase; font-weight: 600; }
        .content-box { background: white; border-radius: 16px; border: 1px solid #eee; padding: 30px; margin-bottom: 30px; }
        .box-title { font-size: 1.1rem; font-weight: 700; color: #333; margin-bottom: 25px; border-bottom: 1px solid #f5f5f5; padding-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .file-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; }
        .file-card { background: white; border: 1px solid #eee; border-radius: 12px; padding: 20px; text-align: center; transition: 0.2s; text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center; height: 100%; position: relative; overflow: hidden; }
        .file-card:hover { border-color: #0d6efd; transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .file-icon { font-size: 1.5rem; margin-bottom: 15px; color: #0d6efd; background: #f0f7ff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .file-name { font-size: 0.9rem; font-weight: 600; color: #333; line-height: 1.4; word-break: break-word; }
        .file-card.restricted { border-color: #ffebee; background: #fffafa; }
        .file-card.restricted .file-icon { background: #ffebee; color: #e74c3c; }
        .file-card.restricted:hover { border-color: #e74c3c; }
        .team-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .team-card { background: #f8f9fa; border: 1px solid #eee; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; transition: 0.2s; }
        .team-avatar { width: 50px; height: 50px; border-radius: 50%; background: #e3f2fd; color: #0d6efd; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; flex-shrink: 0; }
        .team-info h4 { margin: 0; font-size: 1rem; color: #333; }
        .task-row { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #f9f9f9; }
        .priority-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 10px; }
        .p-URGENTE { background: #e74c3c; box-shadow: 0 0 8px rgba(231, 76, 60, 0.4); }
        .p-NORMAL { background: #3498db; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box"><?= getIcone('arquivo') ?> <h3 style="color:#0d6efd; margin-left:10px">Coworking</h3></div>
        <?php renderizar_sidebar(); ?>
    </div>

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
            <div style="text-align:right;">
                <div style="font-size:2rem; font-weight:800; color:#333;"><?= $proj['progresso'] ?>%</div>
                <div style="font-size:0.8rem; color:#888;">Conclusão</div>
                <div style="width:150px; height:6px; background:#eee; border-radius:3px; margin-top:5px; overflow:hidden;">
                    <div style="width:<?= $proj['progresso'] ?>%; background: linear-gradient(90deg, #6A66FF, #0d6efd); height:100%;"></div>
                </div>
            </div>
        </div>

        <div class="page-tabs">
            <div class="page-tab active" onclick="switchPageTab('visao_geral', this)">Visão Geral</div>
            <div class="page-tab" onclick="switchPageTab('equipe', this)">Equipe & Progresso</div>
            <div class="page-tab" onclick="switchPageTab('arquivos', this)">Arquivos & Links</div>
            <?php if($is_socio): ?>
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
                        <div class="box-title"><?= getIcone('task') ?> Cronograma de Atividades</div>
                        <?php if(empty($proj['tarefas_lista'])): ?>
                            <div style="text-align:center; padding:40px; color:#999; background:#f9f9f9; border-radius:8px;">Nenhuma tarefa cadastrada.</div>
                        <?php else: foreach($proj['tarefas_lista'] as $task): ?>
                            <div class="task-row">
                                <div style="display:flex; align-items:center;">
                                    <div class="priority-dot p-<?= $task['prioridade']??'NORMAL' ?>"></div>
                                    <div>
                                        <div style="font-weight:600; color:#333; font-size:0.95rem;"><?= htmlspecialchars($task['titulo']) ?></div>
                                        <div style="font-size:0.8rem; color:#888;">Resp: <?= htmlspecialchars($task['responsavel_nome']) ?></div>
                                    </div>
                                </div>
                                <span style="font-size:0.7rem; padding:4px 10px; background:#f0f0f0; border-radius:4px; font-weight:700; text-transform:uppercase;"><?= str_replace('_', ' ', $task['status']) ?></span>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <div class="side-panel">
                    <div class="content-box">
                        <div class="box-title"><?= getIcone('olho') ?> Detalhes</div>
                        <p style="font-size:0.9rem; line-height:1.6; color:#555;"><?= nl2br(htmlspecialchars($proj['descricao'] ?: 'Sem descrição.')) ?></p>
                        <div style="margin-top:20px;">
                            <span style="font-size:0.75rem; color:#999; font-weight:700; display:block; margin-bottom:5px;">GESTOR</span>
                            <div style="display:flex; align-items:center; gap:8px; font-weight:600; color:#333;">
                                <div style="width:24px; height:24px; background:#6A66FF; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><?= strtoupper(substr($proj['nome_gestor']??'U',0,1)) ?></div>
                                <?= htmlspecialchars($proj['nome_gestor'] ?? 'Não definido') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-equipe" class="tab-content full-layout">
            <div class="content-box">
                <div class="box-title"><?= getIcone('users') ?> Membros Envolvidos & Desempenho</div>
                <?php if(empty($membrosProjeto)): ?>
                    <p style="text-align:center; color:#999;">Nenhum membro vinculado.</p>
                <?php else: ?>
                    <div class="team-grid">
                        <?php foreach($membrosProjeto as $mem): $iniciais = strtoupper(substr($mem['nome'], 0, 2)); ?>
                        <div class="team-card">
                            <div class="team-avatar"><?= $iniciais ?></div>
                            <div class="team-info">
                                <h4><?= htmlspecialchars($mem['nome']) ?></h4>
                                <p><?= htmlspecialchars($mem['cargo_detalhe'] ?: 'Colaborador') ?></p>
                                <div class="team-stats">
                                    <span><?= $mem['tarefas_feitas'] ?> Entregas</span>
                                    <span><?= $mem['tarefas_total'] - $mem['tarefas_feitas'] ?> Pendentes</span>
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
                <div class="box-title"><?= getIcone('pasta') ?> Arquivos e Links Públicos</div>
                
                <div class="file-grid"> <?php if(empty($proj['links'])): ?>
                        <p style="color:#999; grid-column:1/-1; text-align:center; padding: 20px;">Nenhum arquivo público compartilhado.</p>
                    <?php else: foreach($proj['links'] as $arq): 
                        if($arq['tipo'] === 'logo') continue; 
                        $icone = ($arq['tipo'] === 'link') ? getIcone('link') : getIcone('documento'); 
                    ?>
                        <a href="<?= $arq['url'] ?>" target="_blank" class="file-card-item">
                            <div class="file-icon-circle"><?= $icone ?></div>
                            <div class="file-info">
                                <h4><?= htmlspecialchars($arq['titulo']) ?></h4>
                                <span class="file-type"><?= ($arq['tipo']==='link') ? 'Link Externo' : 'Arquivo' ?></span>
                            </div>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <?php if($is_socio): ?>
        <div id="tab-restrito" class="tab-content full-layout">
            <div class="content-box" style="border-color:#ffcdd2;">
                <div class="box-title" style="color:#c62828;"><?= getIcone('cadeado') ?> Área Restrita (Confidencial)</div>
                
                <div class="file-grid">
                    <?php if(empty($proj['privados'])): ?>
                        <p style="color:#999; grid-column:1/-1; text-align:center; padding: 20px;">Nenhum documento confidencial.</p>
                    <?php else: foreach($proj['privados'] as $arq): 
                        $icone = ($arq['tipo'] === 'link') ? getIcone('link') : getIcone('documento'); 
                    ?>
                        <a href="<?= $arq['url'] ?>" target="_blank" class="file-card-item restricted">
                            <div class="file-icon-circle"><?= $icone ?></div>
                            <div class="file-info">
                                <h4><?= htmlspecialchars($arq['titulo']) ?></h4>
                                <span class="file-type">Confidencial</span>
                            </div>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function switchPageTab(tabName, btn) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById('tab-'+tabName).classList.add('active');
            document.querySelectorAll('.page-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
    </script>
</body>
</html>