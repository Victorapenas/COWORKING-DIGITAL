// ARQUIVO: js/dashboard.js

// --- ÍCONES SVG PARA USO GERAL ---
const ICONS = {
    rocket: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z'></path><path d='M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z'></path><path d='M9 12H4s.55-3.03 2-4c1.62-1.1 2.72-2 2.72-2'></path><path d='M15 13v5s3.03-.55 4-2c1.1-1.62 2-2.72 2-2.72'></path></svg>`,
    users: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'></path><circle cx='9' cy='7' r='4'></circle><path d='M23 21v-2a4 4 0 0 0-3-3.87'></path><path d='M16 3.13a4 4 0 0 1 0 7.75'></path></svg>`,
    check: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'></polyline></svg>`,
    alerta: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='12' r='10'></circle><line x1='12' y='8' x2='12' y2='12'></line><line x1='12' y1='16' x2='12.01' y2='16'></line></svg>`,
    folder: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'></path></svg>`,
    task: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M12 20h9'></path><path d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z'></path></svg>`,
    chart: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><line x1='18' y1='20' x2='18' y2='10'></line><line x1='12' y1='20' x2='12' y2='4'></line><line x1='6' y1='20' x2='6' y2='14'></line></svg>`,
    clock: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='12' r='10'></circle><polyline points='12 6 12 12 16 14'></polyline></svg>`,
    refresh: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='23 4 23 10 17 10'></polyline><polyline points='1 20 1 14 7 14'></polyline><path d='M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15'></path></svg>`,
    link: `<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71'></path><path d='M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71'></path></svg>`,
    close: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><line x1='18' y1='6' x2='6' y2='18'></line><line x1='6' y1='6' x2='18' y2='18'></line></svg>`,
    aceno: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M18 11V6a2 2 0 0 0-4 0v5a2 2 0 0 1-4 0V4a2 2 0 0 0-4 0v13c0 3 4 5 6 6s6-3 6-5v-7z'></path></svg>`,
    play: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polygon points='5 3 19 12 5 21 5 3'></polygon></svg>`,
    check_circle: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M22 11.08V12a10 10 0 1 1-5.93-9.14'></path><polyline points='22 4 12 14.01 9 11.01'></polyline></svg>`,
    adicionar: `<svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><line x1='12' y1='5' x2='12' y2='19'></line><line x1='5' y1='12' x2='19' y2='12'></line></svg>`
};

// Variáveis Globais
let cronometro = {
    rodando: false,
    segundos: 0,
    intervalo: null,
    tarefaId: null,
    tarefaObj: null
};

window.tarefasColabCache = [];

document.addEventListener('DOMContentLoaded', () => {
    carregarDashboard();
    setInterval(carregarDashboard, 30000);
});

async function carregarDashboard() {
    try {
        const resp = await fetch('../api/dashboard_stats.php');
        const data = await resp.json();

        if (!data.ok) throw new Error(data.erro);

        const userSpan = document.getElementById('userName');
        if (userSpan) userSpan.innerText = data.usuario_nome;

        if (data.papel === 'COLABORADOR' || data.papel === 'FUNCIONARIO') {
            window.tarefasColabCache = data.minhas_tarefas;
            renderizarDashboardColab(data);
        } else {
            renderizarDashboardGestor(data);
        }

    } catch (err) {
        console.error("Erro ao carregar dashboard:", err);
    }
}

// =============================================================================
// PARTE 1: VISÃO DO COLABORADOR (HUBS DE PRODUTIVIDADE)
// =============================================================================

