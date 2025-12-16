// ARQUIVO: js/minhas_tarefas.js

// --- ÍCONES SVG PARA O HISTÓRICO (Visual Limpo - Sem Emojis) ---
const ICONS_HISTORICO = {
    file: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4318FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>`,
    chat: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2b3674" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>`,
    status: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#05cd99" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>`,
    alert: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ee5d50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`,
    upload: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4318FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>`
};

// Variável para guardar a tarefa atual
let tarefaAtualId = null;

// --- VARIÁVEIS DO TIMER ---
let timerState = {
    running: false,
    seconds: 0,
    intervalId: null,
    tarefaId: null,
    titulo: ''
};

// Inicialização do Timer Flutuante
document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('floatingTimer')) {
        const floatDiv = document.createElement('div');
        floatDiv.id = 'floatingTimer';
        floatDiv.className = 'floating-timer-container';
        floatDiv.style.display = 'none'; // Começa oculto
        floatDiv.onclick = reabrirModalTimer; 
        floatDiv.innerHTML = `
            <div class="float-icon-pulse"></div>
            <span id="floatTimerText" class="float-time-text">00:00</span>
            <span style="font-size:0.8rem; opacity:0.8; margin-left:5px;">Em andamento</span>
        `;
        document.body.appendChild(floatDiv);
    }
});

// --- FUNÇÃO PRINCIPAL: ABRIR MODAL ---
async function abrirModalExecucao(tarefa) {
    const modal = document.getElementById('modalExecucao');
    if (!modal) return;

    tarefaAtualId = tarefa.id;

    // 1. Reset Visual do Formulário
    const form = document.getElementById('formEntrega');
    if(form) form.reset();
    document.getElementById('uploadText').innerText = "Clique para anexar arquivo final";
    
    // Volta para a primeira aba (Execução)
    const firstTab = document.querySelector('.modal-tabs .modal-tab:first-child');
    if(firstTab) switchExecTab('execucao', firstTab);

    // 2. Preenche Cabeçalho e Descrição
    document.getElementById('execId').value = tarefa.id;
    document.getElementById('execTitulo').innerText = tarefa.titulo;
    document.getElementById('execDesc').innerText = tarefa.descricao || "Sem descrição detalhada.";
    
    // 3. Define Status Atual
    const selStatus = document.getElementById('execStatus');
    // Se PENDENTE, sugere EM ANDAMENTO para facilitar
    selStatus.value = (tarefa.status === 'PENDENTE') ? 'EM_ANDAMENTO' : tarefa.status;
    
    // 4. Feedback (Gestor)
    const areaFeedback = document.getElementById('execFeedbackArea');
    if(tarefa.feedback_revisao && tarefa.status !== 'CONCLUIDA') {
        areaFeedback.style.display = 'block';
        document.getElementById('execFeedbackText').innerText = tarefa.feedback_revisao;
    } else {
        areaFeedback.style.display = 'none';
    }

    // 5. Cronograma
    const cronoDiv = document.getElementById('execCronograma');
    if(tarefa.prazo) {
        const d = new Date(tarefa.prazo);
        const hoje = new Date();
        const diff = Math.ceil((d - hoje) / (1000 * 60 * 60 * 24));
        let cor = diff < 0 ? '#e74c3c' : (diff < 3 ? '#f1c40f' : '#2ecc71');
        
        cronoDiv.innerHTML = `<span style="color:${cor}; font-weight:bold;">📅 Entrega: ${d.toLocaleDateString('pt-BR')} ${d.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'})}</span>`;
    } else {
        cronoDiv.innerHTML = `<span style="color:#999;">Sem prazo definido</span>`;
    }

    // 6. Renderizações Principais
    renderizarChecklist(tarefa.checklist, tarefa.id);
    carregarHistorico(tarefa.historico_mensagens);

    // 7. Sincroniza Botão do Timer (Se estiver rodando para esta tarefa)
    const btnTimer = document.getElementById('btnTimerPanel');
    if (btnTimer) {
        if (timerState.running && timerState.tarefaId == tarefa.id) {
            btnTimer.innerHTML = "⏸ Pausar";
            btnTimer.style.backgroundColor = "#ffce20"; 
            btnTimer.style.color = "#333";
        } else {
            btnTimer.innerHTML = "▶ Iniciar"; 
            btnTimer.style.backgroundColor = ""; 
            btnTimer.style.color = "";
        }
    }

    modal.style.display = 'flex';
}

