<?php
// /public/setup_admin.php
// UTILITÁRIO DE INSTALAÇÃO: Cria o primeiro usuário DONO
// *** DELETAR ESTE ARQUIVO APÓS O USO INICIAL ***

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

$mensagem = '';
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = validar_email($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($nome) || !$email || !validar_senha($senha)) {
        $mensagem = "Erro: Por favor, preencha todos os campos corretamente (senha mín. 8 caracteres, e-mail válido).";
    } else {
        try {
            $pdo = conectar_db();
            $pdo->beginTransaction();

            // 1. Verifica se já existe um DONO
            $stmt = $pdo->query("SELECT COUNT(*) FROM usuario u JOIN papel p ON u.papel_id = p.id WHERE p.nome = 'DONO'");
            if ($stmt->fetchColumn() > 0) {
                $mensagem = "Erro: Um usuário DONO já existe no sistema. Por favor, remova este arquivo para proteger sua instalação.";
                $pdo->rollBack();
            } else {
                // 2. Busca o ID do papel 'DONO'
                $stmt = $pdo->prepare("SELECT id FROM papel WHERE nome = 'DONO'");
                $stmt->execute();
                $papel_dono_id = $stmt->fetchColumn();

                if (!$papel_dono_id) {
                    throw new Exception("Papel 'DONO' não encontrado. Importe o SQL corretamente.");
                }

                // 3. Insere o novo usuário DONO
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuario (papel_id, nome, email, senha_hash) VALUES (?, ?, ?, ?)");
                $stmt->execute([$papel_dono_id, $nome, $email, $senha_hash]);

                $mensagem = "Sucesso! Usuário DONO criado. E-mail: $email. Agora **DELETE** este arquivo e acesse <a href='login.php'>login.php</a>.";
                $sucesso = true;
                $pdo->commit();
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) { // Integridade (UNIQUE email)
                $mensagem = "Erro: Este e-mail já está em uso.";
            } else {
                $mensagem = "Erro do BD: " . $e->getMessage();
            }
        } catch (Exception $e) {
            $mensagem = "Erro: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin - Coworking Digital</title>
    <link rel="stylesheet" href="/css/login.css">
    <style>
        body { background-color: #f0f2f5; display: block; padding: 40px; }
        .setup-card { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .setup-card h1 { color: #1e3c72; margin-bottom: 20px; }
        .setup-card p { margin-bottom: 15px; }
        .setup-card label { display: block; font-weight: 600; margin-bottom: 5px; }
        .setup-card input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 15px; }
        .msg-sucesso { color: green; font-weight: 700; margin-top: 15px; }
        .msg-erro { color: red; font-weight: 700; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="setup-card">
        <h1>🔑 Configuração Inicial do Administrador (DONO)</h1>
        <p>Preencha os dados para criar o primeiro usuário com o papel **DONO**.</p>
        <p style="color: red; font-weight: 700;">⚠️ ATENÇÃO: Deletar este arquivo (`setup_admin.php`) após o uso!</p>

        <?php if ($mensagem): ?>
            <div class="<?= $sucesso ? 'msg-sucesso' : 'msg-erro' ?>">
                <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <?php if (!$sucesso): ?>
            <form method="POST">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" required>

                <label for="email">E-mail (Login):</label>
                <input type="email" id="email" name="email" required>

                <label for="senha">Senha (Mín. 8 caracteres):</label>
                <input type="password" id="senha" name="senha" required minlength="8">

                <button type="submit" class="botao-primario">Criar Usuário DONO</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>