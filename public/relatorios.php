<?php
// ARQUIVO: public/relatorios.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';

proteger_pagina();
$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$empresaId = getEmpresaIdLogado($usuario);

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
        /* CSS Específico para esta página injetado aqui para sobrescrever */
        .main-content {
            padding: 20px;
            height: 100vh; /* Ocupa a altura da tela */
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Evita rolagem na página inteira */
        }

        /* Topo Compacto */
        .header-compact {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;
            flex-shrink: 0;
        }
        .header-compact h1 { font-size: 1.5rem; margin: 0; }

        /* Filtros em Linha */
        .filters-bar-slim {
            background: white; padding: 10px 15px; border-radius: 12px;
            border: 1px solid #e0e5f2; display: flex; gap: 10px; align-items: center;
            margin-bottom: 15px; flex-shrink: 0; flex-wrap: wrap;
        }
        .filters-bar-slim .form-group { margin-bottom: 0 !important; flex: 1; min-width: 150px; }
        .filters-bar-slim label { display: none !important; } /* Esconde labels para economizar espaço */
        .filters-bar-slim input, .filters-bar-slim select { margin-bottom: 0 !important; height: 38px !important; font-size: 0.85rem; }
        .btn-limpar { height: 38px; padding: 0 15px; font-size: 0.85rem; white-space: nowrap; }

        /* Grid Principal */
        .report-grid {
            display: grid;
            grid-template-columns: 250px 1fr; /* Coluna KPIs fixa, Resto fluido */
            grid-template-rows: 280px 1fr;    /* Linha Gráficos fixa, Tabela fluida */
            gap: 15px;
            flex-grow: 1;
            overflow: hidden;
        }

        /* KPIs Vertical (Esquerda) */
        .kpi-column {
            grid-row: 1 / -1; /* Ocupa toda altura */
            display: flex; flex-direction: column; gap: 15px;
            overflow-y: auto; padding-right: 5px;
        }
        .kpi-card-mini {
            background: white; padding: 15px; border-radius: 12px;
            border: 1px solid #f0f0f0; display: flex; align-items: center; gap: 15px;
            transition: transform 0.2s;
        }
        .kpi-card-mini:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .kpi-mini-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .kpi-mini-content h3 { margin: 0; font-size: 1.2rem; color: #2b3674; }
        .kpi-mini-content p { margin: 0; font-size: 0.75rem; color: #a3aed0; text-transform: uppercase; }

        /* Gráficos (Topo Direita) */
        .charts-row {
            display: grid; grid-template-columns: 2fr 1fr; gap: 15px;
            height: 100%;
        }
        .chart-box {
            background: white; border-radius: 16px; padding: 15px;
            border: 1px solid #f0f0f0; position: relative;
            display: flex; flex-direction: column;
        }
        .chart-box h4 { margin: 0 0 10px 0; font-size: 0.9rem; color: #2b3674; }
        .canvas-container { flex-grow: 1; position: relative; width: 100%; height: 100%; min-height: 0; }

        /* Tabela (Fundo Direita) */
        .table-section {
            background: white; border-radius: 16px; border: 1px solid #f0f0f0;
            display: flex; flex-direction: column; overflow: hidden;
        }
        .table-header-sticky {
            padding: 15px; border-bottom: 1px solid #eee; flex-shrink: 0;
            font-weight: 700; color: #2b3674; display: flex; justify-content: space-between;
        }
        .table-scroll-wrapper { overflow-y: auto; flex-grow: 1; }
        .table-premium th { position: sticky; top: 0; background: #f8f9fa; z-index: 10; }

        /* Responsividade */
        @media (max-width: 1200px) {
            .report-grid { grid-template-columns: 1fr; grid-template-rows: auto auto 1fr; overflow-y: auto; }
            .kpi-column { grid-row: auto; flex-direction: row; flex-wrap: wrap; }
            .kpi-card-mini { flex: 1; }
            .charts-row { grid-template-columns: 1fr; height: auto; }
            .chart-box { height: 300px; }
            .main-content { height: auto; overflow: auto; }
        }
    </style>
</head>
<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content">
        <div class="header-compact">
            <div>
                <h1 style="color:#2c3e50; font-weight:800;">Relatórios & Insights</h1>
            </div>
            <button class="botao-secundario" onclick="window.print()" style="height:38px; font-size:0.85rem;">
                <?= getIcone('documento') ?> PDF
            </button>
        </div>

        <div class="filters-bar-slim">
            <div style="font-weight:700; color:#2b3674; margin-right:10px; font-size:0.9rem;">
                <?= getIcone('search') ?> Filtrar:
            </div>
            <div class="form-group">
                <input type="month" id="filtroMes" value="<?= date('Y-m') ?>" class="campo-padrao" onchange="carregarDadosRelatorio()">
            </div>
            <div class="form-group">
                <select id="filtroEquipe" class="campo-padrao" onchange="carregarDadosRelatorio()">
                    <option value="">Todas as Equipes</option>
                    <?php foreach ($listaEquipes as $eq): ?>
                        <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select id="filtroMembro" class="campo-padrao" onchange="carregarDadosRelatorio()">
                    <option value="">Todos Colaboradores</option>
                    <?php foreach ($listaMembros as $mem): ?>
                        <option value="<?= $mem['id'] ?>"><?= htmlspecialchars($mem['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="botao-secundario btn-limpar" onclick="limparFiltros()">Limpar</button>
        </div>

        <div class="report-grid">
            
            <div class="kpi-column">
                <div class="kpi-card-mini">
                    <div class="kpi-mini-icon c-blue"><?= getIcone('task') ?></div>
                    <div class="kpi-mini-content">
                        <h3 id="kpiTotal">0</h3>
                        <p>Total Tarefas</p>
                    </div>
                </div>
                <div class="kpi-card-mini">
                    <div class="kpi-mini-icon c-green"><?= getIcone('check') ?></div>
                    <div class="kpi-mini-content">
                        <h3 id="kpiConcluidas">0</h3>
                        <p>Entregues</p>
                    </div>
                </div>
                <div class="kpi-card-mini">
                    <div class="kpi-mini-icon c-purple"><?= getIcone('chart') ?></div>
                    <div class="kpi-mini-content">
                        <h3 id="kpiEficiencia">0%</h3>
                        <p>Eficiência</p>
                    </div>
                </div>
                <div class="kpi-card-mini" style="border-color:#ffcdd2;">
                    <div class="kpi-mini-icon c-red"><?= getIcone('alerta') ?></div>
                    <div class="kpi-mini-content">
                        <h3 id="kpiAtrasadas" style="color:#c62828;">0</h3>
                        <p style="color:#e74c3c;">Atrasadas</p>
                    </div>
                </div>
            </div>

            <div class="charts-row">
                <div class="chart-box">
                    <h4>Produtividade (Volume vs Entregas)</h4>
                    <div class="canvas-container">
                        <canvas id="chartBar"></canvas>
                    </div>
                </div>
                <div class="chart-box">
                    <h4>Status Geral</h4>
                    <div class="canvas-container">
                        <canvas id="chartDoughnut"></canvas>
                    </div>
                </div>
            </div>

            <div class="table-section">
                <div class="table-header-sticky">
                    <span>Detalhamento da Equipe</span>
                    <span style="font-size:0.8rem; font-weight:400; color:#999;">Ord: Entregas</span>
                </div>
                <div class="table-scroll-wrapper">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th style="text-align:center;">Total</th>
                                <th style="text-align:center;">Feitas</th>
                                <th style="text-align:center;">Atraso</th>
                                <th>Eficiência</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaCorpo">
                            <tr><td colspan="6" style="text-align:center; padding:20px;">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script src="../js/relatorios.js?v=<?= time() ?>"></script>
</body>
</html>