<?php
//atualização
// ARQUIVO: api/admin_excluir_membro.php
ob_start();
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

$resposta = [];

try {
    if (!esta_logado()) throw new Exception("Não autorizado.", 401);
    
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    if (!in_array($sessao['papel'], ['DONO', 'GESTOR', 'LIDER'])) {
        throw new Exception("Permissão negada.", 403);
    }

    $dados = get_json_input();
    $id_usuario_alvo = (int)($dados['id'] ?? 0);
    $tipo = $dados['tipo'] ?? 'soft'; // 'soft', 'hard', 'restore'

    if (!$id_usuario_alvo) throw new Exception("ID inválido.");
    if ($id_usuario_alvo === $sessao['id']) throw new Exception("Você não pode alterar sua própria conta aqui.");

    $pdo = conectar_db();

    // Verifica usuário alvo
    $stmt = $pdo->prepare("SELECT papel_id, empresa_id FROM usuario WHERE id = ?");
    $stmt->execute([$id_usuario_alvo]);
    $alvo = $stmt->fetch();

    if (!$alvo) throw new Exception("Usuário não encontrado.");
    
    // Verifica empresa
    $stmtEmp = $pdo->prepare("SELECT empresa_id FROM usuario WHERE id = ?");
    $stmtEmp->execute([$sessao['id']]);
    $empresaAdmin = $stmtEmp->fetchColumn();

    if ($alvo['empresa_id'] != $empresaAdmin) throw new Exception("Usuário de outra organização.");

    // Proteção Hierárquica
    if ($alvo['papel_id'] == 1 && $sessao['papel'] !== 'DONO' && $sessao['papel'] !== 'LIDER') {
        throw new Exception("Apenas Donos podem gerenciar outros Donos.");
    }

    if ($tipo === 'hard') {
        // EXCLUSÃO DEFINITIVA
        try {
            $stmt = $pdo->prepare("DELETE FROM usuario WHERE id = ?");
            $stmt->execute([$id_usuario_alvo]);
            $msg = "Usuário excluído permanentemente do banco de dados.";
        } catch (PDOException $e) {
            // Se falhar por causa de tarefas vinculadas (Foreign Key constraint)
            throw new Exception("Não é possível excluir definitivamente pois este usuário possui histórico (tarefas/comentários). Use a opção 'Arquivar'.");
        }
    } 
    elseif ($tipo === 'restore') {
        // RESTAURAR (Voltar a Ativo)
        $stmt = $pdo->prepare("UPDATE usuario SET ativo = 1 WHERE id = ?");
        $stmt->execute([$id_usuario_alvo]);
        $msg = "Membro restaurado com sucesso! Ele pode acessar o sistema novamente.";
    } 
    else {
        // SOFT DELETE (Arquivar)
        // Remove da equipe atual também para não ficar "fantasma" na equipe
        $stmt = $pdo->prepare("UPDATE usuario SET ativo = 0, equipe_id = NULL WHERE id = ?");
        $stmt->execute([$id_usuario_alvo]);
        $msg = "Membro arquivado. Ele não terá mais acesso, mas o histórico foi mantido.";
    }

    $resposta = ['ok' => true, 'mensagem' => $msg];

} catch (Exception $e) {
    http_response_code(400);
    $resposta = ['ok' => false, 'erro' => $e->getMessage()];
}

ob_end_clean();
echo json_encode($resposta);
?>