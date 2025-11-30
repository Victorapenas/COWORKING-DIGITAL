<?php
// ARQUIVO: api/login.php
require_once 'funcoes_api.php';
require_once __DIR__ . '/../config/config.php';
require_once 'servico_email.php'; 

$dados = get_json_input();
$email = trim($dados['email'] ?? '');
$senha = trim($dados['senha'] ?? '');

if (!$email || empty($senha)) {
    responder_erro("Preencha e-mail e senha.");
}

try {
    $pdo = conectar_db();

    // CORREÇÃO AQUI:
    // Fazemos um JOIN para buscar o NOME do papel (p.nome) e apelidamos de 'papel'
    // Isso garante que a sessão receba 'DONO', 'GESTOR' ou 'FUNCIONARIO' em vez do ID numérico.
    $sql = "SELECT u.id, u.nome, u.email, u.senha_hash, u.ativo, u.precisa_redefinir_senha, 
                   p.nome as papel 
            FROM usuario u
            JOIN papel p ON u.papel_id = p.id
            WHERE u.email = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // 2. Verifica Senha
    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        
        if ($usuario['ativo'] == 0) {
            responder_erro("Conta desativada. Contate o suporte.");
        }

        // === FLUXO: PRIMEIRO ACESSO ===
        if ($usuario['precisa_redefinir_senha'] == 1) {
            
            $codigo = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $expira = date('Y-m-d H:i:s', time() + (15 * 60)); 

            // Invalida anteriores e cria novo token
            $pdo->prepare("UPDATE token_recuperacao_senha SET usado_em = NOW() WHERE usuario_id = ?")->execute([$usuario['id']]);
            $pdo->prepare("INSERT INTO token_recuperacao_senha (usuario_id, codigo, expira_em) VALUES (?, ?, ?)")->execute([$usuario['id'], $codigo, $expira]);

            enviar_email_codigo($usuario['email'], $usuario['nome'], $codigo);

            echo json_encode([
                'ok' => false,
                'primeiro_acesso' => true,
                'email' => $usuario['email'],
                'mensagem' => "Primeiro acesso detectado. Valide seu e-mail.",
                'codigo_debug' => $codigo 
            ]);
            exit;
        }

        // === FLUXO: LOGIN NORMAL ===
        // Agora a função iniciar_sessao receberá o 'papel' correto (Ex: DONO)
        iniciar_sessao($usuario);
        echo json_encode(['ok' => true]);

    } else {
        aplicar_rate_limit();
        responder_erro("E-mail ou senha incorretos.");
    }

} catch (Exception $e) {
    responder_erro("Erro interno: " . $e->getMessage(), 500);
}
?>