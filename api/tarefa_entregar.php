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

    // Recebe dados do formulário
    $tarefaId = (int)($_POST['tarefa_id'] ?? 0);
    $statusSolicitado = $_POST['status'] ?? '';
    $progresso = isset($_POST['progresso']) ? (int)$_POST['progresso'] : -1;
    $comentario = trim($_POST['comentario'] ?? '');
    $feedback = trim($_POST['feedback_revisao'] ?? ''); // Campo usado por gestores
    
    // Checkboxes do checklist (apenas índices dos itens marcados no front)
    $checklistDone = $_POST['checklist_done'] ?? []; 
    if (!is_array($checklistDone)) $checklistDone = [];
    $checklistDone = array_map('intval', $checklistDone); 
    
    if (!$tarefaId) throw new Exception("Tarefa não identificada.");

    // 1. Busca dados atuais da tarefa para validação e merge
    $stmt = $pdo->prepare("SELECT responsavel_id, projeto_id, status, checklist, feedback_revisao FROM tarefa WHERE id = ?");
    $stmt->execute([$tarefaId]);
    $tarefa = $stmt->fetch();

    if (!$tarefa) throw new Exception("Tarefa não encontrada.");
    
    $isResp = ($tarefa['responsavel_id'] == $sessao['id']);
    $isGestor = in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR']);

    // Permissão: Só o responsável ou gestores podem alterar
    if (!$isResp && !$isGestor) {
        throw new Exception("Sem permissão para alterar esta tarefa.");
    }
    
    // 2. Lógica de Status
    $novoStatus = $statusSolicitado;

    // Se for Colaborador tentando concluir direto, força para EM_REVISAO
    if (!$isGestor && ($statusSolicitado === 'CONCLUIDA' || $statusSolicitado === 'EM_REVISAO')) {
        $novoStatus = 'EM_REVISAO'; 
        $progresso = -1; // Força recálculo automático do progresso
    }

    // Apenas gestores podem definir status CONCLUIDA
    if ($statusSolicitado === 'CONCLUIDA' && !$isGestor) {
        throw new Exception("Apenas gestores podem aprovar a tarefa.");
    }

    // 3. Processa Upload de Arquivo GERAL (Entrega Final da Tarefa)
    $caminhoArquivo = null;
    $nomeOriginal = null;
    
    if (isset($_FILES['arquivo_entrega']) && $_FILES['arquivo_entrega']['error'] === 0) {
        // Caminho absoluto para evitar erros de referência
        $uploadBase = __DIR__ . '/../public/uploads/entregas/';
        
        // Garante que a pasta existe
        if (!is_dir($uploadBase)) {
            if (!mkdir($uploadBase, 0777, true)) {
                throw new Exception("Erro ao criar pasta de uploads no servidor.");
            }
        }
        
        $ext = pathinfo($_FILES['arquivo_entrega']['name'], PATHINFO_EXTENSION);
        $nomeOriginal = $_FILES['arquivo_entrega']['name'];
        // Nome único para evitar sobrescrita
        $novoNome = 'entrega_' . $tarefaId . '_' . uniqid() . '.' . $ext;
        
        if (move_uploaded_file($_FILES['arquivo_entrega']['tmp_name'], $uploadBase . $novoNome)) {
            // Salva o caminho relativo a partir de 'public/' para o banco
            $caminhoArquivo = 'uploads/entregas/' . $novoNome;
        } else {
            throw new Exception("Falha ao mover o arquivo enviado.");
        }
    }

    // 4. Lógica Crítica do Checklist (Merge)
    // Mescla o que veio do formulário com o que já existe no banco para não perder evidências
    $checklistAtual = json_decode($tarefa['checklist'] ?? '[]', true);
    $concluidosCount = 0;
    $totalItens = count($checklistAtual);

    if (!empty($checklistAtual)) {
        foreach ($checklistAtual as $index => &$item) {
            // Verifica o tipo do item
            $tipo = $item['tipo_evidencia'] ?? 'check';

            // Se for item simples (apenas marcar), atualiza baseado no checkbox enviado
            if ($tipo === 'check' || $tipo === 'toggle') {
                $item['concluido'] = in_array($index, $checklistDone) ? 1 : 0; 
            }
            
            // Se for item de Arquivo ou Link, MANTÉM o estado atual do banco (não sobrescreve com vazio)
            
            // Contagem para progresso
            if (!empty($item['concluido'])) {
                $concluidosCount++;
            }
        }
        unset($item); // Quebra referência

        $novoJsonChecklist = json_encode($checklistAtual, JSON_UNESCAPED_UNICODE);
        
        // Recalcula progresso se não foi forçado manualmente
        if ($progresso === -1) { 
            $novoProgresso = ($totalItens > 0) ? round(($concluidosCount / $totalItens) * 100) : 0;
        } else {
            $novoProgresso = $progresso;
        }
    } else {
        // Se não tem checklist, usa o progresso manual enviado
        $novoProgresso = ($progresso === -1) ? 0 : $progresso;
        $novoJsonChecklist = $tarefa['checklist']; // Mantém o atual
    }

    // Se concluiu, força 100%
    if ($novoStatus === 'CONCLUIDA') $novoProgresso = 100;

    // 5. Atualização no Banco de Dados
    $sqlUp = "UPDATE tarefa SET 
                status = ?, 
                progresso = ?, 
                checklist = ?, 
                atualizado_em = NOW()";
    
    $paramsUp = [$novoStatus, $novoProgresso, $novoJsonChecklist];

    // Se houve feedback de revisão (gestor devolvendo), salva
    if ($isGestor && !empty($feedback)) {
        // Se devolveu (não concluiu), salva o feedback
        if ($novoStatus !== 'CONCLUIDA') {
            $sqlUp .= ", feedback_revisao = ?";
            $paramsUp[] = $feedback;
        } else {
            // Se aprovou, limpa o feedback antigo
            $sqlUp .= ", feedback_revisao = NULL";
        }
    } else if ($novoStatus === 'CONCLUIDA') {
        $sqlUp .= ", feedback_revisao = NULL, concluida_em = NOW()";
    }

    $sqlUp .= " WHERE id = ?";
    $paramsUp[] = $tarefaId;

    $stmtUp = $pdo->prepare($sqlUp);
    $stmtUp->execute($paramsUp);

    // 6. Registro no Histórico (Comentários) - SEM EMOJIS
    $msgFinal = $comentario;
    
    // Adiciona anexo ao texto do histórico se houver upload geral
    if ($caminhoArquivo) {
        $msgFinal .= "\n\n[ARQUIVO_ANEXO]:$caminhoArquivo:$nomeOriginal";
    }
    
    // Log automático de mudança de status (Texto limpo)
    if ($novoStatus && $novoStatus !== $tarefa['status']) {
        if ($novoStatus == 'EM_REVISAO' && $tarefa['status'] == 'CONCLUIDA') $msgFinal = "Líder solicitou refação. " . $msgFinal;
        elseif ($novoStatus == 'EM_REVISAO') $msgFinal = "Enviou para revisão. " . $msgFinal;
        elseif ($novoStatus == 'CONCLUIDA') $msgFinal = "Aprovou e concluiu a tarefa. " . $msgFinal;
        elseif ($novoStatus == 'EM_ANDAMENTO' && $tarefa['status'] == 'EM_REVISAO') $msgFinal = "Devolveu para ajustes. " . $msgFinal;
        else $msgFinal = "[Mudou status para: $novoStatus] " . $msgFinal;
    }

    // Se houver feedback do gestor (e não for aprovação), adiciona ao histórico
    if (!empty($feedback) && ($novoStatus != 'CONCLUIDA') && $feedback !== $tarefa['feedback_revisao']) {
        $msgFinal .= "\n\n[MOTIVO/FEEDBACK]: " . $feedback;
    }

    // Insere comentário apenas se tiver conteúdo
    if (!empty(trim($msgFinal))) {
        $stmtCom = $pdo->prepare("INSERT INTO comentario_tarefa (tarefa_id, usuario_id, mensagem, criado_em) VALUES (?, ?, ?, NOW())");
        $stmtCom->execute([$tarefaId, $sessao['id'], trim($msgFinal)]);
    }

    echo json_encode(['ok' => true, 'mensagem' => 'Tarefa atualizada com sucesso!', 'novo_status' => $novoStatus]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
ob_end_flush();
?>