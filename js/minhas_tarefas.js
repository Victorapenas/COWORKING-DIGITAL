// ARQUIVO: js/minhas_tarefas.js

// --- VARIÁVEIS GLOBAIS DO TIMER ---
let timerState = {
    running: false,
    seconds: 0,
    intervalId: null,
    tarefaId: null,
    titulo: ''
};

// Variável global para armazenar a tarefa aberta (contexto)
let tarefaAtualContexto = null;

document.addEventListener('DOMContentLoaded', () => {
    // Cria o elemento flutuante se não existir (apenas uma vez)
    if (!document.getElementById('floatingTimer')) {
        const floatDiv = document.createElement('div');
        floatDiv.id = 'floatingTimer';
        floatDiv.className = 'floating-timer-container';
        floatDiv.onclick = reabrirModalTimer; // Ao clicar, abre o modal da tarefa ativa
        floatDiv.innerHTML = `
            <div class="float-icon-pulse"></div>
            <span id="floatTimerText" class="float-time-text">00:00</span>
            <span style="font-size:0.8rem; opacity:0.8; margin-left:5px;">Em andamento</span>
        `;
        document.body.appendChild(floatDiv);
    }
});

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

    // --- Configura o Feedback do Gestor/Líder ---
    const feedbackArea = document.getElementById('execFeedbackArea');
    const feedbackText = document.getElementById('execFeedbackText');

    // Se houver feedback e a tarefa não estiver concluída, mostra o alerta
    if (tarefa.feedback_revisao && tarefa.status !== 'CONCLUIDA') {
        feedbackText.innerText = tarefa.feedback_revisao;
        feedbackArea.style.display = 'block';
    } else {
        feedbackArea.style.display = 'none';
        feedbackText.innerText = '';
    }

    // Configura o Select de Status (Lógica de Fluxo)
    configurarSelectStatus(tarefa.status);

    // Remove a barra manual se ela existir (pois agora é automático)
    const grupoManual = document.getElementById('groupProgressoManual');
    if(grupoManual) grupoManual.style.display = 'none';

    // Renderiza o Checklist Rico (Avançado)
    renderizarChecklistRico(tarefa.checklist, tarefa.id);

    // Ajusta o botão do Timer se já estiver rodando para esta tarefa
    const btnTimer = document.getElementById('btnTimerPanel');
    if (btnTimer) {
        if (timerState.running && timerState.tarefaId == tarefa.id) {
            btnTimer.innerHTML = "⏸ Pausar";
            btnTimer.style.backgroundColor = "#ffce20"; 
            btnTimer.style.color = "#333";
        } else {
            btnTimer.innerHTML = "▶ Iniciar"; // Reset se for outra tarefa
            btnTimer.style.backgroundColor = ""; 
            btnTimer.style.color = "";
        }
    }

    modal.style.display = 'flex';
}

/**
 * Define quais opções de status aparecem para o colaborador
 */
function configurarSelectStatus(statusAtual) {
    const select = document.getElementById('execStatus');
    select.innerHTML = ''; // Limpa opções

    // Opções baseadas no fluxo: Colaborador -> Revisão -> Gestor
    
    // Se está pendente, fazendo ou em revisão (retornada)
    if (['PENDENTE', 'EM_ANDAMENTO', 'EM_REVISAO'].includes(statusAtual)) {
        select.add(new Option("Em Andamento", "EM_ANDAMENTO"));
        select.add(new Option("Enviar para Revisão (Gestor)", "EM_REVISAO"));
    }
    // Se já está concluída, permite reabrir (caso o Líder mande refazer e o status mude manualmente)
    else if (statusAtual === 'CONCLUIDA') {
        select.add(new Option("Concluída", "CONCLUIDA"));
        select.add(new Option("Reabrir para Ajustes", "EM_ANDAMENTO"));
    }

    select.value = statusAtual;
}

// --- FUNÇÃO DO TIMER ---
function toggleTimerPainel() {
    const btn = document.getElementById('btnTimerPanel');
    
    if (!timerState.running) {
        // INICIAR
        timerState.running = true;
        timerState.tarefaId = document.getElementById('execId').value;
        // Pega título se disponível, ou usa genérico
        const elTitulo = document.getElementById('execTitulo');
        timerState.titulo = elTitulo ? elTitulo.innerText : 'Tarefa';
        
        if(btn) {
            btn.innerHTML = "⏸ Pausar";
            btn.style.backgroundColor = "#ffce20"; 
            btn.style.color = "#333";
        }
        
        // Inicia contagem
        if (timerState.intervalId) clearInterval(timerState.intervalId);
        timerState.intervalId = setInterval(() => {
            timerState.seconds++;
            atualizarDisplaysTimer();
        }, 1000);

    } else {
        // PAUSAR E SALVAR
        timerState.running = false;
        clearInterval(timerState.intervalId);
        
        if(btn) {
            btn.innerHTML = "▶ Continuar";
            btn.style.backgroundColor = ""; 
            btn.style.color = "";
        }
        
        // Salva tempo no servidor (conversão p/ minutos)
        const minutos = Math.ceil(timerState.seconds / 60);
        if (minutos > 0) {
            salvarTempoAPI(timerState.tarefaId, minutos);
            // Reseta segundos locais após salvar, para não duplicar na próxima
            timerState.seconds = 0; 
        }
        
        const floatTimer = document.getElementById('floatingTimer');
        if(floatTimer) floatTimer.style.display = 'none';
    }
}

