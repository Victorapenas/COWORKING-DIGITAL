<?php
// ARQUIVO: public/tarefa.php
// =============================================================================
// CONTEÚDO DO MODAL DE CRIAÇÃO/EDIÇÃO DE TAREFAS
// =============================================================================

// PREVENÇÃO DE ERRO E CONTEXTO:
$idProjetoContexto = isset($id) ? $id : '';
$listaMembrosContexto = isset($membrosProjeto) ? $membrosProjeto : [];

// Função auxiliar (deve existir em includes/funcoes.php)
if (!function_exists('getIcone')) {
    function getIcone($nome) {
        // Ícones simples para botões de UI
        return ' <i class="fa fa-' . ($nome == 'adicionar' ? 'plus' : ($nome == 'remover' ? 'times' : 'file')) . '"></i> ';
    }
}
?>

<div id="modalTarefa" class="modal">
    <div class="modal-content" style="width: 700px; max-width: 90%;"> 
        <span class="close-btn" onclick="closeModal('modalTarefa')">&times;</span>
        <h3 id="modalTarefaTitle" style="margin-top: 0; color: #2b3674;">Nova Tarefa</h3>

        <form id="formCriarTarefa">
            <input type="hidden" name="projeto_id" id="tarefaProjetoId" value="<?= $idProjetoContexto ?>">
            <input type="hidden" name="id" id="tarefaId" value="">

            <div class="tabs-container">
                <div class="tabs-header">
                    <button type="button" class="tab-btn active" data-tab="tab-detalhes" onclick="switchTarefaTab(this, 'tab-detalhes')">Detalhes</button>
                    <button type="button" class="tab-btn" data-tab="tab-checklist" onclick="switchTarefaTab(this, 'tab-checklist')">Checklist</button>
                </div>

                <div class="tabs-content">
                    
                    <div id="tab-detalhes" class="tab-panel active">
                        <div class="form-group">
                            <label for="nomeTarefa">Nome da Tarefa:</label>
                            <input type="text" id="nomeTarefa" name="nome" required class="campo-padrao">
                        </div>

                        <div class="form-group">
                            <label for="descricaoTarefa">Descrição:</label>
                            <textarea id="descricaoTarefa" name="descricao" rows="3" class="campo-padrao"></textarea>
                        </div>

                        <div style="display:flex; gap: 20px;">
                            <div style="flex:1;">
                                <div class="form-group">
                                    <label for="responsavelTarefa">Responsável:</label>
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
                                        <?php 
                                            endforeach; 
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div style="flex:1;">
                                <div class="form-group">      
                                    <label for="prioridadeTarefa">Prioridade:</label>
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
                                    <label for="prazoTarefa">Prazo:</label>
                                    <input type="date" id="prazoTarefa" name="prazo" class="campo-padrao">
                                </div>
                            </div>
                            <div style="flex:1;">
                                <div class="form-group">
                                    <label for="statusTarefa">Status:</label>
                                    <select id="statusTarefa" name="status" required class="campo-padrao">
                                        <option value="A_FAZER">A Fazer</option>
                                        <option value="EM_ANDAMENTO">Em Andamento</option>
                                        <option value="CONCLUIDA">Concluída</option>
                                        <option value="CANCELADA">Cancelada</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tab-checklist" class="tab-panel">
                        <h4 style="color:#666; font-size:1rem; margin-bottom:15px;">Defina as etapas para a conclusão da tarefa.</h4>
                        
                        <div id="checklistContainer">
                            </div>

                        <button type="button" class="botao-secundario" onclick="adicionarItemChecklist()" style="margin-top:15px;">
                            <?= getIcone('adicionar') ?> Adicionar Função/Etapa
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="padding-top: 20px;">
                <button type="button" class="botao-secundario" onclick="closeModal('modalTarefa')">Cancelar</button>
                <button type="submit" class="botao-primario">Salvar Tarefa</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- FUNÇÕES GLOBAIS DE CHECKLIST ---
    window.adicionarItemChecklist = function(descricao = '', concluido = false, id = '') {
        const container = document.getElementById('checklistContainer');
        const novoItem = document.createElement('div');
        // CLASSE QUE O CSS CORRIGIDO ESTÁ MIRANDO
        novoItem.className = 'checklist-item'; 
        
        const indice = container.children.length; 
        const itemId = id || 0; 

        novoItem.innerHTML = `
            <input type="hidden" name="checklist_id[]" value="${itemId}">
            <input type="checkbox" name="checklist_concluido[${indice}]" ${concluido ? 'checked' : ''} value="${indice}">
            <input type="text" name="checklist_descricao[]" placeholder="Descreva a função ou etapa" required value="${descricao.replace(/"/g, '&quot;')}" title="Descrição da Etapa" class="campo-padrao">
            <button type="button" class="remover-btn" onclick="this.closest('.checklist-item').remove()" title="Remover Item">
                &times; 
            </button>
        `;
        container.appendChild(novoItem);
    };
    
    // --- FUNÇÃO DE TROCA DE ABAS (AJUSTADA PARA USAR .tab-btn E .tab-panel) ---
    window.switchTarefaTab = function(button, targetId) {
        const modalTarefa = button.closest('.modal-content');
        
        // Remove 'active' de todos os botões e painéis, usando as classes padronizadas do painel.css
        modalTarefa.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        modalTarefa.querySelectorAll('.tab-panel').forEach(pane => pane.classList.remove('active'));

        // Adiciona 'active' ao botão clicado e ao painel correspondente
        button.classList.add('active');
        document.getElementById(targetId).classList.add('active');
        
        // Garante que haja pelo menos um item no checklist ao abrir a aba (se vazia)
        if (targetId === 'tab-checklist' && document.getElementById('checklistContainer').children.length === 0) {
             adicionarItemChecklist();
        }
    }

    window.handleTarefaSubmit = async function(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const txtOriginal = btn.textContent;
        
        btn.disabled = true; 
        btn.textContent = "Salvando...";

        try {
            const formData = new FormData(form); 
            const id = document.getElementById('tarefaId').value;
            const endpoint = id ? '../api/tarefa_editar.php' : '../api/tarefa_criar.php';
            
            const resp = await fetch(endpoint, { method: 'POST', body: formData });
            
            let json;
            const textResp = await resp.text();
            try {
                json = JSON.parse(textResp);
            } catch (parseErr) {
                console.error("Resposta bruta do servidor:", textResp);
                throw new Error("Resposta inválida do servidor. Verifique o console para a resposta bruta do PHP.");
            }

            if (json.ok) {
                alert(json.mensagem);
                window.location.reload(); 
            } else {
                alert(json.erro || "Erro desconhecido ao salvar tarefa.");
            }
        } catch (err) { 
            alert("Erro: " + err.message); 
        } finally {
            btn.disabled = false; 
            btn.textContent = txtOriginal;
        }
    };
    
    if (typeof openTarefaModal === 'undefined') {
        window.openTarefaModal = async function(projetoId = null, tarefaId = null) {
            const form = document.getElementById('formCriarTarefa');
            if(form) form.reset();
            
            document.getElementById('checklistContainer').innerHTML = '';
            
            const inputProjetoId = document.getElementById('tarefaProjetoId');
            if (inputProjetoId) inputProjetoId.value = projetoId;
            
            const inputTarefaId = document.getElementById('tarefaId');
            if (inputTarefaId) inputTarefaId.value = tarefaId || "";
            
            const titleElement = document.getElementById('modalTarefaTitle');
            if (titleElement) titleElement.innerText = tarefaId ? "Editar Tarefa" : "Nova Tarefa";

            if (tarefaId) {
                try {
                    const resp = await fetch(`../api/tarefa_buscar.php?id=${tarefaId}`);
                    const textResp = await resp.text();
                    let json;

                    try {
                        json = JSON.parse(textResp);
                    } catch (parseErr) {
                        console.error("Erro ao analisar JSON de tarefa_buscar.php. Resposta bruta:", textResp);
                        throw new Error("Resposta inválida ao buscar tarefa. Verifique o console.");
                    }
                    
                    if (json.ok) {
                        const t = json.tarefa;
                        
                        // Detalhes
                        const nome = document.getElementById('nomeTarefa'); if (nome) nome.value = t.titulo;
                        const desc = document.getElementById('descricaoTarefa'); if (desc) desc.value = t.descricao;
                        const respId = document.getElementById('responsavelTarefa'); if (respId) respId.value = t.responsavel_id;
                        const prior = document.getElementById('prioridadeTarefa'); if (prior) prior.value = t.prioridade;
                        const prazo = document.getElementById('prazoTarefa'); if (prazo) prazo.value = t.prazo;
                        const status = document.getElementById('statusTarefa'); if (status) status.value = t.status;
                        
                        // Checklist (Código Corrigido da etapa anterior)
                        const checklistContainer = document.getElementById('checklistContainer');
                        let checklist = [];
                        let checklistData = t.checklist;

                        if (checklistData && typeof checklistData === 'string' && checklistData.trim() !== '') {
                            try {
                                checklist = JSON.parse(checklistData);
                            } catch (e) {
                                console.error("Erro ao decodificar JSON do Checklist:", e, checklistData);
                                checklist = [];
                            }
                        } else if (Array.isArray(t.checklist)) {
                            checklist = t.checklist;
                        }

                        if (Array.isArray(checklist) && checklist.length > 0 && checklistContainer) {
                            checklistContainer.innerHTML = ''; 
                            checklist.forEach(item => {
                                adicionarItemChecklist(item.descricao, item.concluido == 1, item.id); 
                            });
                        } else {
                            adicionarItemChecklist(); 
                        }

                    } else {
                        alert('Erro ao carregar tarefa: ' + (json.erro || "Erro desconhecido."));
                        return;
                    }
                } catch(e) {
                    console.error("Erro no processamento da edição:", e);
                    alert("Falha grave ao processar dados da tarefa: " + e.message); 
                    return;
                }
                
            } else {
                // Modo CRIAÇÃO
                adicionarItemChecklist(); 
            }
            
            const detalhesButton = document.querySelector('#modalTarefa .tab-btn[data-tab="tab-detalhes"]');
            if (detalhesButton) {
                // Força o clique no botão Detalhes
                window.switchTarefaTab(detalhesButton, 'tab-detalhes');
            }

            // Mostra o modal
            if (typeof showModal === 'function') {
                showModal('modalTarefa'); 
            }
        };
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('formCriarTarefa');
        if (form) {
            form.addEventListener('submit', window.handleTarefaSubmit);
        }
    });
</script>