function renderizarDashboardColab(data) {
    // 1. KPIs
    const kpiDiv = document.querySelector('.kpi-grid-modern');
    if (kpiDiv && data.kpis) {
        kpiDiv.innerHTML = data.kpis.map(k => `
            <div class="kpi-card-modern">
                <div class="kpi-content">
                    <h3 style="color:${k.cor === 'red' ? '#e74c3c' : '#2b3674'}">${k.valor}</h3>
                    <span>${k.titulo}</span>
                </div>
                <div class="kpi-icon-modern kc-${k.cor}" style="font-size:1.5rem;">
                    ${ICONS[k.icone] || '•'}
                </div>
            </div>
        `).join('');
    }

    // 2. LISTA DE TAREFAS (COM SEPARAÇÃO DE FOCO)
    const lista = document.getElementById('listaTarefasColab');
    if (lista) {
        if(data.minhas_tarefas.length === 0) {
            lista.innerHTML = '<div style="padding:40px; text-align:center; color:#999;">Tudo limpo! Nenhuma tarefa pendente.</div>';
        } else {
            // Filtra tarefas urgentes ou atrasadas
            const urgentes = data.minhas_tarefas.filter(t => t.prioridade === 'URGENTE' || (t.prazo && new Date(t.prazo) < new Date()));
            const outras = data.minhas_tarefas.filter(t => t.prioridade !== 'URGENTE' && (!t.prazo || new Date(t.prazo) >= new Date()));

            let html = '';

            // Seção de Foco do Dia
            if (urgentes.length > 0) {
                html += `<div style="margin-bottom:15px; font-weight:700; color:#ee5d50; display:flex; align-items:center; gap:8px;">${ICONS.alerta} Atenção / Foco do Dia</div>`;
                html += urgentes.map(t => renderizarCardTarefaColab(t, true)).join('');
                html += `<hr style="margin:20px 0; border:0; border-top:1px dashed #ddd;">`;
            }

            // Seção Próximas Atividades
            html += `<div style="margin-bottom:15px; font-weight:700; color:#2b3674;">Próximas Atividades</div>`;
            html += outras.map(t => renderizarCardTarefaColab(t, false)).join('');

            lista.innerHTML = html;
        }
    }

    // 3. Agenda Lateral
    const agendaDiv = document.getElementById('agendaDiaContainer');
    if (agendaDiv && data.agenda_dia) {
        if(data.agenda_dia.length === 0) {
            agendaDiv.innerHTML = '<p style="color:#999; text-align:center; padding:20px;">Nenhuma entrega para hoje.</p>';
        } else {
            agendaDiv.innerHTML = data.agenda_dia.map(t => {
                const hora = t.prazo ? t.prazo.split(' ')[1].substring(0, 5) : '--:--';
                const isLate = t.prazo && new Date(t.prazo) < new Date();
                return `
                <div class="agenda-item ${isLate ? 'urgent' : 'today'}">
                    <div class="agenda-time" style="color:${isLate ? '#ee5d50' : '#0d6efd'}">
                        ${hora}
                    </div>
                    <div class="agenda-details">
                        <h4>${t.titulo}</h4>
                        <span>${isLate ? 'ATRASADO' : 'Entrega Hoje'}</span>
                    </div>
                </div>`;
            }).join('');
        }
    }
}

// Renderiza um card de tarefa rico com ações rápidas e barra de progresso
function renderizarCardTarefaColab(t, isUrgent) {
    const corIcone = t.status === 'EM_ANDAMENTO' ? '#4318FF' : '#a3aed0';
    const bgIcone = t.status === 'EM_ANDAMENTO' ? '#e3f2fd' : '#f4f7fe';
    const borderStyle = isUrgent ? 'border-left: 4px solid #ee5d50;' : '';
    
    // Calcula progresso se houver (visual)
    const prog = t.progresso || 0;
    
    return `
    <div class="task-row" style="${borderStyle} padding:15px; background:white; margin-bottom:10px; border-radius:10px; box-shadow:0 2px 5px rgba(0,0,0,0.02); display:flex; flex-direction:column; gap:10px; cursor:default;">
        
        <div style="display:flex; align-items:center; gap:15px; cursor:pointer;" onclick="abrirPainelTarefa(${t.id})">
            <div class="task-icon" style="background:${bgIcone}; color:${corIcone}; width:40px; height:40px;">
                ${t.status === 'EM_ANDAMENTO' ? ICONS.play : ICONS.task}
            </div>
            <div style="flex:1;">
                <h4 style="color:#2b3674; font-size:1rem; margin-bottom:3px;">${t.titulo}</h4>
                <div style="font-size:0.8rem; color:#888;">${t.projeto_nome} • ${formatarData(t.prazo)}</div>
            </div>
            <div style="text-align:right;">
                <span class="task-prio tp-${t.prioridade.toLowerCase()}">${t.prioridade}</span>
                ${t.status === 'EM_ANDAMENTO' ? '<div style="font-size:0.7rem; color:#4318FF; font-weight:bold; margin-top:5px;">EM EXECUÇÃO</div>' : ''}
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px dashed #eee; padding-top:10px; margin-top:5px;">
             <div style="flex:1; margin-right:20px;">
                 <div style="height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden;">
                     <div style="height:100%; width:${prog}%; background:${isUrgent ? '#ee5d50' : '#05cd99'};"></div>
                 </div>
                 <div style="font-size:0.7rem; color:#999; margin-top:3px;">${prog}% Concluído</div>
             </div>
             
             <div style="display:flex; gap:10px;">
                 <button onclick="abrirPainelTarefa(${t.id})" class="botao-secundario" style="height:30px; font-size:0.8rem; padding:0 12px; border-color:#e0e0e0;">
                    ${ICONS.folder} Ver Arquivos
                 </button>
                 <button onclick="abrirPainelTarefa(${t.id})" class="botao-primario" style="height:30px; font-size:0.8rem; padding:0 15px;">
                    ${ICONS.play} Trabalhar
                 </button>
             </div>
        </div>
    </div>`;
}

