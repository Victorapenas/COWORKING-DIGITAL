<?php
// ARQUIVO: public/tarefa.php
// ATUALIZADO: Checklist com definição de tipos de evidência (Arquivo, Link, Texto)

$idProjetoContexto = isset($id) ? $id : '';
$listaMembrosContexto = isset($membrosProjeto) ? $membrosProjeto : [];
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
                                        <option value="">Selecione...</option>
                                        <?php 
                                        if (!empty($listaMembrosContexto)) {
                                            foreach ($listaMembrosContexto as $membro): 
                                                $cargoLabel = !empty($membro['cargo_detalhe']) ? " (" . $membro['cargo_detalhe'] . ")" : "";
                                        ?>
                                            <option value="<?= $membro['id'] ?>">
                                                <?= htmlspecialchars($membro['nome']) . htmlspecialchars($cargoLabel) ?>
                                            </option>
                                        <?php endforeach; } ?>
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
                                    <label>Prazo:</label>
                                    <input type="date" id="prazoTarefa" name="prazo" class="campo-padrao">
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
                            <i class="fa fa-info-circle"></i> Defina o que o colaborador deve entregar. Você pode exigir arquivos específicos (PNG, PDF) ou Links.
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
    // Função para adicionar item visualmente no DOM (Versão Avançada)
    window.adicionarItemChecklist = function(dados = {}) {
        const container = document.getElementById('checklistContainer');
        const index = container.children.length; 

        // Defaults
        const descricao = dados.descricao || '';
        const tipo = dados.tipo_evidencia || 'check'; // check, arquivo, link
        const formatos = dados.formatos || ''; // .png, .pdf
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
                        <option value="arquivo" ${tipo === 'arquivo' ? 'selected' : ''}>Upload de Arquivo</option>
                        <option value="link" ${tipo === 'link' ? 'selected' : ''}>Link Externo (Drive/Figma)</option>
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

    // Mostra/Esconde campo de formatos
    window.toggleFormatos = function(select) {
        const divFormatos = select.closest('div').nextElementSibling;
        if(select.value === 'arquivo') {
            divFormatos.style.display = 'block';
            divFormatos.querySelector('input').focus();
        } else {
            divFormatos.style.display = 'none';
        }
    }
    
    // Switch de abas
    window.switchTarefaTab = function(button, targetId) {
        const modal = document.getElementById('modalTarefa');
        modal.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        modal.querySelectorAll('.tab-panel').forEach(pane => pane.classList.remove('active'));
        button.classList.add('active');
        document.getElementById(targetId).classList.add('active');
        
        if (targetId === 'tab-checklist' && document.getElementById('checklistContainer').children.length === 0) {
             adicionarItemChecklist();
        }
    }

    // Submit
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
    
    // Abrir Modal
    if (typeof openTarefaModal === 'undefined') {
        window.openTarefaModal = async function(projetoId = null, tarefaId = null) {
            const form = document.getElementById('formCriarTarefa');
            if(form) form.reset();
            document.getElementById('checklistContainer').innerHTML = '';
            document.getElementById('tarefaProjetoId').value = projetoId || '';
            document.getElementById('tarefaId').value = tarefaId || "";
            document.getElementById('modalTarefaTitle').innerText = tarefaId ? "Editar Tarefa" : "Nova Tarefa";

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
                        document.getElementById('prazoTarefa').value = t.prazo;
                        document.getElementById('statusTarefa').value = t.status === 'A_FAZER' ? 'PENDENTE' : t.status;
                        
                        let checklist = [];
                        try { checklist = JSON.parse(t.checklist); } catch(e){}

                        if (Array.isArray(checklist) && checklist.length > 0) {
                            checklist.forEach(item => adicionarItemChecklist(item));
                        } else {
                            adicionarItemChecklist(); 
                        }
                    }
                } catch(e) { alert("Erro ao carregar."); return; }
            } else {
                adicionarItemChecklist(); 
            }
            document.getElementById('modalTarefa').style.display = 'flex';
        };
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('formCriarTarefa');
        if (form) form.addEventListener('submit', window.handleTarefaSubmit);
    });
</script>