function atualizarDisplaysTimer() {
    const format = formatTime(timerState.seconds);
    
    // Atualiza flutuante
    const floatText = document.getElementById('floatTimerText');
    if (floatText) floatText.innerText = format;
}

function formatTime(totalSeconds) {
    const m = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
    const s = (totalSeconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
}

// Quando fecha o modal, se o timer estiver rodando, mostra o flutuante
function fecharPainelLateral() {
    const modal = document.getElementById('modalExecucao');
    if(modal) modal.style.display = 'none';
    
    if (timerState.running) {
        const floatTimer = document.getElementById('floatingTimer');
        if(floatTimer) floatTimer.style.display = 'flex';
    }
}

// Ao clicar no flutuante, reabre o modal da tarefa que está rodando
async function reabrirModalTimer() {
    if (timerState.tarefaId) {
        const floatTimer = document.getElementById('floatingTimer');
        if(floatTimer) floatTimer.style.display = 'none';
        
        // Busca dados atualizados e abre
        try {
            const resp = await fetch(`../api/tarefa_buscar.php?id=${timerState.tarefaId}`);
            const json = await resp.json();
            if(json.ok) {
                abrirModalExecucao(json.tarefa);
            }
        } catch(e) {
            console.error("Erro ao reabrir tarefa do timer", e);
        }
    }
}

// Salva o tempo no backend
async function salvarTempoAPI(id, minutos) {
    const fd = new FormData(); 
    fd.append('tarefa_id', id); 
    fd.append('tempo_gasto', minutos);
    
    try { await fetch('../api/tarefa_entregar.php', { method: 'POST', body: fd }); } catch (e) {}
}


/**
 * Renderiza o checklist com lógica de tipos (Arquivo, Link, Check)
 * ESTRUTURA RICA com Scroll e Inputs dedicados.
 */
function renderizarChecklistRico(checklistJson, tarefaId) {
    const container = document.getElementById('listaChecklistColab');
    const txtProgresso = document.getElementById('execProgressoTexto');
    
    let itens = [];
    try { itens = (typeof checklistJson === 'string') ? JSON.parse(checklistJson) : checklistJson; } catch(e){}
    
    // Se não houver itens
    if (!itens || itens.length === 0) {
        container.innerHTML = '<div style="padding:20px; text-align:center; color:#999;">Nenhum requisito de entrega definido.</div>';
        if(txtProgresso) txtProgresso.innerText = "0%";
        return;
    }

    // Calcula progresso visual
    const feitos = itens.filter(i => i.concluido == 1).length;
    const pct = Math.round((feitos / itens.length) * 100);
    if(txtProgresso) txtProgresso.innerText = pct + "%";

    // Inicia o container com scroll
    let html = `<div class="checklist-scroll-container">`; 
    
    itens.forEach((item, idx) => {
        const isDone = item.concluido == 1;
        // O tipo do item simples agora é 'toggle' ou 'check' (mantendo compatibilidade)
        const tipo = item.tipo_evidencia || 'check'; 
        const formatos = item.formatos || '*'; 
        
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

            // Para itens simples (toggle), o status final é salvo via submissão principal
            if (tipo === 'check' || tipo === 'toggle') {
                 acaoHtml = `
                    <div style="margin-top:5px;">
                        <label class="custom-chk">
                            <input type="checkbox" name="checklist_done[]" value="${idx}" checked disabled>
                            <span class="chk-checkmark"></span>
                            <span style="font-size:0.8rem; color:#05cd99; font-weight: 600;">Marcado como Feito</span>
                        </label>
                        <button type="button" onclick="removerEvidencia(${tarefaId}, ${idx})" class="btn-remove-evidencia" style="margin-left:10px; font-size:0.8rem; color:#d32f2f;">Desmarcar</button>
                    </div>
                `;
            } else {
                // Itens ricos concluídos (Arquivo/Link)
                acaoHtml = `
                    <div class="chk-done-box">
                        <a href="${link}" target="_blank" class="link-evidencia">
                            ✅ ${labelEvidencia}
                        </a>
                        <button type="button" onclick="removerEvidencia(${tarefaId}, ${idx})" class="btn-remove-evidencia" title="Remover/Refazer">&times;</button>
                    </div>
                `;
            }
        } 
        // CASO 2: ITEM PENDENTE (Mostra o input correto)
        else {
            if (tipo === 'arquivo') {
                // Input de Arquivo
                acaoHtml = `
                    <div class="chk-upload-box" style="margin-top:10px;">
                        <label for="file_chk_${idx}" class="btn-upload-req">
                            📤 Enviar Arquivo (${formatos || 'Todos'})
                        </label>
                        <input type="file" id="file_chk_${idx}" accept="${formatos}" style="display:none" onchange="uploadChecklistItem(this, ${tarefaId}, ${idx})">
                        <span id="loading_${idx}" style="display:none; font-size:0.8rem; color:#666; margin-left:10px;">Enviando...</span>
                    </div>
                `;
            } else if (tipo === 'link') {
                // Input de Link
                acaoHtml = `
                    <div class="chk-link-box" style="margin-top:10px;">
                        <input type="text" id="link_chk_${idx}" placeholder="Cole o link aqui..." class="input-link-req">
                        <button type="button" onclick="salvarLinkChecklist(${tarefaId}, ${idx})" class="btn-save-link">Salvar</button>
                    </div>
                `;
            } else {
                // Checkbox Simples (TIPO TOGGLE/CHECK)
                acaoHtml = `
                    <div style="margin-top:5px;">
                        <label class="custom-chk">
                            <input type="checkbox" onchange="toggleCheckItemPainel(${tarefaId}, ${idx}, this)">
                            <span class="chk-checkmark"></span>
                            <span style="font-size:0.8rem; color:#666;">Marcar como feito</span>
                        </label>
                    </div>
                `;
            }
        }

        // Monta o HTML do item usando a classe chk-rich-item (definida no CSS novo)
        html += `
            <div class="chk-rich-item ${isDone ? 'done' : ''}">
                <div class="chk-info">
                    <span class="chk-index" style="background:#eee; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.8rem; margin-right:10px;">${idx + 1}</span>
                    <div class="chk-texts" style="flex:1;">
                        <span class="chk-desc" style="font-weight:600; color:#333;">${item.descricao}</span>
                        ${tipo !== 'check' && tipo !== 'toggle' ? `<div style="margin-top:2px;"><span class="chk-type-badge">${tipo === 'arquivo' ? 'REQUER ARQUIVO' : 'REQUER LINK'}</span></div>` : ''}
                    </div>
                </div>
                <div class="chk-action-area" style="padding-left:34px;">
                    ${acaoHtml}
                </div>
            </div>
        `;
    });

    html += `</div>`; // Fecha container scroll

    // Envolve a lista em um div que será lido na submissão
    container.innerHTML = `<div id="checklistFormContainer">${html}</div>`;
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
    const loadingSpan = document.getElementById(`loading_${idx}`);
    if(loadingSpan) loadingSpan.style.display = 'inline';

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', { method: 'POST', body: fd });
        const json = await resp.json();
        
        if (json.ok) {
            // Recarrega o checklist para mostrar o estado "Concluído"
            atualizarContextoTarefa(tarefaId);
        } else {
            alert('Erro: ' + json.erro);
            if(loadingSpan) loadingSpan.style.display = 'none';
        }
    } catch (e) { 
        console.error(e); 
        alert('Erro de conexão'); 
        if(loadingSpan) loadingSpan.style.display = 'none';
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
 * Salva o checkbox simples (toggle)
 */
async function toggleCheckItemPainel(tarefaId, idx, checkbox) {
    try {
        await fetch('../api/tarefa_checklist_toggle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tarefa_id: tarefaId, index: idx, feito: checkbox.checked, acao: 'toggle' })
        });
        // Atualiza para refletir progresso visualmente e travar se necessário
        atualizarContextoTarefa(tarefaId);
    } catch (e) {
        checkbox.checked = !checkbox.checked; // Reverte se der erro
        alert("Erro de conexão ao salvar.");
    }
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
 * Recarrega os dados da tarefa para atualizar a view do checklist
 */
async function atualizarContextoTarefa(id) {
    const resp = await fetch(`../api/tarefa_buscar.php?id=${id}`);
    const json = await resp.json();
    if(json.ok) {
        tarefaAtualContexto = json.tarefa;
        // Reabre o modal com os novos dados para atualizar a UI
        // Nota: Isso pode causar um "piscar" na tela, idealmente faríamos update parcial do DOM,
        // mas reutilizar abrirModalExecucao garante consistência total.
        abrirModalExecucao(json.tarefa); 
    }
}

// Submissão Final do Formulário Principal (Botão "Salvar Progresso" ou "Enviar")
document.getElementById('formEntrega').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const txtOriginal = btn.innerText;
    
    btn.disabled = true; 
    btn.innerText = "Processando...";

    // Prepara FormData base
    const formData = new FormData(this);

    // O status do checklist agora é salvo em tempo real via AJAX nos itens individuais.
    // O formulário principal foca em atualizar status da tarefa, comentário geral e arquivo de entrega geral.
    
    try {
        const resp = await fetch('../api/tarefa_entregar.php', { method: 'POST', body: formData });
        const json = await resp.json();

        if (json.ok) {
            alert(json.mensagem);
            
            // Se concluiu a tarefa, para o timer
            if(timerState.running && timerState.tarefaId == document.getElementById('execId').value) {
                toggleTimerPainel(); // Isso vai parar o timer
            }
            
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
    const modal = document.getElementById('modalExecucao');
    if (event.target == modal) {
        fecharPainelLateral();
    }
}