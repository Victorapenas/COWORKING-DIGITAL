<?php
// ARQUIVO: api/senha/solicitar.php
require_once __DIR__ . '/../funcoes_api.php';
require_once __DIR__ . '/../servico_email.php'; // Importante incluir isso!

$dados = get_json_input();
$email = validar_email($dados['email'] ?? '');

if (!$email) {
    responder_erro("E-mail inválido.");
}

try {
    $pdo = conectar_db();

    // 1. Busca usuário
    $stmt = $pdo->prepare("SELECT id, nome FROM usuario WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        // Por segurança, fingimos que deu certo ou damos erro genérico
        aplicar_rate_limit();
        responder_erro("E-mail não encontrado.");
    }
    
    // 2. Invalida tokens antigos
    $pdo->prepare("UPDATE token_recuperacao_senha SET usado_em = NOW() WHERE usuario_id = ? AND usado_em IS NULL")->execute([$usuario['id']]);

    // 3. Gera novo código
    $codigo = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $expira_em = date('Y-m-d H:i:s', time() + (15 * 60)); // 15 min

    $stmt = $pdo->prepare("INSERT INTO token_recuperacao_senha (usuario_id, codigo, expira_em) VALUES (?, ?, ?)");
    $stmt->execute([$usuario['id'], $codigo, $expira_em]);

    // 4. ENVIA O E-MAIL (Grava no log)
    enviar_email_codigo($email, $usuario['nome'], $codigo);

    echo json_encode([
        'ok' => true, 
        'mensagem' => 'Código reenviado com sucesso.'
    ]);

} catch (Exception $e) {
    responder_erro("Erro: " . $e->getMessage(), 500);
}
?>