<?php
//atualização
// ARQUIVO: api/empresa_editar.php

// 1. Limpa qualquer saída anterior (Warnings, HTML, espaços)
ob_start();
ini_set('display_errors', 0); // Desliga output de erros no corpo
ini_set('log_errors', 1);     // Liga log de erros no arquivo do servidor
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) {
        throw new Exception("Sessão expirada. Faça login novamente.", 401);
    }

    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    // Validação de permissão
    if (!in_array($sessao['papel'], ['DONO', 'LIDER'])) {
        throw new Exception("Permissão negada.", 403);
    }

    $pdo = conectar_db();
    $empresaId = getEmpresaIdLogado($sessao);

    // Recebe dados do FormData (POST)
    $nome = $_POST['nome'] ?? '';
    $padrao = $_POST['padrao_senha'] ?? '';

    if (empty($nome)) {
        throw new Exception("O nome da empresa é obrigatório.");
    }

    // --- UPLOAD DA LOGO ---
    $logoUrl = null;
    if (isset($_FILES['logo_arquivo']) && $_FILES['logo_arquivo']['error'] === 0) {
        
        $ext = strtolower(pathinfo($_FILES['logo_arquivo']['name'], PATHINFO_EXTENSION));
        $permitidos = ['png', 'jpg', 'jpeg', 'svg'];
        
        if (!in_array($ext, $permitidos)) {
            throw new Exception("Formato de imagem inválido. Use PNG, JPG ou SVG.");
        }

        // Caminho físico
        $pastaUploads = __DIR__ . '/../public/uploads/logos/';
        
        if (!is_dir($pastaUploads)) {
            mkdir($pastaUploads, 0777, true);
        }

        // Gera nome único para evitar cache
        $novoNome = 'logo_' . $empresaId . '_' . time() . '.' . $ext;
        $caminhoCompleto = $pastaUploads . $novoNome;

        if (move_uploaded_file($_FILES['logo_arquivo']['tmp_name'], $caminhoCompleto)) {
            $logoUrl = 'uploads/logos/' . $novoNome; // Caminho relativo para salvar no banco
        } else {
            throw new Exception("Falha ao mover o arquivo de logo para a pasta.");
        }
    }

    // --- UPDATE NO BANCO ---
    $sql = "UPDATE empresa SET nome = ?, padrao_senha = ?";
    $params = [$nome, $padrao];

    if ($logoUrl) {
        $sql .= ", logo_url = ?";
        $params[] = $logoUrl;
    }

    $sql .= " WHERE id = ?";
    $params[] = $empresaId;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Limpa qualquer lixo antes de enviar o JSON
    ob_clean(); 
    echo json_encode([
        'ok' => true, 
        'mensagem' => 'Dados da empresa atualizados com sucesso!',
        'nova_logo' => $logoUrl
    ]);

} catch (Exception $e) {
    ob_clean(); // Limpa lixo em caso de erro
    http_response_code(400); // Bad Request
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
exit;
?>