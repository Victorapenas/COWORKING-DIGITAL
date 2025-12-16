// ARQUIVO: js/minhas_tarefas.js

// Variável para guardar a tarefa atual e recarregar quando necessário
let tarefaAtualId = null;

// Variáveis de Estado do Timer (Caso exista na tela)
let timerState = {
    running: false,
    seconds: 0,
    intervalId: null,
    tarefaId: null,
    titulo: ''
};

// --- FUNÇÃO PRINCIPAL: ABRIR MODAL ---
async function abrirModalExecucao(tarefa) {
    const modal = document.getElementById('modalExecucao');
    if (!modal) return;

    tarefaAtualId = tarefa.id;

    // 1. Reset Visual do Formulário
    const form = document.getElementById('formEntrega');
    if(form) form.reset();
    
    document.getElementById('uploadText').innerText = "Clique para anexar arquivo final";
    
    // Volta para a primeira aba
    const firstTab = document.querySelector('.modal-tabs .modal-tab:first-child');
    if(firstTab) switchExecTab('execucao', firstTab);

    // 2. Preenche Cabeçalho e Descrição
    document.getElementById('execId').value = tarefa.id;
    document.getElementById('execTitulo').innerText = tarefa.titulo;
    document.getElementById('execDesc').innerText = tarefa.descricao || "Sem descrição detalhada.";
    
    // 3. Define Status Atual no Select
    const selStatus = document.getElementById('execStatus');
    // Se estiver 'PENDENTE' visualmente mostra como 'EM_ANDAMENTO' para o usuário começar
    selStatus.value = (tarefa.status === 'PENDENTE') ? 'EM_ANDAMENTO' : tarefa.status;
    
    // 4. Exibe Feedback se houver (e não estiver concluída)
    const areaFeedback = document.getElementById('execFeedbackArea');
    if(tarefa.feedback_revisao && tarefa.status !== 'CONCLUIDA') {
        areaFeedback.style.display = 'block';
        document.getElementById('execFeedbackText').innerText = tarefa.feedback_revisao;
    } else {
        areaFeedback.style.display = 'none';
    }

    // 5. Renderiza Cronograma (Prazo)
    const cronoDiv = document.getElementById('execCronograma');
    if(tarefa.prazo) {
        const d = new Date(tarefa.prazo);
        const hoje = new Date();
        const diff = Math.ceil((d - hoje) / (1000 * 60 * 60 * 24));
        let cor = diff < 0 ? '#e74c3c' : (diff < 3 ? '#f1c40f' : '#2ecc71');
        
        // Ajuste de fuso horário simples para exibição
        const dataFormatada = d.toLocaleDateString('pt-BR');
        const horaFormatada = d.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
        
        cronoDiv.innerHTML = `<span style="color:${cor}; font-weight:bold;">📅 Entrega: ${dataFormatada} às ${horaFormatada}</span>`;
    } else {
        cronoDiv.innerHTML = `<span style="color:#999;">Sem prazo definido</span>`;
    }

    // 6. RENDERIZA O CHECKLIST (Com lógica de Upload Individual)
    renderizarChecklist(tarefa.checklist, tarefa.id);

    // 7. CARREGA O HISTÓRICO (Chat e Arquivos)
    carregarHistorico(tarefa.historico_mensagens);

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

    // Calcula progresso visual
    const total = itens.length;
    const feitos = itens.filter(i => i.concluido == 1).length;
    if(txtProg) txtProg.innerText = Math.round((feitos/total)*100) + '%';

    itens.forEach((item, idx) => {
        const div = document.createElement('div');
        div.style.cssText = "padding:12px; border-bottom:1px solid #eee; display:flex; flex-direction:column; gap:8px;";
        
        // Cabeçalho do item
        let htmlBase = `
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:600; color:#333; ${item.concluido ? 'text-decoration:line-through; opacity:0.7;' : ''}">${item.descricao}</span>
                <span style="font-size:0.7rem; background:#f0f0f0; padding:2px 6px; border-radius:4px; text-transform:uppercase;">${item.tipo_evidencia || 'CHECK'}</span>
            </div>
        `;

        // Se JÁ ESTÁ CONCLUÍDO -> Mostra botão de ver/remover
        if(item.concluido) {
            let linkEvidencia = '#';
            let nomeEvidencia = 'Item Concluído';
            
            // Verifica link do arquivo/url
            if(item.evidencia_url) {
                linkEvidencia = item.evidencia_url.startsWith('http') ? item.evidencia_url : '../public/' + item.evidencia_url;
                nomeEvidencia = item.evidencia_nome || 'Ver Anexo';
            }

            htmlBase += `
                <div style="background:#e8f5e9; padding:8px; border-radius:6px; display:flex; justify-content:space-between; align-items:center;">
                    <a href="${linkEvidencia}" target="_blank" style="text-decoration:none; color:#2e7d32; font-weight:bold; font-size:0.85rem;">✅ ${nomeEvidencia}</a>
                    <button type="button" onclick="reverterItem(${tarefaId}, ${idx})" style="background:none; border:none; color:#d32f2f; cursor:pointer; font-size:0.8rem;">(Desfazer)</button>
                </div>
            `;
            // Hidden input para manter o status no submit geral (caso seja check simples)
            htmlBase += `<input type="hidden" name="checklist_done[]" value="${idx}">`;
        } 
        // Se PENDENTE -> Mostra o input adequado
        else {
            if(item.tipo_evidencia === 'arquivo') {
                htmlBase += `
                    <div style="display:flex; gap:10px; align-items:center;">
                        <label class="botao-secundario" style="font-size:0.8rem; padding:5px 10px; cursor:pointer; border-style:dashed;">
                            📤 Enviar Arquivo
                            <input type="file" style="display:none;" onchange="uploadItemChecklist(this, ${tarefaId}, ${idx})">
                        </label>
                        <span id="loading_chk_${idx}" style="display:none; font-size:0.8rem; color:#666;">Enviando...</span>
                    </div>
                `;
            } else if (item.tipo_evidencia === 'link') {
                htmlBase += `
                    <div style="display:flex; gap:5px;">
                        <input type="text" id="link_chk_${idx}" class="campo-padrao" placeholder="Cole o link aqui..." style="margin:0 !important; font-size:0.85rem;">
                        <button type="button" onclick="salvarLinkChecklist(${tarefaId}, ${idx})" class="botao-primario" style="padding:5px 10px; font-size:0.8rem;">Salvar</button>
                    </div>
                `;
            } else {
                // Checkbox Simples
                htmlBase += `
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
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

// --- CARREGA HISTÓRICO (CHAT) ---
function carregarHistorico(mensagens) {
    const container = document.getElementById('containerHistorico');
    container.innerHTML = '';

    if(!mensagens || mensagens.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:20px; color:#ccc;">Nenhum registro de atividade ainda.</div>';
        return;
    }

    mensagens.forEach(msg => {
        // Detecta se tem anexo na mensagem (formato: [ARQUIVO_ANEXO]:caminho:nome)
        let texto = msg.mensagem;
        let anexoHtml = '';
        
        if(texto.includes('[ARQUIVO_ANEXO]:')) {
            const parts = texto.split('[ARQUIVO_ANEXO]:');
            texto = parts[0]; // Texto antes do anexo
            if(parts[1]) {
                const fileData = parts[1].split(':');
                const fileUrl = '../public/' + fileData[0];
                const fileName = fileData[1] || 'Anexo';
                
                anexoHtml = `
                    <div style="margin-top:10px; background:white; padding:8px; border-radius:6px; border:1px solid #ddd;">
                        <a href="${fileUrl}" target="_blank" style="text-decoration:none; color:#0d6efd; font-weight:bold; display:flex; align-items:center; gap:5px;">
                            📎 ${fileName}
                        </a>
                    </div>
                `;
            }
        }

        // Estilo diferente para Gestor/Líder
        const bg = (msg.papel === 'GESTOR' || msg.papel === 'DONO' || msg.papel === 'LIDER') ? '#fff3e0' : '#f1f3f9';
        const border = (msg.papel === 'GESTOR' || msg.papel === 'DONO' || msg.papel === 'LIDER') ? '4px solid #ffb74d' : '4px solid #0d6efd';

        const div = document.createElement('div');
        div.style.cssText = `background:${bg}; border-left:${border}; padding:15px; border-radius:8px; margin-bottom:10px;`;
        div.innerHTML = `
            <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:0.75rem; color:#666;">
                <strong>${msg.nome} (${msg.papel})</strong>
                <span>${msg.data_formatada}</span>
            </div>
            <div style="font-size:0.9rem; color:#333; line-height:1.4;">${texto}</div>
            ${anexoHtml}
        `;
        container.appendChild(div);
    });
}

// --- AÇÕES DO CHECKLIST (AJAX) ---

// 1. Upload de Arquivo
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
            recaregarTarefa(tarefaId); // Recarrega para atualizar a UI
        } else {
            alert("Erro: " + json.erro);
            if(loading) loading.style.display = 'none';
        }
    } catch(e) { 
        console.error(e); 
        alert("Erro ao enviar arquivo."); 
        if(loading) loading.style.display = 'none';
    }
}

// 2. Salvar Link
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

// 3. Reverter Item (Desfazer conclusão)
async function reverterItem(tarefaId, index) {
    if(!confirm("Remover esta evidência e marcar como pendente?")) return;
    
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

// --- FUNÇÕES AUXILIARES ---

// Recarrega os dados completos da tarefa (chamado após uploads)
async function recaregarTarefa(id) {
    const resp = await fetch(`../api/tarefa_buscar.php?id=${id}`);
    const json = await resp.json();
    if(json.ok) abrirModalExecucao(json.tarefa);
}

// SUBMIT DO FORMULÁRIO GERAL (COMENTÁRIO + STATUS + ARQUIVO FINAL)
document.getElementById('formEntrega').addEventListener('submit', async function(e){
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const txt = btn.innerText;
    
    btn.innerText = "Salvando..."; 
    btn.disabled = true;

    const fd = new FormData(this);

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

// Helpers de UI
function previewUpload(input) {
    if(input.files && input.files[0]) {
        document.getElementById('uploadText').innerText = "Selecionado: " + input.files[0].name;
    }
}

function switchExecTab(tab, btn) {
    // Esconde todos os painéis
    document.querySelectorAll('.tab-exec-panel').forEach(p => p.style.display = 'none');
    // Mostra o selecionado
    document.getElementById('tab-' + tab).style.display = 'block';
    
    // Atualiza classes dos botões
    document.querySelectorAll('.modal-tabs .modal-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

// Função global para fechar modal
window.closeModal = function(id) {
    const el = document.getElementById(id);
    if(el) el.style.display = 'none';
}

// Fecha modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('modalExecucao');
    if (event.target == modal) {
        closeModal('modalExecucao');
    }
}