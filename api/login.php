<?php
//atualização
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

    $sql = "SELECT u.id, u.nome, u.email, u.senha_hash, u.ativo, u.precisa_redefinir_senha, 
                   p.nome as papel 
            FROM usuario u
            JOIN papel p ON u.papel_id = p.id
            WHERE u.email = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        
        if ($usuario['ativo'] == 0) {
            responder_erro("Conta desativada. Contate o suporte.");
        }

        if ($usuario['precisa_redefinir_senha'] == 1) {
            // Lógica de primeiro acesso... (mantida igual)
            $codigo = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $expira = date('Y-m-d H:i:s', time() + (15 * 60)); 
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

        // === CORREÇÃO: ATUALIZAR ATIVIDADE ===
        // Isso faz o campo "Visto: Agora" funcionar no painel
        $pdo->prepare("UPDATE usuario SET ultima_atividade = NOW() WHERE id = ?")->execute([$usuario['id']]);

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