// --- RENDERIZAÇÃO DO CHECKLIST ---
function renderizarChecklist(jsonChecklist, tarefaId) {
    const container = document.getElementById('listaChecklistColab');
    const txtProg = document.getElementById('execProgressoTexto');
    container.innerHTML = '';

    let itens = [];
    try { itens = typeof jsonChecklist === 'string' ? JSON.parse(jsonChecklist) : jsonChecklist; } catch(e){}

    if(!itens || itens.length === 0) {
        container.innerHTML = '<div style="padding:20px; text-align:center; color:#999;">Nenhum item de checklist.</div>';
        if(txtProg) txtProg.innerText = '0%';
        return;
    }

    // Calcula progresso
    const feitos = itens.filter(i => i.concluido == 1).length;
    if(txtProg) txtProg.innerText = Math.round((feitos/itens.length)*100) + '%';

    itens.forEach((item, idx) => {
        const div = document.createElement('div');
        div.style.cssText = "padding:12px; border-bottom:1px solid #f0f0f0; display:flex; flex-direction:column; gap:8px;";
        
        let htmlBase = `
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:600; color:#333; ${item.concluido ? 'text-decoration:line-through; opacity:0.6;' : ''}">${item.descricao}</span>
                <span style="font-size:0.65rem; background:#f0f0f0; padding:2px 6px; border-radius:4px; text-transform:uppercase; color:#666;">${item.tipo_evidencia || 'CHECK'}</span>
            </div>
        `;

        // Se CONCLUÍDO -> Mostra Link/Arquivo
        if(item.concluido) {
            let acaoVer = '';
            if(item.evidencia_url) {
                // Ajusta caminho se for local ou externo
                const url = item.evidencia_url.startsWith('http') ? item.evidencia_url : '../public/' + item.evidencia_url;
                const nomeArq = item.evidencia_nome || 'Ver Entrega';
                acaoVer = `<a href="${url}" target="_blank" style="text-decoration:none; color:#2e7d32; font-weight:bold; font-size:0.85rem; display:flex; align-items:center; gap:5px;">📄 ${nomeArq}</a>`;
            } else {
                acaoVer = `<span style="color:#2e7d32; font-size:0.85rem;">✅ Feito</span>`;
            }

            htmlBase += `
                <div style="background:#e8f5e9; padding:8px; border-radius:6px; display:flex; justify-content:space-between; align-items:center;">
                    ${acaoVer}
                    <button type="button" onclick="reverterItem(${tarefaId}, ${idx})" style="background:none; border:none; color:#d32f2f; cursor:pointer; font-size:0.8rem;">(Desfazer)</button>
                </div>
                <input type="hidden" name="checklist_done[]" value="${idx}">
            `;
        } 
        // Se PENDENTE -> Mostra Input
        else {
            if(item.tipo_evidencia === 'arquivo') {
                htmlBase += `
                    <div style="display:flex; gap:10px; align-items:center; margin-top:5px;">
                        <label class="botao-secundario" style="font-size:0.8rem; padding:6px 12px; cursor:pointer; border:1px dashed #4318FF; color:#4318FF; background:#f4f7fe;">
                            ${ICONS_HISTORICO.upload} Anexar Arquivo
                            <input type="file" style="display:none;" onchange="uploadItemChecklist(this, ${tarefaId}, ${idx})">
                        </label>
                        <span id="loading_chk_${idx}" style="display:none; font-size:0.8rem; color:#666;">Enviando...</span>
                    </div>
                `;
            } else if (item.tipo_evidencia === 'link') {
                htmlBase += `
                    <div style="display:flex; gap:5px; margin-top:5px;">
                        <input type="text" id="link_chk_${idx}" class="campo-padrao" placeholder="Cole o link aqui..." style="margin:0 !important; font-size:0.85rem; padding:6px;">
                        <button type="button" onclick="salvarLinkChecklist(${tarefaId}, ${idx})" class="botao-primario" style="padding:6px 12px; font-size:0.8rem;">Salvar</button>
                    </div>
                `;
            } else {
                // Checkbox simples (salvo no submit geral)
                htmlBase += `
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:5px;">
                        <input type="checkbox" name="checklist_done[]" value="${idx}">
                        <span style="font-size:0.9rem;">Marcar como feito</span>
                    </label>
                `;
            }
        }

        div.innerHTML = htmlBase;
        container.appendChild(div);
    });
}

