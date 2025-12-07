<?php
// ARQUIVO: public/calendario.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
proteger_pagina();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agenda & Prazos</title>
    <link rel="stylesheet" href="../css/painel.css">
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales-all.global.min.js"></script>
    
    <style>
        /* === CONTAINER GERAL === */
        .calendar-container {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.02);
            min-height: 780px;
            font-family: 'Inter', sans-serif;
        }

        /* === CABEÇALHO (TOOLBAR) === */
        .fc-header-toolbar { margin-bottom: 25px !important; }
        .fc-toolbar-title { font-size: 1.6rem !important; color: #2b3674; font-weight: 800; text-transform: capitalize; }
        
        .fc-button {
            background-color: #fff !important;
            border: 1px solid #e0e5f2 !important;
            color: #707eae !important;
            font-weight: 600 !important;
            text-transform: capitalize !important;
            border-radius: 10px !important;
            padding: 8px 16px !important;
            box-shadow: none !important;
            transition: all 0.2s !important;
        }
        .fc-button:hover, .fc-button-active {
            background-color: #0d6efd !important;
            color: #fff !important;
            border-color: #0d6efd !important;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2) !important;
        }

        /* === GRID HORÁRIO (SEMANA/DIA) === */
        .fc-timegrid-axis { background-color: #f8f9fa; border-right: 1px solid #eee; }
        .fc-timegrid-slot { height: 3.5em !important; border-bottom: 1px solid #f1f1f1 !important; }
        
        /* Texto da Hora (09:00) */
        .fc-timegrid-slot-label-cushion { 
            font-size: 0.75rem !important; 
            font-weight: 700 !important; 
            color: #a3aed0 !important; 
            text-transform: uppercase; 
        }
        
        .fc-col-header-cell { background: #fff; padding: 10px 0 !important; border-bottom: 2px solid #f0f0f0 !important; }
        .fc-col-header-cell-cushion { color: #2b3674; font-weight: 700; font-size: 0.9rem; text-decoration: none !important; }
        
        /* Destaque do Hoje */
        .fc-day-today { background-color: rgba(13, 110, 253, 0.02) !important; }

        /* === VISUALIZAÇÃO DE LISTA === */
        .fc-list { border: none !important; }
        .fc-list-day-cushion { background-color: #f4f7fe !important; }
        .fc-list-day-text, .fc-list-day-side-text { color: #2b3674; font-weight: 700; font-size: 1rem; }
        .fc-list-event:hover td { background-color: #f8f9fa !important; }
        .fc-list-event-title { font-weight: 600; color: #333; }
        .fc-list-event-time { color: #0d6efd; font-weight: 600; }
        .fc-list-event-dot { border-width: 5px !important; }

        /* === EVENTOS (CORREÇÃO DE CORES) === */
        .fc-event {
            border: none !important; border-radius: 6px !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin: 1px 2px !important;
            cursor: pointer; transition: transform 0.1s;
        }
        .fc-event:hover { transform: scale(1.02); z-index: 10; }

        /* FORÇA A COR DO TEXTO DENTRO DOS EVENTOS PARA NÃO FICAR BRANCO */
        .fc-event-main, .fc-event-title, .fc-event-time {
            color: inherit !important; /* Herda a cor definida nas classes abaixo */
            font-weight: 700 !important;
        }

        /* Cores Pastéis (Fundo Claro + Texto Escuro) */
        
        /* TAREFA: Fundo Azul Claro, Texto Azul Forte */
        .evt-tarefa { 
            background-color: #e3f2fd !important; 
            border-left: 4px solid #0d6efd !important; 
            color: #0d6efd !important; 
        }
        
        /* PROJETO: Fundo Roxo Claro, Texto Roxo Forte */
        .evt-projeto { 
            background-color: #f3e5f5 !important; 
            border-left: 4px solid #9b59b6 !important; 
            color: #7b1fa2 !important; 
        }
        
        /* CONTRATO: Fundo Vermelho Claro, Texto Vermelho Forte */
        .evt-contrato { 
            background-color: #ffebee !important; 
            border-left: 4px solid #c62828 !important; 
            color: #c62828 !important; 
        }

        /* Barra Vermelha (Hora Atual) */
        .fc-now-indicator-line { border-color: #e74c3c; border-width: 2px; }
        .fc-now-indicator-arrow { border-color: #e74c3c; border-width: 6px; }

        /* Filtros */
        .filters-bar {
            display: flex; gap: 15px; margin-bottom: 25px; padding: 15px 20px;
            background: white; border-radius: 15px; align-items: center; border: 1px solid #eff0f6;
        }
        .filter-select {
            padding: 8px 15px; border: 1px solid #e0e5f2; border-radius: 8px;
            color: #2b3674; font-weight: 500; outline: none; background: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php renderizar_sidebar(); ?>

    <div class="main-content">
        <div class="topbar">
            <h1 style="font-size:1.8rem; color:#2c3e50; font-weight: 800;">Agenda & Prazos</h1>
            <div class="profile"><div class="avatar-profile"><?= strtoupper(substr($_SESSION[SESSAO_USUARIO_KEY]['nome'], 0, 2)) ?></div></div>
        </div>

        <div class="filters-bar">
            <div style="color:#0d6efd; display:flex; align-items:center; gap:8px; font-weight:700;">
                <?= getIcone('calendario') ?> Filtros:
            </div>
            <select id="filtroTipo" class="filter-select">
                <option value="todos">Todos os Eventos</option>
                <option value="tarefa">✅ Tarefas</option>
                <option value="projeto">🚀 Entregas de Projeto</option>
                <option value="contrato">📄 Contratos (Privado)</option>
            </select>
            <select id="filtroStatus" class="filter-select">
                <option value="pendente">Pendentes / Em Andamento</option>
                <option value="concluido">Concluídos</option>
                <option value="todos">Todos os Status</option>
            </select>
        </div>

        <div class="calendar-container">
            <div id='calendar'></div>
        </div>
    </div>
    
    <script src="../js/calendario.js"></script>
</body>
</html>