function abrirPainelTarefa(param) {
    let tarefa = null;

    if (typeof param === 'number' || (typeof param === 'string' && !isNaN(param) && !param.trim().startsWith('{'))) {
        if (window.tarefasColabCache) {
            tarefa = window.tarefasColabCache.find(t => t.id == param);
        }
        if (!tarefa) {
            fetch(`../api/tarefa_buscar.php?id=${param}`)
                .then(r => r.json())
                .then(d => {
                    if(d.ok) abrirPainelTarefa(JSON.stringify(d.tarefa)); 
                });
            return;
        } else {
             // Se achou no cache, busca atualização completa (para garantir que temos os arquivos do projeto)
             fetch(`../api/tarefa_buscar.php?id=${param}`)
                .then(r => r.json())
                .then(d => { if(d.ok) abrirPainelTarefa(JSON.stringify(d.tarefa)); });
            return;
        }
    } else {
        tarefa = (typeof param === 'string') ? JSON.parse(param) : param;
    }

    if (!tarefa) return;

    if(cronometro.rodando && cronometro.tarefaId != tarefa.id) {
        if(!confirm("Você tem uma tarefa em andamento. Deseja pausar a anterior e abrir esta?")) {
            return; 
        }
        toggleCronometro(); 
    }

    const body = document.getElementById('painelLateralBody');
    const footer = document.getElementById('painelLateralFooter');
    
    let checklistHtml = renderizarChecklistCompleto(tarefa);

    // --- NOVA SEÇÃO: ARQUIVOS DE APOIO (Do Projeto) ---
    let recursosHtml = '';
    if (tarefa.recursos_projeto && tarefa.recursos_projeto.length > 0) {
        const listaRecursos = tarefa.recursos_projeto.map(r => {
            const icon = r.tipo === 'link' ? ICONS.link : ICONS.folder;
            const url = r.tipo === 'link' ? r.url : `../public/${r.url}`;
            return `
                <a href="${url}" target="_blank" style="display:flex; align-items:center; gap:10px; padding:10px; background:#f9f9f9; border:1px solid #eee; border-radius:8px; text-decoration:none; color:#333; margin-bottom:5px; transition:0.2s;">
                    <span style="color:#6A66FF;">${icon}</span>
                    <span style="font-size:0.9rem; font-weight:500;">${r.titulo}</span>
                </a>
            `;
        }).join('');
        
        recursosHtml = `
            <div style="margin-bottom:25px;">
                <h4 style="color:#2b3674; font-size:0.95rem; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                    ${ICONS.folder} Arquivos de Apoio (Projeto)
                </h4>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    ${listaRecursos}
                </div>
            </div>
        `;
    } else {
        recursosHtml = `
             <div style="margin-bottom:25px; padding:15px; background:#fcfcfc; border:1px dashed #ddd; border-radius:8px; text-align:center; color:#999; font-size:0.85rem;">
                Sem arquivos de apoio no projeto.
             </div>
        `;
    }

    // Bloco de Alerta de Recusa
    let feedbackHtml = '';
    if (tarefa.feedback_revisao && tarefa.status !== 'CONCLUIDA') {
        feedbackHtml = `
            <div style="background:#fff5f5; border-left:4px solid #ee5d50; padding:15px; margin-bottom:20px; border-radius:4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <h4 style="color:#ee5d50; margin:0 0 5px 0; display:flex; align-items:center; gap:8px;">
                    ${ICONS.alerta} Atenção: Tarefa Devolvida
                </h4>
                <p style="color:#333; margin:0; font-size:0.95rem; line-height:1.5;">${tarefa.feedback_revisao}</p>
            </div>
        `;
    }

    body.innerHTML = `
        <input type="hidden" id="modalTarefaId" value="${tarefa.id}">
        
        ${feedbackHtml} 
        
        <div style="margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span class="task-prio tp-${tarefa.prioridade.toLowerCase()}">${tarefa.prioridade}</span>
                <span style="font-size:0.8rem; color:#888;">Prazo: ${formatarData(tarefa.prazo)}</span>
            </div>
            <h2 style="margin:0 0 10px 0; color:#2b3674; font-size:1.6rem;">${tarefa.titulo}</h2>
            <p style="font-size:0.95rem; color:#555; line-height:1.6; background:#f9f9f9; padding:15px; border-radius:10px;">
                ${tarefa.descricao || 'Sem descrição detalhada.'}
            </p>
        </div>

        ${recursosHtml}

        <div style="background:#2b3674; color:white; padding:20px; border-radius:15px; text-align:center; margin-bottom:25px; display:flex; justify-content:space-between; align-items:center;">
            <div style="text-align:left;">
                <small style="opacity:0.7; font-size:0.8rem;">TEMPO TOTAL</small>
                <div id="modalTimerDisplay" style="font-size:2rem; font-weight:800; font-family:monospace;">
                    ${formatarSegundos(cronometro.tarefaId == tarefa.id ? cronometro.segundos : (tarefa.tempo_total_minutos * 60))}
                </div>
            </div>
            <button id="btnCronometroModal" onclick="toggleCronometro('${tarefa.id}', '${tarefa.titulo.replace(/'/g, "")}')" 
                style="background:${cronometro.rodando && cronometro.tarefaId == tarefa.id ? '#ffce20' : '#05cd99'}; color:${cronometro.rodando && cronometro.tarefaId == tarefa.id ? '#333' : '#fff'}; border:none; padding:10px 25px; border-radius:30px; font-weight:bold; font-size:1rem; cursor:pointer; box-shadow:0 4px 10px rgba(0,0,0,0.2);">
                ${cronometro.rodando && cronometro.tarefaId == tarefa.id ? '⏸ PAUSAR' : '▶ INICIAR'}
            </button>
        </div>

        <h3 style="color:#2b3674; font-size:1.1rem; border-bottom:2px solid #eee; padding-bottom:10px; margin-bottom:15px;">
            Checklist de Execução
        </h3>
        <div id="formChecklistExec">
            ${checklistHtml}
        </div>
    `;

    footer.innerHTML = `
        <div style="width:100%;">
            <label style="display:block; font-size:0.8rem; margin-bottom:5px;">Comentário Final (Opcional)</label>
            <textarea id="comentarioEntrega" class="campo-padrao" rows="2" placeholder="Alguma observação para o gestor?"></textarea>
            <button onclick="enviarParaRevisao(${tarefa.id})" class="botao-primario" style="width:100%; margin-top:10px; height:50px; font-size:1rem;">
                🚀 Finalizar e Enviar para Revisão
            </button>
        </div>
    `;

    document.getElementById('painelLateral').classList.add('open');
    document.querySelector('.side-overlay').classList.add('open');
}

