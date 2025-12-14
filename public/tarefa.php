<?php
// ARQUIVO: public/tarefa.php
// ATUALIZADO: Correção de Responsável (busca automática), Horário e Modal

// --- LÓGICA DE RECUPERAÇÃO DE MEMBROS (FALLBACK) ---
// Se a lista de membros não foi passada pelo arquivo pai (ex: projeto_detalhes.php),
// tentamos encontrá-la nas variáveis globais ou buscamos no banco.
if (!isset($listaMembrosContexto) || empty($listaMembrosContexto)) {
    if (isset($membrosProjeto)) {
        $listaMembrosContexto = $membrosProjeto;
    } elseif (isset($membrosDisponiveis)) {
        $listaMembrosContexto = $membrosDisponiveis; // Comum em equipes.php e dashboard.php
    } elseif (isset($listaMembros)) {
        $listaMembrosContexto = $listaMembros; // Comum em relatorios.php
    } elseif (isset($empresaId) && function_exists('getMembrosDisponiveis')) {
        // Último recurso: busca direta no banco se tivermos o ID da empresa
        $listaMembrosContexto = getMembrosDisponiveis($empresaId);
    } else {
        $listaMembrosContexto = [];
    }
}

$idProjetoContexto = isset($id) ? $id : '';
?>

