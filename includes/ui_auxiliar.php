<?php
// ARQUIVO: includes/ui_auxiliar.php
require_once __DIR__ . '/funcoes.php'; 

function renderizar_sidebar(){
    $paginaAtual = basename($_SERVER['PHP_SELF']);
    
    // --- CONTEXTO DO USUÁRIO ---
    $sessao = $_SESSION[SESSAO_USUARIO_KEY] ?? [];
    $papel = $sessao['papel'] ?? 'FUNCIONARIO';
    
    // Define se é colaborador (para aplicar tema e esconder menus)
    $isColab = ($papel == 'FUNCIONARIO' || $papel == 'COLABORADOR');
    
    // Aplica classe CSS 'theme-blue' se for colaborador
    $classeTema = $isColab ? 'theme-blue' : '';
    
    // --- LOGO DA EMPRESA ---
    $logoClienteUrl = null;
    $nomeEmpresa = "Minha Empresa";
    
    if (isset($sessao['empresa_id'])) {
        try {
            $pdo = conectar_db();
            $stmt = $pdo->prepare("SELECT nome, logo_url FROM empresa WHERE id = ?");
            $stmt->execute([$sessao['empresa_id']]);
            $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($empresa) {
                $nomeEmpresa = $empresa['nome'];
                if (!empty($empresa['logo_url']) && file_exists(__DIR__ . '/../public/' . $empresa['logo_url'])) {
                    $logoClienteUrl = '../public/' . $empresa['logo_url'];
                }
            }
        } catch (Exception $e) { /* Silêncio */ }
    }
    ?>
    
    <div class="sidebar <?= $classeTema ?>">
        
        <div class="sidebar-header">
            <div class="client-logo-wrapper">
                <?php if ($logoClienteUrl): ?>
                    <img src="<?= $logoClienteUrl ?>" alt="<?= htmlspecialchars($nomeEmpresa) ?>" class="client-logo-img">
                <?php else: ?>
                    <span style="color:#0d6efd; font-weight:800; font-size:1.8rem;">
                        <?= strtoupper(substr($nomeEmpresa, 0, 1)) ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="client-name-text">
                <?= htmlspecialchars($nomeEmpresa) ?>
            </div>
            <div style="font-size: 0.75rem; color: #a3aed0; margin-top: 4px; font-weight: 500;">
                Espaço de Trabalho
            </div>
        </div>

        <div class="sidebar-menu-container">
            <div class="nav-title">MEU ESPAÇO</div>

            <a href="dashboard.php" class="nav-item <?= $paginaAtual == 'dashboard.php' ? 'active' : '' ?>">
                <?= getIcone('arquivo') ?> 
                <?= $isColab ? 'Minha Mesa' : 'Visão Geral' ?>
            </a>

            <?php if (!$isColab): ?>
            <a href="equipes.php" class="nav-item <?= $paginaAtual == 'equipes.php' ? 'active' : '' ?>">
                <?= getIcone('users') ?> Gestão de Equipes
            </a>
            <?php endif; ?>

            <a href="projetos.php" class="nav-item <?= (strpos($paginaAtual, 'projeto') !== false) ? 'active' : '' ?>">
                <?= getIcone('pasta') ?> Projetos
            </a>

            <a href="minhas_tarefas.php" class="nav-item <?= (strpos($paginaAtual, 'tarefa') !== false && strpos($paginaAtual, 'projeto') === false) ? 'active' : '' ?>">
                <?= getIcone('task') ?> Minhas Tarefas
            </a>

            <a href="calendario.php" class="nav-item <?= $paginaAtual == 'calendario.php' ? 'active' : '' ?>">
                <?= getIcone('calendario') ?> Calendário
            </a>

            <?php if (!$isColab): ?>
            <a href="relatorios.php" class="nav-item <?= $paginaAtual == 'relatorios.php' ? 'active' : '' ?>">
                <?= getIcone('documento') ?> Relatórios
            </a>
            <?php endif; ?>

            <div class="separator"></div>
            <div class="nav-title">SISTEMA</div>

            <?php if (!$isColab): ?>
            <a href="configuracoes.php" class="nav-item <?= $paginaAtual == 'configuracoes.php' ? 'active' : '' ?>">
                <?= getIcone('config') ?> Configurações
            </a>
            <?php endif; ?>

            <a href="#" class="nav-item" onclick="logoutSistema()">
                <?= getIcone('sair') ?> Sair
            </a>
        </div>

        <div class="sidebar-footer">
            <?php 
                $logoSys = '../imgs/logo coworking.png';
                if (!file_exists(__DIR__ . '/../imgs/logo coworking.png')) {
                    $logoSys = '../css/coworking_digital.svg';
                }
            ?>
            <img src="<?= $logoSys ?>" alt="Sistema Coworking" class="system-logo-img">
        </div>

    </div>
    <script>
        function logoutSistema() {
            if(confirm('Deseja realmente sair do sistema?')) {
                document.body.style.cursor = 'wait';
                fetch('../api/logout.php', { method: 'POST' })
                    .then(() => window.location.href = 'login.php')
                    .catch(() => window.location.href = 'login.php');
            }
        }
    </script>
    <?php
}


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
                <div class="info-icone-wrap" style="fill:white"><?= $icone ?></div>
            <?php endforeach; ?>
        </div>
        <h2 class="info-titulo">Gerencie seu coworking com inteligência</h2>
        <p class="info-subtitulo">Conecte sua equipe, organize tarefas e comunique-se melhor</p>
    </div>
    <?php
}

function renderizar_logo() {
    $logoSistema = '../imgs/logo coworking.png'; 
    if (!file_exists(__DIR__ . '/../imgs/logo coworking.png')) {
        $logoSistema = '../css/coworking_digital.svg';
    }
    ?>
    <div class="logo-box">
        <img src="<?= $logoSistema ?>" alt="Coworking Digital" class="logo-sistema-img">
    </div>
    <?php
}

function renderizar_topo_personalizado() {
    $nomeUsuario = htmlspecialchars($_SESSION[SESSAO_USUARIO_KEY]['nome'] ?? 'Usuário');
    $iniciais = strtoupper(substr($nomeUsuario, 0, 2));
    ?>
    <div class="topbar">
        <div></div> 

        <div class="profile">
            <div style="text-align:right; font-size:0.85rem; color:#666; margin-right:10px;">
                Olá, <strong><?= $nomeUsuario ?></strong>
            </div>
            <div class="avatar-profile"><?= $iniciais ?></div>
        </div>
    </div>
    <?php
}
?>