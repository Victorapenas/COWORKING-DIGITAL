// ARQUIVO: js/dashboard.js
//atualização
document.addEventListener('DOMContentLoaded', () => {
    carregarDashboard();
});

// --- ÍCONES SVG (Sistema de Design) ---
const ICONS = {
    rocket: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z'></path><path d='M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z'></path><path d='M9 12H4s.55-3.03 2-4c1.62-1.1 2.72-2 2.72-2'></path><path d='M15 13v5s3.03-.55 4-2c1.1-1.62 2-2.72 2-2.72'></path></svg>`,
    users: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'></path><circle cx='9' cy='7' r='4'></circle><path d='M23 21v-2a4 4 0 0 0-3-3.87'></path><path d='M16 3.13a4 4 0 0 1 0 7.75'></path></svg>`,
    check: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'></polyline></svg>`,
    alert: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='#e74c3c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='12' r='10'></circle><line x1='12' y1='8' x2='12' y2='12'></line><line x1='12' y1='16' x2='12.01' y2='16'></line></svg>`,
    folder: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'></path></svg>`,
    eye: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path><circle cx='12' cy='12' r='3'></circle></svg>`,
    task: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M12 20h9'></path><path d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z'></path></svg>`,
    chart: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><line x1='18' y1='20' x2='18' y2='10'></line><line x1='12' y1='20' x2='12' y2='4'></line><line x1='6' y1='20' x2='6' y2='14'></line></svg>`,
    play: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>`
};

// --- FUNÇÃO PRINCIPAL: CARREGAR DADOS ---
async function carregarDashboard() {
    try {
        const resp = await fetch('../api/dashboard_stats.php');
        const data = await resp.json();
        
        if (!data.ok) throw new Error(data.erro);

        // Atualiza Nome do Usuário no Topo
        const userSpan = document.getElementById('userName');
        if(userSpan) userSpan.innerText = data.usuario_nome;

        // === ROTEAMENTO DE VISUALIZAÇÃO ===
        if (data.papel === 'COLABORADOR' || data.papel === 'FUNCIONARIO') {
            // >>> MODO OPERACIONAL (Colaborador)
            renderizarDashboardColab(data);
        } else {
            // >>> MODO GESTÃO (Líder/Gestor)
            renderizarDashboardGestor(data);
        }

    } catch (err) {
        console.error("Erro ao carregar dashboard:", err);
    }
}

// =============================================================================
// LÓGICA DO COLABORADOR (OPERACIONAL)
// =============================================================================

