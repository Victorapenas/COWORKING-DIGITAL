<?php
// ARQUIVO: api/tarefa_buscar.php
ob_start(); 
ini_set('display_errors', 0); 
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../includes/funcoes.php'; 
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) {
        throw new Exception("Sessão expirada.");
    }
    
    $tarefaId = (int)($_GET['id'] ?? 0);
    if (!$tarefaId) {
        throw new Exception("ID da tarefa ausente.");
    }
    
    $pdo = conectar_db();
    
    // 1. Busca dados da tarefa E dados do Projeto vinculado (links e arquivos publicos)
    $sql = "SELECT t.*, 
                   u.nome as responsavel_nome, 
                   c.nome as criador_nome,
                   p.nome as projeto_nome,
                   p.links_externos as projeto_links, 
                   p.arquivos_privados as projeto_arquivos_privados
            FROM tarefa t
            LEFT JOIN usuario u ON t.responsavel_id = u.id
            LEFT JOIN usuario c ON t.criador_id = c.id
            LEFT JOIN projeto p ON t.projeto_id = p.id
            WHERE t.id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tarefaId]);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarefa) {
        throw new Exception("Tarefa não encontrada.");
    }
    
    // Formata data
    if ($tarefa['prazo']) {
        $tarefa['prazo'] = date('Y-m-d', strtotime($tarefa['prazo']));
    }

    // 2. Processa Arquivos do Projeto (Recurso de Apoio)
    // O colaborador vê 'links_externos' (que contem arquivos públicos)
    // Se for SÓCIO (LIDER/DONO) vendo a tarefa, poderia ver os privados, mas aqui focamos no colaborador
    $recursosProjeto = [];
    if (!empty($tarefa['projeto_links'])) {
        $recursosProjeto = json_decode($tarefa['projeto_links'], true) ?? [];
    }
    // Removemos os campos brutos para limpar o JSON
    unset($tarefa['projeto_links']);
    unset($tarefa['projeto_arquivos_privados']); // Colaborador não vê privados do projeto por aqui
    
    // Adiciona lista processada
    $tarefa['recursos_projeto'] = $recursosProjeto;

    // 3. Busca Histórico de Comentários (Chat)
    $sqlCom = "SELECT c.mensagem, c.criado_em, u.nome, p.nome as papel
               FROM comentario_tarefa c
               JOIN usuario u ON c.usuario_id = u.id
               JOIN papel p ON u.papel_id = p.id
               WHERE c.tarefa_id = ?
               ORDER BY c.criado_em DESC"; 
    $stmtCom = $pdo->prepare($sqlCom);
    $stmtCom->execute([$tarefaId]);
    $historico = $stmtCom->fetchAll(PDO::FETCH_ASSOC);

    foreach($historico as &$h) {
        $h['data_formatada'] = date('d/m H:i', strtotime($h['criado_em']));
    }

    $tarefa['historico_mensagens'] = $historico;
    
    echo json_encode(['ok' => true, 'tarefa' => $tarefa]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}

ob_end_flush();
?>