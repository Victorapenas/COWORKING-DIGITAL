// ARQUIVO: js/dashboard.js
//atualização
document.addEventListener('DOMContentLoaded', () => {
    carregarDashboard();
});

// MAPA DE ÍCONES (SVG)
const ICONS = {
    rocket: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z'></path><path d='M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z'></path><path d='M9 12H4s.55-3.03 2-4c1.62-1.1 2.72-2 2.72-2'></path><path d='M15 13v5s3.03-.55 4-2c1.1-1.62 2-2.72 2-2.72'></path></svg>`,
    users: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'></path><circle cx='9' cy='7' r='4'></circle><path d='M23 21v-2a4 4 0 0 0-3-3.87'></path><path d='M16 3.13a4 4 0 0 1 0 7.75'></path></svg>`,
    check: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'></polyline></svg>`,
    alert: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='#e74c3c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='12' r='10'></circle><line x1='12' y1='8' x2='12' y2='12'></line><line x1='12' y1='16' x2='12.01' y2='16'></line></svg>`,
    folder: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'></path></svg>`,
    eye: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path><circle cx='12' cy='12' r='3'></circle></svg>`,
    task: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M12 20h9'></path><path d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z'></path></svg>`,
    chart: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><line x1='18' y1='20' x2='18' y2='10'></line><line x1='12' y1='20' x2='12' y2='4'></line><line x1='6' y1='20' x2='6' y2='14'></line></svg>`,
    alerta: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z'></path><line x1='12' y1='9' x2='12' y2='13'></line><line x1='12' y1='17' x2='12.01' y2='17'></line></svg>`,
    pasta: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'></path></svg>`,
    olho: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path><circle cx='12' cy='12' r='3'></circle></svg>`
};

async function carregarDashboard() {
    try {
        const resp = await fetch('../api/dashboard_stats.php');
        const data = await resp.json();
        if (!data.ok) throw new Error(data.erro);

        // Header
        const userSpan = document.getElementById('userName');
        if(userSpan) userSpan.innerText = data.usuario_nome;
        
        // Ações Rápidas (Líder/Gestor)
        const qa = document.getElementById('quickActions');
        if (qa && (data.papel === 'DONO' || data.papel === 'LIDER' || data.papel === 'GESTOR')) {
            qa.style.display = 'flex';
        }

        // KPIs
        renderizarKPIs(data.kpis);

        // --- LÓGICA PRINCIPAL (HÍBRIDA) ---
        const listContainer = document.getElementById('mainListContainer');
        const title = document.getElementById('mainListTitle');
        
        if (listContainer) {
            // Se for Líder/Dono, mostra Projetos E (se tiver) as tarefas pessoais dele
            if (data.papel === 'DONO' || data.papel === 'LIDER') {
                title.innerText = "Status da Empresa";
                renderizarProjetos(data.listas, listContainer);
                
                // === NOVIDADE: SEÇÃO PESSOAL PARA O DONO ===
                if (data.minhas_tarefas && data.minhas_tarefas.length > 0) {
                    const onlineSec = document.getElementById('onlineSection');
                    // Injeta a lista pessoal ANTES da lista de online (na lateral)
                    // Ou cria um novo container se preferir
                    const divPessoal = document.createElement('div');
                    divPessoal.innerHTML = `
                        <div class="side-card" style="border-left: 4px solid #6A66FF;">
                            <h4 style="margin:0 0 15px 0; color:#2b3674; display:flex; align-items:center; gap:8px;">
                                ${ICONS.task} Minhas Pendências
                            </h4>
                            <div class="list-container">
                                ${gerarHTMLTarefasSimples(data.minhas_tarefas)}
                            </div>
                        </div>
                    `;
                    // Insere no topo da coluna direita
                    const rightCol = document.querySelector('.right-col');
                    if(rightCol) rightCol.insertBefore(divPessoal, rightCol.firstChild);
                }

                // Renderiza equipe online
                if (data.online_users && data.online_users.length > 0) {
                    const onlineSec = document.getElementById('onlineSection');
                    if(onlineSec) {
                        onlineSec.style.display = 'block';
                        renderizarOnline(data.online_users);
                    }
                }
            } 
            // Se for Gestor ou Colaborador
            else {
                title.innerText = (data.papel === 'GESTOR') ? "Tarefas da Equipe" : "Minhas Próximas Entregas";
                renderizarTarefas(data.listas, listContainer, data.papel === 'GESTOR');
                
                // Se gestor tiver aprovações
                if (data.papel === 'GESTOR' && data.pendencias && data.pendencias.length > 0) {
                    const approvSec = document.getElementById('approvalSection');
                    if(approvSec) {
                        approvSec.style.display = 'block';
                        renderizarAprovacoes(data.pendencias);
                    }
                }
            }
        }

        // Gráfico
        const chartCanvas = document.getElementById('prodChart');
        if(chartCanvas) renderizarGrafico(data.grafico);

    } catch (err) {
        console.error(err);
    }
}

// --- FUNÇÕES DE RENDERIZAÇÃO ---

function gerarHTMLTarefasSimples(tarefas) {
    return tarefas.map(t => {
        const prazo = t.prazo ? new Date(t.prazo).toLocaleDateString('pt-BR') : 'S/ Data';
        return `
        <div class="list-item" style="padding:10px; margin-bottom:10px; border-radius:8px; border:1px solid #eee; cursor:pointer;" onclick="openTarefaModal(null, ${t.id})">
            <div style="font-weight:600; font-size:0.9rem; color:#333;">${t.titulo}</div>
            <div style="font-size:0.75rem; color:#888; display:flex; justify-content:space-between; margin-top:5px;">
                <span>${t.projeto_nome || 'Geral'}</span>
                <span style="color:${t.status==='ATRASADA'?'red':'#0d6efd'}">${prazo}</span>
            </div>
        </div>`;
    }).join('');
}

function renderizarKPIs(kpis) {
    const container = document.getElementById('kpiContainer');
    if(!container) return;
    container.innerHTML = '';

    kpis.forEach(k => {
        let bgClass = 'c-blue';
        if(k.cor === 'green') bgClass = 'c-green';
        if(k.cor === 'orange') bgClass = 'c-orange';
        if(k.cor === 'purple') bgClass = 'c-purple';
        if(k.cor === 'red') bgClass = 'c-purple'; 

        const svg = ICONS[k.icone] || ICONS['task'];

        container.innerHTML += `
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

function renderizarProjetos(projetos, container) {
    if (!projetos || projetos.length === 0) {
        container.innerHTML = '<div style="padding:20px; text-align:center; color:#999;">Nenhum projeto ativo.</div>';
        return;
    }

    container.innerHTML = projetos.map(p => `
        <div class="project-item" onclick="window.location.href='projeto_detalhes.php?id=${p.id}'">
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="background:#e3f2fd; padding:10px; border-radius:10px; color:#0d6efd;">
                    ${ICONS['folder']}
                </div>
                <div>
                    <h4 style="margin:0; color:#333;">${p.nome}</h4>
                    <span style="font-size:0.8rem; color:#888;">${p.cliente_nome || 'Interno'}</span>
                </div>
            </div>
            <div class="prog-container">
                <div style="font-size:0.8rem; font-weight:bold; color:#0d6efd;">${p.progresso}%</div>
                <div class="prog-bar">
                    <div class="prog-fill" style="width:${p.progresso}%"></div>
                </div>
            </div>
        </div>
    `).join('');
}

function renderizarTarefas(tarefas, container, isGestor) {
    if (!tarefas || tarefas.length === 0) {
        container.innerHTML = '<div style="padding:20px; text-align:center; color:#999;">Tudo limpo! Nenhuma tarefa pendente.</div>';
        return;
    }

    container.innerHTML = tarefas.map(t => {
        const prazo = t.prazo ? new Date(t.prazo).toLocaleDateString('pt-BR') : 'Sem prazo';
        const sub = isGestor ? `Resp: <strong>${t.responsavel}</strong>` : `Projeto: ${t.projeto_nome || 'Geral'}`;
        
        return `
        <div class="project-item" onclick="openTarefaModal(null, ${t.id})">
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="background:#f4f7fe; padding:10px; border-radius:10px; color:#6A66FF;">
                    ${ICONS['task']}
                </div>
                <div>
                    <h4 style="margin:0; color:#333; font-size:0.95rem;">${t.titulo}</h4>
                    <span style="font-size:0.8rem; color:#888;">${sub}</span>
                </div>
            </div>
            <div style="text-align:right;">
                <span style="font-size:0.75rem; background:${t.prioridade==='URGENTE'?'#ffebee':'#e3f2fd'}; color:${t.prioridade==='URGENTE'?'#c62828':'#0d6efd'}; padding:4px 8px; border-radius:10px; font-weight:bold;">${t.prioridade}</span>
                <div style="font-size:0.75rem; color:#999; margin-top:5px;">📅 ${prazo}</div>
            </div>
        </div>
        `;
    }).join('');
}

function renderizarOnline(users) {
    const div = document.getElementById('onlineList');
    if(div) {
        div.innerHTML = users.map(u => `
            <div class="online-avatar" title="${u.nome}">
                ${u.nome.substring(0,2).toUpperCase()}
                <div class="online-dot"></div>
            </div>
        `).join('');
    }
}

function renderizarAprovacoes(list) {
    const div = document.getElementById('approvalList');
    if(div) {
        div.innerHTML = list.map(item => `
            <div class="approval-item">
                <div style="font-weight:700; color:#333; font-size:0.9rem;">${item.titulo}</div>
                <div style="font-size:0.8rem; color:#777;">De: ${item.responsavel}</div>
                <div class="approval-actions">
                    <button class="btn-mini btn-ok" onclick="openTarefaModal(null, ${item.id})">Analisar</button>
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