function renderizarDashboardColab(data) {
    // 1. Atualiza Header
    const userSpan = document.getElementById('userName');
    if(userSpan) userSpan.innerText = data.usuario_nome;

    // 2. Atualiza KPIs
    const elPend = document.getElementById('kpiPendentes');
    const elUrg = document.getElementById('kpiUrgentes');
    const elConc = document.getElementById('kpiConcluidas');
    const elMeta = document.getElementById('kpiMetaMes');
    const elProd = document.getElementById('kpiProd');

    // Filtros de contagem
    const pendentes = data.minhas_tarefas.length;
    const urgentes = data.minhas_tarefas.filter(t => t.prioridade === 'URGENTE').length;
    
    // KPI de Entregues vem do array kpis da API
    const entreguesObj = data.kpis.find(k => k.icone === 'check');
    const entregues = entreguesObj ? entreguesObj.valor : 0;
    
    if(elPend) elPend.innerText = pendentes;
    if(elUrg) elUrg.innerText = urgentes;
    if(elConc) elConc.innerText = entregues;
    if(elMeta) elMeta.innerText = entregues; // Simplificação: meta = entregues no mes

    // Cálculo Simples de Produtividade (Entregues vs Total que tinha)
    // Em um sistema real, viria do backend. Aqui simulamos.
    let totalMes = entregues + pendentes;
    let prod = totalMes > 0 ? Math.round((entregues / totalMes) * 100) : 0;
    if(elProd) elProd.innerText = prod + '%';

    // 3. Lista de Foco
    const lista = document.getElementById('listaTarefasColab');
    if(lista) {
        if(data.minhas_tarefas.length === 0) {
            lista.innerHTML = `<div style="text-align:center; color:#ccc; padding:30px;">Sem tarefas pendentes. Bom trabalho! 🚀</div>`;
        } else {
            lista.innerHTML = data.minhas_tarefas.map(t => {
                // Prepara JSON seguro
                const tJson = JSON.stringify(t).replace(/"/g, '&quot;');
                
                // Classe Prioridade
                let pClass = 'tp-normal';
                if(t.prioridade === 'URGENTE') pClass = 'tp-alta';
                else if(t.prioridade === 'IMPORTANTE') pClass = 'tp-media';

                // Data formatada
                let dataF = t.prazo ? new Date(t.prazo).toLocaleDateString('pt-BR') : 'Sem data';

                return `
                <div class="task-row" onclick="abrirPainelTarefa('${tJson}')">
                    <div class="task-icon">${ICONS.task}</div>
                    <div class="task-info">
                        <h4>${t.titulo}</h4>
                        <span>${t.projeto_nome || 'Geral'} • ${dataF}</span>
                    </div>
                    <span class="task-prio ${pClass}">${t.prioridade}</span>
                    <div style="margin-left:15px; color:#ddd;">&#10095;</div>
                </div>
                `;
            }).join('');
        }
    }

    // 4. Renderizar Gráfico de Produtividade (Colaborador)
    const ctx = document.getElementById('chartProdColab');
    if (ctx && data.grafico) {
        if(window.myChartColab) window.myChartColab.destroy();
        
        window.myChartColab = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.grafico.labels,
                datasets: [{
                    label: 'Entregas',
                    data: data.grafico.data,
                    borderColor: '#4318FF',
                    backgroundColor: 'rgba(67, 24, 255, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4318FF',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
}

// === INTERAÇÕES DO MODO COLABORADOR (MODAL LATERAL) ===

// Abre o "Modalzinho no canto"
window.carregarChecklistLateral = function(tarefa) {
    const container = document.getElementById('sideChecklistContent');
    const sideModal = document.getElementById('sideChecklist');
    const overlay = document.querySelector('.side-overlay');
    
    // Abre o modal
    sideModal.classList.add('open');
    overlay.classList.add('open');

    // Parse do Checklist
    let checklist = [];
    try { checklist = JSON.parse(tarefa.checklist || '[]'); } catch(e){}

    let checklistHTML = '';
    if (checklist && checklist.length > 0) {
        checklistHTML = '<div style="margin-top:15px; max-height:400px; overflow-y:auto;">';
        checklist.forEach((item, idx) => {
            const checked = (item.concluido == 1) ? 'checked' : '';
            const style = checked ? 'text-decoration:line-through; color:#aaa;' : 'color:#333;';
            
            checklistHTML += `
                <div style="padding:12px 0; border-bottom:1px solid #eee; display:flex; align-items:flex-start; gap:10px;">
                    <input type="checkbox" ${checked} onchange="toggleCheckItemSide(${tarefa.id}, ${idx}, this)" style="margin-top:4px; cursor:pointer;">
                    <span style="${style} font-size:0.95rem; line-height:1.4; flex:1;">${item.descricao}</span>
                </div>`;
        });
        checklistHTML += '</div>';
    } else {
        checklistHTML = `
            <div style="text-align:center; padding:30px 0; color:#999; font-style:italic;">
                <div style="font-size:2rem; margin-bottom:10px;">📝</div>
                Esta tarefa não possui checklist.<br>Use o botão abaixo para ver detalhes ou concluir.
            </div>`;
    }

    container.innerHTML = `
        <div style="margin-bottom:20px;">
            <span class="st-badge ${tarefa.status.toLowerCase()}" style="margin-bottom:10px; display:inline-block;">${tarefa.status.replace('_',' ')}</span>
            <h3 style="margin:5px 0 15px 0; color:#2b3674; font-size:1.3rem; line-height:1.3;">${tarefa.titulo}</h3>
            
            <div style="background:#f0f7ff; padding:15px; border-radius:12px; border:1px solid #cce5ff; color:#004085; font-size:0.9rem;">
                <strong>Descrição:</strong><br>
                ${tarefa.descricao || 'Sem descrição definida.'}
            </div>
        </div>
        
        <h4 style="margin:0; border-bottom:2px solid #f0f0f0; padding-bottom:10px; color:#2b3674;">Etapas / Checklist</h4>
        ${checklistHTML}

        <div style="margin-top:30px; display:grid; gap:10px;">
            <button class="botao-primario" onclick="window.location.href='minhas_tarefas.php'" style="width:100%; justify-content:center;">
                Ir para Execução Completa
            </button>
        </div>
    `;
}

// Alternar visibilidade do modal
window.toggleSideChecklist = function() {
    const modal = document.getElementById('sideChecklist');
    const overlay = document.querySelector('.side-overlay');
    if(modal) modal.classList.toggle('open');
    if(overlay) overlay.classList.toggle('open');
}

// Atualizar item do checklist rapidamente (API)
window.toggleCheckItemSide = async function(tarefaId, index, checkbox) {
    const span = checkbox.nextElementSibling;
    
    // Efeito visual imediato
    if(checkbox.checked) {
        span.style.textDecoration = 'line-through';
        span.style.color = '#aaa';
    } else {
        span.style.textDecoration = 'none';
        span.style.color = '#333';
    }

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ 
                tarefa_id: tarefaId, 
                index: index, 
                feito: checkbox.checked 
            })
        });
        const json = await resp.json();
        
        if(!json.ok) {
            alert("Erro ao salvar: " + json.erro);
            checkbox.checked = !checkbox.checked; // Reverte visualmente
        }
    } catch(e) { 
        console.error(e);
        checkbox.checked = !checkbox.checked;
    }
}


