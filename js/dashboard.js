// ARQUIVO: js/dashboard.js

document.addEventListener('DOMContentLoaded', () => {
    carregarDashboard();
});

// --- ÍCONES SVG ---
const ICONS = {
    rocket: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z'></path><path d='M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z'></path><path d='M9 12H4s.55-3.03 2-4c1.62-1.1 2.72-2 2.72-2'></path><path d='M15 13v5s3.03-.55 4-2c1.1-1.62 2-2.72 2-2.72'></path></svg>`,
    users: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'></path><circle cx='9' cy='7' r='4'></circle><path d='M23 21v-2a4 4 0 0 0-3-3.87'></path><path d='M16 3.13a4 4 0 0 1 0 7.75'></path></svg>`,
    check: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'></polyline></svg>`,
    alerta: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='12' r='10'></circle><line x1='12' y1='8' x2='12' y2='12'></line><line x1='12' y1='16' x2='12.01' y2='16'></line></svg>`,
    folder: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'></path></svg>`,
    olho: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path><circle cx='12' cy='12' r='3'></circle></svg>`,
    task: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M12 20h9'></path><path d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z'></path></svg>`,
    chart: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><line x1='18' y1='20' x2='18' y2='10'></line><line x1='12' y1='20' x2='12' y2='4'></line><line x1='6' y1='20' x2='6' y2='14'></line></svg>`,
    clock: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='12' r='10'></circle><polyline points='12 6 12 12 16 14'></polyline></svg>`
};

// --- CARREGAMENTO GERAL ---
async function carregarDashboard() {
    try {
        const resp = await fetch('../api/dashboard_stats.php');
        const data = await resp.json();
        
        if (!data.ok) throw new Error(data.erro);

        const userSpan = document.getElementById('userName');
        if(userSpan) userSpan.innerText = data.usuario_nome;

        if (data.papel === 'COLABORADOR' || data.papel === 'FUNCIONARIO') {
            renderizarDashboardColab(data);
        } else {
            renderizarDashboardGestor(data);
        }

    } catch (err) {
        console.error("Erro ao carregar dashboard:", err);
    }
}

// =============================================================================
// 1. VISÃO DO COLABORADOR
// =============================================================================

