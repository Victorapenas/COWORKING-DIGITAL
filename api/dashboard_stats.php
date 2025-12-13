<?php
// ARQUIVO: api/dashboard_stats.php
// Objetivo: Fornecer dados segregados para o Dashboard (Visão Gestor vs Colaborador)

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/funcoes_api.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada");

    $pdo = conectar_db();
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    $empresaId = getEmpresaIdLogado($sessao);
    $usuarioId = $sessao['id'];
    $papel = $sessao['papel'];

    $response = [
        'ok' => true,
        'papel' => $papel,
        'usuario_nome' => $sessao['nome'],
        'kpis' => [],
        'listas' => [],        // Projetos/Tarefas gerais (Gestor)
        'pendencias' => [],    // APROVAÇÕES (Gestor - Tarefas EM_REVISAO)
        'minhas_tarefas' => [], // Lista de execução (Colaborador)
        'meus_projetos' => [],  // Atalhos (Colaborador)
        'online_users' => [],
        'grafico' => []
    ];

    // =================================================================================
    // VISÃO DE GESTOR / DONO / LÍDER
    // =================================================================================
    if ($papel === 'DONO' || $papel === 'LIDER' || $papel === 'GESTOR') {
        
        // 1. KPI: Itens para Revisar (Prioridade Máxima)
        // Busca tarefas que os colaboradores marcaram como prontas (EM_REVISAO)
        // Se for GESTOR, filtra pelos projetos que ele gerencia. Se for DONO, vê tudo da empresa.
        $filtroGestao = ($papel === 'GESTOR') ? "AND p.gestor_id = $usuarioId" : "AND t.empresa_id = $empresaId";
        
        $sqlRevisao = "SELECT COUNT(*) FROM tarefa t 
                       LEFT JOIN projeto p ON t.projeto_id = p.id 
                       WHERE t.status = 'EM_REVISAO' $filtroGestao";
        $qtdRevisao = $pdo->query($sqlRevisao)->fetchColumn();

        // 2. Outros KPIs
        $qtdAtivas = $pdo->query("SELECT COUNT(*) FROM tarefa t LEFT JOIN projeto p ON t.projeto_id = p.id WHERE t.status IN ('PENDENTE','EM_ANDAMENTO') $filtroGestao")->fetchColumn();
        $qtdProjetos = $pdo->query("SELECT COUNT(*) FROM projeto p WHERE p.empresa_id = $empresaId AND p.status = 'EM_ANDAMENTO'")->fetchColumn();

        $response['kpis'] = [
            ['titulo' => 'Para Aprovar', 'valor' => $qtdRevisao, 'icone' => 'alerta', 'cor' => 'red'], // Destaque Vermelho
            ['titulo' => 'Em Andamento', 'valor' => $qtdAtivas, 'icone' => 'task', 'cor' => 'blue'],
            ['titulo' => 'Projetos Ativos', 'valor' => $qtdProjetos, 'icone' => 'folder', 'cor' => 'purple']
        ];

        // 3. LISTA DE APROVAÇÃO (O "Inbox" do Gestor)
        $sqlPendencias = "SELECT t.id, t.titulo, u.nome as responsavel, t.status, t.prazo, p.nome as projeto_nome, t.checklist
                          FROM tarefa t 
                          JOIN usuario u ON t.responsavel_id = u.id 
                          LEFT JOIN projeto p ON t.projeto_id = p.id
                          WHERE t.status = 'EM_REVISAO' $filtroGestao
                          ORDER BY t.atualizado_em ASC"; // As mais antigas primeiro
        
        $response['pendencias'] = $pdo->query($sqlPendencias)->fetchAll(PDO::FETCH_ASSOC);

        // 4. Lista Geral de Acompanhamento (Tarefas da Equipe)
        $response['listas'] = $pdo->query("
            SELECT t.id, t.titulo, t.prioridade, t.prazo, u.nome as responsavel, t.status, t.progresso
            FROM tarefa t 
            LEFT JOIN projeto p ON t.projeto_id = p.id 
            JOIN usuario u ON t.responsavel_id = u.id
            WHERE t.status IN ('PENDENTE', 'EM_ANDAMENTO') $filtroGestao
            ORDER BY t.prazo ASC LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 5. Gráfico de Produtividade (Últimos 7 dias)
        $grafico = ['labels' => [], 'data' => []];
        for ($i = 6; $i >= 0; $i--) {
            $data = date('Y-m-d', strtotime("-$i days"));
            $dia = date('d/m', strtotime("-$i days"));
            $qtd = $pdo->query("SELECT COUNT(*) FROM tarefa t LEFT JOIN projeto p ON t.projeto_id = p.id WHERE t.status = 'CONCLUIDA' AND DATE(t.concluida_em) = '$data' $filtroGestao")->fetchColumn();
            $grafico['labels'][] = $dia;
            $grafico['data'][] = $qtd;
        }
        $response['grafico'] = $grafico;

    } 
    // =================================================================================
    // VISÃO DE COLABORADOR (OPERACIONAL)
    // =================================================================================
    else {
        
        // 1. Busca Tarefas do Colaborador
        // Ordena por Prioridade e depois Prazo
        // Inclui campo 'feedback_revisao' para mostrar se foi devolvida
        $sqlMinhas = "SELECT t.id, t.titulo, t.descricao, t.prioridade, t.prazo, t.status, 
                             t.checklist, t.tempo_total_minutos, t.progresso, t.feedback_revisao,
                             p.nome as projeto_nome
                      FROM tarefa t 
                      LEFT JOIN projeto p ON t.projeto_id = p.id
                      WHERE t.responsavel_id = ? 
                      AND t.status IN ('PENDENTE', 'EM_ANDAMENTO', 'EM_REVISAO')
                      ORDER BY 
                        CASE WHEN t.feedback_revisao IS NOT NULL AND t.status != 'EM_REVISAO' THEN 0 ELSE 1 END, 
                        FIELD(t.prioridade, 'URGENTE', 'IMPORTANTE', 'NORMAL'), 
                        t.prazo ASC";
                      
        $stmtM = $pdo->prepare($sqlMinhas);
        $stmtM->execute([$usuarioId]);
        $response['minhas_tarefas'] = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        // 2. KPIs Pessoais
        $pendentes = 0;
        $revisao = 0;
        $devolvidas = 0;

        foreach ($response['minhas_tarefas'] as $t) {
            if ($t['status'] == 'EM_REVISAO') $revisao++;
            elseif (!empty($t['feedback_revisao'])) $devolvidas++;
            else $pendentes++;
        }
        
        $concluidasMes = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE responsavel_id = $usuarioId AND status = 'CONCLUIDA' AND MONTH(concluida_em) = MONTH(CURRENT_DATE())")->fetchColumn();

        $response['kpis'] = [
            ['titulo' => 'A Fazer', 'valor' => $pendentes, 'icone' => 'task', 'cor' => 'blue'],
            ['titulo' => 'Em Revisão', 'valor' => $revisao, 'icone' => 'clock', 'cor' => 'orange'],
            ['titulo' => 'Devolvidas', 'valor' => $devolvidas, 'icone' => 'alerta', 'cor' => 'red'], // Atenção!
            ['titulo' => 'Entregues (Mês)', 'valor' => $concluidasMes, 'icone' => 'check', 'cor' => 'green']
        ];

        // 3. Gráfico de Entregas Pessoais
        $grafico = ['labels' => [], 'data' => []];
        for ($i = 6; $i >= 0; $i--) {
            $data = date('Y-m-d', strtotime("-$i days"));
            $dia = date('d/m', strtotime("-$i days"));
            $qtd = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE responsavel_id = $usuarioId AND status = 'CONCLUIDA' AND DATE(concluida_em) = '$data'")->fetchColumn();
            $grafico['labels'][] = $dia;
            $grafico['data'][] = $qtd;
        }
        $response['grafico'] = $grafico;
    }

    ob_clean(); 
    echo json_encode($response);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
exit;
?>