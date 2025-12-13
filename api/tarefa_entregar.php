<?php
// ARQUIVO: api/tarefa_entregar.php
ob_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php';
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    $pdo = conectar_db();

    // Recebe dados
    $tarefaId = (int)($_POST['tarefa_id'] ?? 0);
    $statusSolicitado = $_POST['status'] ?? '';
    $progresso = isset($_POST['progresso']) ? (int)$_POST['progresso'] : -1;
    $comentario = trim($_POST['comentario'] ?? '');
    $feedback = trim($_POST['feedback_revisao'] ?? ''); // Novo campo para gestor
    
    if (!$tarefaId) throw new Exception("Tarefa não identificada.");

    // 1. Verifica permissões e dados atuais
    $stmt = $pdo->prepare("SELECT responsavel_id, projeto_id, status FROM tarefa WHERE id = ?");
    $stmt->execute([$tarefaId]);
    $tarefa = $stmt->fetch();

    if (!$tarefa) throw new Exception("Tarefa não encontrada.");
    
    $isResp = ($tarefa['responsavel_id'] == $sessao['id']);
    $isGestor = in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR']);

    if (!$isResp && !$isGestor) {
        throw new Exception("Sem permissão para alterar esta tarefa.");
    }

    // 2. LÓGICA DE STATUS RESTRITA
    $novoStatus = $statusSolicitado;

    // REGRA DE OURO: Colaborador não conclui direto. Vai para Revisão.
    if (!$isGestor && ($statusSolicitado === 'CONCLUIDA' || $statusSolicitado === 'EM_REVISAO')) {
        $novoStatus = 'EM_REVISAO'; 
        // Opcional: Define progresso 99% para indicar que acabou a parte dele
        $progresso = 99; 
    }

    // REGRA: Apenas Gestor/Líder define CONCLUIDA
    if ($statusSolicitado === 'CONCLUIDA' && !$isGestor) {
        throw new Exception("Apenas gestores podem aprovar a tarefa.");
    }

    // REGRA 3: Se Gestor devolve (Pendente/Andamento), pode salvar feedback
    // (Lógica aplicada no update abaixo)

    // 3. Upload de Arquivo (Geral da Tarefa)
    $caminhoArquivo = null;
    $nomeOriginal = null;
    if (isset($_FILES['arquivo_entrega']) && $_FILES['arquivo_entrega']['error'] === 0) {
        $uploadDir = __DIR__ . '/../public/uploads/entregas/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = pathinfo($_FILES['arquivo_entrega']['name'], PATHINFO_EXTENSION);
        $nomeOriginal = $_FILES['arquivo_entrega']['name'];
        $novoNome = 'entrega_' . $tarefaId . '_' . uniqid() . '.' . $ext;
        
        if (move_uploaded_file($_FILES['arquivo_entrega']['tmp_name'], $uploadDir . $novoNome)) {
            $caminhoArquivo = 'uploads/entregas/' . $novoNome;
        }
    }

    // 4. Update no Banco
    $sqlUp = "UPDATE tarefa SET atualizado_em = NOW()";
    $paramsUp = [];

    if ($novoStatus) {
        $sqlUp .= ", status = ?";
        $paramsUp[] = $novoStatus;
        
        if ($novoStatus === 'CONCLUIDA') {
            $sqlUp .= ", concluida_em = NOW(), progresso = 100, feedback_revisao = NULL"; // Limpa feedback antigo se aprovou
        }
    }
    
    if ($progresso >= 0 && $novoStatus !== 'CONCLUIDA') {
        $sqlUp .= ", progresso = ?";
        $paramsUp[] = $progresso;
    }

    // Se Gestor está devolvendo, salva o feedback
    if ($isGestor && ($novoStatus == 'PENDENTE' || $novoStatus == 'EM_ANDAMENTO') && !empty($feedback)) {
        $sqlUp .= ", feedback_revisao = ?";
        $paramsUp[] = $feedback;
    }

    $sqlUp .= " WHERE id = ?";
    $paramsUp[] = $tarefaId;

    $stmtUp = $pdo->prepare($sqlUp);
    $stmtUp->execute($paramsUp);

    // 5. Histórico (Comentários)
    $msgFinal = $comentario;
    
    if ($caminhoArquivo) {
        $msgFinal .= "\n\n[ARQUIVO_ANEXO]:$caminhoArquivo:$nomeOriginal";
    }
    
    if ($novoStatus && $novoStatus !== $tarefa['status']) {
        $label = str_replace('_', ' ', $novoStatus);
        if ($novoStatus == 'EM_REVISAO') $msgFinal = "🚀 Enviou para revisão. " . $msgFinal;
        if ($novoStatus == 'CONCLUIDA') $msgFinal = "✅ Aprovou e concluiu a tarefa. " . $msgFinal;
        if ($novoStatus == 'EM_ANDAMENTO' && $tarefa['status'] == 'EM_REVISAO') $msgFinal = "⚠️ Devolveu para ajustes. " . $msgFinal;
    }

    if (!empty(trim($msgFinal)) || !empty($feedback)) {
        // Se tiver feedback de revisão, adiciona no comentário também para histórico
        if(!empty($feedback)) $msgFinal .= "\n\n[FEEDBACK DO GESTOR]: " . $feedback;

        $stmtCom = $pdo->prepare("INSERT INTO comentario_tarefa (tarefa_id, usuario_id, mensagem, criado_em) VALUES (?, ?, ?, NOW())");
        $stmtCom->execute([$tarefaId, $sessao['id'], trim($msgFinal)]);
    }

    echo json_encode(['ok' => true, 'mensagem' => 'Atualizado com sucesso.', 'novo_status' => $novoStatus]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
ob_end_flush();
?>