function renderizarChecklistCompleto(tarefa) {
    let list = [];
    try { list = JSON.parse(tarefa.checklist || '[]'); } catch(e){}
    
    if(list.length === 0) return '<p style="color:#999; font-style:italic; padding:10px;">Nenhum item de verificação.</p>';

    return list.map((item, idx) => `
        <div class="chk-item ${item.concluido ? 'done' : ''}" style="display:block; padding:15px; border-radius:10px; border:1px solid #eee; margin-bottom:10px; background:#fff;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:5px;">
                <input type="checkbox" class="chk-checkbox" ${item.concluido ? 'checked' : ''} onchange="toggleCheckItemPainel(${tarefa.id}, ${idx}, this)">
                <span class="chk-text" style="font-weight:600;">${item.descricao}</span>
            </div>
            
            <div style="padding-left:30px; margin-top:5px;">
                ${renderizarAreaEvidencia(tarefa.id, idx, item)}
            </div>
        </div>
    `).join('');
}

function renderizarAreaEvidencia(tId, idx, item) {
    if(item.tipo_evidencia === 'check' || !item.tipo_evidencia) return '';

    if(item.evidencia_url) {
        return `
            <div style="background:#e8f5e9; padding:8px 12px; border-radius:6px; display:inline-flex; align-items:center; justify-content:space-between; gap:15px;">
                <a href="${item.evidencia_url}" target="_blank" style="text-decoration:none; color:#2e7d32; font-weight:600; font-size:0.9rem;">
                    📄 ${item.evidencia_nome || 'Ver Arquivo'}
                </a>
                <button type="button" onclick="uploadArquivoChecklist(${tId}, ${idx}, null, 'remover')" style="background:none; border:none; color:#d32f2f; cursor:pointer; font-weight:bold;">&times;</button>
            </div>
        `;
    } else {
        if(item.tipo_evidencia === 'link') {
            return `
                <div style="display:flex; gap:5px;">
                    <input type="text" id="link_chk_${idx}" placeholder="Cole o link aqui..." style="padding:5px; border:1px solid #ddd; border-radius:4px; flex:1; font-size:0.85rem;">
                    <button type="button" onclick="salvarLinkChecklist(${tId}, ${idx})" style="background:#2b3674; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">Salvar</button>
                </div>
            `;
        } else {
            return `
                <label style="cursor:pointer; color:#666; font-size:0.8rem; border:1px dashed #ccc; padding:5px 10px; border-radius:4px; display:inline-flex; align-items:center; gap:5px; background:#f9f9f9;">
                    ${ICONS.folder} Anexar Arquivo
                    <input type="file" style="display:none;" onchange="uploadArquivoChecklist(${tId}, ${idx}, this, 'upload')">
                </label>
            `;
        }
    }
}

function toggleCronometro(tId, tTitulo) {
    const btn = document.getElementById('btnCronometroModal');
    
    if(!cronometro.rodando) {
        if(tId) {
            cronometro.tarefaId = tId;
            cronometro.tarefaObj = { id: tId, titulo: tTitulo }; 
            const display = document.getElementById('modalTimerDisplay');
            if(display && display.innerText !== "00:00") {
                cronometro.segundos = parseTempoParaSegundos(display.innerText);
            }
        }
        
        cronometro.rodando = true;
        if(btn) {
            btn.innerHTML = "⏸ PAUSAR";
            btn.style.background = "#ffce20";
            btn.style.color = "#333";
        }

        if (cronometro.intervalo) clearInterval(cronometro.intervalo);
        cronometro.intervalo = setInterval(() => {
            cronometro.segundos++;
            atualizarDisplays();
        }, 1000);
        
        const float = document.getElementById('floatingTimer');
        if(float) float.style.display = 'none';

    } else {
        clearInterval(cronometro.intervalo);
        cronometro.rodando = false;
        
        if(btn) {
            btn.innerHTML = "▶ INICIAR";
            btn.style.background = "#05cd99";
            btn.style.color = "#fff";
        }

        salvarTempoNoBanco(cronometro.tarefaId, Math.ceil(cronometro.segundos / 60));
    }
}

function minimizarTarefa() {
    document.getElementById('painelLateral').classList.remove('open');
    document.querySelector('.side-overlay').classList.remove('open');
    
    if(cronometro.rodando) {
        const float = document.getElementById('floatingTimer');
        if(float) {
            float.style.display = 'flex';
            document.getElementById('floatTimerText').innerText = formatarSegundos(cronometro.segundos);
        }
    }
}

