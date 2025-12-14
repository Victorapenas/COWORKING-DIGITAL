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
    $feedback = trim($_POST['feedback_revisao'] ?? ''); // Novo campo para gestor/líder
    
    // Checkboxes do checklist
    $checklistDone = $_POST['checklist_done'] ?? []; 
    $checklistDone = array_map('intval', (array)$checklistDone); 
    
    if (!$tarefaId) throw new Exception("Tarefa não identificada.");

    // 1. Verifica permissões e dados atuais
    $stmt = $pdo->prepare("SELECT responsavel_id, projeto_id, status, checklist, feedback_revisao FROM tarefa WHERE id = ?");
    $stmt->execute([$tarefaId]);
    $tarefa = $stmt->fetch();

    if (!$tarefa) throw new Exception("Tarefa não encontrada.");
    
    $isResp = ($tarefa['responsavel_id'] == $sessao['id']);
    $isGestor = in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR']);

    if (!$isResp && !$isGestor) {
        throw new Exception("Sem permissão para alterar esta tarefa.");
    }
    
    // 2. Lógica de Status
    $novoStatus = $statusSolicitado;

    // Colaborador sempre manda para revisão (não pode concluir direto)
    if (!$isGestor && ($statusSolicitado === 'CONCLUIDA' || $statusSolicitado === 'EM_REVISAO')) {
        $novoStatus = 'EM_REVISAO'; 
        $progresso = -1; // Força recálculo baseado no checklist
    }

    if ($statusSolicitado === 'CONCLUIDA' && !$isGestor) {
        throw new Exception("Apenas gestores podem aprovar a tarefa.");
    }

    // 3. Lógica do Checklist e Cálculo de Progresso
    $novoProgresso = $progresso;
    $sqlUp = "UPDATE tarefa SET atualizado_em = NOW()";
    $paramsUp = [];
    $updateFields = [];

    $checklist = json_decode($tarefa['checklist'] ?? '[]', true);
    if (!empty($checklist)) {
        $totalItens = count($checklist);
        $concluidos = 0;
        
        foreach ($checklist as $index => &$item) {
            // Apenas atualiza itens 'toggle' (simples) via este endpoint
            if ($item['tipo_evidencia'] === 'toggle' || $item['tipo_evidencia'] === 'check') {
                $item['concluido'] = in_array($index, $checklistDone) ? 1 : 0; 
            }
            if (!empty($item['concluido'])) {
                $concluidos++;
            }
        }
        unset($item); 

        $novoJsonChecklist = json_encode($checklist, JSON_UNESCAPED_UNICODE);
        $updateFields[] = "checklist = ?";
        $paramsUp[] = $novoJsonChecklist;

        if ($progresso === -1) { 
            $novoProgresso = $totalItens > 0 ? round(($concluidos / $totalItens) * 100) : 0;
        }
    }
    
    // 4. Update de Arquivo (Geral da Tarefa)
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

    // 5. Monta o restante do UPDATE
    if ($novoStatus) {
        $updateFields[] = "status = ?";
        $paramsUp[] = $novoStatus;
        
        if ($novoStatus === 'CONCLUIDA') {
            $updateFields[] = "concluida_em = NOW()";
            $updateFields[] = "progresso = 100";
            $updateFields[] = "feedback_revisao = NULL"; // Limpa feedback antigo se aprovou
        }
    }
    
    if ($novoProgresso >= 0 && $novoStatus !== 'CONCLUIDA') {
        $updateFields[] = "progresso = ?";
        $paramsUp[] = $novoProgresso;
    }

    // [CORREÇÃO PRINCIPAL] Salva o feedback se for uma devolução ou revisão
    // Isso cobre: Devolução para Colab (PENDENTE/ANDAMENTO) e Devolução para Gestor (REVISAO)
    if ($isGestor && !empty($feedback)) {
        if ($novoStatus == 'PENDENTE' || $novoStatus == 'EM_ANDAMENTO' || $novoStatus == 'EM_REVISAO') {
            $updateFields[] = "feedback_revisao = ?";
            $paramsUp[] = $feedback;
        }
    } else if($isGestor && $novoStatus == 'CONCLUIDA') {
        $updateFields[] = "feedback_revisao = NULL";
    }

    // Junta os campos para o SQL
    if (!empty($updateFields)) {
        $sqlUp .= ", " . implode(", ", $updateFields);
    }
    
    $sqlUp .= " WHERE id = ?";
    $paramsUp[] = $tarefaId;

    $stmtUp = $pdo->prepare($sqlUp);
    $stmtUp->execute($paramsUp);

    // 6. Histórico (Comentários)
    $msgFinal = $comentario;
    
    if ($caminhoArquivo) {
        $msgFinal .= "\n\n[ARQUIVO_ANEXO]:$caminhoArquivo:$nomeOriginal";
    }
    
    // Mensagem de Transição de Status
    if ($novoStatus && $novoStatus !== $tarefa['status']) {
        if ($novoStatus == 'EM_REVISAO' && $tarefa['status'] == 'CONCLUIDA') $msgFinal = "⚠️ Líder solicitou refação. " . $msgFinal;
        elseif ($novoStatus == 'EM_REVISAO') $msgFinal = "🚀 Enviou para revisão. " . $msgFinal;
        elseif ($novoStatus == 'CONCLUIDA') $msgFinal = "✅ Aprovou e concluiu a tarefa. " . $msgFinal;
        elseif ($novoStatus == 'EM_ANDAMENTO' && $tarefa['status'] == 'EM_REVISAO') $msgFinal = "⚠️ Devolveu para ajustes. " . $msgFinal;
    }

    // Se houver mensagem ou feedback, insere no histórico
    if (!empty(trim($msgFinal)) || (!empty($feedback) && $feedback !== $tarefa['feedback_revisao'])) {
        $comentario_historico = trim($msgFinal);
        
        // Se Gestor/Líder enviou feedback (e não é aprovação), adiciona ao histórico
        if(!empty($feedback) && ($novoStatus != 'CONCLUIDA')) {
            $comentario_historico .= "\n\n[MOTIVO/FEEDBACK]: " . $feedback;
        }

        $stmtCom = $pdo->prepare("INSERT INTO comentario_tarefa (tarefa_id, usuario_id, mensagem, criado_em) VALUES (?, ?, ?, NOW())");
        $stmtCom->execute([$tarefaId, $sessao['id'], trim($comentario_historico)]);
    }

    echo json_encode(['ok' => true, 'mensagem' => 'Atualizado com sucesso.', 'novo_status' => $novoStatus]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
ob_end_flush();
?>