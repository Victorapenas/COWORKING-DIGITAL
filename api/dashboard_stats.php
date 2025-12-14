<?php
// ARQUIVO: api/dashboard_stats.php
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
        'listas' => [],              
        'pendencias' => [],          
        'concluidas_recentes' => [], 
        'minhas_tarefas' => [],      
        'agenda_dia' => [],          
        'grafico' => []
    ];

    // =================================================================================
    // VISÃO DE GESTOR / DONO / LÍDER
    // =================================================================================
    if ($papel === 'DONO' || $papel === 'LIDER' || $papel === 'GESTOR') {
        
        $filtroGestao = ($papel === 'GESTOR') ? "AND p.gestor_id = $usuarioId" : "AND t.empresa_id = $empresaId";
        
        // 1. KPI: Pendências de Revisão
        $sqlRevisao = "SELECT COUNT(*) FROM tarefa t LEFT JOIN projeto p ON t.projeto_id = p.id WHERE t.status = 'EM_REVISAO' $filtroGestao";
        $qtdRevisao = $pdo->query($sqlRevisao)->fetchColumn();

        // 2. KPI: Tarefas em Andamento (Geral)
        $qtdAtivas = $pdo->query("SELECT COUNT(*) FROM tarefa t LEFT JOIN projeto p ON t.projeto_id = p.id WHERE t.status IN ('PENDENTE','EM_ANDAMENTO') $filtroGestao")->fetchColumn();
        
        // 3. KPI: Projetos Ativos
        $sqlProjetos = "SELECT COUNT(*) FROM projeto p WHERE p.empresa_id = $empresaId AND p.status = 'EM_ANDAMENTO' AND p.ativo = 1";
        if ($papel === 'GESTOR') {
             $sqlProjetos .= " AND p.gestor_id = $usuarioId";
        }
        $qtdProjetos = $pdo->query($sqlProjetos)->fetchColumn();

        // Monta KPIs com Links para navegação dinâmica
        $response['kpis'] = [
            ['titulo' => 'Para Aprovar', 'valor' => $qtdRevisao, 'icone' => 'alerta', 'cor' => 'red', 'link' => '#listaPendencias'], 
            ['titulo' => 'Em Andamento', 'valor' => $qtdAtivas, 'icone' => 'task', 'cor' => 'blue', 'link' => 'minhas_tarefas.php'],
            ['titulo' => 'Projetos Ativos', 'valor' => $qtdProjetos, 'icone' => 'folder', 'cor' => 'purple', 'link' => 'projetos.php']
        ];

        // Lista de Pendências (Para Aprovação/Ajuste)
        $sqlPendencias = "SELECT t.id, t.titulo, u.nome as responsavel, t.status, t.prazo, p.nome as projeto_nome, t.checklist
                          FROM tarefa t 
                          JOIN usuario u ON t.responsavel_id = u.id 
                          LEFT JOIN projeto p ON t.projeto_id = p.id
                          WHERE t.status = 'EM_REVISAO' $filtroGestao
                          ORDER BY t.atualizado_em ASC";
        
        $response['pendencias'] = $pdo->query($sqlPendencias)->fetchAll(PDO::FETCH_ASSOC);

        // Lista de Tarefas Concluídas Recentes (Para Auditoria do Líder ou Visualização do Gestor)
        $sqlConcluidas = "SELECT t.id, t.titulo, u.nome as responsavel, t.concluida_em, p.nome as projeto_nome
                          FROM tarefa t 
                          JOIN usuario u ON t.responsavel_id = u.id 
                          LEFT JOIN projeto p ON t.projeto_id = p.id
                          WHERE t.status = 'CONCLUIDA' $filtroGestao
                          ORDER BY t.concluida_em DESC LIMIT 10";
        $response['concluidas_recentes'] = $pdo->query($sqlConcluidas)->fetchAll(PDO::FETCH_ASSOC);

        // Minhas Tarefas (Para execução pessoal do Gestor/Líder)
        // Isso permite que o gestor também execute tarefas atribuídas a ele
        $sqlMinhas = "SELECT t.id, t.titulo, t.prioridade, t.prazo, t.status, p.nome as projeto_nome
                      FROM tarefa t 
                      LEFT JOIN projeto p ON t.projeto_id = p.id
                      WHERE t.responsavel_id = ? 
                      AND t.status IN ('PENDENTE', 'EM_ANDAMENTO')
                      ORDER BY t.prazo ASC LIMIT 5";
                      
        $stmtM = $pdo->prepare($sqlMinhas);
        $stmtM->execute([$usuarioId]);
        $response['minhas_tarefas'] = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        // Gráfico de Produtividade (Últimos 7 dias)
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
        
        $sqlKPI = "SELECT 
            SUM(CASE WHEN status NOT IN ('CONCLUIDA', 'CANCELADA', 'EM_REVISAO') AND prazo < CURDATE() THEN 1 ELSE 0 END) as atrasadas,
            SUM(CASE WHEN status IN ('PENDENTE', 'EM_ANDAMENTO') AND prioridade = 'URGENTE' THEN 1 ELSE 0 END) as urgentes,
            SUM(CASE WHEN status = 'EM_ANDAMENTO' THEN 1 ELSE 0 END) as em_andamento,
            SUM(CASE WHEN status = 'CONCLUIDA' AND DATE(concluida_em) = CURDATE() THEN 1 ELSE 0 END) as entregues_hoje
            FROM tarefa WHERE responsavel_id = ?";
            
        $stmtKPI = $pdo->prepare($sqlKPI);
        $stmtKPI->execute([$usuarioId]);
        $kpis = $stmtKPI->fetch(PDO::FETCH_ASSOC);

        $response['kpis'] = [
            ['titulo' => 'Atrasadas', 'valor' => $kpis['atrasadas'] ?? 0, 'icone' => 'alerta', 'cor' => 'red', 'link' => '#'],
            ['titulo' => 'Urgentes', 'valor' => $kpis['urgentes'] ?? 0, 'icone' => 'alerta', 'cor' => 'orange', 'link' => '#'],
            ['titulo' => 'Em Andamento', 'valor' => $kpis['em_andamento'] ?? 0, 'icone' => 'play', 'cor' => 'blue', 'link' => '#'],
            ['titulo' => 'Entregues Hoje', 'valor' => $kpis['entregues_hoje'] ?? 0, 'icone' => 'check_circle', 'cor' => 'green', 'link' => '#']
        ];

        // Lista inteligente: Prioriza Atrasadas > Urgentes > Hoje
        $sqlMinhas = "SELECT t.id, t.titulo, t.descricao, t.prioridade, t.prazo, t.status, 
                             t.checklist, t.tempo_total_minutos, t.progresso, t.feedback_revisao,
                             p.nome as projeto_nome
                      FROM tarefa t 
                      LEFT JOIN projeto p ON t.projeto_id = p.id
                      WHERE t.responsavel_id = ? 
                      AND t.status IN ('PENDENTE', 'EM_ANDAMENTO')
                      ORDER BY 
                        CASE 
                            WHEN t.prazo < CURDATE() THEN 0 
                            WHEN t.prioridade = 'URGENTE' THEN 1 
                            WHEN DATE(t.prazo) = CURDATE() THEN 2
                            ELSE 3 
                        END, 
                        t.prazo ASC
                      LIMIT 30";
                      
        $stmtM = $pdo->prepare($sqlMinhas);
        $stmtM->execute([$usuarioId]);
        $response['minhas_tarefas'] = $stmtM->fetchAll(PDO::FETCH_ASSOC);
        
        $sqlAgenda = "SELECT t.id, t.titulo, t.prazo, t.status, t.prioridade, p.nome as projeto_nome
                      FROM tarefa t 
                      LEFT JOIN projeto p ON t.projeto_id = p.id
                      WHERE t.responsavel_id = ? 
                      AND t.status NOT IN ('CONCLUIDA', 'CANCELADA', 'EM_REVISAO')
                      AND (t.prazo IS NULL OR DATE(t.prazo) <= CURDATE())
                      ORDER BY t.prazo ASC";
        $stmtAg = $pdo->prepare($sqlAgenda);
        $stmtAg->execute([$usuarioId]);
        $response['agenda_dia'] = $stmtAg->fetchAll(PDO::FETCH_ASSOC);
        
        $response['grafico'] = null; 
    }

    ob_clean(); 
    echo json_encode($response);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
exit;
?>