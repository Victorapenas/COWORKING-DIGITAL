// ARQUIVO: js/minhas_tarefas.js

// Variável global para armazenar a tarefa aberta
let tarefaAtualContexto = null;

/**
 * Abre o modal de execução da tarefa para o colaborador.
 */
function abrirModalExecucao(tarefa) {
    const modal = document.getElementById('modalExecucao');
    if (!modal) return;

    tarefaAtualContexto = tarefa; // Guarda o contexto

    // Preenche cabeçalho e descrição
    document.getElementById('execId').value = tarefa.id;
    document.getElementById('execTitulo').innerText = tarefa.titulo;
    document.getElementById('execDesc').innerText = tarefa.descricao || "Sem instruções adicionais.";
    
    // Configura Cronograma/Prazo (Visual)
    const cronogramaDiv = document.getElementById('execCronograma');
    if(cronogramaDiv && tarefa.prazo) {
        const dataPrazo = new Date(tarefa.prazo);
        const hoje = new Date();
        const diffDias = Math.ceil((dataPrazo - hoje) / (1000 * 60 * 60 * 24));
        
        let corPrazo = '#05cd99'; // Verde
        let textoPrazo = `Entrega até: ${dataPrazo.toLocaleDateString('pt-BR')}`;
        
        if(diffDias < 0) {
            corPrazo = '#ee5d50'; // Vermelho
            textoPrazo = `ATRASADO (${Math.abs(diffDias)} dias)`;
        } else if (diffDias <= 2) {
            corPrazo = '#ffa000'; // Laranja
            textoPrazo = `Prazo apertado: ${dataPrazo.toLocaleDateString('pt-BR')}`;
        }

        cronogramaDiv.innerHTML = `
            <div style="background:${corPrazo}15; color:${corPrazo}; padding:8px 15px; border-radius:8px; display:inline-flex; align-items:center; gap:8px; font-weight:700; border:1px solid ${corPrazo}30;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                ${textoPrazo}
            </div>
        `;
    }

    // Configura o Select de Status (Lógica de Fluxo)
    configurarSelectStatus(tarefa.status);

    // Renderiza o Checklist Rico (Avançado)
    renderizarChecklistRico(tarefa.checklist, tarefa.id);

    modal.style.display = 'flex';
}

/**
 * Define quais opções de status aparecem para o colaborador
 */
function configurarSelectStatus(statusAtual) {
    const select = document.getElementById('execStatus');
    select.innerHTML = ''; // Limpa opções

    // Opções baseadas no fluxo: Colaborador -> Revisão -> Gestor
    
    // Se está pendente ou fazendo
    if (['PENDENTE', 'EM_ANDAMENTO', 'EM_REVISAO'].includes(statusAtual)) {
        select.add(new Option("Em Andamento", "EM_ANDAMENTO"));
        select.add(new Option("Enviar para Revisão (Gestor)", "EM_REVISAO"));
    }
    // Se já está concluída, permite reabrir (caso o Líder mande refazer)
    else if (statusAtual === 'CONCLUIDA') {
        select.add(new Option("Concluída", "CONCLUIDA"));
        select.add(new Option("Reabrir para Ajustes", "EM_ANDAMENTO"));
    }

    select.value = statusAtual;
}

/**
 * Renderiza o checklist com lógica de tipos (Arquivo, Link, Check)
 */
