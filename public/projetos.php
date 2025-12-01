<?php
// ARQUIVO: public/projetos.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';

proteger_pagina();
$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$empresaId = getEmpresaIdLogado($usuario);
$papel = $usuario['papel'];
$pode_editar = in_array($papel, ['DONO', 'LIDER', 'GESTOR']);
$is_socio = in_array($papel, ['DONO', 'LIDER']);

$projetosAtivos = getProjetos($empresaId, true);
$projetosArquivados = getProjetos($empresaId, false);
$listaEquipes = listarEquipes($empresaId);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Projetos</title>
    <link rel="stylesheet" href="../css/painel.css">
    <style>
        /* Card Melhorado e Cores de Status mantidos */
        .project-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        .proj-card { 
            background: white; border: 1px solid #f0f0f0; border-radius: 16px; padding: 25px; 
            transition: all 0.2s ease; position: relative; overflow: hidden; cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;
        }
        .proj-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: #6A66FF; }
        .client-logo-bg { position: absolute; top: -15px; right: -15px; width: 100px; height: 100px; opacity: 0.04; transform: rotate(15deg); pointer-events: none; }
        .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        .st-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; width: fit-content; }
        .st-PLANEJADO { background: #f3f3f3; color: #777; border: 1px solid #e0e0e0; }
        .st-PLANEJADO .status-dot { background: #bbb; }
        .st-EM_ANDAMENTO { background: #e3f2fd; color: #0d6efd; border: 1px solid #bbdefb; }
        .st-EM_ANDAMENTO .status-dot { background: #0d6efd; }
        .st-CONCLUIDO { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .st-CONCLUIDO .status-dot { background: #2ecc71; }
        .st-CANCELADO { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .st-CANCELADO .status-dot { background: #e74c3c; }
        .proj-actions { 
            display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; padding-top: 15px; border-top: 1px dashed #eee; 
            opacity: 0; transition: 0.3s; 
        }
        .proj-card:hover .proj-actions { opacity: 1; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box"><?= getIcone('arquivo') ?> <h3 style="color:#0d6efd; margin-left:10px;">Coworking</h3></div>
        <?php renderizar_sidebar(); ?>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="search-box"><input type="text" id="searchBox" placeholder="Pesquisar projetos..." onkeyup="filtrarProjetos()"></div>
            <div class="profile"><div class="avatar-profile"><?= strtoupper(substr($usuario['nome'], 0, 2)) ?></div></div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:end; margin-bottom: 30px;">
            <div>
                <h1 style="font-size: 2rem; color: #2c3e50; font-weight:800; margin:0;">Projetos</h1>
                <p style="color: #7f8c8d; margin-top:5px;">Gerencie o ciclo de vida, arquivos e entregas.</p>
            </div>
            <?php if ($pode_editar): ?>
                <button class="botao-primario" onclick="openModal()">+ Novo Projeto</button>
            <?php endif; ?>
        </div>

        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchMainTab('ativos', this)">Em Andamento</button>
            <button class="tab-btn" onclick="switchMainTab('arquivados', this)">Arquivados</button>
        </div>

        <div id="view-ativos" class="main-tab-content">
            <div class="project-grid">
                <?php if (empty($projetosAtivos)): ?>
                    <div class="empty-state" style="grid-column: 1/-1; text-align:center; padding:60px; color:#999; border:2px dashed #eee; border-radius:16px;">
                        <div style="font-size:3rem; margin-bottom:15px; opacity:0.3;">📂</div>
                        <p>Nenhum projeto ativo. Crie o primeiro agora!</p>
                    </div>
                <?php else: foreach ($projetosAtivos as $p): echo renderizarCardProjeto($p); endforeach; endif; ?>
            </div>
        </div>

        <div id="view-arquivados" class="main-tab-content" style="display:none;">
            <div class="project-grid">
                <?php if (empty($projetosArquivados)): ?>
                    <p style="color:#aaa; padding:20px;">Lixeira vazia.</p>
                <?php else: foreach ($projetosArquivados as $p): echo renderizarCardProjeto($p); endforeach; endif; ?>
            </div>
        </div>
    </div>

    <div id="modalProjeto" class="modal">
        <div class="modal-content" style="width: 700px;">
            <h3 id="modalTitle">Novo Projeto</h3>
            <div class="modal-tabs">
                <div class="modal-tab active" onclick="switchFormTab('info', this)">Informações</div>
                <div class="modal-tab" onclick="switchFormTab('anexos', this)">Anexos e Links</div>
                <?php if($is_socio): ?>
                <div class="modal-tab" onclick="switchFormTab('privado', this)" style="color:#6A66FF;"><?= getIcone('cadeado') ?> Área do Sócio</div>
                <?php endif; ?>
            </div>

            <form id="formCriarProjeto" enctype="multipart/form-data">
                <input type="hidden" name="id" id="projId"> 
                
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

                    <div class="form-group">
                        <label>Logo do Cliente (Opcional)</label>
                        <div class="file-upload-zone" onclick="document.getElementById('logo_input').click()">
                            <?= getIcone('imagem') ?> <br> Clique para enviar a logo
                        </div>
                        <input type="file" id="logo_input" name="logo_cliente" accept="image/*" style="display:none;" onchange="previewFile(this)">
                        <div id="logo_preview" style="font-size:0.8rem; margin-top:5px; color:#0d6efd;"></div>
                    </div>
                    <div class="form-group"><label>Descrição</label><textarea name="descricao" id="projDesc" rows="3" style="width:100%; border:1px solid #ddd; border-radius:8px; padding:10px;"></textarea></div>
                </div>

                <div id="tab-anexos" class="tab-panel">
                    <div class="form-group">
                        <label>Arquivos Públicos</label>
                        <input type="file" name="docs_publicos[]" multiple class="campo-form" style="padding:10px;">
                    </div>
                    <div class="form-group">
                        <label>Links Externos</label>
                        <div id="containerLinksPublicos"></div>
                        <button type="button" onclick="addLinkInput('containerLinksPublicos')" class="btn-add-mini">+ Adicionar Link</button>
                    </div>
                </div>

                <?php if($is_socio): ?>
                <div id="tab-privado" class="tab-panel">
                    <div class="form-group"><label>Contratos / Documentos Confidenciais</label><input type="file" name="docs_privados[]" multiple class="campo-form"></div>
                    <div class="form-group"><label>Links Privados</label><div id="containerLinksPrivados"></div><button type="button" onclick="addLinkInput('containerLinksPrivados', true)" class="btn-add-mini">+ Link Privado</button></div>
                </div>
                <?php endif; ?>

                <div class="modal-footer">
                    <button type="button" class="botao-secundario" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="botao-primario">Salvar Projeto</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalExcluir" class="modal">
        <div class="modal-content" style="width: 450px; text-align:center;">
            <div style="margin:0 auto 15px auto; width:60px; height:60px; background:#ffebee; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#c62828;">
                <?= getIcone('lixo') ?>
            </div>
            <h3>Gerenciar Projeto</h3>
            <p id="msgExcluir" style="color:#666; font-size:0.9rem; margin-bottom:20px;">O que deseja fazer com este projeto?</p>
            <input type="hidden" id="idProjetoExcluir">
            
            <div class="modal-delete-options" style="display:flex; flex-direction:column; gap:10px;">
                <button id="btnSoftDelete" class="btn-archive" onclick="confirmarAcaoProjeto(null, 'soft')" style="padding:15px; background:#fff3e0; color:#e65100; border:1px solid #ffe0b2; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:10px;">
                    <?= getIcone('pasta') ?> 
                    <div style="text-align:left;"><strong>Arquivar Projeto</strong><br><span style="font-size:0.7rem; font-weight:normal;">Mover para aba Arquivados. Pode ser restaurado.</span></div>
                </button>

                <button id="btnHardDelete" class="btn-delete-hard" onclick="confirmarAcaoProjeto(null, 'hard')" style="padding:15px; background:#ffebee; color:#c62828; border:1px solid #ffcdd2; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:10px;">
                    <?= getIcone('lixo') ?>
                    <div style="text-align:left;"><strong>Excluir Definitivamente</strong><br><span style="font-size:0.7rem; font-weight:normal;">Apaga tudo do banco. Irreversível.</span></div>
                </button>
            </div>
            <button onclick="fecharModalExcluir()" style="margin-top:20px; background:none; border:none; color:#888; cursor:pointer;">Cancelar</button>
        </div>
    </div>

    <script src="../js/projetos.js"></script>
    <script>
        // LOGICA DO MULTI-SELECT DROPDOWN
        document.querySelector('.custom-select-wrapper').addEventListener('click', function() {
            this.querySelector('.custom-select').classList.toggle('open');
        });

        document.querySelectorAll('.custom-option').forEach(option => {
            option.addEventListener('click', function() {
                this.classList.toggle('selected');
                atualizarInputsEquipe();
            });
        });

        function atualizarInputsEquipe() {
            const selected = document.querySelectorAll('.custom-option.selected');
            const containerHidden = document.getElementById('hiddenEquipesInputs');
            const triggerText = document.getElementById('equipeTriggerText');
            
            containerHidden.innerHTML = '';
            let names = [];

            selected.forEach(opt => {
                const val = opt.getAttribute('data-value');
                names.push(opt.textContent.trim());
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'equipes[]'; // Array pro PHP
                input.value = val;
                containerHidden.appendChild(input);
            });

            if (names.length > 0) {
                triggerText.textContent = names.join(', ');
                triggerText.style.color = '#333';
                triggerText.style.fontWeight = '600';
            } else {
                triggerText.textContent = 'Selecione as equipes...';
                triggerText.style.color = '#333';
                triggerText.style.fontWeight = '400';
            }
        }

        // Funções UI normais
        function switchMainTab(tab, btn) {
            document.querySelectorAll('.main-tab-content').forEach(d => d.style.display = 'none');
            document.getElementById('view-'+tab).style.display = 'block';
            document.querySelectorAll('.tabs-header .tab-btn').forEach(b => b.classList.remove('active'));
            if(btn) btn.classList.add('active');
        }
        function switchFormTab(tab, btn) {
            document.querySelectorAll('#formCriarProjeto .tab-panel').forEach(p => p.classList.remove('active'));
            document.getElementById('tab-'+tab).classList.add('active');
            document.querySelectorAll('#formCriarProjeto .modal-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        function previewFile(input) {
            if(input.files && input.files[0]) document.getElementById('logo_preview').innerText = "Arquivo: " + input.files[0].name;
        }
        function filtrarProjetos() {
            const term = document.getElementById('searchBox').value.toLowerCase();
            document.querySelectorAll('.proj-card').forEach(card => card.style.display = card.innerText.toLowerCase().includes(term) ? 'flex' : 'none');
        }
    </script>
</body>
</html>

<?php
function renderizarCardProjeto($p) {
    // CORREÇÃO DO ERRO DO ARRAY: Extrair nomes antes de imprimir
    $teamsArr = [];
    if (!empty($p['equipes']) && is_array($p['equipes'])) {
        foreach($p['equipes'] as $eq) {
            // Se for array ['id'=>x, 'nome'=>'Y'], pega o nome. Se for string, usa string.
            $teamsArr[] = is_array($eq) ? ($eq['nome'] ?? '') : $eq;
        }
    }
    $teams = !empty($teamsArr) ? implode(', ', $teamsArr) : 'Sem equipe';

    // Status e Labels
    $statusLabel = str_replace('_', ' ', $p['status']);
    $statusClass = 'st-'.$p['status'];
    $logoHtml = !empty($p['logo_url']) ? '<div class="client-logo-bg"><img src="'.$p['logo_url'].'"></div>' : '';
    $jsonProjeto = htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8');

    // Botões
    $botoes = '<a href="projeto_detalhes.php?id='.$p['id'].'" class="btn-round view" title="Ver Detalhes">'.getIcone('olho').'</a>';
    if ($p['ativo'] == 1) {
        $botoes .= '<button onclick="event.stopPropagation(); abrirModalEditarProjeto('.$jsonProjeto.')" class="btn-round edit" title="Editar">'.getIcone('editar').'</button>';
        $botoes .= '<button onclick="event.stopPropagation(); abrirModalAcao('.$p['id'].', \''.addslashes($p['nome']).'\', \'active_context\')" class="btn-round delete" title="Arquivar">'.getIcone('lixo').'</button>';
    } else {
        $botoes .= '<button onclick="event.stopPropagation(); confirmarAcaoProjeto('.$p['id'].', \'restore\')" class="btn-round restore" title="Restaurar">'.getIcone('restaurar').'</button>';
        $botoes .= '<button onclick="event.stopPropagation(); confirmarAcaoProjeto('.$p['id'].', \'hard\')" class="btn-round delete" title="Excluir">'.getIcone('lixo').'</button>';
    }

    return '
    <div class="proj-card" ondblclick="window.location.href=\'projeto_detalhes.php?id='.$p['id'].'\'">
        '.$logoHtml.'
        <div class="proj-header"><span class="st-badge '.$statusClass.'"><div class="status-dot"></div> '.$statusLabel.'</span></div>
        <div style="margin-top:10px;">
            <div style="font-size:0.8rem; color:#888; text-transform:uppercase;">'.htmlspecialchars($p['cliente_nome']?:'Interno').'</div>
            <h3 style="font-size:1.4rem; color:#333; margin:5px 0; font-weight:700;">'.htmlspecialchars($p['nome']).'</h3>
            <p style="font-size:0.9rem; color:#666; height:40px; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">'.htmlspecialchars($p['descricao']?:'Sem descrição').'</p>
        </div>
        <div style="margin-top:20px;">
            <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:#555; margin-bottom:5px;"><span>Progresso</span><strong>'.$p['progresso'].'%</strong></div>
            <div style="height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden;"><div style="height:100%; background:linear-gradient(90deg, #6A66FF, #0d6efd); width:'.$p['progresso'].'%"></div></div>
            <div style="margin-top:10px; font-size:0.8rem; color:#888; display:flex; gap:5px; align-items:center;">'.getIcone('users').' '.$teams.'</div>
        </div>
        <div class="proj-actions">'.$botoes.'</div>
    </div>';
}
?>