// --- RENDERIZAR HISTÓRICO COM ÍCONES (SEM EMOJIS) ---
function carregarHistorico(mensagens) {
    const container = document.getElementById('containerHistorico');
    container.innerHTML = '';

    if(!mensagens || mensagens.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:30px; color:#ccc; font-style:italic;">Nenhuma atividade registrada ainda.</div>';
        return;
    }

    mensagens.forEach(msg => {
        let texto = msg.mensagem;
        let anexoHtml = '';
        let tipoIcone = ICONS_HISTORICO.chat; 
        
        // Verifica Anexo no texto
        if(texto.includes('[ARQUIVO_ANEXO]:')) {
            const parts = texto.split('[ARQUIVO_ANEXO]:');
            texto = parts[0]; 
            if(parts[1]) {
                tipoIcone = ICONS_HISTORICO.file;
                const fileData = parts[1].split(':');
                const fileUrl = '../public/' + fileData[0];
                const fileName = fileData[1] || 'Anexo';
                
                anexoHtml = `
                    <a href="${fileUrl}" target="_blank" style="display:flex; align-items:center; gap:10px; background:white; padding:10px; border:1px solid #e0e5f2; border-radius:8px; text-decoration:none; margin-top:8px; transition:0.2s;">
                        <div style="background:#eef2ff; padding:8px; border-radius:6px;">${ICONS_HISTORICO.file}</div>
                        <div style="font-weight:600; color:#2b3674; font-size:0.9rem;">${fileName}</div>
                    </a>
                `;
            }
        }

        // Verifica tipo de mensagem para ícone
        const txtLower = texto.toLowerCase();
        if(txtLower.includes('status') || txtLower.includes('enviou') || txtLower.includes('aprovou')) {
            tipoIcone = ICONS_HISTORICO.status;
        }
        if(txtLower.includes('motivo') || txtLower.includes('devolveu') || txtLower.includes('feedback')) {
            tipoIcone = ICONS_HISTORICO.alert;
        }

        // Estilo visual (Destaque para gestor)
        const isGestor = (msg.papel === 'GESTOR' || msg.papel === 'DONO' || msg.papel === 'LIDER');
        const bg = isGestor ? '#fffbf0' : '#f4f7fe';
        const border = isGestor ? '1px solid #ffe0b2' : '1px solid #e0e5f2';

        const div = document.createElement('div');
        div.style.cssText = `background:${bg}; border:${border}; padding:15px; border-radius:12px; margin-bottom:12px;`;
        div.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="opacity:0.8;">${tipoIcone}</div>
                    <strong style="color:#2b3674; font-size:0.85rem;">${msg.nome}</strong>
                    <span style="font-size:0.7rem; background:rgba(0,0,0,0.05); padding:2px 6px; border-radius:4px;">${msg.papel}</span>
                </div>
                <span style="font-size:0.75rem; color:#999;">${msg.data_formatada}</span>
            </div>
            <div style="font-size:0.9rem; color:#444; line-height:1.5; padding-left:28px;">${texto}</div>
            <div style="padding-left:28px;">${anexoHtml}</div>
        `;
        container.appendChild(div);
    });
}

// --- LÓGICA DO TIMER (MANTIDA) ---
function toggleTimerPainel() {
    const btn = document.getElementById('btnTimerPanel');
    
    if (!timerState.running) {
        // INICIAR
        timerState.running = true;
        timerState.tarefaId = document.getElementById('execId').value;
        const elTitulo = document.getElementById('execTitulo');
        timerState.titulo = elTitulo ? elTitulo.innerText : 'Tarefa';
        
        if(btn) {
            btn.innerHTML = "⏸ Pausar";
            btn.style.backgroundColor = "#ffce20"; 
            btn.style.color = "#333";
        }
        
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
            btn.innerHTML = "▶ Iniciar";
            btn.style.backgroundColor = ""; 
            btn.style.color = "";
        }
        
        const minutos = Math.ceil(timerState.seconds / 60);
        if (minutos > 0) {
            salvarTempoAPI(timerState.tarefaId, minutos);
            timerState.seconds = 0; 
        }
        
        const floatTimer = document.getElementById('floatingTimer');
        if(floatTimer) floatTimer.style.display = 'none';
    }
}

function atualizarDisplaysTimer() {
    const format = formatTime(timerState.seconds);
    const floatText = document.getElementById('floatTimerText');
    if (floatText) floatText.innerText = format;
}

function formatTime(totalSeconds) {
    const m = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
    const s = (totalSeconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
}

// --- FUNÇÕES AJAX (UPLOADS E AÇÕES) ---

// Upload de Arquivo no Checklist
async function uploadItemChecklist(input, tarefaId, index) {
    if(!input.files[0]) return;
    const loading = document.getElementById(`loading_chk_${index}`);
    if(loading) loading.style.display = 'inline';

    const fd = new FormData();
    fd.append('tarefa_id', tarefaId);
    fd.append('index', index);
    fd.append('acao', 'upload');
    fd.append('arquivo_item', input.files[0]);

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', { method: 'POST', body: fd });
        const json = await resp.json();
        if(json.ok) {
            recaregarTarefa(tarefaId); 
        } else {
            alert("Erro: " + json.erro);
            if(loading) loading.style.display = 'none';
        }
    } catch(e) { 
        alert("Erro ao enviar arquivo."); 
        if(loading) loading.style.display = 'none';
    }
}

// Salvar Link no Checklist
async function salvarLinkChecklist(tarefaId, index) {
    const input = document.getElementById(`link_chk_${index}`);
    const url = input.value.trim();
    if(!url) return alert("Digite uma URL.");

    const fd = new FormData();
    fd.append('tarefa_id', tarefaId);
    fd.append('index', index);
    fd.append('acao', 'link');
    fd.append('link_url', url);

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', { method: 'POST', body: fd });
        const json = await resp.json();
        if(json.ok) recaregarTarefa(tarefaId);
        else alert("Erro: " + json.erro);
    } catch(e) { alert("Erro de conexão."); }
}

// Reverter Item Concluído
async function reverterItem(tarefaId, index) {
    if(!confirm("Remover esta evidência?")) return;
    
    const fd = new FormData();
    fd.append('tarefa_id', tarefaId);
    fd.append('index', index);
    fd.append('acao', 'remover_evidencia');

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', { method: 'POST', body: fd });
        const json = await resp.json();
        if(json.ok) recaregarTarefa(tarefaId);
    } catch(e) { alert("Erro de conexão."); }
}

// Salvar Tempo (Timer)
async function salvarTempoAPI(id, minutos) {
    const fd = new FormData(); 
    fd.append('tarefa_id', id); 
    fd.append('tempo_gasto', minutos);
    try { await fetch('../api/tarefa_entregar.php', { method: 'POST', body: fd }); } catch (e) {}
}

// Recarregar Tarefa
async function recaregarTarefa(id) {
    const resp = await fetch(`../api/tarefa_buscar.php?id=${id}`);
    const json = await resp.json();
    if(json.ok) abrirModalExecucao(json.tarefa);
}

// --- SUBMIT GERAL DO FORMULÁRIO ---
document.getElementById('formEntrega').addEventListener('submit', async function(e){
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const txt = btn.innerText;
    
    btn.innerText = "Salvando..."; 
    btn.disabled = true;

    const fd = new FormData(this);

    // Se o timer estiver rodando para esta tarefa, para e salva o tempo
    if(timerState.running && timerState.tarefaId == fd.get('tarefa_id')) {
        toggleTimerPainel(); 
        const minutos = Math.ceil(timerState.seconds / 60);
        if(minutos > 0) fd.append('tempo_gasto', minutos);
    }

    try {
        const resp = await fetch('../api/tarefa_entregar.php', { method: 'POST', body: fd });
        const json = await resp.json();
        
        if(json.ok) {
            alert("Atualizado com sucesso!");
            window.location.reload();
        } else {
            alert("Erro: " + json.erro);
            btn.innerText = txt; 
            btn.disabled = false;
        }
    } catch(e) {
        alert("Erro de conexão.");
        btn.innerText = txt; 
        btn.disabled = false;
    }
});

// --- UI HELPERS ---
function previewUpload(input) {
    if(input.files && input.files[0]) {
        document.getElementById('uploadText').innerText = "Selecionado: " + input.files[0].name;
    }
}

function switchExecTab(tab, btn) {
    document.querySelectorAll('.tab-exec-panel').forEach(p => p.style.display = 'none');
    document.getElementById('tab-' + tab).style.display = 'block';
    document.querySelectorAll('.modal-tabs .modal-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

// Funções do Modal/Painel
function fecharPainelLateral() {
    const modal = document.getElementById('modalExecucao');
    if(modal) modal.style.display = 'none';
    
    if (timerState.running) {
        const floatTimer = document.getElementById('floatingTimer');
        if(floatTimer) floatTimer.style.display = 'flex';
    }
}

async function reabrirModalTimer() {
    if (timerState.tarefaId) {
        const floatTimer = document.getElementById('floatingTimer');
        if(floatTimer) floatTimer.style.display = 'none';
        
        try {
            const resp = await fetch(`../api/tarefa_buscar.php?id=${timerState.tarefaId}`);
            const json = await resp.json();
            if(json.ok) abrirModalExecucao(json.tarefa);
        } catch(e) {}
    }
}

// Expor função global para fechar modal
window.closeModal = function(id) {
    if(id === 'modalExecucao') fecharPainelLateral();
    else document.getElementById(id).style.display = 'none';
}

// Fechar ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('modalExecucao');
    if (event.target == modal) {
        fecharPainelLateral();
    }
}