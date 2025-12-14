// ARQUIVO: js/relatorios.js
// ATUALIZADO: Gráficos Responsivos e Dinâmicos

let chartBarInstance = null;
let chartDoughnutInstance = null;

// Configurações globais do Chart.js para visual limpo
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#a3aed0';

document.addEventListener('DOMContentLoaded', () => {
    carregarDadosRelatorio();
});

function limparFiltros() {
    // Pega a data atual para resetar o mês
    const hoje = new Date();
    const mesStr = hoje.getFullYear() + '-' + String(hoje.getMonth() + 1).padStart(2, '0');
    
    document.getElementById('filtroMes').value = mesStr;
    document.getElementById('filtroEquipe').value = "";
    document.getElementById('filtroMembro').value = "";
    carregarDadosRelatorio();
}

async function carregarDadosRelatorio() {
    const mes = document.getElementById('filtroMes').value;
    const equipe = document.getElementById('filtroEquipe').value;
    const membro = document.getElementById('filtroMembro').value;

    const url = `../api/relatorios_dados.php?mes=${mes}&equipe=${equipe}&membro=${membro}`;

    // Mostra loading visual na tabela
    document.getElementById('tabelaCorpo').innerHTML = '<tr><td colspan="6" style="text-align:center; color:#999; padding:20px;">Atualizando dados...</td></tr>';

    try {
        const resp = await fetch(url);
        const data = await resp.json();

        if (!data.ok) {
            console.error("Erro na API");
            return;
        }

        // 1. Atualiza KPIs com animação simples
        animateValue("kpiTotal", parseInt(document.getElementById('kpiTotal').innerText), data.kpis.total_tarefas, 500);
        animateValue("kpiConcluidas", parseInt(document.getElementById('kpiConcluidas').innerText), data.kpis.concluidas, 500);
        document.getElementById('kpiEficiencia').innerText = data.kpis.eficiencia + '%';
        animateValue("kpiAtrasadas", parseInt(document.getElementById('kpiAtrasadas').innerText), data.kpis.atrasadas, 500);

        // 2. Renderiza Gráficos
        renderizarGraficoBarras(data.membros);
        renderizarGraficoRosca(data.status_chart);

        // 3. Renderiza Tabela
        renderizarTabela(data.membros);

    } catch (err) {
        console.error("Erro ao carregar relatórios:", err);
        document.getElementById('tabelaCorpo').innerHTML = '<tr><td colspan="6" style="text-align:center; color:red;">Erro de conexão.</td></tr>';
    }
}

function animateValue(id, start, end, duration) {
    if (start === end) return;
    const range = end - start;
    let current = start;
    const increment = end > start ? 1 : -1;
    const stepTime = Math.abs(Math.floor(duration / range));
    const obj = document.getElementById(id);
    const timer = setInterval(function() {
        current += increment;
        obj.innerHTML = current;
        if (current == end) {
            clearInterval(timer);
        }
    }, stepTime > 0 ? stepTime : 10); // Evita timer 0
}

function renderizarGraficoBarras(membros) {
    const ctx = document.getElementById('chartBar').getContext('2d');
    
    // Limita a 10 membros para o gráfico não ficar polúido, ordena por total
    const topMembros = membros.slice(0, 10);

    const labels = topMembros.map(m => m.nome.split(' ')[0]); 
    const dataConcluidas = topMembros.map(m => m.concluidas);
    const dataTotal = topMembros.map(m => m.total);

    if (chartBarInstance) chartBarInstance.destroy();

    chartBarInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Concluídas',
                    data: dataConcluidas,
                    backgroundColor: '#05cd99',
                    borderRadius: 4,
                    barPercentage: 0.6,
                },
                {
                    label: 'Total',
                    data: dataTotal,
                    backgroundColor: '#eff4fb',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    grouped: false, // Faz a barra ficar atrás (efeito de preenchimento)
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // IMPORTANTE PARA OCUPAR O CONTAINER CSS
            plugins: {
                legend: { display: false },
                tooltip: { 
                    backgroundColor: '#2b3674',
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5], color: '#f0f0f0' },
                    ticks: { font: { size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
}

function renderizarGraficoRosca(statusData) {
    const ctx = document.getElementById('chartDoughnut').getContext('2d');
    
    if (chartDoughnutInstance) chartDoughnutInstance.destroy();

    const dataValues = [statusData.Concluido, statusData.Execucao, statusData.Atrasado, statusData.Planejado];
    const total = dataValues.reduce((a, b) => a + b, 0);

    // Se não tiver dados, mostra um gráfico vazio cinza
    const isEmpty = total === 0;
    const finalData = isEmpty ? [1] : dataValues;
    const finalColors = isEmpty ? ['#f0f0f0'] : ['#05cd99', '#3b82f6', '#ef4444', '#e5e7eb'];

    chartDoughnutInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Concluído', 'Em Execução', 'Atrasado', 'Planejado'],
            datasets: [{
                data: finalData,
                backgroundColor: finalColors,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // IMPORTANTE
            cutout: '70%',
            plugins: {
                legend: { 
                    position: 'right',
                    labels: { boxWidth: 10, usePointStyle: true, font: { size: 10 } } 
                },
                tooltip: { enabled: !isEmpty }
            }
        }
    });
}

function renderizarTabela(membros) {
    const tbody = document.getElementById('tabelaCorpo');
    tbody.innerHTML = '';

    if (membros.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">Nenhum dado encontrado.</td></tr>';
        return;
    }

    membros.forEach(m => {
        const eficiencia = m.taxa_sucesso;
        let corBarra = '#05cd99';
        if (eficiencia < 50) corBarra = '#ef4444';
        else if (eficiencia < 80) corBarra = '#f59e0b';

        let statusBadge = `<span class="status-pill pill-green">Excelente</span>`;
        if (m.taxa_atraso > 20) statusBadge = `<span class="status-pill pill-red">Atenção</span>`;
        else if (eficiencia < 70) statusBadge = `<span class="status-pill pill-yellow">Monitorar</span>`;

        const row = `
            <tr>
                <td>
                    <div style="font-weight:600; color:#2b3674;">${m.nome}</div>
                    <div style="font-size:0.75rem; color:#999;">${m.nome_equipe || '-'}</div>
                </td>
                <td style="text-align:center;">${m.total}</td>
                <td style="text-align:center; color:#05cd99; font-weight:700;">${m.concluidas}</td>
                <td style="text-align:center; color:#ef4444;">${m.atrasadas}</td>
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div class="mini-progress" style="width:60px; margin:0;"><div class="mini-fill" style="width:${eficiencia}%; background:${corBarra};"></div></div>
                        <span style="font-size:0.8rem; font-weight:600;">${eficiencia}%</span>
                    </div>
                </td>
                <td>${statusBadge}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}