// =============================================================================
// LÓGICA DO GESTOR (VISÃO MACRO)
// =============================================================================

function renderizarDashboardGestor(data) {
    // 1. Renderiza KPIs
    const kpiContainer = document.getElementById('kpiContainer');
    if (kpiContainer && data.kpis) {
        kpiContainer.innerHTML = '';
        data.kpis.forEach(k => {
            let bgClass = 'c-blue';
            if(k.cor === 'green') bgClass = 'c-green';
            if(k.cor === 'orange') bgClass = 'c-orange';
            if(k.cor === 'purple') bgClass = 'c-purple';
            if(k.cor === 'red') bgClass = 'c-purple'; // Ajuste visual

            const svg = ICONS[k.icone] || ICONS['task'];

            kpiContainer.innerHTML += `
                <div class="card-info">
                    <div class="icon-box ${bgClass}" style="width:50px; height:50px; color:white; display:flex; align-items:center; justify-content:center;">
                        ${svg}
                    </div>
                    <div class="kpi-text">
                        <h3>${k.valor || 0}</h3>
                        <p>${k.titulo}</p>
                    </div>
                </div>
            `;
        });
    }

    // 2. Renderiza Lista Principal (Projetos ou Tarefas da Equipe)
    const listContainer = document.getElementById('mainListContainer');
    const title = document.getElementById('mainListTitle'); // Se existir no HTML

    if (listContainer) {
        if (data.papel === 'DONO' || data.papel === 'LIDER') {
            if(title) title.innerText = "Status da Empresa";
            renderizarProjetos(data.listas, listContainer);
            
            // Renderiza Equipe Online (Lateral)
            if (data.online_users) renderizarOnline(data.online_users);
            
        } else if (data.papel === 'GESTOR') {
            if(title) title.innerText = "Tarefas da Equipe";
            renderizarTarefasEquipe(data.listas, listContainer);
            
            // Renderiza Aprovações (Se houver seção lateral)
            if (data.pendencias) renderizarAprovacoes(data.pendencias);
        }
    }

    // 3. Renderiza Gráfico
    const chartCanvas = document.getElementById('prodChart');
    if(chartCanvas && data.grafico) renderizarGrafico(data.grafico);
}

