<?php
// /includes/ui_auxiliar.php

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
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1.8 14.8c-.89-.48-1.57-1.16-2.05-2.05-.7-.7-.78-1.74-.21-2.48l1.63-2.12c.56.74.49 1.78-.21 2.48-.48.89-1.16 1.57-2.05 2.05-.5.28-1.07.42-1.65.42s-1.15-.14-1.65-.42zM12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm.8 4h-1.6c-.22 0-.4.18-.4.4v1.2c0 .22.18.4.4.4h1.6c.22 0 .4-.18.4-.4V8.4c0-.22-.18-.4-.4-.4zM12 18c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/>
            </svg>
        </div>
        <h1>COWORKING</h1>
    </div>
    <?php
}
?>