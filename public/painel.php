<?php
// /public/painel.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/../includes/ui_auxiliar.php';
proteger_pagina();

$usuario = $_SESSION[SESSAO_USUARIO_KEY];
$papel = $usuario['papel']; // DONO, GESTOR, FUNCIONARIO
$pode_cadastrar = ($papel === 'DONO' || $papel === 'GESTOR');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel - Coworking Digital</title>
    <link rel="stylesheet" href="../css/login.css">
    <style>
        body { display: block; background: #f0f2f5; padding: 20px; }
        .container-painel { max-width: 900px; margin: 0 auto; }
        .header-painel { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .badge { padding: 5px 10px; border-radius: 15px; font-weight: bold; color: white; font-size: 0.8rem; }
        .badge-dono { background: #6A66FF; } .badge-func { background: #28C76F; }
        .card-dashboard { background: white; padding: 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        
        /* Estilo do Formulário Interno */
        .form-cadastro { display: grid; grid-template-columns: 1fr 1fr 150px auto; gap: 10px; align-items: end; }
        .form-cadastro input, .form-cadastro select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; }
        .box-senha { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-top: 15px; display: none; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="container-painel">
        <div class="header-painel">
            <div>
                <h2>Olá, <?= htmlspecialchars($usuario['nome']) ?>!</h2>
                <p style="color:#666;">Empresa: Coworking Digital</p>
            </div>
            <div style="text-align:right;">
                <span class="badge badge-<?= ($papel=='DONO')?'dono':'func' ?>"><?= $papel ?></span>
                <br><br>
                <button id="btnLogout" class="botao-primario" style="padding: 8px 15px; font-size: 0.9rem; background: #e74c3c;">Sair</button>
            </div>
        </div>

        <?php if ($pode_cadastrar): ?>
        <div class="card-dashboard">
            <h3 style="color: var(--cor-primaria); margin-bottom: 15px;">Cadastrar Membro da Equipe</h3>
            <form id="formAdd" class="form-cadastro">
                <div>
                    <label>Nome</label>
                    <input type="text" id="novo_nome" placeholder="Ex: João" required>
                </div>
                <div>
                    <label>E-mail</label>
                    <input type="email" id="novo_email" placeholder="joao@empresa.com" required>
                </div>
                <div>
                    <label>Papel</label>
                    <select id="novo_papel">
                        <option value="FUNCIONARIO">Funcionário</option>
                        <option value="GESTOR">Gestor</option>
                    </select>
                </div>
                <button type="submit" class="botao-primario">Adicionar</button>
            </form>
            <div id="msgSucesso" class="box-senha"></div>
        </div>
        <?php endif; ?>

        <div class="card-dashboard">
            <h3>📋 Minhas Tarefas</h3>
            <p>Aqui vai a lista de tarefas do usuário...</p>
            <div style="padding: 40px; text-align: center; background: #f9f9f9; border-radius: 8px; margin-top: 10px; color: #aaa;">
                (Placeholder das Tarefas)
            </div>
        </div>
    </div>

    <script>
        // Logout
        document.getElementById('btnLogout').addEventListener('click', async () => {
            await fetch('../api/logout.php', { method: 'POST' }); // Crie esse arquivo se não existir
            window.location.href = 'login.php';
        });

        // Cadastro de Membro
        const formAdd = document.getElementById('formAdd');
        if (formAdd) {
            formAdd.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = formAdd.querySelector('button');
                const msgBox = document.getElementById('msgSucesso');
                
                btn.textContent = "Salvando..."; btn.disabled = true;
                msgBox.style.display = 'none';

                const dados = {
                    nome: document.getElementById('novo_nome').value,
                    email: document.getElementById('novo_email').value,
                    papel: document.getElementById('novo_papel').value
                };

                try {
                    const resp = await fetch('../api/admin_adicionar_membro.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(dados)
                    });
                    const json = await resp.json();

                    if (json.ok) {
                        msgBox.innerHTML = `<strong>✅ Sucesso!</strong> Senha padrão gerada: <h2>${json.senha_gerada}</h2><small>Informe ao usuário. Ele deverá trocar no primeiro acesso.</small>`;
                        msgBox.style.display = 'block';
                        formAdd.reset();
                    } else {
                        alert(json.erro || "Erro ao cadastrar");
                    }
                } catch (err) {
                    alert("Erro de conexão");
                } finally {
                    btn.textContent = "Adicionar"; btn.disabled = false;
                }
            });
        }
    </script>
</body>
</html>