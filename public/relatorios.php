<?php
// ARQUIVO: public/relatorios.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php'; // Para getIcone()

proteger_pagina();
$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$empresaId = getEmpresaIdLogado($usuario);

// Dados para os filtros (Selects)
$listaEquipes = listarEquipes($empresaId);
$listaMembros = getMembrosDisponiveis($empresaId);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatórios de Desempenho</title>
    <link rel="stylesheet" href="../css/painel.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Estilos de Impressão */
        @media print {
            .sidebar, .topbar, .filters-bar, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            body { background: white; }
            .card-info, .content-box { box-shadow: none !important; border: 1px solid #ddd !important; }
        }

        /* Barra de Filtros Estilo "Card" */
        .filters-container {
            background: white; padding: 20px; border-radius: 20px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.02); margin-bottom: 30px;
            border: 1px solid #f0f0f0;
        }
        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: end; }
        
        /* Tabela Detalhada */
        .table-custom { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table-custom th { text-align: left; padding: 15px; color: #a3aed0; font-size: 0.8rem; font-weight: 600; border-bottom: 1px solid #eee; }
        .table-custom td { padding: 15px; border-bottom: 1px solid #f9f9f9; vertical-align: middle; color: #2b3674; font-size: 0.9rem; font-weight: 500; }
        .table-custom tr:last-child td { border-bottom: none; }
        
        .status-pill { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .pill-green { background: #e8f5e9; color: #2e7d32; }
        .pill-red { background: #ffebee; color: #c62828; }
        .pill-yellow { background: #fff8e1; color: #f57f17; }

        /* Barra de Progresso dentro da tabela */
        .mini-progress { height: 6px; width: 100px; background: #eee; border-radius: 3px; overflow: hidden; display: inline-block; vertical-align: middle; margin-right: 10px; }
        .mini-fill { height: 100%; border-radius: 3px; }
    </style>
</head>
<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <div>
                <h1 style="font-size:1.8rem; font-weight:800; color:#2c3e50; margin:0;">Relatórios & Inteligência</h1>
                <p style="color:#7f8c8d; font-size:0.9rem; margin-top:5px;">Análise de performance de equipes e colaboradores.</p>
            </div>
            <button class="botao-primario" onclick="window.print()" style="background:#2c3e50;">
                <?= getIcone('documento') ?> Exportar PDF
            </button>
        </div>

        <div class="filters-container">
            <h4 style="margin:0 0 15px 0; color:#2b3674;">Filtros de Análise</h4>
            <div class="filter-row">
                <div class="form-group">
                    <label>Período (Mês)</label>
                    <input type="month" id="filtroMes" value="<?= date('Y-m') ?>" class="campo-padrao" onchange="carregarDadosRelatorio()">
                </div>
                <div class="form-group">
                    <label>Equipe</label>
                    <select id="filtroEquipe" class="campo-padrao" onchange="carregarDadosRelatorio()">
                        <option value="">Todas as Equipes</option>
                        <?php foreach($listaEquipes as $eq): ?>
                            <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Colaborador</label>
                    <select id="filtroMembro" class="campo-padrao" onchange="carregarDadosRelatorio()">
                        <option value="">Todos os Colaboradores</option>
                        <?php foreach($listaMembros as $mem): ?>
                            <option value="<?= $mem['id'] ?>"><?= htmlspecialchars($mem['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button class="botao-secundario" onclick="limparFiltros()" style="width:100%;">Limpar Filtros</button>
                </div>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="card-info">
                <div class="icon-box c-blue"><?= getIcone('task') ?></div>
                <div class="kpi-text"><h3 id="kpiTotal">0</h3><p>Total Tarefas</p></div>
            </div>
            <div class="card-info">
                <div class="icon-box c-green"><?= getIcone('check') ?></div>
                <div class="kpi-text"><h3 id="kpiConcluidas">0</h3><p>Entregues</p></div>
            </div>
            <div class="card-info">
                <div class="icon-box c-purple"><?= getIcone('chart') ?></div>
                <div class="kpi-text"><h3 id="kpiEficiencia">0%</h3><p>Eficiência Global</p></div>
            </div>
            <div class="card-info" style="border-color: #ffcdd2;">
                <div class="icon-box c-orange" style="background:#e74c3c;"><?= getIcone('alerta') ?></div>
                <div class="kpi-text"><h3 id="kpiAtrasadas" style="color:#c62828;">0</h3><p style="color:#e74c3c;">Atrasadas</p></div>
            </div>
        </div>

        <div class="dashboard-cards" style="grid-template-columns: 2fr 1fr;">
            <div class="content-box" style="background:white; padding:25px; border-radius:20px;">
                <h4 style="margin:0 0 20px 0; color:#2b3674;">Produtividade por Colaborador</h4>
                <canvas id="chartBar" height="250"></canvas>
            </div>
            <div class="content-box" style="background:white; padding:25px; border-radius:20px;">
                <h4 style="margin:0 0 20px 0; color:#2b3674;">Status Geral</h4>
                <div style="height:250px; display:flex; justify-content:center;">
                    <canvas id="chartDoughnut"></canvas>
                </div>
            </div>
        </div>

        <div class="content-box" style="background:white; padding:30px; border-radius:20px; margin-bottom:50px;">
            <h4 style="margin:0 0 10px 0; color:#2b3674;">Desempenho Detalhado da Equipe</h4>
            <div style="overflow-x:auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>COLABORADOR / EQUIPE</th>
                            <th>TOTAL TAREFAS</th>
                            <th>CONCLUÍDAS</th>
                            <th>ATRASADAS</th>
                            <th>TAXA DE SUCESSO</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaCorpo">
                        <tr><td colspan="6" style="text-align:center;">Carregando dados...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="../js/relatorios.js?v=<?= time() ?>"></script>
</body>
</html>