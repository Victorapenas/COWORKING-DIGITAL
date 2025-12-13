<?php
//atualização
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
    $status = $_POST['status'] ?? '';
    $progresso = isset($_POST['progresso']) ? (int)$_POST['progresso'] : -1;
    $comentario = trim($_POST['comentario'] ?? '');
    
    // NOVO: Recebe o tempo total gasto em minutos
    $tempoGasto = isset($_POST['tempo_gasto']) ? (int)$_POST['tempo_gasto'] : -1;

    if (!$tarefaId) throw new Exception("Tarefa não identificada.");

    // 1. Verifica se a tarefa pertence ao usuário ou se é gestor
    $stmt = $pdo->prepare("SELECT responsavel_id, projeto_id FROM tarefa WHERE id = ?");
    $stmt->execute([$tarefaId]);
    $tarefa = $stmt->fetch();

    if (!$tarefa) throw new Exception("Tarefa não encontrada.");
    
    // Permissão: Só quem é responsável ou Gestor/Dono pode entregar
    $isResp = ($tarefa['responsavel_id'] == $sessao['id']);
    $isGestor = in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR']);

    if (!$isResp && !$isGestor) {
        throw new Exception("Você não é o responsável por esta tarefa.");
    }

    // 2. Upload de Arquivo (Entrega)
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

    // 3. Atualiza Status, Progresso E TEMPO na Tarefa Principal
    $sqlUp = "UPDATE tarefa SET atualizado_em = NOW()";
    $paramsUp = [];

    if ($status) {
        $sqlUp .= ", status = ?";
        $paramsUp[] = $status;
        
        // Se concluiu, define data de conclusão e 100%
        if ($status === 'CONCLUIDA') {
            $sqlUp .= ", concluida_em = NOW(), progresso = 100";
        }
    }
    
    if ($progresso >= 0 && $status !== 'CONCLUIDA') {
        $sqlUp .= ", progresso = ?";
        $paramsUp[] = $progresso;
    }

    // NOVO: Atualiza o tempo total se foi enviado
    if ($tempoGasto >= 0) {
        $sqlUp .= ", tempo_total_minutos = ?";
        $paramsUp[] = $tempoGasto;
    }

    $sqlUp .= " WHERE id = ?";
    $paramsUp[] = $tarefaId;

    $stmtUp = $pdo->prepare($sqlUp);
    $stmtUp->execute($paramsUp);

    // 4. Registra no Histórico (Comentários)
    // Se houve arquivo ou comentário, salva na tabela de comentários
    if ($comentario || $caminhoArquivo) {
        $msgFinal = $comentario;
        if ($caminhoArquivo) {
            // Adiciona link do arquivo no texto do comentário (formato simples)
            $msgFinal .= "\n\n[ARQUIVO_ANEXO]:$caminhoArquivo:$nomeOriginal";
        }
        
        // Se mudou status, registra
        if ($status) {
            $statusLabel = str_replace('_', ' ', $status);
            $msgFinal = "[Mudou status para: $statusLabel] " . $msgFinal;
        }

        if (!empty(trim($msgFinal))) {
            $stmtCom = $pdo->prepare("INSERT INTO comentario_tarefa (tarefa_id, usuario_id, mensagem, criado_em) VALUES (?, ?, ?, NOW())");
            $stmtCom->execute([$tarefaId, $sessao['id'], trim($msgFinal)]);
        }
    }

    echo json_encode(['ok' => true, 'mensagem' => 'Atualização enviada com sucesso!']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
ob_end_flush();
?>