<?php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';

proteger_pagina();
$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$empresaId = getEmpresaIdLogado($usuario);

$lideranca = getLideranca($empresaId);
$equipes = getEquipesDetalhadas($empresaId);
$stats = getStatsGeral($empresaId);
$listaEquipesSimples = listarEquipes($empresaId);
$membrosDisponiveis = getMembrosDisponiveis($empresaId);

$gestores = getMembrosFiltrados($empresaId, 'GESTOR');
$colaboradores = getMembrosFiltrados($empresaId, 'COLABORADOR');
$arquivados = getMembrosFiltrados($empresaId, 'ARQUIVADO');

$papel = $usuario['papel'];
$is_dono = ($papel === 'DONO' || $papel === 'LIDER');
$pode_editar = ($is_dono || $papel === 'GESTOR');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Equipes</title>
    <link rel="stylesheet" href="../css/painel.css">
    <style>
        /* Estilos específicos desta página que não são globais */
        .section-content { display:none; animation: fadeIn 0.3s ease-in-out; } 
        .section-content.active { display:block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        .role-pill { padding: 4px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-left: 8px; vertical-align: middle; }
        .bg-lider { background-color: #f3e5f5; color: #7b1fa2; border: 1px solid #e1bee7; }
        .bg-gestor { background-color: #fff8e1; color: #ffa000; border: 1px solid #ffecb3; }
        .bg-colab { background-color: #e3f2fd; color: #1976d2; border: 1px solid #bbdefb; }
        .bg-arquivado { background-color: #eee; color: #999; }
        
        .btn-add-socio { background: white; border: 1px solid #ddd; color: #333; padding: 5px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; transition: 0.2s; }
        .btn-add-socio:hover { background: #f5f5f5; border-color: #bbb; }

        .empty-state-card { padding: 30px; text-align: center; border: 2px dashed #e0e0e0; border-radius: 12px; color: #999; background-color: #fcfcfc; margin: 10px 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; }
        .empty-state-icon { font-size: 2rem; color: #ccc; margin-bottom: 5px; }

        /* Estilo local para o cabeçalho dos modais internos */
        .modal-tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #eee; }
        .modal-tab { padding: 10px 15px; cursor: pointer; color: #666; font-weight: 600; border-bottom: 2px solid transparent; }
        .modal-tab.active { color: #0d6efd; border-bottom-color: #0d6efd; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
    </style>
</head>
<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <div>
                <h1 style="font-size:1.8rem; font-weight:800; color:#2c3e50; margin:0;">Gerenciamento de Equipes</h1>
                <p style="color:#7f8c8d; font-size:0.9rem; margin-top:5px;">Gerencie membros, funções e níveis de acesso.</p>
            </div>
            <?php if($pode_editar): ?>
                <button class="botao-primario" onclick="abrirModalEquipe()">
                    <span style="font-size:1.1rem; margin-right:5px;">+</span> Nova Equipe
                </button>
            <?php endif; ?>
        </div>

        <div class="dashboard-cards">
            <div class="card-info"><div class="icon-box c-purple"><?= getIcone('users') ?></div><div class="kpi-text"><h3><?= $stats['total_membros'] ?></h3><p>Membros</p></div></div>
            <div class="card-info"><div class="icon-box c-green"><?= getIcone('online') ?></div><div class="kpi-text"><h3><?= $stats['online'] ?></h3><p>Online</p></div></div>
            <div class="card-info"><div class="icon-box c-orange"><?= getIcone('clock') ?></div><div class="kpi-text"><h3><?= $stats['ativas'] ?></h3><p>Ativas</p></div></div>
            <div class="card-info"><div class="icon-box c-blue"><?= getIcone('check') ?></div><div class="kpi-text"><h3><?= $stats['concluidas'] ?></h3><p>Feitas</p></div></div>
        </div>

        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchTab('equipes', this)">Visão por Equipes</button>
            <button class="tab-btn" onclick="switchTab('gestores', this)">Gestores</button>
            <button class="tab-btn" onclick="switchTab('colaboradores', this)">Colaboradores</button>
            <button class="tab-btn" onclick="switchTab('arquivados', this)">Arquivados</button>
        </div>

        <div id="view-equipes" class="section-content active">
            <?php if(!empty($lideranca)): ?>
            <div class="team-section" style="border-left:4px solid #6A66FF;">
                <div class="team-header">
                    <div>
                        <h3 class="team-title" style="color:#6A66FF">Liderança Executiva</h3>
                        <span class="team-desc">Acesso Administrativo Total</span>
                    </div>
                    <?php if($is_dono): ?>
                        <button class="btn-add-socio" onclick="abrirModalMembro(true, 'DONO', false)">+ Adicionar Sócio</button>
                    <?php endif; ?>
                </div>
                <?php foreach($lideranca as $l): echo renderizarMembroCard($l, $is_dono, 'LIDERANCA'); endforeach; ?>
            </div>
            <?php endif; ?>

            <?php foreach($equipes as $grp): ?>
            <div class="team-section">
                <div class="team-header">
                    <div>
                        <h3 class="team-title"><?= htmlspecialchars($grp['info']['nome']) ?></h3>
                        <span class="team-desc"><?= htmlspecialchars($grp['info']['descricao'] ?: 'Equipe de trabalho') ?></span>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <?php if($pode_editar): ?>
                            <button class="botao-equipe" onclick="abrirModalMembroComEquipe(<?= $grp['info']['id'] ?>)">+ Membro</button>
                            
                            <?php if(strtolower($grp['info']['nome']) !== 'geral'): ?>
                                <button class="btn-icon del" onclick="confirmarExclusaoEquipe(<?= $grp['info']['id'] ?>, '<?= addslashes($grp['info']['nome']) ?>')"><?= getIcone('lixo') ?></button>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    if(empty($grp['membros'])) {
                        echo '<div class="empty-state-card"><div class="empty-state-icon">'.getIcone('users').'</div><strong>Equipe vazia</strong><span style="font-size:0.85rem">Adicione membros para começar.</span></div>';
                    } else {
                        foreach($grp['membros'] as $m) echo renderizarMembroCard($m, $pode_editar, 'EQUIPE'); 
                    }
                ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div id="view-gestores" class="section-content">
            <div style="text-align:right; margin-bottom:15px;"><?php if($pode_editar): ?><button class="botao-primario" onclick="abrirModalMembro(false, 'GESTOR', false)">+ Novo Gestor</button><?php endif; ?></div>
            <div class="team-section"><?php if(empty($gestores)) echo '<div class="empty-state-card">Nenhum gestor encontrado.</div>'; foreach($gestores as $m) echo renderizarMembroCard($m, $pode_editar, 'NORMAL'); ?></div>
        </div>
        
        <div id="view-colaboradores" class="section-content">
            <div style="text-align:right; margin-bottom:15px;"><?php if($pode_editar): ?><button class="botao-primario" onclick="abrirModalMembro(false, 'FUNCIONARIO', false)">+ Novo Colaborador</button><?php endif; ?></div>
            <div class="team-section"><?php if(empty($colaboradores)) echo '<div class="empty-state-card">Nenhum colaborador encontrado.</div>'; foreach($colaboradores as $m) echo renderizarMembroCard($m, $pode_editar, 'NORMAL'); ?></div>
        </div>
        
        <div id="view-arquivados" class="section-content">
            <div class="team-section" style="background:#f9f9f9; border: 1px dashed #ddd;">
                <h3 style="margin:0 0 20px 0; color:#999;">Usuários Desativados</h3>
                <?php if(empty($arquivados)) echo '<div class="empty-state-card">Lixeira vazia.</div>'; foreach($arquivados as $m) echo renderizarMembroCard($m, $is_dono, 'ARQUIVADO'); ?>
            </div>
        </div>

        <div class="bottom-bar">
            <div class="prog-geral">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="font-weight:600; color:#444;">Taxa de Conclusão Global</span>
                    <span style="font-weight:bold; color:#0d6efd;"><?= $stats['progresso_geral'] ?>%</span>
                </div>
                <div class="prog-bar-bg" style="height:8px; background:#f0f2f5; border-radius:4px; overflow:hidden;">
                    <div style="height:100%; background:#0d6efd; width:<?= $stats['progresso_geral'] ?>%; transition: width 0.5s;"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalExcluirEquipe" class="modal">
        <div class="modal-content" style="text-align:center; width:400px;">
            <h3>Excluir Equipe</h3>
            <p id="msgDelEq" style="color:#666; margin-bottom:20px;"></p>
            <input type="hidden" id="idEquipeDel">
            <p style="font-size:0.85rem; color:#888; background:#f5f5f5; padding:10px; border-radius:5px;">
                <strong>Nota:</strong> Os membros desta equipe NÃO serão excluídos. Eles serão movidos para "Geral" (Sem Equipe).
            </p>
            <input type="hidden" id="checkDelMembros" value="false"> 
            <div class="modal-footer" style="justify-content:center; margin-top:20px;">
                <button class="botao-secundario" onclick="fecharModais()">Cancelar</button>
                <button class="botao-primario" style="background:#e74c3c;" onclick="confirmarDelEquipe()">Excluir Equipe</button>
            </div>
        </div>
    </div>
    
    <div id="modalAddMembro" class="modal">
        <div class="modal-content" style="width: 500px;">
            <h3>Adicionar à Equipe</h3>
            <div class="modal-tabs" id="tabsHeaderMembro">
                <div class="modal-tab active" id="tabBtnNovo" onclick="switchModalTab('novo')">Novo Cadastro</div>
                <div class="modal-tab" id="tabBtnExistente" onclick="switchModalTab('existente')">Membro Existente</div>
            </div>
            <div id="tab-novo" class="tab-panel active">
                <form id="formAddMember">
                    <div class="form-group"><label>Nome Completo</label><input type="text" id="novo_nome" required></div>
                    <div class="form-group"><label>E-mail Corporativo</label><input type="email" id="novo_email" required></div>
                    <div class="form-group"><label>Cargo / Função</label><input type="text" id="novo_cargo" placeholder="Ex: Desenvolvedor Senior" required></div>
                    <div class="form-group">
                        <label>Equipe Destino</label>
                        <select id="select_equipe_membro" class="campo-padrao">                            <option value="">Sem equipe (Geral)</option>
                            <?php foreach($listaEquipesSimples as $eq):?>
                                <option value="<?=$eq['id']?>"><?=$eq['nome']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nível de Acesso</label>
                        <div class="radio-group" style="flex-wrap:wrap;">
                            <label id="lblSocio" style="display:none; color:#6A66FF; font-weight:bold;"><input type="radio" name="nivel_acesso" value="DONO"> Sócio/Dono</label>
                            <label><input type="radio" name="nivel_acesso" value="GESTOR"> Gestor</label>
                            <label><input type="radio" name="nivel_acesso" value="FUNCIONARIO" checked> Colaborador</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="botao-secundario" onclick="fecharModais()">Cancelar</button>
                        <button type="submit" class="botao-primario">Salvar Cadastro</button>
                    </div>
                </form>
            </div>
            <div id="tab-existente" class="tab-panel">
                <form id="formMoveMember">
                    <div class="form-group">
                        <label>Selecione o Colaborador (Apenas Ativos)</label>
<select id="select_membro_existente" required class="campo-padrao">                            <option value="">-- Escolha um usuário --</option>
                            <?php foreach($membrosDisponiveis as $md): 
                                $statusEq = $md['nome_equipe_atual'] ? "(Em: {$md['nome_equipe_atual']})" : "(Sem Equipe)";
                            ?>
                                <option value="<?=$md['id']?>"><?= htmlspecialchars($md['nome']) ?> <?= $statusEq ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p style="font-size:0.8rem; color:#888; margin-top:5px;">Este usuário será movido para a equipe selecionada abaixo.</p>
                    </div>
                    <div class="form-group">
                        <label>Mover para Equipe</label>
                        <select id="select_equipe_destino_move" style="width:100%; padding:10px; border-radius:5px; border:1px solid #ddd;">
                            <option value="">Sem equipe (Remover da atual)</option>
                            <?php foreach($listaEquipesSimples as $eq):?>
                                <option value="<?=$eq['id']?>"><?=$eq['nome']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="botao-secundario" onclick="fecharModais()">Cancelar</button>
                        <button type="submit" class="botao-primario">Mover Membro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEditarMembro" class="modal">
        <div class="modal-content">
            <h3>Editar Membro</h3>
            <form id="formEditarMembro">
                <input type="hidden" id="edit_id">
                <div class="form-group"><label>Nome</label><input type="text" id="edit_nome" required></div>
                <div class="form-group"><label>E-mail</label><input type="email" id="edit_email" required></div>
                <div class="form-group"><label>Cargo</label><input type="text" id="edit_cargo" required></div>
                <div class="form-group">
                    <label>Nível</label>
                    <div id="containerRadiosEditar" class="radio-group">
                        <label><input type="radio" name="edit_papel" value="GESTOR"> Gestor</label>
                        <label><input type="radio" name="edit_papel" value="FUNCIONARIO"> Colaborador</label>
                    </div>
                    <div id="msgDonoFixo" style="display:none; padding:10px; background:#f3e5f5; color:#7b1fa2; border-radius:5px; font-weight:bold; font-size:0.9rem;">
                        <?= getIcone('coroa') ?> Sócio/Dono (Acesso Total Fixo)
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="botao-secundario" onclick="fecharModais()">Cancelar</button>
                    <button type="submit" class="botao-primario">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalExcluirMembro" class="modal">
        <div class="modal-content" style="text-align:center; width:450px;">
            <div style="margin:0 auto 20px; width:60px; height:60px; background:#ffebee; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#d32f2f;">
                <?= getIcone('lixo') ?>
            </div>
            <h3>Gerenciar Membro</h3>
            <p id="msgExcluirMem" style="color:#666; margin-bottom:20px; font-weight:bold;"></p>
            <p style="font-size:0.9rem; color:#888; margin-bottom:20px;">O que deseja fazer com este usuário?</p>
            <input type="hidden" id="idMemDel">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button class="botao-secundario" style="background:#fff3e0; color:#e65100; border:1px solid #ffe0b2; padding:12px; display:flex; align-items:center; justify-content:center; gap:10px;" onclick="confirmarExclusaoMembro('soft')">
                    <?= getIcone('pasta') ?> 
                    <div style="text-align:left;">
                        <strong>Arquivar (Desativar)</strong><br><span style="font-size:0.75rem">Remove o acesso, mas mantém o histórico.</span>
                    </div>
                </button>
                <button class="botao-secundario" style="background:#ffebee; color:#c62828; border:1px solid #ffcdd2; padding:12px; display:flex; align-items:center; justify-content:center; gap:10px;" onclick="confirmarExclusaoMembro('hard')">
                    <?= getIcone('lixo') ?>
                    <div style="text-align:left;">
                        <strong>Excluir Definitivamente</strong><br><span style="font-size:0.75rem">Apaga tudo do banco. Irreversível.</span>
                    </div>
                </button>
            </div>
            <button onclick="fecharModais()" style="margin-top:20px; background:none; border:none; cursor:pointer; color:#888;">Cancelar</button>
        </div>
    </div>

    <div id="modalCriarEquipe" class="modal">
        <div class="modal-content">
            <h3>Nova Equipe</h3>
            <form id="formCriarEquipe">
                <div class="form-group"><label>Nome da Equipe</label><input type="text" id="nome_nova_equipe" placeholder="Ex: Marketing" required></div>
                <div class="form-group"><label>Descrição Curta</label><input type="text" id="desc_nova_equipe" placeholder="Ex: Criação e campanhas"></div>
                <div class="modal-footer">
                    <button type="button" class="botao-secundario" onclick="fecharModais()">Cancelar</button>
                    <button type="submit" class="botao-primario">Criar Equipe</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/equipes.js"></script>
    <script>
        function switchTab(v,b){
            document.querySelectorAll('.section-content').forEach(d=>d.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(x=>x.classList.remove('active'));
            document.getElementById('view-'+v).classList.add('active');
            b.classList.add('active');
        }
        function switchModalTab(tab) {
            document.querySelectorAll('.modal-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            document.getElementById('tab-'+tab).classList.add('active');
            event.target.classList.add('active');
        }
        function abrirModalMembro(isSocio, papelDefault = 'FUNCIONARIO', mostrarAbaExistente = false){
            document.getElementById('modalAddMembro').style.display='flex'; 
            const tabHeader = document.getElementById('tabBtnExistente');
            if(mostrarAbaExistente) { tabHeader.style.display = 'block'; } 
            else { tabHeader.style.display = 'none'; switchModalTab('novo'); }
            document.getElementById('select_equipe_membro').disabled = false;
            document.getElementById('select_equipe_membro').value = "";
            document.getElementById('select_equipe_destino_move').disabled = false;
            document.getElementById('select_equipe_destino_move').value = "";
            const lblSocio = document.getElementById('lblSocio');
            if(isSocio) {
                lblSocio.style.display = 'flex';
                lblSocio.querySelector('input').checked = true;
                switchModalTab('novo');
            } else {
                lblSocio.style.display = 'none';
                const radio = document.querySelector(`input[name="nivel_acesso"][value="${papelDefault}"]`);
                if(radio) radio.checked = true;
            }
        }
        function abrirModalMembroComEquipe(id){
            abrirModalMembro(false, 'FUNCIONARIO', true);
            document.getElementById('lblSocio').style.display = 'none';
            document.getElementById('select_equipe_membro').value = id; 
            document.getElementById('select_equipe_destino_move').value = id; 
        }
        function abrirModalEquipe(){document.getElementById('modalCriarEquipe').style.display='flex';}
        function fecharModais(){document.querySelectorAll('.modal').forEach(m=>m.style.display='none');}
        
        function abrirModalEditar(membro) {
            document.getElementById('edit_id').value = membro.id;
            document.getElementById('edit_nome').value = membro.nome;
            document.getElementById('edit_email').value = membro.email;
            document.getElementById('edit_cargo').value = membro.cargo_detalhe || '';
            const containerRadios = document.getElementById('containerRadiosEditar');
            const msgDono = document.getElementById('msgDonoFixo');
            if (membro.papel_sistema === 'DONO' || membro.papel_sistema === 'LIDER') {
                containerRadios.style.display = 'none';
                msgDono.style.display = 'block';
            } else {
                containerRadios.style.display = 'flex';
                msgDono.style.display = 'none';
                const role = (membro.papel_sistema === 'GESTOR') ? 'GESTOR' : 'FUNCIONARIO';
                document.querySelector(`input[name="edit_papel"][value="${role}"]`).checked = true;
            }
            document.getElementById('modalEditarMembro').style.display = 'flex';
        }

        function abrirModalExcluirMembro(id, nome) {
            document.getElementById('idMemDel').value = id;
            document.getElementById('msgExcluirMem').innerText = nome;
            document.getElementById('modalExcluirMembro').style.display = 'flex';
        }
        
        function confirmarExclusaoEquipe(id, nome) {
            document.getElementById('idEquipeDel').value = id;
            document.getElementById('msgDelEq').innerText = "Você tem certeza que deseja excluir a equipe " + nome + "?";
            document.getElementById('modalExcluirEquipe').style.display = 'flex';
        }
    </script>
</body>
</html>

<?php
// Substitua a função renderizarMembroCard no final do arquivo equipes.php por esta:

function renderizarMembroCard($m, $podeEditar, $contexto) {
    // Define papéis e cores
    $papelSistema = $m['papel_sistema'] ?? 'FUNCIONARIO';
    $roleClass = 'role-colab';
    $roleLabel = 'Colaborador';
    
    if ($papelSistema === 'DONO' || $papelSistema === 'LIDER') {
        $roleClass = 'role-dono';
        $roleLabel = 'Sócio / Líder';
    } elseif ($papelSistema === 'GESTOR') {
        $roleClass = 'role-gestor';
        $roleLabel = 'Gestor';
    }

    // Status Online
    $onlineClass = $m['is_online'] ? 'online' : '';
    
    // Cálculo de Progresso
    $total = (int)$m['total'];
    $concluidas = (int)$m['concluidas'];
    $percent = ($total > 0) ? round(($concluidas / $total) * 100) : 0;
    $pendentes = $total - $concluidas;
    
    // JSON para JS
    $jsonMembro = htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8');
    
    // Botões de Ação
    $botoes = '';
    if ($podeEditar && $contexto !== 'ARQUIVADO') {
        $botoes .= '<button class="btn-icon" onclick=\'abrirModalEditar('.$jsonMembro.')\' title="Configurar">'.getIcone('config').'</button>';
        $botoes .= '<button class="btn-icon del" onclick="abrirModalExcluirMembro('.$m['id'].', \''.addslashes($m['nome']).'\')" title="Excluir">'.getIcone('lixo').'</button>';
    } else if ($podeEditar && $contexto === 'ARQUIVADO') {
        $botoes .= '<button class="btn-icon" onclick="confirmarExclusaoMembro(\'restore\', '.$m['id'].')" title="Restaurar">'.getIcone('restaurar').'</button>';
        $botoes .= '<button class="btn-icon del" onclick="confirmarExclusaoMembro(\'hard\', '.$m['id'].')" title="Excluir Permanentemente">'.getIcone('lixo').'</button>';
    }

    // Estilo para arquivados
    $opacity = ($contexto === 'ARQUIVADO') ? 'opacity: 0.6; filter: grayscale(100%);' : '';

    // HTML do Card
    return '
    <div class="member-card-modern" style="'.$opacity.'">
        
        <div class="mem-card-header">
            <div class="mem-avatar-large">
                '.strtoupper(substr($m['nome'], 0, 2)).'
                <div class="mem-online-badge '.$onlineClass.'"></div>
            </div>
            <div class="mem-info">
                <h4>'.htmlspecialchars($m['nome']).'</h4>
                <p>'.htmlspecialchars($m['cargo_detalhe'] ?: 'Sem cargo definido').'</p>
            </div>
        </div>

        <div class="mem-progress-container">
            <div class="prog-labels">
                <span>Progresso</span>
                <span>'.$percent.'%</span>
            </div>
            <div class="prog-track">
                <div class="prog-fill" style="width: '.$percent.'%"></div>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:5px; font-size:0.75rem; color:#a3aed0;">
                <span>'.$concluidas.' entregues</span>
                <span>'.$pendentes.' pendentes</span>
            </div>
        </div>

        <div class="mem-footer">
            <span class="role-tag '.$roleClass.'">'.$roleLabel.'</span>
            <div class="mem-actions-btn">
                '.$botoes.'
            </div>
        </div>
    </div>';
}
?>