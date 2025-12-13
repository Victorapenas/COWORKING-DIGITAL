// ARQUIVO: js/relatorios.js
//atualização

let chartBarInstance = null;
let chartDoughnutInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    carregarDadosRelatorio();
});

function limparFiltros() {
    document.getElementById('filtroMes').value = "";
    document.getElementById('filtroEquipe').value = "";
    document.getElementById('filtroMembro').value = "";
    carregarDadosRelatorio();
}

async function carregarDadosRelatorio() {
    const mes = document.getElementById('filtroMes').value;
    const equipe = document.getElementById('filtroEquipe').value;
    const membro = document.getElementById('filtroMembro').value;

    const url = `../api/relatorios_dados.php?mes=${mes}&equipe=${equipe}&membro=${membro}`;

    try {
        const resp = await fetch(url);
        const data = await resp.json();

        if (!data.ok) {
            console.error("Erro na API");
            return;
        }

        // 1. Atualiza KPIs
        document.getElementById('kpiTotal').innerText = data.kpis.total_tarefas;
        document.getElementById('kpiConcluidas').innerText = data.kpis.concluidas;
        document.getElementById('kpiEficiencia').innerText = data.kpis.eficiencia + '%';
        document.getElementById('kpiAtrasadas').innerText = data.kpis.atrasadas;

        // 2. Renderiza Gráficos
        renderizarGraficoBarras(data.membros);
        renderizarGraficoRosca(data.status_chart);

        // 3. Renderiza Tabela
        renderizarTabela(data.membros);

    } catch (err) {
        console.error("Erro ao carregar relatórios:", err);
    }
}

function renderizarGraficoBarras(membros) {
    const ctx = document.getElementById('chartBar').getContext('2d');
    
    // Prepara dados
    const labels = membros.map(m => m.nome.split(' ')[0]); // Primeiro nome
    const dataConcluidas = membros.map(m => m.concluidas);
    const dataTotal = membros.map(m => m.total);

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
                    borderRadius: 5
                },
                {
                    label: 'Total Atribuído',
                    data: dataTotal,
                    backgroundColor: '#e0e5f2',
                    borderRadius: 5,
                    hidden: true // Oculto por padrão para limpar o visual
                }
            ]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } },
            plugins: { legend: { position: 'top' } }
        }
    });
}

function renderizarGraficoRosca(statusData) {
    const ctx = document.getElementById('chartDoughnut').getContext('2d');
    
    if (chartDoughnutInstance) chartDoughnutInstance.destroy();

    chartDoughnutInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Concluído', 'Em Execução', 'Atrasado', 'Planejado'],
            datasets: [{
                data: [statusData.Concluido, statusData.Execucao, statusData.Atrasado, statusData.Planejado],
                backgroundColor: ['#05cd99', '#3b82f6', '#ef4444', '#e5e7eb'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '75%',
            plugins: { legend: { position: 'right' } }
        }
    });
}

function renderizarTabela(membros) {
    const tbody = document.getElementById('tabelaCorpo');
    tbody.innerHTML = '';

    if (membros.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">Nenhum dado encontrado para este filtro.</td></tr>';
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
                    <div style="font-weight:600;">${m.nome}</div>
                    <div style="font-size:0.75rem; color:#999;">${m.nome_equipe || 'Geral'}</div>
                </td>
                <td>${m.total}</td>
                <td style="color:#05cd99; font-weight:700;">${m.concluidas}</td>
                <td style="color:#ef4444;">${m.atrasadas}</td>
                <td>
                    <div class="mini-progress"><div class="mini-fill" style="width:${eficiencia}%; background:${corBarra};"></div></div>
                    <span>${eficiencia}%</span>
                </td>
                <td>${statusBadge}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}