function renderizarDashboardColab(data) {
    // Renderiza KPIs
    const kpiContainer = document.querySelector('.kpi-grid-modern');
    if (kpiContainer && data.kpis) {
        kpiContainer.innerHTML = data.kpis.map(k => `
            <div class="kpi-card-modern">
                <div class="kpi-content">
                    <h3 style="color:${k.cor === 'red' ? '#e74c3c' : '#2b3674'}">${k.valor}</h3>
                    <span>${k.titulo}</span>
                </div>
                <div class="kpi-icon-modern kc-${k.cor}">${ICONS[k.icone] || ICONS.task}</div>
            </div>
        `).join('');
    }

    // Renderiza Lista de Tarefas
    const lista = document.getElementById('listaTarefasColab');
    if (lista) {
        if (data.minhas_tarefas.length === 0) {
            lista.innerHTML = `<div style="text-align:center; color:#ccc; padding:30px;">Tudo limpo! Sem tarefas pendentes.</div>`;
        } else {
            lista.innerHTML = data.minhas_tarefas.map(t => {
                const tJson = JSON.stringify(t).replace(/"/g, '&quot;');
                
                // Status Visual
                let statusBadge = '';
                let rowStyle = '';
                
                if(t.status === 'EM_REVISAO') {
                    statusBadge = `<span style="background:#fff3cd; color:#856404; padding:2px 8px; border-radius:4px; font-size:0.7rem; font-weight:bold;">⏳ EM REVISÃO</span>`;
                    rowStyle = 'opacity: 0.7;';
                } else if (t.feedback_revisao) {
                    statusBadge = `<span style="background:#ffebee; color:#c62828; padding:2px 8px; border-radius:4px; font-size:0.7rem; font-weight:bold;">⚠️ CORREÇÃO</span>`;
                    rowStyle = 'border-left: 4px solid #c62828;';
                }

                let pClass = 'tp-normal';
                if(t.prioridade === 'URGENTE') pClass = 'tp-alta';
                else if(t.prioridade === 'IMPORTANTE') pClass = 'tp-media';

                return `
                <div class="task-row" onclick="abrirPainelTarefa('${tJson}')" style="${rowStyle}">
                    <div class="task-icon">${ICONS.task}</div>
                    <div class="task-info">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <h4>${t.titulo}</h4>
                            ${statusBadge}
                        </div>
                        <span>${t.projeto_nome || 'Geral'} • ${new Date(t.prazo).toLocaleDateString('pt-BR')}</span>
                    </div>
                    <span class="task-prio ${pClass}" style="margin-left:10px;">${t.prioridade}</span>
                </div>
                `;
            }).join('');
        }
    }

    // Renderiza Gráfico
    if (document.getElementById('chartProdColab') && data.grafico) {
        renderizarGrafico(document.getElementById('chartProdColab'), data.grafico);
    }
}

// --- PAINEL LATERAL DO COLABORADOR ---
let tarefaAtualColab = null;

function abrirPainelTarefa(tarefaJson) {
    const tarefa = typeof tarefaJson === 'string' ? JSON.parse(tarefaJson) : tarefaJson;
    tarefaAtualColab = tarefa; // Guarda referência global
    
    const modal = document.getElementById('painelLateral');
    const body = document.getElementById('painelLateralBody');
    const footer = document.getElementById('painelLateralFooter');
    
    document.querySelector('.side-overlay').classList.add('open');
    modal.classList.add('open');

    // 1. Cabeçalho e Feedback de Erro (se houver)
    let feedbackHtml = '';
    if(tarefa.feedback_revisao && tarefa.status !== 'EM_REVISAO') {
        feedbackHtml = `
        <div style="background:#ffebee; border-left:4px solid #f44336; padding:15px; margin-bottom:20px; border-radius:4px;">
            <strong style="color:#d32f2f;">⚠️ Correção Solicitada pelo Gestor:</strong>
            <p style="margin:5px 0 0; color:#333; font-style:italic;">"${tarefa.feedback_revisao}"</p>
        </div>`;
    }

    let statusHtml = `<span class="task-prio tp-normal">${tarefa.status.replace('_',' ')}</span>`;
    if(tarefa.status === 'EM_REVISAO') statusHtml = `<span class="task-prio" style="background:#fff3cd; color:#856404;">⏳ EM ANÁLISE</span>`;

    let html = `
        ${feedbackHtml}
        <div style="margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                ${statusHtml}
                <span style="font-size:0.8rem; color:#888;">Prazo: ${new Date(tarefa.prazo).toLocaleDateString('pt-BR')}</span>
            </div>
            <h2 style="margin:0 0 10px 0; color:#2b3674; font-size:1.4rem;">${tarefa.titulo}</h2>
            <p style="font-size:0.9rem; color:#666; line-height:1.5;">${tarefa.descricao || 'Sem descrição.'}</p>
        </div>
        
        <h4 style="margin:20px 0 10px; font-size:0.95rem; color:#333;">Checklist de Entregas</h4>
        <div id="listaChecklistPainel">
    `;

    // 2. Checklist com Upload
    let checklist = [];
    try { checklist = JSON.parse(tarefa.checklist || '[]'); } catch(e){}

    if (checklist.length > 0) {
        checklist.forEach((item, idx) => {
            const checked = item.concluido == 1 ? 'checked' : '';
            const isReview = (tarefa.status === 'EM_REVISAO');
            
            // Exibe arquivo se já existir
            let fileHtml = '';
            if(item.arquivo) {
                fileHtml = `
                <div style="margin-top:5px; font-size:0.8rem; display:flex; align-items:center; gap:5px;">
                    <a href="../public/${item.arquivo.url}" target="_blank" style="color:#0d6efd; text-decoration:none;">
                        📎 ${item.arquivo.nome}
                    </a>
                    <span style="color:#999; font-size:0.7rem;">(${item.arquivo.data})</span>
                </div>`;
            }
            
            // Botão de upload (só aparece se não estiver em revisão)
            let uploadBtn = '';
            if(!isReview) {
                uploadBtn = `
                <label style="cursor:pointer; color:#666; font-size:0.75rem; border:1px dashed #ccc; padding:2px 8px; border-radius:4px; margin-top:5px; display:inline-block;">
                    ${item.arquivo ? '🔄 Trocar Arquivo' : '+ Anexar Entrega'}
                    <input type="file" style="display:none;" onchange="uploadArquivoChecklist(${tarefa.id}, ${idx}, this)">
                </label>`;
            }

            html += `
                <div class="chk-item" style="${isReview ? 'opacity:0.8; pointer-events:none;' : ''}">
                    <input type="checkbox" class="chk-checkbox" ${checked} onchange="toggleCheckItemPainel(${tarefa.id}, ${idx}, this)">
                    <div style="flex:1;">
                        <span class="chk-text">${item.descricao}</span>
                        ${fileHtml}
                        ${uploadBtn}
                    </div>
                </div>
            `;
        });
    } else {
        html += `<div style="text-align:center; color:#999; font-style:italic;">Sem itens de checklist.</div>`;
    }
    html += `</div>`;

    body.innerHTML = html;

    // 3. Footer: Botão de Ação
    if (tarefa.status === 'EM_REVISAO') {
        footer.innerHTML = `
            <div style="text-align:center; color:#856404; font-size:0.9rem;">
                <strong>Tarefa em Análise pelo Gestor</strong><br>
                Você não pode editar enquanto estiver em revisão.
            </div>
        `;
    } else {
        footer.innerHTML = `
            <button class="botao-primario" onclick="enviarParaRevisao(${tarefa.id})" style="width:100%; background:#4318FF;">
                🚀 Finalizar e Enviar para Revisão
            </button>
        `;
    }
}

function fecharPainelLateral() {
    document.getElementById('painelLateral').classList.remove('open');
    document.querySelector('.side-overlay').classList.remove('open');
}

// Upload de Arquivo no Checklist
async function uploadArquivoChecklist(tarefaId, index, input) {
    if(!input.files || input.files.length === 0) return;
    
    const file = input.files[0];
    const fd = new FormData();
    fd.append('tarefa_id', tarefaId);
    fd.append('index', index);
    fd.append('arquivo_item', file);
    fd.append('feito', 'true'); // Upload já marca o item como feito

    // Feedback visual
    const label = input.parentElement;
    const originalText = label.innerText;
    label.innerText = "Enviando...";

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', { method:'POST', body:fd });
        const json = await resp.json();
        if(json.ok) {
            // Atualiza apenas visualmente ou recarrega o painel
            // Para simplicidade, vamos recarregar o dashboard e reabrir o painel buscando dados novos
            // (Em produção, ideal seria atualizar o DOM localmente)
            label.innerText = "Sucesso!";
            
            // Recarrega dados e atualiza o modal aberto
            carregarDashboard().then(() => {
               // Reabre o modal com os dados atualizados (precisaria buscar a tarefa atualizada no array)
               // Como simplificação: fecha e pede para usuário reabrir se quiser ver a mudança
               alert("Arquivo enviado com sucesso!");
               fecharPainelLateral();
               carregarDashboard();
            });
        } else {
            alert("Erro: " + json.erro);
            label.innerText = originalText;
        }
    } catch(e) { console.error(e); }
}

