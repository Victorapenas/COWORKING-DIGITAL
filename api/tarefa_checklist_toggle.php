<?php
//atualização
// ARQUIVO: api/tarefa_checklist_toggle.php
ob_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    
    $pdo = conectar_db();
    $dados = get_json_input();

    $tarefaId = (int)($dados['tarefa_id'] ?? 0);
    $itemIndex = (int)($dados['index'] ?? -1);
    $feito = !empty($dados['feito']); // true ou false

    if (!$tarefaId || $itemIndex < 0) throw new Exception("Dados inválidos.");

    // 1. Busca o checklist atual
    $stmt = $pdo->prepare("SELECT checklist, responsavel_id FROM tarefa WHERE id = ?");
    $stmt->execute([$tarefaId]);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarefa) throw new Exception("Tarefa não encontrada.");

    // Permissão: Apenas o responsável ou gestores podem marcar
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    $isResp = ($tarefa['responsavel_id'] == $sessao['id']);
    $isGestor = in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR']);

    if (!$isResp && !$isGestor) {
        throw new Exception("Sem permissão para alterar esta tarefa.");
    }

    // 2. Manipula o JSON
    $checklist = json_decode($tarefa['checklist'] ?? '[]', true);
    
    if (!isset($checklist[$itemIndex])) {
        throw new Exception("Item do checklist não existe.");
    }

    // Atualiza o status do item específico
    $checklist[$itemIndex]['concluido'] = $feito ? 1 : 0;

    // 3. Recalcula o Progresso Geral
    $totalItens = count($checklist);
    $itensFeitos = 0;
    foreach ($checklist as $item) {
        if (!empty($item['concluido'])) $itensFeitos++;
    }

    // Regra de 3 simples para porcentagem
    $novoProgresso = ($totalItens > 0) ? round(($itensFeitos / $totalItens) * 100) : 0;

    // Se chegou a 100%, sugere status 'EM_REVISAO' ou mantém o atual se já for CONCLUIDA
    // (Opcional: você pode forçar mudança de status aqui se quiser)

    // 4. Salva no Banco
    $novoJson = json_encode($checklist, JSON_UNESCAPED_UNICODE);
    
    $sql = "UPDATE tarefa SET checklist = ?, progresso = ?, atualizado_em = NOW() WHERE id = ?";
    $stmtUp = $pdo->prepare($sql);
    $stmtUp->execute([$novoJson, $novoProgresso, $tarefaId]);

    echo json_encode([
        'ok' => true, 
        'progresso' => $novoProgresso,
        'mensagem' => 'Item atualizado.'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
ob_end_flush();
?>