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
    
    // NOVO: Recebe os índices dos itens de checklist marcados como concluídos
    $checklistDone = $_POST['checklist_done'] ?? []; 
    $checklistDone = array_map('intval', (array)$checklistDone); // Garante que é um array de inteiros
    
    if (!$tarefaId) throw new Exception("Tarefa não identificada.");

    // 1. Verifica permissões e dados atuais (ADICIONA 'checklist' e 'feedback_revisao' NO SELECT)
    $stmt = $pdo->prepare("SELECT responsavel_id, projeto_id, status, checklist, feedback_revisao FROM tarefa WHERE id = ?");
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
        // Vamos manter o progresso dinâmico do checklist
        $progresso = -1; // Força o recálculo do progresso pela lógica do checklist
    }

    // REGRA: Apenas Gestor/Líder define CONCLUIDA
    if ($statusSolicitado === 'CONCLUIDA' && !$isGestor) {
        throw new Exception("Apenas gestores podem aprovar a tarefa.");
    }

    // 3. LÓGICA DO CHECKLIST E CÁLCULO DE PROGRESSO (NOVO BLOCO)
    $novoProgresso = $progresso;
    $sqlUp = "UPDATE tarefa SET atualizado_em = NOW()";
    $paramsUp = [];
    $updateFields = [];

    $checklist = json_decode($tarefa['checklist'] ?? '[]', true);
    if (!empty($checklist)) {
        $totalItens = count($checklist);
        $concluidos = 0;
        
        foreach ($checklist as $index => &$item) {
            // Apenas atualiza itens 'toggle' (simples)
            if ($item['tipo_evidencia'] === 'toggle' || $item['tipo_evidencia'] === 'check') {
                // Se o índice estiver na lista de 'checklist_done', marca como concluído
                $item['concluido'] = in_array($index, $checklistDone) ? 1 : 0; 
            }

            // Conta todos os itens concluídos (toggle, arquivo, link)
            if (!empty($item['concluido'])) {
                $concluidos++;
            }
        }
        unset($item); // Boas práticas para referências em foreach

        // 3.1. Atualiza o checklist JSON no banco de dados
        $novoJsonChecklist = json_encode($checklist, JSON_UNESCAPED_UNICODE);
        $updateFields[] = "checklist = ?";
        $paramsUp[] = $novoJsonChecklist;

        // 3.2. Recalcula progresso se não foi enviado manualmente
        if ($progresso === -1) { 
            $novoProgresso = $totalItens > 0 ? round(($concluidos / $totalItens) * 100) : 0;
        }
    }
    
    // 4. Update de Arquivo (Geral da Tarefa) - Mantido para compatibilidade
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
    
    // Salva o progresso recalculado ou o progresso manual (se não for CONCLUIDA)
    if ($novoProgresso >= 0 && $novoStatus !== 'CONCLUIDA') {
        $updateFields[] = "progresso = ?";
        $paramsUp[] = $novoProgresso;
    }

    // Se Gestor está devolvendo, salva o feedback
    if ($isGestor && ($novoStatus == 'PENDENTE' || $novoStatus == 'EM_ANDAMENTO') && !empty($feedback)) {
        $updateFields[] = "feedback_revisao = ?";
        $paramsUp[] = $feedback;
    } else if($isGestor && $novoStatus == 'CONCLUIDA') {
        // Se o gestor está aprovando, garante que o feedback será limpado (já feito acima, mas reforçando)
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
        if ($novoStatus == 'EM_REVISAO') $msgFinal = "🚀 Enviou para revisão. " . $msgFinal;
        if ($novoStatus == 'CONCLUIDA') $msgFinal = "✅ Aprovou e concluiu a tarefa. " . $msgFinal;
        // Status de devolução
        if ($novoStatus == 'EM_ANDAMENTO' && $tarefa['status'] == 'EM_REVISAO') {
             $msgFinal = "⚠️ Devolveu para ajustes. " . $msgFinal;
        }
    }

    // Se houver mensagem ou feedback, insere no histórico
    if (!empty(trim($msgFinal)) || (!empty($feedback) && $feedback !== $tarefa['feedback_revisao'])) {
        $comentario_historico = trim($msgFinal);
        
        // Se Gestor enviou feedback, adiciona ao comentário de histórico
        if(!empty($feedback) && ($novoStatus == 'PENDENTE' || $novoStatus == 'EM_ANDAMENTO')) {
            $comentario_historico .= "\n\n[FEEDBACK DO GESTOR]: " . $feedback;
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