async function toggleCheckItemPainel(id, index, checkbox) {
    try {
        await fetch('../api/tarefa_checklist_toggle.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ tarefa_id: id, index: index, feito: checkbox.checked })
        });
        // Não precisa fazer nada se der certo, o checkbox já mudou
    } catch(e) { 
        checkbox.checked = !checkbox.checked; // Reverte erro
        alert("Erro de conexão"); 
    }
}

async function enviarParaRevisao(id) {
    if(!confirm("Confirma que finalizou todas as entregas? A tarefa será enviada para aprovação do gestor.")) return;
    
    const fd = new FormData();
    fd.append('tarefa_id', id);
    fd.append('status', 'CONCLUIDA'); // O backend vai interceptar e mudar para EM_REVISAO
    fd.append('comentario', 'Colaborador finalizou as etapas e enviou para revisão.');

    try {
        const resp = await fetch('../api/tarefa_entregar.php', { method:'POST', body:fd });
        const json = await resp.json();
        
        if(json.ok) {
            alert(json.novo_status === 'EM_REVISAO' ? "Enviado para o Gestor com sucesso! 🚀" : "Tarefa salva.");
            fecharPainelLateral();
            carregarDashboard();
        } else {
            alert(json.erro);
        }
    } catch(e) { alert("Erro de conexão."); }
}


// =============================================================================
// 2. VISÃO DO GESTOR
// =============================================================================

