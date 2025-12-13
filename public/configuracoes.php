<?php
//atualização
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php';
proteger_pagina();

$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$isLeader = in_array($usuario['papel'], ['DONO', 'LIDER']);
$empresa = buscarEmpresaPorId(getEmpresaIdLogado($usuario));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configurações</title>
    <link rel="stylesheet" href="../css/painel.css">
    <style>
        .config-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        @media(min-width: 992px) {
            .config-grid { grid-template-columns: 1fr 1fr; }
        }
        .config-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            height: fit-content;
        }
        .card-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex; align-items: center; gap: 10px;
        }
        .preview-logo {
            width: 100%; height: 100px;
            background: #f9f9f9; border: 2px dashed #e0e0e0;
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            margin-bottom: 15px; overflow: hidden;
        }
        .preview-logo img { max-height: 80px; max-width: 90%; }
        .helper-text { font-size: 0.85rem; color: #888; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php renderizar_sidebar(); ?>
    </div>

    <div class="main-content">
        <h1 style="margin-bottom:30px; color:#2c3e50; font-weight:800;">Configurações</h1>

        <div class="config-grid">
            
            <div class="config-card">
                <div class="card-header"><?= getIcone('user') ?> Meu Perfil</div>
                
                <form id="formPerfil">
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" id="p_nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>E-mail de Acesso</label>
                        <input type="email" id="p_email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-top: 20px;">
                        <h4 style="margin:0 0 15px 0; color:#555; font-size:0.95rem;">Alterar Senha</h4>
                        <div class="form-group">
                            <label>Nova Senha</label>
                            <input type="password" id="p_senha1" placeholder="Deixe em branco para manter a atual">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Confirmar Senha</label>
                            <input type="password" id="p_senha2">
                        </div>
                    </div>

                    <button type="submit" class="botao-primario" style="width:100%; margin-top:25px;">Salvar Meu Perfil</button>
                </form>
            </div>

            <?php if($isLeader): ?>
            <div class="config-card" style="border-top: 4px solid #6A66FF;">
                <div class="card-header" style="color: #6A66FF;">
                    <?= getIcone('config') ?> Dados da Organização
                </div>
                
                <form id="formEmpresa" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nome do Espaço / Empresa</label>
                        <input type="text" name="nome" value="<?= htmlspecialchars($empresa['nome']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Logotipo Personalizado</label>
                        <div class="preview-logo">
                            <?php if(!empty($empresa['logo_url'])): ?>
                                <img src="<?= $empresa['logo_url'] ?>" alt="Logo Atual">
                            <?php else: ?>
                                <span style="color:#ccc;">Sem logo definida</span>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="logo_arquivo" accept="image/*" class="campo-form" style="padding:10px;">
                        <p class="helper-text">Esta logo substituirá o ícone padrão no menu lateral.</p>
                    </div>

                    <div class="form-group">
                        <label>Senha Padrão (Para novos membros)</label>
                        <input type="text" name="padrao_senha" value="<?= htmlspecialchars($empresa['padrao_senha'] ?? '@NomeEmpresa123') ?>">
                        <p class="helper-text">Senha inicial automática ao cadastrar novos colaboradores.</p>
                    </div>

                    <button type="submit" class="botao-primario" style="width:100%; margin-top:20px; background-color: #6A66FF;">Atualizar Empresa</button>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="../js/configuracoes.js"></script>
</body>
</html>