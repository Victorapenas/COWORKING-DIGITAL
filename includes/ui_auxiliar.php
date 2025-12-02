<?php
// ARQUIVO: includes/ui_auxiliar.php

function renderizar_painel_info() {
    $icones = [
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 12c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V8h12v3z"/></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11 2v4H7c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-4V2h-2zm-1 6h4v4h-4zM6 22H4V4h2v18z"/></svg>'
    ];
    ?>
    <div class="painel-info">
        <div class="info-icones">
            <?php foreach ($icones as $icone): ?>
                <div class="info-icone-wrap"><?= $icone ?></div>
            <?php endforeach; ?>
        </div>
        <h2 class="info-titulo">Gerencie seu coworking com inteligência</h2>
        <p class="info-subtitulo">Conecte sua equipe, organize tarefas e comunique-se melhor</p>
    </div>
    <?php
}

function renderizar_logo() {
    ?>
    <div class="logo">
        <div class="logo-icon">
            <img src="../css/coworking_digital.svg" alt="Coworking Digital">
        </div>
    </div>
    <?php
}

function renderizar_sidebar(){
    // Identifica a página atual para marcar no menu
    $paginaAtual = basename($_SERVER['PHP_SELF']);
    ?>
    <div class="nav-menu">
        <h5>NAVEGAÇÃO</h5>

        <a href="dashboard.php" class="<?= $paginaAtual == 'dashboard.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
            Visão Geral
        </a>

        <a href="equipes.php" class="<?= $paginaAtual == 'equipes.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.57.85 2.76 2.33 2.98 3.45H23v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            Gestão de Equipes
        </a>

        <a href="projetos.php" class="<?= (strpos($paginaAtual, 'projeto') !== false) ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>
            Projetos
        </a>

        <a href="calendario.php" class="<?= $paginaAtual == 'calendario.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM5 7V6h14v1H5z"/></svg>
            Calendário
        </a>

        <a href="arquivos.php" class="<?= $paginaAtual == 'arquivos.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>
            Arquivos
        </a>

        <div class="separator"></div>

        <a href="configuracoes.php" class="<?= $paginaAtual == 'configuracoes.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.31-.61-.22l-2.49 1c-.52-.38-1.09-.72-1.71-.98L15 2H9L8.71 4.43c-.62.26-1.19.6-1.71.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.31.61.22l2.49-1c.52.38 1.09.72 1.71.98L9 22h6l.29-2.43c.62-.26 1.19-.6 1.71-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
            Configurações
        </a>

        <a href="#" id="btnLogout">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H9v2h9.17l-2.58 2.58L17 17l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
            Sair
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnLogout = document.getElementById('btnLogout');
            if (btnLogout) {
                btnLogout.addEventListener('click', async (e) => {
                    e.preventDefault();
                    
                    // Feedback visual opcional (cursor loading)
                    document.body.style.cursor = 'wait';

                    try {
                        const resp = await fetch('../api/logout.php', { method: 'POST' });
                        const json = await resp.json();
                        
                        if (json.ok) {
                            window.location.href = 'login.php';
                        } else {
                            // Se a API retornar erro, força redirecionamento mesmo assim
                            window.location.href = 'login.php';
                        }
                    } catch (err) {
                        console.error('Erro ao sair:', err);
                        // Fallback em caso de erro de rede
                        window.location.href = 'login.php';
                    }
                });
            }
        });
    </script>
    <?php
}
?>