function renderizarDashboardGestor(data) {
    // 1. KPIs
    const kpiContainer = document.getElementById('kpiContainer');
    if (kpiContainer && data.kpis) {
        kpiContainer.innerHTML = data.kpis.map(k => `
            <div class="card-info">
                <div class="icon-box c-${k.cor}" style="width:50px; height:50px; display:flex; align-items:center; justify-content:center;">
                    ${ICONS[k.icone] || ICONS.task}
                </div>
                <div class="kpi-text">
                    <h3>${k.valor || 0}</h3>
                    <p>${k.titulo}</p>
                </div>
            </div>
        `).join('');
    }

    const mainList = document.getElementById('mainListContainer');
    if (mainList) {
        let html = '';

        // 2. LISTA DE APROVAÇÕES (EM_REVISAO) - Prioridade no topo
        if (data.pendencias && data.pendencias.length > 0) {
            html += `<h4 style="margin:0 0 15px 0; color:#d32f2f; display:flex; align-items:center; gap:8px;">
                        ${ICONS.alerta} Atenção Necessária (Aprovações)
                     </h4>`;
            
            html += data.pendencias.map(t => `
                <div style="background:#fff5f5; border:1px solid #ffcdd2; border-radius:12px; padding:15px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-weight:700; color:#b71c1c; font-size:1rem;">${t.titulo}</div>
                        <div style="font-size:0.85rem; color:#555; margin-top:3px;">
                            Entregue por: <strong>${t.responsavel}</strong> • ${t.projeto_nome || 'Geral'}
                        </div>
                    </div>
                    <button class="botao-secundario" onclick="abrirModalRevisao(${t.id})" style="font-size:0.85rem; background:white; border-color:#d32f2f; color:#d32f2f;">
                        🔍 Revisar Entrega
                    </button>
                </div>
            `).join('');
            
            html += `<hr style="border:0; border-top:1px solid #eee; margin:25px 0;">`;
        }

        // 3. Lista Geral
        html += `<h4 style="margin:0 0 15px 0; color:#2b3674;">Acompanhamento Geral</h4>`;
        
        if (data.listas && data.listas.length > 0) {
            // Se for gestor vendo tarefas
            if (data.papel === 'GESTOR') {
                html += data.listas.map(t => `
                    <div class="project-item" style="padding:15px; border-bottom:1px solid #eee;">
                        <div style="display:flex; align-items:center; gap:15px;">
                            <div style="background:#f4f7fe; padding:10px; border-radius:10px; color:#6A66FF;">${ICONS.task}</div>
                            <div style="flex:1;">
                                <h4 style="margin:0; color:#333; font-size:0.95rem;">${t.titulo}</h4>
                                <span style="font-size:0.8rem; color:#888;">Resp: <strong>${t.responsavel}</strong></span>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:0.8rem; font-weight:bold; color:#0d6efd;">${t.progresso}%</div>
                                <div style="font-size:0.75rem; color:#999;">${t.status}</div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } 
            // Se for Dono vendo Projetos
            else {
                html += data.listas.map(p => `
                    <div class="project-item" style="padding:15px; border-bottom:1px solid #eee;">
                        <div style="display:flex; align-items:center; gap:15px;">
                            <div style="background:#e3f2fd; padding:10px; border-radius:10px; color:#0d6efd;">${ICONS.folder}</div>
                            <div style="flex:1;">
                                <h4 style="margin:0; color:#333;">${p.nome}</h4>
                                <span style="font-size:0.8rem; color:#888;">${p.cliente_nome || 'Interno'}</span>
                            </div>
                            <div class="prog-container" style="width:100px; text-align:right;">
                                <div style="font-size:0.8rem; font-weight:bold; color:#0d6efd; margin-bottom:5px;">${p.progresso}%</div>
                                <div style="height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden;">
                                    <div style="height:100%; background:linear-gradient(90deg, #6A66FF, #0d6efd); width:${p.progresso}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        } else {
            html += `<div style="text-align:center; color:#999; padding:20px;">Nada em andamento.</div>`;
        }

        mainList.innerHTML = html;
    }

    if (document.getElementById('prodChart') && data.grafico) {
        renderizarGrafico(document.getElementById('prodChart'), data.grafico);
    }
}

// --- MODAL DE REVISÃO DO GESTOR ---
async function abrirModalRevisao(tarefaId) {
    // 1. Busca detalhes (Checklist e arquivos)
    // Precisamos de um endpoint que retorne a tarefa completa. Usaremos tarefa_buscar.php
    try {
        const resp = await fetch('../api/tarefa_buscar.php?id=' + tarefaId);
        const json = await resp.json();
        if(!json.ok) return alert(json.erro);
        
        const t = json.tarefa;
        const checklist = JSON.parse(t.checklist || '[]');

        // Cria o modal dinamicamente
        const modalHtml = `
            <div id="modalRevisaoOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:3000; display:flex; justify-content:center; align-items:center; backdrop-filter:blur(3px);">
                <div style="background:white; width:600px; padding:30px; border-radius:20px; max-height:90vh; overflow-y:auto; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
                    <h2 style="margin-top:0; color:#2b3674;">Revisão de Entrega</h2>
                    <p style="color:#666; margin-bottom:20px;">O colaborador marcou esta tarefa como pronta. Verifique os itens.</p>
                    
                    <div style="background:#f8f9fa; padding:20px; border-radius:12px; margin-bottom:20px; border:1px solid #eef0f7;">
                        <h4 style="margin:0 0 10px 0; color:#333;">${t.titulo}</h4>
                        
                        <div style="margin-top:15px;">
                            ${checklist.map(i => `
                                <div style="margin-bottom:10px; padding-bottom:10px; border-bottom:1px dashed #ddd;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span style="color:${i.concluido ? '#2ecc71' : '#ccc'}; font-size:1.2rem;">${i.concluido ? '☑' : '☐'}</span>
                                        <span style="font-weight:500; color:#333;">${i.descricao}</span>
                                    </div>
                                    ${i.arquivo ? `
                                        <div style="margin-left:28px; margin-top:5px; font-size:0.85rem; background:white; padding:5px 10px; border:1px solid #ddd; border-radius:6px; display:inline-block;">
                                            <a href="../public/${i.arquivo.url}" target="_blank" style="text-decoration:none; color:#0d6efd; display:flex; align-items:center; gap:5px;">
                                                📎 ${i.arquivo.nome} <span style="color:#999; font-size:0.7rem;">(${i.arquivo.data})</span>
                                            </a>
                                        </div>
                                    ` : '<div style="margin-left:28px; margin-top:2px; font-size:0.75rem; color:#aaa;">(Sem arquivo anexado)</div>'}
                                </div>
                            `).join('')}
                            ${checklist.length === 0 ? '<div style="color:#999; font-style:italic;">Sem checklist definido.</div>' : ''}
                        </div>
                    </div>
                    
                    <label style="font-weight:bold; color:#d32f2f; font-size:0.9rem;">Feedback (Obrigatório se reprovar):</label>
                    <textarea id="txtFeedbackRevisao" style="width:100%; height:80px; margin-bottom:20px; border:1px solid #ddd; padding:10px; border-radius:8px; font-family:inherit;" placeholder="Descreva o que precisa ser ajustado..."></textarea>
                    
                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button onclick="document.getElementById('modalRevisaoOverlay').remove()" class="botao-secundario">Cancelar</button>
                        <button onclick="processarRevisao(${t.id}, 'reprovar')" class="botao-secundario" style="color:#d32f2f; border-color:#d32f2f;">❌ Devolver para Ajustes</button>
                        <button onclick="processarRevisao(${t.id}, 'aprovar')" class="botao-primario" style="background:#2ecc71;">✅ Aprovar & Concluir</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);

    } catch(e) { alert("Erro ao carregar tarefa."); }
}

async function processarRevisao(id, acao) {
    const feedback = document.getElementById('txtFeedbackRevisao').value;
    
    if (acao === 'reprovar' && !feedback.trim()) {
        alert("Para devolver a tarefa, você DEVE escrever um feedback explicando o que ajustar.");
        return;
    }

    // Se aprovar, status vira CONCLUIDA (100%). Se reprovar, volta para EM_ANDAMENTO.
    const status = (acao === 'aprovar') ? 'CONCLUIDA' : 'EM_ANDAMENTO';
    
    const fd = new FormData();
    fd.append('tarefa_id', id);
    fd.append('status', status);
    if(feedback) fd.append('feedback_revisao', feedback);
    if(acao === 'aprovar') fd.append('progresso', 100);

    // Salva na API
    try {
        const resp = await fetch('../api/tarefa_entregar.php', { method:'POST', body:fd });
        const json = await resp.json();
        
        if(json.ok) {
            document.getElementById('modalRevisaoOverlay').remove();
            alert(acao === 'aprovar' ? "Tarefa Aprovada e Concluída! 🎉" : "Tarefa Devolvida para Ajustes.");
            carregarDashboard(); // Atualiza a lista do gestor
        } else {
            alert(json.erro);
        }
    } catch(e) { alert("Erro de conexão"); }
}

// --- UTILITÁRIO GRÁFICO ---
function renderizarGrafico(canvas, dados) {
    if(!window.Chart) return;
    if(window.myChartInstance) window.myChartInstance.destroy();
    
    window.myChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: dados.labels,
            datasets: [{
                label: 'Entregas',
                data: dados.data,
                borderColor: '#6A66FF',
                backgroundColor: 'rgba(106, 102, 255, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6A66FF'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
}