function maximizarTarefa() {
    if(cronometro.tarefaId) {
        fetch(`../api/tarefa_buscar.php?id=${cronometro.tarefaId}`)
            .then(res => res.json())
            .then(data => {
                if(data.ok) {
                    abrirPainelTarefa(JSON.stringify(data.tarefa));
                    const float = document.getElementById('floatingTimer');
                    if(float) float.style.display = 'none';
                }
            });
    }
}

function atualizarDisplays() {
    const txt = formatarSegundos(cronometro.segundos);
    const modalDisplay = document.getElementById('modalTimerDisplay');
    const floatDisplay = document.getElementById('floatTimerText');
    
    if(modalDisplay) modalDisplay.innerText = txt;
    if(floatDisplay) floatDisplay.innerText = txt;
}

async function toggleCheckItemPainel(id, index, checkbox) {
    const itemDiv = checkbox.closest('.chk-item');
    if (checkbox.checked) itemDiv.classList.add('done'); else itemDiv.classList.remove('done');
    
    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tarefa_id: id, index: index, feito: checkbox.checked, acao: 'toggle' })
        });
        const json = await resp.json();
    } catch (e) {
        checkbox.checked = !checkbox.checked;
        alert("Erro de conexão ao salvar checklist.");
    }
}

async function uploadArquivoChecklist(tarefaId, index, input, acao) {
    const fd = new FormData();
    fd.append('tarefa_id', tarefaId);
    fd.append('index', index);
    fd.append('acao', acao === 'remover' ? 'remover_evidencia' : 'upload');

    if(acao === 'upload' && input && input.files.length > 0) {
        fd.append('arquivo_item', input.files[0]);
    }

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', { method: 'POST', body: fd });
        const json = await resp.json();
        if (json.ok) {
            fetch(`../api/tarefa_buscar.php?id=${tarefaId}`)
                .then(r => r.json())
                .then(d => {
                    if(d.ok) abrirPainelTarefa(JSON.stringify(d.tarefa));
                });
        } else {
            alert("Erro: " + json.erro);
        }
    } catch (e) { console.error(e); alert("Erro ao processar arquivo."); }
}

async function salvarLinkChecklist(tarefaId, index) {
    const input = document.getElementById(`link_chk_${index}`);
    const url = input.value.trim();
    if(!url) return alert("Digite um link válido.");

    const fd = new FormData();
    fd.append('tarefa_id', tarefaId);
    fd.append('index', index);
    fd.append('acao', 'link');
    fd.append('link_url', url);

    try {
        const resp = await fetch('../api/tarefa_checklist_toggle.php', { method: 'POST', body: fd });
        const json = await resp.json();
        if(json.ok) {
             fetch(`../api/tarefa_buscar.php?id=${tarefaId}`)
                .then(r => r.json())
                .then(d => { if(d.ok) abrirPainelTarefa(JSON.stringify(d.tarefa)); });
        } else {
            alert("Erro: " + json.erro);
        }
    } catch(e) { alert("Erro de conexão"); }
}

async function enviarParaRevisao(id) {
    if (!confirm("Tem certeza que finalizou? A tarefa será enviada ao gestor para revisão.")) return;
    
    let minutosExtras = 0;
    if(cronometro.rodando && cronometro.tarefaId == id) {
        minutosExtras = Math.ceil(cronometro.segundos / 60);
        toggleCronometro();
    }

    const com = document.getElementById('comentarioEntrega').value;
    
    const fd = new FormData(); 
    fd.append('tarefa_id', id); 
    fd.append('status', 'EM_REVISAO'); 
    fd.append('comentario', com || 'Tarefa finalizada e enviada para revisão.');
    
    if (minutosExtras > 0) {
        fd.append('tempo_gasto', minutosExtras);
    }
    
    try {
        const resp = await fetch('../api/tarefa_entregar.php', { method: 'POST', body: fd });
        const json = await resp.json();
        if (json.ok) {
            alert("Sucesso! Tarefa enviada para revisão.");
            minimizarTarefa(); 
            carregarDashboard();
        } else {
            alert(json.erro);
        }
    } catch (e) { alert("Erro de conexão."); }
}

function formatarSegundos(totalSec) {
    if (!totalSec || totalSec < 0) totalSec = 0;
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    
    const pad = (n) => n.toString().padStart(2, '0');
    if (h > 0) return `${h}h ${pad(m)}m`;
    return `${pad(m)}:${pad(s)}`;
}

function parseTempoParaSegundos(texto) {
    if(!texto) return 0;
    if(texto.includes('h')) {
        const h = parseInt(texto.split('h')[0]) || 0;
        const m = parseInt(texto.split('h')[1]) || 0;
        return (h*3600) + (m*60);
    }
    const parts = texto.split(':');
    if(parts.length === 2) return (parseInt(parts[0])*60) + parseInt(parts[1]);
    return 0;
}