function renderizarChecklistRico(checklistJson, tarefaId) {
    const container = document.getElementById('listaChecklistColab');
    const txtProgresso = document.getElementById('execProgressoTexto');
    
    let itens = [];
    try { itens = (typeof checklistJson === 'string') ? JSON.parse(checklistJson) : checklistJson; } catch(e){}
    
    // Se não houver itens
    if (!itens || itens.length === 0) {
        container.innerHTML = '<div style="padding:20px; text-align:center; color:#999;">Nenhum requisito de entrega definido.</div>';
        txtProgresso.innerText = "0%";
        return;
    }

    // Calcula progresso visual
    const feitos = itens.filter(i => i.concluido == 1).length;
    const pct = Math.round((feitos / itens.length) * 100);
    txtProgresso.innerText = pct + "%";

    let html = '';
    
    itens.forEach((item, idx) => {
        const isDone = item.concluido == 1;
        const tipo = item.tipo_evidencia || 'check'; // check, arquivo, link
        const formatos = item.formatos || '*'; // ex: .png, .pdf
        
        let acaoHtml = '';

        // CASO 1: ITEM JÁ CONCLUÍDO (Mostra a evidência e opção de remover)
        if (isDone) {
            let labelEvidencia = "Concluído";
            let link = "#";
            
            // Verifica se tem url de evidência salva
            if (item.evidencia_url) {
                // Se não começar com http, assume que é upload local na pasta public
                link = item.evidencia_url.startsWith('http') ? item.evidencia_url : '../public/' + item.evidencia_url;
                labelEvidencia = item.evidencia_nome || "Ver Entrega";
            }

            acaoHtml = `
                <div class="chk-done-box">
                    <a href="${link}" target="_blank" class="link-evidencia">
                        ✅ ${labelEvidencia}
                    </a>
                    <button type="button" onclick="removerEvidencia(${tarefaId}, ${idx})" class="btn-remove-evidencia" title="Remover/Refazer">&times;</button>
                </div>
            `;
        } 
        // CASO 2: ITEM PENDENTE (Mostra o input correto)
        else {
            if (tipo === 'arquivo') {
                // Input de Arquivo
                acaoHtml = `
                    <div class="chk-upload-box">
                        <label for="file_chk_${idx}" class="btn-upload-req">
                            📤 Enviar Arquivo (${formatos || 'Todos'})
                        </label>
                        <input type="file" id="file_chk_${idx}" accept="${formatos}" style="display:none" onchange="uploadChecklistItem(this, ${tarefaId}, ${idx})">
                    </div>
                `;
            } else if (tipo === 'link') {
                // Input de Link
                acaoHtml = `
                    <div class="chk-link-box">
                        <input type="text" id="link_chk_${idx}" placeholder="Cole o link aqui..." class="input-link-req">
                        <button type="button" onclick="salvarLinkChecklist(${tarefaId}, ${idx})" class="btn-save-link">Salvar</button>
                    </div>
                `;
            } else {
                // Checkbox Simples
                acaoHtml = `
                    <label class="custom-chk">
                        <input type="checkbox" onchange="toggleItemSimples(${tarefaId}, ${idx}, this)">
                        <span class="chk-checkmark"></span>
                        <span style="font-size:0.8rem; color:#666;">Marcar como feito</span>
                    </label>
                `;
            }
        }

        // Monta o HTML do item
        html += `
            <div class="checklist-rich-item ${isDone ? 'done' : ''}">
                <div class="chk-info">
                    <span class="chk-index">${idx + 1}</span>
                    <div class="chk-texts">
                        <span class="chk-desc">${item.descricao}</span>
                        ${tipo !== 'check' ? `<span class="chk-type-badge">${tipo === 'arquivo' ? 'Requer Arquivo' : 'Requer Link'}</span>` : ''}
                    </div>
                </div>
                <div class="chk-action-area">
                    ${acaoHtml}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// --- FUNÇÕES AUXILIARES DE AÇÃO ---

/**
 * Envia arquivo para o servidor via AJAX
 */
async function uploadChecklistItem(input, tarefaId, idx) {
    if (!input.files || input.files.length === 0) return;

    const fd = new FormData();
    fd.append('tarefa_id', tarefaId);
    fd.append('index', idx);
    fd.append('acao', 'upload');
    fd.append('arquivo_item', input.files[0]);

    // Feedback visual imediato
    const label = input.previousElementSibling; // O label do botão
    if(label) {
        label.innerText = "Enviando...";
        label.style.opacity = "0.7";
    }

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', { method: 'POST', body: fd });
        const json = await resp.json();
        
        if (json.ok) {
            // Recarrega o checklist para mostrar o estado "Concluído"
            atualizarContextoTarefa(tarefaId);
        } else {
            alert('Erro: ' + json.erro);
            if(label) label.innerText = "Tentar Novamente";
        }
    } catch (e) { 
        console.error(e); 
        alert('Erro de conexão'); 
        if(label) label.innerText = "Erro. Tentar novamente.";
    }
}

/**
 * Salva link de evidência via AJAX
 */
async function salvarLinkChecklist(tarefaId, idx) {
    const input = document.getElementById(`link_chk_${idx}`);
    const url = input.value.trim();
    if (!url) return alert("Por favor, cole um link válido.");

    const fd = new FormData();
    fd.append('tarefa_id', tarefaId);
    fd.append('index', idx);
    fd.append('acao', 'link');
    fd.append('link_url', url);

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', { method: 'POST', body: fd });
        const json = await resp.json();
        
        if (json.ok) {
            atualizarContextoTarefa(tarefaId);
        } else {
            alert("Erro ao salvar link: " + json.erro);
        }
    } catch (e) { alert('Erro de conexão'); }
}

/**
 * Remove a evidência (arquivo ou link) e desmarca o item
 */
async function removerEvidencia(tarefaId, idx) {
    if(!confirm("Deseja remover esta entrega? O item voltará a ficar pendente.")) return;
    
    const fd = new FormData();
    fd.append('tarefa_id', tarefaId);
    fd.append('index', idx);
    fd.append('acao', 'remover_evidencia');

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', { method: 'POST', body: fd });
        const json = await resp.json();
        if (json.ok) atualizarContextoTarefa(tarefaId);
    } catch (e) { alert('Erro de conexão'); }
}

/**
 * Alterna itens simples (tipo check)
 */
async function toggleItemSimples(tarefaId, idx, checkbox) {
    const fd = new FormData();
    fd.append('tarefa_id', tarefaId);
    fd.append('index', idx);
    fd.append('acao', 'toggle');
    fd.append('feito', checkbox.checked);

    try {
        await fetch('../api/tarefa_checklist_toggle.php', { method: 'POST', body: fd });
        // Atualiza estilo visualmente sem recarregar tudo, para agilidade
        const itemDiv = checkbox.closest('.checklist-rich-item');
        if(checkbox.checked) itemDiv.classList.add('done');
        else itemDiv.classList.remove('done');
    } catch (e) { console.error(e); alert("Erro ao sincronizar."); }
}

/**
 * Recarrega os dados da tarefa para atualizar a view do checklist
 */
async function atualizarContextoTarefa(id) {
    const resp = await fetch(`../api/tarefa_buscar.php?id=${id}`);
    const json = await resp.json();
    if(json.ok) {
        tarefaAtualContexto = json.tarefa;
        renderizarChecklistRico(json.tarefa.checklist, id);
    }
}

// Submissão Final do Formulário Principal (Botão "Salvar Progresso" ou "Enviar")
document.getElementById('formEntrega').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const txtOriginal = btn.innerText;
    
    btn.disabled = true; 
    btn.innerText = "Processando...";

    try {
        const formData = new FormData(this);
        const resp = await fetch('../api/tarefa_entregar.php', { method: 'POST', body: formData });
        const json = await resp.json();

        if (json.ok) {
            alert(json.mensagem);
            window.location.reload();
        } else {
            alert(json.erro || "Erro ao salvar.");
            btn.disabled = false; 
            btn.innerText = txtOriginal;
        }
    } catch (err) {
        alert("Erro de conexão.");
        btn.disabled = false;
        btn.innerText = txtOriginal;
    }
});

// Fecha modal ao clicar fora
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(m => {
        if (event.target == m) m.style.display = "none";
    });
}