<div id="modalTarefa" class="modal">
    <div class="modal-content" style="width: 800px; max-width: 95%;"> 
        <span class="close-btn" onclick="closeModal('modalTarefa')">&times;</span>
        
        <h3 id="modalTarefaTitle" style="margin-top: 0; color: #2b3674;">Nova Tarefa</h3>

        <form id="formCriarTarefa">
            <input type="hidden" name="projeto_id" id="tarefaProjetoId" value="<?= $idProjetoContexto ?>">
            <input type="hidden" name="id" id="tarefaId" value="">

            <div class="tabs-container">
                <div class="tabs-header">
                    <button type="button" class="tab-btn active" data-tab="tab-detalhes" onclick="switchTarefaTab(this, 'tab-detalhes')">Detalhes</button>
                    <button type="button" class="tab-btn" data-tab="tab-checklist" onclick="switchTarefaTab(this, 'tab-checklist')">Requisitos de Entrega (Checklist)</button>
                </div>

                <div class="tabs-content">
                    <div id="tab-detalhes" class="tab-panel active">
                        <div class="form-group">
                            <label>Nome da Tarefa:</label>
                            <input type="text" id="nomeTarefa" name="nome" required class="campo-padrao" placeholder="Ex: Criar Layout da Home">
                        </div>

                        <div class="form-group">
                            <label>Descrição:</label>
                            <textarea id="descricaoTarefa" name="descricao" rows="3" class="campo-padrao" placeholder="Detalhes do que precisa ser feito..."></textarea>
                        </div>

                        <div style="display:flex; gap: 20px;">
                            <div style="flex:1;">
                                <div class="form-group">
                                    <label>Responsável:</label>
                                    <select id="responsavelTarefa" name="responsavel_id" required class="campo-padrao">
                                        <option value="">Selecione o responsável...</option>
                                        <?php 
                                        if (!empty($listaMembrosContexto)) {
                                            foreach ($listaMembrosContexto as $membro): 
                                                // Garante compatibilidade com diferentes formatos de array de membros
                                                $mId = $membro['id'];
                                                $mNome = $membro['nome'];
                                                $mCargo = !empty($membro['cargo_detalhe']) ? " (" . $membro['cargo_detalhe'] . ")" : "";
                                        ?>
                                            <option value="<?= $mId ?>">
                                                <?= htmlspecialchars($mNome . $mCargo) ?>
                                            </option>
                                        <?php endforeach; } else { ?>
                                            <option value="" disabled>Nenhum membro encontrado</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div style="flex:1;">
                                <div class="form-group">      
                                    <label>Prioridade:</label>
                                        <select id="prioridadeTarefa" name="prioridade" required class="campo-padrao">
                                            <option value="NORMAL">Normal</option>
                                            <option value="IMPORTANTE">Importante</option>
                                            <option value="URGENTE">Urgente</option>
                                        </select>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; gap: 20px;">
                            <div style="flex:1;">
                                <div class="form-group">
                                    <label>Prazo e Horário:</label>
                                    <input type="datetime-local" id="prazoTarefa" name="prazo" class="campo-padrao">
                                </div>
                            </div>
                            <div style="flex:1;">
                                <div class="form-group">
                                    <label>Status:</label>
                                    <select id="statusTarefa" name="status" required class="campo-padrao">
                                        <option value="PENDENTE">A Fazer</option>
                                        <option value="EM_ANDAMENTO">Em Andamento</option>
                                        <option value="EM_REVISAO">Em Revisão</option>
                                        <option value="CONCLUIDA">Concluída</option>
                                        <option value="CANCELADA">Cancelada</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tab-checklist" class="tab-panel">
                        <div style="background: #e3f2fd; padding: 10px; border-radius: 8px; font-size: 0.85rem; color: #0d6efd; margin-bottom: 15px;">
                            <i class="fa fa-info-circle"></i> <strong>Configuração de Entrega:</strong> Defina o que o colaborador deve entregar. Se você selecionar "Requer Arquivo", o colaborador só poderá concluir o item enviando o anexo.
                        </div>

                        <div id="checklistContainer" style="max-height: 350px; overflow-y: auto; padding-right:5px;">
                            </div>
                        
                        <button type="button" class="botao-secundario" onclick="adicionarItemChecklist()" style="margin-top:15px; width:100%; border-style:dashed;">
                            + Adicionar Requisito de Entrega
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="padding-top: 20px; border-top:1px solid #eee; margin-top:20px;">
                <button type="button" class="botao-secundario" onclick="closeModal('modalTarefa')">Cancelar</button>
                <button type="submit" class="botao-primario">Salvar Tarefa</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- LÓGICA DO CHECKLIST DINÂMICO ---

    // Adiciona um item ao checklist (Visualmente)
    window.adicionarItemChecklist = function(dados = {}) {
        const container = document.getElementById('checklistContainer');
        const index = container.children.length; 

        // Valores padrão ou carregados da edição
        const descricao = dados.descricao || '';
        const tipo = dados.tipo_evidencia || 'check'; // check, arquivo, link
        const formatos = dados.formatos || ''; // ex: .png, .pdf
        const concluido = dados.concluido == 1;

        const itemDiv = document.createElement('div');
        itemDiv.className = 'checklist-item-row';
        itemDiv.style.cssText = "background:#f8f9fa; border:1px solid #e0e0e0; padding:15px; border-radius:10px; margin-bottom:10px; position:relative;";

        itemDiv.innerHTML = `
            <div style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
                <span style="background:#2b3674; color:white; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:bold;">${index + 1}</span>
                <input type="text" name="checklist_descricao[]" value="${descricao}" placeholder="O que deve ser feito/entregue?" required class="campo-padrao" style="margin:0 !important; flex:1;">
                <button type="button" onclick="this.closest('.checklist-item-row').remove()" style="background:none; border:none; color:#e74c3c; cursor:pointer; font-size:1.2rem;" title="Remover Item">&times;</button>
            </div>
            
            <div style="display:flex; gap:15px; align-items:center; background:white; padding:10px; border-radius:8px; border:1px solid #eee;">
                <div style="flex:1;">
                    <label style="font-size:0.75rem; color:#888; margin-bottom:3px; display:block;">Tipo de Entrega:</label>
                    <select name="checklist_tipo[]" class="campo-padrao" style="margin:0 !important; padding:5px;" onchange="toggleFormatos(this)">
                        <option value="check" ${tipo === 'check' ? 'selected' : ''}>Apenas Marcar (Simples)</option>
                        <option value="arquivo" ${tipo === 'arquivo' ? 'selected' : ''}>Requer Arquivo (Upload)</option>
                        <option value="link" ${tipo === 'link' ? 'selected' : ''}>Requer Link (Drive/Figma)</option>
                    </select>
                </div>
                
                <div class="div-formatos" style="flex:1; display:${tipo === 'arquivo' ? 'block' : 'none'};">
                    <label style="font-size:0.75rem; color:#888; margin-bottom:3px; display:block;">Formatos Aceitos (Ex: .png, .pdf):</label>
                    <input type="text" name="checklist_formatos[]" value="${formatos}" placeholder="Todos ou ex: .pdf, .jpg" class="campo-padrao" style="margin:0 !important; padding:5px;">
                </div>
            </div>
            
            <input type="hidden" name="checklist_concluido_status[]" value="${concluido ? 1 : 0}">
        `;
        
        container.appendChild(itemDiv);
    };

    // Mostra/Esconde campo de formatos dependendo do tipo selecionado
    window.toggleFormatos = function(select) {
        const divFormatos = select.closest('div').nextElementSibling;
        if(select.value === 'arquivo') {
            divFormatos.style.display = 'block';
            const input = divFormatos.querySelector('input');
            if(input) input.focus();
        } else {
            divFormatos.style.display = 'none';
        }
    }
    
    // Alterna abas (Detalhes <-> Checklist)
    window.switchTarefaTab = function(button, targetId) {
        const modal = document.getElementById('modalTarefa');
        modal.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        modal.querySelectorAll('.tab-panel').forEach(pane => pane.classList.remove('active'));
        button.classList.add('active');
        document.getElementById(targetId).classList.add('active');
        
        // Se abriu a aba checklist e está vazia, adiciona o primeiro item automaticamente
        if (targetId === 'tab-checklist' && document.getElementById('checklistContainer').children.length === 0) {
             adicionarItemChecklist();
        }
    }

    // Função de limpeza
    window.resetChecklist = function() {
        const container = document.getElementById('checklistContainer');
        if (container) {
            container.innerHTML = ''; 
        }
    }

    // Lógica de Submissão (AJAX)
    // Nota: Essa função será anexada ao form pelo js/tarefas.js principal
    // Mantemos aqui para garantir que ela exista caso o arquivo JS externo falhe ou demore.
    window.handleTarefaSubmit = async function(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const txtOriginal = btn.textContent;
        btn.disabled = true; btn.textContent = "Salvando...";

        try {
            const formData = new FormData(form); 
            const id = document.getElementById('tarefaId').value;
            const endpoint = id ? '../api/tarefa_editar.php' : '../api/tarefa_criar.php';
            
            const resp = await fetch(endpoint, { method: 'POST', body: formData });
            const json = await resp.json();

            if (json.ok) {
                alert(json.mensagem);
                window.location.reload(); 
            } else {
                alert("Erro: " + (json.erro || "Falha desconhecida."));
            }
        } catch (err) { alert("Erro de conexão."); } finally {
            btn.disabled = false; btn.textContent = txtOriginal;
        }
    };
    
    // Função Global para Abrir o Modal (Carrega dados se for edição)
    // Verifica se já foi definida para evitar conflitos
    if (typeof openTarefaModal === 'undefined') {
        window.openTarefaModal = async function(projetoId = null, tarefaId = null) {
            const form = document.getElementById('formCriarTarefa');
            if(form) form.reset();
            
            // Reseta Checklist
            resetChecklist();
            
            document.getElementById('tarefaProjetoId').value = projetoId || '';
            document.getElementById('tarefaId').value = tarefaId || "";
            document.getElementById('modalTarefaTitle').innerText = tarefaId ? "Editar Tarefa" : "Nova Tarefa";

            // Se for edição, busca dados na API
            if (tarefaId) {
                try {
                    const resp = await fetch(`../api/tarefa_buscar.php?id=${tarefaId}`);
                    const json = await resp.json();
                    
                    if (json.ok) {
                        const t = json.tarefa;
                        document.getElementById('nomeTarefa').value = t.titulo;
                        document.getElementById('descricaoTarefa').value = t.descricao;
                        document.getElementById('responsavelTarefa').value = t.responsavel_id;
                        document.getElementById('prioridadeTarefa').value = t.prioridade;
                        
                        // CORREÇÃO DATA: Formata para o input datetime-local (YYYY-MM-DDTHH:MM)
                        if(t.prazo) {
                            // Cria objeto Date e ajusta para string ISO compatível
                            const dateObj = new Date(t.prazo);
                            // Ajuste manual de fuso horário para garantir exibição correta no input
                            dateObj.setMinutes(dateObj.getMinutes() - dateObj.getTimezoneOffset());
                            document.getElementById('prazoTarefa').value = dateObj.toISOString().slice(0,16);
                        }
                        
                        document.getElementById('statusTarefa').value = t.status === 'A_FAZER' ? 'PENDENTE' : t.status;
                        
                        let checklist = [];
                        try { checklist = JSON.parse(t.checklist); } catch(e){}

                        if (Array.isArray(checklist) && checklist.length > 0) {
                            checklist.forEach(item => adicionarItemChecklist(item));
                        } else {
                            adicionarItemChecklist(); 
                        }
                    }
                } catch(e) { alert("Erro ao carregar dados da tarefa."); return; }
            } else {
                // Se for nova tarefa, inicia checklist vazio
                adicionarItemChecklist(); 
            }
            document.getElementById('modalTarefa').style.display = 'flex';
        };
    }
    
    // Inicialização
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('formCriarTarefa');
        if (form) form.addEventListener('submit', window.handleTarefaSubmit);
    });
</script>