async function salvarTempoNoBanco(id, minutos) {
    const fd = new FormData();
    fd.append('tarefa_id', id);
    fd.append('tempo_gasto', minutos);
    await fetch('../api/tarefa_entregar.php', { method:'POST', body:fd });
}

function formatarData(dataStr) {
    if(!dataStr) return 'Sem prazo';
    return new Date(dataStr).toLocaleDateString('pt-BR');
}

// =============================================================================
// PARTE 2: VISÃO DO GESTOR E LÍDER (ATUALIZADA)
// =============================================================================

function renderizarDashboardGestor(data) {
    // 1. KPIs Clicáveis
    const kpiContainer = document.getElementById('kpiContainer');
    if (kpiContainer) {
        kpiContainer.innerHTML = data.kpis.map(k => `
            <a href="${k.link || '#'}" class="card-info" style="border-left: 4px solid ${getColor(k.cor)}; display:flex; text-decoration:none;">
                <div class="icon-box c-${k.cor}">${ICONS[k.icone] || ICONS.task}</div>
                <div class="kpi-text">
                    <h3>${k.valor}</h3>
                    <p>${k.titulo}</p>
                </div>
            </a>
        `).join('');
    }

    // 2. Lista de Pendências (Aprovações)
    const pendenciasContainer = document.getElementById('listaPendencias');
    const badge = document.getElementById('badgePendencias');
    if (pendenciasContainer && data.pendencias) {
        badge.innerText = data.pendencias.length;
        if (data.pendencias.length === 0) {
            pendenciasContainer.innerHTML = `
                <div style="text-align:center; padding:50px 20px; color:#a3aed0;">
                    <div style="font-size:3rem; opacity:0.3; margin-bottom:10px;">✅</div>
                    <p>Nenhuma entrega pendente.</p>
                </div>`;
        } else {
            pendenciasContainer.innerHTML = data.pendencias.map(t => `
                <div class="inbox-item priority" onclick="abrirModalRevisao(${t.id}, false)">
                    <div style="display:flex; align-items:center; gap:15px;">
                        <div style="width:40px; height:40px; border-radius:50%; background:#ffebee; color:#d32f2f; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                            ${t.responsavel.substring(0,2).toUpperCase()}
                        </div>
                        <div>
                            <div style="font-weight:700; color:#2b3674; font-size:0.95rem;">${t.titulo}</div>
                            <div style="font-size:0.8rem; color:#707eae;">Enviado por <strong>${t.responsavel}</strong></div>
                        </div>
                    </div>
                    <button class="botao-secundario" style="padding:5px 12px; font-size:0.8rem; color:#d32f2f; border-color:#ffcdd2;">Revisar</button>
                </div>
            `).join('');
        }
    }

    // 3. Minhas Tarefas (Execução do Gestor/Líder) - CORREÇÃO AQUI
    const listaMinhas = document.getElementById('listaMinhasTarefasGestor');
    if (listaMinhas && data.minhas_tarefas) {
        if (data.minhas_tarefas.length > 0) {
            listaMinhas.innerHTML = data.minhas_tarefas.map(t => `
                <div class="task-row-mini" onclick="abrirTarefaGestor(${t.id})" style="cursor:pointer;">
                    <div>
                        <span style="font-weight:600; color:#333; display:block;">${t.titulo}</span>
                        <span class="task-prj">${t.projeto_nome || 'Sem projeto'}</span>
                    </div>
                    <span class="st-badge st-${t.status}" style="font-size:0.65rem;">
                        ${t.status === 'EM_ANDAMENTO' ? 'Executando' : 'Pendente'}
                    </span>
                </div>
            `).join('');
        } else {
            listaMinhas.innerHTML = '<p style="text-align:center; padding:20px; color:#999;">Você está livre! Nenhuma tarefa atribuída a você.</p>';
        }
    }

    // 4. Histórico de Auditoria (Líder)
    const listaAuditoria = document.getElementById('listaAuditoria');
    if (listaAuditoria) {
        if (data.concluidas_recentes && data.concluidas_recentes.length > 0) {
            listaAuditoria.innerHTML = data.concluidas_recentes.map(t => `
                <div class="inbox-item" onclick="abrirModalRevisao(${t.id}, true)" style="border-left: 4px solid #05cd99;">
                    <div style="flex:1;">
                        <div style="font-weight:700; color:#2b3674; font-size:0.95rem;">${t.titulo}</div>
                        <div style="font-size:0.75rem; color:#aaa;">Responsável: ${t.responsavel}</div>
                    </div>
                    <span style="font-size:0.8rem; color:#05cd99; font-weight:bold;">Concluída</span>
                </div>
            `).join('');
        } else {
            listaAuditoria.innerHTML = '<p style="text-align:center; padding:20px; color:#999;">Nenhuma entrega recente para auditar.</p>';
        }
    }

    // 5. Gráfico
    if (document.getElementById('prodChart') && data.grafico) {
        renderizarGrafico(document.getElementById('prodChart'), data.grafico);
    }
}

// Função para abrir a tarefa do gestor (Redireciona para Minhas Tarefas com parâmetro para abrir modal)
function abrirTarefaGestor(tarefaId) {
    window.location.href = `minhas_tarefas.php?abrir_execucao=${tarefaId}`;
}

