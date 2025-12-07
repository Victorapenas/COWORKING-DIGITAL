document.addEventListener('DOMContentLoaded', () => {
    carregarDashboard();
});

async function carregarDashboard() {
    const resp = await fetch('../api/dashboard_stats.php');
    const data = await resp.json();

    if(data.ok) {
        // Render KPIs
        document.getElementById('kpiContainer').innerHTML = `
            <div class="card-info"><div class="icon-box c-blue">📂</div><div class="kpi-text"><h3>${data.kpis.projetos_ativos}</h3><p>Projetos Ativos</p></div></div>
            <div class="card-info"><div class="icon-box c-orange">⚡</div><div class="kpi-text"><h3>${data.kpis.tarefas_pendentes}</h3><p>Tarefas Abertas</p></div></div>
            <div class="card-info"><div class="icon-box c-green">🟢</div><div class="kpi-text"><h3>${data.kpis.equipe_online}</h3><p>Online Agora</p></div></div>
            <div class="card-info"><div class="icon-box" style="background:#e74c3c;">🚨</div><div class="kpi-text"><h3>${data.kpis.emergencias}</h3><p>Alertas</p></div></div>
        `;

        // Render Projetos
        const projHtml = data.projetos.map(p => `
            <div style="display:flex; justify-content:space-between; align-items:center; padding:15px; border-bottom:1px solid #eee;">
                <div><strong>${p.nome}</strong><br><small style="color:#999">${p.status}</small></div>
                <div style="text-align:right;"><span style="color:#0d6efd; font-weight:bold;">${p.progresso || 0}%</span></div>
            </div>
        `).join('');
        document.getElementById('listaProjetosRecentes').innerHTML = projHtml || '<p style="color:#999">Nenhum projeto recente.</p>';

        // Render Emergencias
        const emergHtml = data.emergencias.map(e => `
            <div style="padding:10px; background:#fff5f5; border-left:3px solid #e74c3c; margin-bottom:10px; border-radius:4px;">
                <strong style="color:#c0392b">${e.titulo}</strong><br><small>Prioridade: ${e.prioridade}</small>
            </div>
        `).join('');
        document.getElementById('listaEmergencias').innerHTML = emergHtml ? `<h4 style="margin-bottom:10px; color:#c0392b;">🚨 Atenção Necessária</h4>${emergHtml}` : '';

        // Chart.js (Simples)
        const ctx = document.getElementById('graficoSemana').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex'],
                datasets: [{
                    label: 'Tarefas Concluídas',
                    data: [12, 19, 3, 5, 2], // Dados mockados para visual (pode conectar na API depois)
                    backgroundColor: '#6A66FF',
                    borderRadius: 5
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }
}