// --- Funções Auxiliares do Gestor ---

function renderizarProjetos(projetos, container) {
    if (!projetos || projetos.length === 0) {
        container.innerHTML = '<div style="padding:20px; text-align:center; color:#999;">Nenhum projeto ativo.</div>';
        return;
    }

    container.innerHTML = projetos.map(p => `
        <div class="project-item" onclick="window.location.href='projeto_detalhes.php?id=${p.id}'" style="padding:15px; border-bottom:1px solid #eee; cursor:pointer;">
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="background:#e3f2fd; padding:10px; border-radius:10px; color:#0d6efd;">
                    ${ICONS['folder']}
                </div>
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

function renderizarTarefasEquipe(tarefas, container) {
    if (!tarefas || tarefas.length === 0) {
        container.innerHTML = '<div style="padding:20px; text-align:center; color:#999;">Tudo em dia! Nenhuma tarefa pendente na equipe.</div>';
        return;
    }

    container.innerHTML = tarefas.map(t => {
        const prazo = t.prazo ? new Date(t.prazo).toLocaleDateString('pt-BR') : 'Sem prazo';
        
        return `
        <div class="project-item" onclick="openTarefaModal(null, ${t.id})" style="padding:15px; border-bottom:1px solid #eee; cursor:pointer;">
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="background:#f4f7fe; padding:10px; border-radius:10px; color:#6A66FF;">
                    ${ICONS['task']}
                </div>
                <div style="flex:1;">
                    <h4 style="margin:0; color:#333; font-size:0.95rem;">${t.titulo}</h4>
                    <span style="font-size:0.8rem; color:#888;">Resp: <strong>${t.responsavel}</strong></span>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:0.75rem; background:${t.prioridade==='URGENTE'?'#ffebee':'#e3f2fd'}; color:${t.prioridade==='URGENTE'?'#c62828':'#0d6efd'}; padding:4px 8px; border-radius:10px; font-weight:bold;">${t.prioridade}</span>
                    <div style="font-size:0.75rem; color:#999; margin-top:5px;">📅 ${prazo}</div>
                </div>
            </div>
        </div>
        `;
    }).join('');
}

function renderizarOnline(users) {
    const div = document.getElementById('onlineList'); // Certifique-se de ter este ID no HTML do gestor
    if(div && users) {
        div.innerHTML = users.map(u => `
            <div class="online-avatar" title="${u.nome}" style="position:relative; display:inline-block; margin-right:5px;">
                <div style="width:35px; height:35px; background:#6A66FF; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.8rem;">
                    ${u.nome.substring(0,2).toUpperCase()}
                </div>
                <div style="width:10px; height:10px; background:#2ecc71; border:2px solid white; border-radius:50%; position:absolute; bottom:0; right:0;"></div>
            </div>
        `).join('');
    }
}

function renderizarAprovacoes(list) {
    const div = document.getElementById('approvalList'); // Certifique-se de ter este ID
    if(div && list) {
        div.innerHTML = list.map(item => `
            <div class="approval-item" style="padding:10px; border-bottom:1px solid #eee;">
                <div style="font-weight:700; color:#333; font-size:0.9rem;">${item.titulo}</div>
                <div style="font-size:0.8rem; color:#777;">De: ${item.responsavel}</div>
                <div class="approval-actions" style="margin-top:5px;">
                    <button onclick="openTarefaModal(null, ${item.id})" style="padding:5px 10px; background:#e3f2fd; color:#0d6efd; border:none; border-radius:5px; cursor:pointer; font-size:0.8rem;">Analisar</button>
                </div>
            </div>
        `).join('');
    }
}

function renderizarGrafico(dados) {
    const ctx = document.getElementById('prodChart').getContext('2d');
    if(window.myChart) window.myChart.destroy();
    
    window.myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dados.labels,
            datasets: [{
                label: 'Entregas',
                data: dados.data,
                borderColor: '#6A66FF',
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2,
                fill: true,
                backgroundColor: 'rgba(106, 102, 255, 0.1)'
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