/**
 * Nova Função de Modal de Revisão (Rica e Completa)
 * @param {int} tarefaId 
 * @param {boolean} isAuditoria Se true, exibe modo de auditoria do Líder (Refazer). Se false, modo Gestor (Revisar).
 */
async function abrirModalRevisao(tarefaId, isAuditoria = false) {
    try {
        const resp = await fetch(`../api/tarefa_buscar.php?id=${tarefaId}`);
        const json = await resp.json();
        if (!json.ok) return alert(json.erro);
        const t = json.tarefa;
        
        const antigo = document.getElementById('modalRevisaoOverlay');
        if (antigo) antigo.remove();

        // 1. Processar Arquivos/Evidências
        let evidenceHtml = '';
        const checklist = t.checklist ? JSON.parse(t.checklist) : [];
        const itemsWithEvidence = checklist.filter(item => item.concluido && (item.evidencia_url || item.evidencia_nome));

        if(itemsWithEvidence.length > 0) {
            const cards = itemsWithEvidence.map(item => {
                const isLink = item.tipo_evidencia === 'link';
                const icon = isLink ? '🔗' : '📄';
                const cssClass = isLink ? 'is-link' : 'is-file';
                const url = item.evidencia_url.startsWith('http') ? item.evidencia_url : `../public/${item.evidencia_url}`;
                
                return `
                    <a href="${url}" target="_blank" class="evidence-card ${cssClass}">
                        <div class="evidence-icon">${icon}</div>
                        <div class="evidence-title">${item.evidencia_nome || 'Arquivo sem nome'}</div>
                        <div class="evidence-tag">${item.descricao.substring(0, 20)}...</div>
                    </a>
                `;
            }).join('');
            evidenceHtml = `<div class="evidence-grid">${cards}</div>`;
        } else {
            evidenceHtml = `<div style="text-align:center; padding:20px; color:#999; border:2px dashed #eee; border-radius:10px;">Sem arquivos anexados. Verifique os checkboxes.</div>`;
        }

        // 2. Processar Histórico de Conversa (Mensagens)
        let chatHtml = '';
        if (t.historico_mensagens && t.historico_mensagens.length > 0) {
            chatHtml = t.historico_mensagens.map(msg => {
                let roleClass = 'msg-colaborador';
                if(msg.papel === 'GESTOR') roleClass = 'msg-gestor';
                if(msg.papel === 'LIDER' || msg.papel === 'DONO') roleClass = 'msg-lider';
                
                return `
                    <div class="chat-message ${roleClass}">
                        <div class="chat-header">
                            <span>${msg.nome} (${msg.papel})</span>
                            <span>${msg.data_formatada}</span>
                        </div>
                        <div class="chat-body">${msg.mensagem}</div>
                    </div>
                `;
            }).join('');
        } else {
            chatHtml = '<p style="color:#aaa; font-style:italic;">Nenhuma mensagem registrada.</p>';
        }

        // 3. Alerta de Motivo e Recusa (LÓGICA APRIMORADA)
        let alertaMotivoHtml = '';
        let feedbackAnterior = ''; // Variável para guardar o texto do Líder

        if (t.feedback_revisao) {
            // Verifica se a mensagem veio do Líder
            const isLiderFeedback = t.feedback_revisao.includes('[LÍDER');
            const alertClass = isLiderFeedback ? 'alert-lider' : 'audit-alert';
            const alertTitle = isLiderFeedback ? '⚠️ MENSAGEM DA LIDERANÇA' : 'Atenção: Tarefa Devolvida';
            const colorStyle = isLiderFeedback ? 'background:#fff8e1; border-left:5px solid #ffb300; color:#856404;' : '';

            // Guarda para autocompletar
            feedbackAnterior = t.feedback_revisao;

            alertaMotivoHtml = `
                <div class="${alertClass}" style="${colorStyle} padding:15px; border-radius:8px; margin-bottom:20px;">
                    <h4 style="margin:0 0 5px 0; display:flex; align-items:center; gap:10px;">
                        ${ICONS.alerta} ${alertTitle}
                    </h4>
                    <p style="margin:0; font-size:0.95rem;">${t.feedback_revisao}</p>
                </div>
            `;
        }

        // 4. Botões de Ação
        let footerHtml = '';
        // Passamos o feedbackAnterior como argumento
        // Escapamos as aspas para não quebrar o JS
        const safeFeedback = feedbackAnterior.replace(/"/g, '&quot;').replace(/'/g, "\\'");

        if (isAuditoria) {
            // Modo LÍDER (Refazer)
            footerHtml = `
                <button class="botao-secundario" onclick="document.getElementById('modalRevisaoOverlay').remove()">Cancelar</button>
                <button id="btnLiderRefazer" class="botao-primario" style="background:#ffce20; color:#333;" onclick="mostrarAreaRecusa('LIDER', '${safeFeedback}')">
                    ${ICONS.refresh} Solicitar Refação (Devolver ao Gestor)
                </button>
                <button id="btnLiderConfirmar" class="botao-primario" style="background:#d32f2f; display:none;" onclick="confirmarDecisao(${t.id}, 'refazer_lider')">
                    Confirmar Devolução
                </button>
            `;
        } else {
            // Modo GESTOR (Aprovar/Recusar)
            footerHtml = `
                <button id="btnMostrarRecusa" class="botao-secundario" style="color:#d32f2f; border-color:#ffcdd2;" onclick="mostrarAreaRecusa('GESTOR', '${safeFeedback}')">Recusar / Pedir Ajuste</button>
                <button id="btnConfirmarRecusa" class="botao-primario" style="background:#d32f2f; display:none;" onclick="confirmarDecisao(${t.id}, 'recusar')">Confirmar Devolução</button>
                <button id="btnAprovar" class="botao-primario" style="background:#05cd99;" onclick="confirmarDecisao(${t.id}, 'aprovar')">Aprovar e Finalizar</button>
            `;
        }

        const html = `
        <div id="modalRevisaoOverlay">
            <div class="modal-revisao-card">
                <div class="modal-revisao-header">
                    <h3 style="margin:0; color:#2b3674;">
                        ${isAuditoria ? 'Auditoria de Qualidade (Líder)' : 'Revisão de Entrega'}
                    </h3>
                    <button onclick="document.getElementById('modalRevisaoOverlay').remove()" style="border:none; background:none; font-size:1.5rem; cursor:pointer;">&times;</button>
                </div>
                <div class="modal-revisao-body">
                    ${alertaMotivoHtml}
                    
                    <h4 style="margin:0 0 10px 0; color:#666;">Arquivos e Entregas</h4>
                    ${evidenceHtml}
                    
                    <h4 style="margin:20px 0 10px 0; color:#666;">Histórico da Tarefa</h4>
                    <div class="chat-container">
                        ${chatHtml}
                    </div>

                    <div id="areaFeedback" class="feedback-box">
                        <label style="color:#d32f2f; font-weight:bold;">Motivo da Devolução / Instruções:</label>
                        <textarea id="txtFeedback" rows="3" class="campo-padrao" style="width:100%; border-color:#ffcdd2;" placeholder="Explique o que precisa ser corrigido..."></textarea>
                    </div>
                </div>
                <div class="modal-revisao-footer">
                    ${footerHtml}
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', html);
    } catch(e) { console.error(e); }
}

// CORREÇÃO: Função recebe o feedback opcional para preencher
function mostrarAreaRecusa(origem, textoPrevia = '') {
    const area = document.getElementById('areaFeedback');
    const txt = document.getElementById('txtFeedback');
    
    area.style.display = 'block';
    
    // AUTO-PREENCHIMENTO INTELIGENTE
    // Se o Gestor está recusando e existe um texto do Líder, preenche para facilitar
    if (origem === 'GESTOR' && textoPrevia && textoPrevia.includes('[LÍDER')) {
        txt.value = textoPrevia + "\n\nFavor ajustar conforme solicitado acima.";
    }
    
    txt.focus();

    if (origem === 'GESTOR') {
        document.getElementById('btnMostrarRecusa').style.display = 'none';
        document.getElementById('btnAprovar').style.display = 'none';
        document.getElementById('btnConfirmarRecusa').style.display = 'inline-flex';
    } else if (origem === 'LIDER') {
        document.getElementById('btnLiderRefazer').style.display = 'none';
        document.getElementById('btnLiderConfirmar').style.display = 'inline-flex';
    }
}

async function confirmarDecisao(id, acao) {
    const fd = new FormData();
    fd.append('tarefa_id', id);
    
    const feedback = document.getElementById('txtFeedback').value;

    if (acao === 'aprovar') {
        fd.append('status', 'CONCLUIDA');
        fd.append('progresso', 100);
        fd.append('comentario', 'Aprovado pelo gestor.');
    } else {
        // RECUSAR (Gestor) ou REFAZER (Líder/Auditoria)
        if(!feedback.trim()) return alert("É obrigatório informar o motivo.");
        
        if (acao === 'refazer_lider') {
            fd.append('status', 'EM_REVISAO'); // Volta para o Gestor
            fd.append('feedback_revisao', '[LÍDER SOLICITOU REFAÇÃO]: ' + feedback);
            fd.append('comentario', 'Tarefa devolvida pelo líder para revisão.');
        } else {
            // Gestor devolvendo para Colaborador
            fd.append('status', 'EM_ANDAMENTO'); 
            fd.append('feedback_revisao', '[GESTOR SOLICITOU AJUSTE]: ' + feedback);
            fd.append('comentario', 'Tarefa devolvida para ajustes.');
        }
    }
    
    try {
        await fetch('../api/tarefa_entregar.php', { method:'POST', body:fd });
        document.getElementById('modalRevisaoOverlay').remove();
        carregarDashboard();
    } catch(e) { alert("Erro ao processar."); }
}

function getColor(c) {
    const colors = { blue:'#4318FF', red:'#ee5d50', green:'#05cd99', orange:'#ffce20', purple:'#6A66FF' };
    return colors[c] || '#ccc';
}

function renderizarGrafico(canvas, dados) {
    if (!window.Chart) return;
    if (window.myChartInstance) window.myChartInstance.destroy();
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
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } }
    });
}