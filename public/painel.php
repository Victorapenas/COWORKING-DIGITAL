<?php
// /public/painel.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
require_once __DIR__ . '/../includes/funcoes.php'; // funcoes.php já chama conexao.php internamente, se precisar
proteger_pagina();

$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$papel = $usuario['papel']; // DONO, GESTOR, FUNCIONARIO
$pode_cadastrar = ($papel === 'DONO' || $papel === 'GESTOR');

// 1. OBTENDO O ID DA EMPRESA LOGADA CHAMANDO A FUNÇÃO AUXILIAR
$empresaIdLogado = getEmpresaIdLogado($usuario);

// 2. CHAMA AS FUNÇÕES PASSANDO O ID DA EMPRESA
$membrosLideranca = getMembrosLideranca($empresaIdLogado);
$membrosFuncionariosAgrupados = getMembrosFuncionarios($empresaIdLogado); 

// Contagem total dos membros (incluindo Liderança) para o Dashboard Card
$totalMembrosLideranca = count($membrosLideranca);
$totalMembrosFuncionario = 0;
foreach ($membrosFuncionariosAgrupados as $funcao => $membros) {
    $totalMembrosFuncionario += count($membros);
}
$totalGeralMembros = $totalMembrosLideranca + $totalMembrosFuncionario;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel - Coworking Digital</title>
    <link rel="stylesheet" href="../css/painel.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="#0d6efd"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm-1 15h2v-6h-2v6zm0-8h2V7h-2v2z"/></svg>
            <h3 style="color: #0d6efd; margin: 0;">Coworking</h3>
        </div>

        <div class="profile-box">
            <div class="avatar">AD</div>
            <div>
                <strong style="display: block;"><?= htmlspecialchars($usuario['nome']) ?></strong>
                <small><?= htmlspecialchars($papel) ?></small>
            </div>
        </div>
        <?php renderizar_sidebar(); ?>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="search-box">
                <input type="text" placeholder="Buscar..." />
            </div>
            <div class="profile">
                <span class="icon-badge">1</span>
                <div class="avatar"><?= strtoupper(substr($usuario['nome'], 0, 2)) ?></div>
            </div>
        </div>
        <div style="font-size: 1.5rem; font-weight: bold; color: #333;">Gerenciamento de Equipes</div>
        <p style="color: var(--cor-secundaria); margin-bottom: 20px;">Gerencie membros, funções e níveis de acesso das equipes</p>

        <div class="dashboard-cards">
            <div class="card-info">
                <div class="icon"><?= $totalGeralMembros ?></div>
                <p>Total de Membros</p>
            </div>
            <div class="card-info">
                <div class="icon">16</div>
                <p>Tarefas Ativas</p>
            </div>
            <div class="card-info">
                <div class="icon">233</div>
                <p>Tarefas Concluídas</p>
            </div>
        </div>

        <div class="team-management-section">

            <div class="team-card">
                <div class="team-header">
                    <div>
                        <h3 style="color: #6A66FF;">Liderança Executiva</h3>
                        <p style="font-size: 0.9rem; color: #666;">Nível máximo de acesso ao sistema</p>
                    </div>
                    <div class="team-stats">
                        <span><?= count($membrosLideranca) ?> membros</span>
                        <?php if ($pode_cadastrar): ?>
                            <button class="botao-equipe" onclick="openModal()" style="margin-left: 15px;">+ Adicionar Membro</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="member-list">
                    <?php if (empty($membrosLideranca)): ?>
                        <p style="text-align: center; color: #aaa;">Nenhum membro na Liderança Executiva.</p>
                    <?php else: ?>
                        <?php foreach ($membrosLideranca as $membro): ?>
                            <?php 
                                $iniciais = strtoupper(substr($membro['nome'], 0, 2));
                                $papelDisplay = ($membro['papel_nome'] === 'DONO') ? 'CEO' : 'Gestor';
                                $badgeClass = ($membro['papel_nome'] === 'DONO') ? 'badge-dono' : 'badge-gestor';
                            ?>
                            <div class="member-item">
                                <div class="member-info">
                                    <div class="avatar-sm" style="background: #6A66FF;"><?= $iniciais ?></div>
                                    <div class="info-text">
                                        <h4><?= htmlspecialchars($membro['nome']) ?> <span class="badge <?= $badgeClass ?>"><?= $papelDisplay ?></span></h4>
                                        <p><span style="font-style: italic;"><?= htmlspecialchars($membro['email']) ?></span></p>
                                        <p style="font-size: 0.7rem; color: #aaa;">Entrou em: <?= date('Y-m-d', strtotime($membro['criado_em'])) ?></p>
                                    </div>
                                </div>
                                <div class="member-actions">
                                    <button class="botao-config">Configurar</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="team-card">
                <div class="team-header">
                    <div>
                        <h3 style="color: #28C76F;">Membros</h3>
                    </div>
                    <div class="team-stats">
                        <span><?= $totalMembrosFuncionario ?> membros</span>
                        <span>11 ativas (Simulado)</span>
                        <span>100 concluídas (Simulado)</span>
                        <?php if ($pode_cadastrar): ?>
                            <button class="botao-equipe" onclick="openModal()" style="margin-left: 15px;">+ Adicionar Membro</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="member-list">
                    <?php if (empty($membrosFuncionariosAgrupados)): ?>
                        <p style="text-align: center; color: #aaa;">Nenhum membro na equipe de Desenvolvimento.</p>
                    <?php else: ?>
                        
                        <?php foreach ($membrosFuncionariosAgrupados as $cargoDetalhe => $membros): ?>
                            <h4 style="margin-top: 25px; margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 5px; color: #333;">
                                🧑‍💻 <?= htmlspecialchars($cargoDetalhe) ?> (<?= count($membros) ?>)
                            </h4>
                            
                            <?php foreach ($membros as $membro): ?>
                                <?php 
                                    $iniciais = strtoupper(substr($membro['nome'], 0, 2));
                                    // Pega o papel_display definido na função PHP
                                    $papelDisplay = $membro['papel_display'] ?? 'Membro'; 
                                ?>
                                <div class="member-item">
                                    <div class="member-info">
                                        <div class="avatar-sm" style="background: #00cfe8;"><?= $iniciais ?></div>
                                        <div class="info-text">
                                            <h4><?= htmlspecialchars($membro['nome']) ?> <span class="badge badge-funcionario"><?= $papelDisplay ?></span></h4>
                                            <p><span style="font-style: italic;"><?= htmlspecialchars($membro['email']) ?></span></p>
                                            <p style="font-size: 0.7rem; color: #aaa;">Entrou em: <?= date('Y-m-d', strtotime($membro['criado_em'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="member-actions">
                                        <button class="botao-config">Configurar</button>
                                        <button class="botao-mensagem">Mensagem</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
    
                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <div id="addMemberModal" class="modal">
        <div class="modal-content">
            <h3>Adicionar Novo Membro</h3>
            <form id="formAddMember">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" id="novo_nome" placeholder="Digite o nome completo..." required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="novo_email" placeholder="email@coworking.com" required>
                </div>
                <div class="form-group">
                    <label>Função</label>
                    <input type="text" id="nova_funcao" placeholder="Ex: Desenvolvedor, Designer..." required>
                </div>
                <div class="form-group">
                    <label>Equipe</label>
                    <select id="nova_equipe" required>
                        <option value="">Selecione uma equipe</option>
                        <option value="GESTOR">Liderança Executiva (Gestor)</option>
                        <option value="FUNCIONARIO">Desenvolvimento (Funcionário)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nível de Acesso (Papel do Sistema)</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="nivel_acesso" value="GESTOR" checked>
                            <span class="label-senior">Gestor/Chefe da Equipe</span>
                        </label>
                        <label>
                            <input type="radio" name="nivel_acesso" value="FUNCIONARIO">
                            Membro/Funcionário
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="botao-secundario" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="botao-primario">Adicionar Membro</button>
                </div>
            </form>
            <div id="msgSucesso" class="box-senha" style="display: none;"></div>
        </div>
    </div>
    <script src="../js/painel.js"></script>
</body>
</html>