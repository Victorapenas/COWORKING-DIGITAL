<?php
// ARQUIVO: api/dashboard_stats.php

// 1. Blindagem de Erros (Evita que Warnings quebrem o JSON)
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
        'listas' => [],        // Usado pelo Gestor (Projetos/Equipe)
        'minhas_tarefas' => [], // Usado pelo Colaborador (Lista de Foco)
        'meus_projetos' => [],  // Usado pelo Colaborador (Lateral)
        'pendencias' => [],     // Usado pelo Gestor (Aprovações)
        'online_users' => [],
        'grafico' => []
    ];

    // --- 1. BUSCA TAREFAS PESSOAIS (Fundamental para o Colaborador) ---
    // Trazemos checklist e descrição para o modal lateral abrir instantaneamente
    $sqlMinhas = "SELECT t.id, t.titulo, t.descricao, t.prioridade, t.prazo, t.status, 
                         t.checklist, t.tempo_total_minutos, t.progresso,
                         p.nome as projeto_nome
                  FROM tarefa t 
                  LEFT JOIN projeto p ON t.projeto_id = p.id
                  WHERE t.responsavel_id = ? 
                  AND t.status NOT IN ('CONCLUIDA', 'CANCELADA')
                  ORDER BY FIELD(t.prioridade, 'URGENTE', 'IMPORTANTE', 'NORMAL'), t.prazo ASC LIMIT 20";
                  
    $stmtM = $pdo->prepare($sqlMinhas);
    $stmtM->execute([$usuarioId]);
    $response['minhas_tarefas'] = $stmtM->fetchAll(PDO::FETCH_ASSOC);

    // --- 2. LÓGICA DE VISUALIZAÇÃO POR CARGO ---

    if ($papel === 'DONO' || $papel === 'LIDER') {
        // === VISÃO DE DONO ===
        $response['kpis'] = [
            ['titulo' => 'Projetos Ativos', 'valor' => $pdo->query("SELECT COUNT(*) FROM projeto WHERE empresa_id = $empresaId AND status = 'EM_ANDAMENTO'")->fetchColumn(), 'icone' => 'rocket', 'cor' => 'blue'],
            ['titulo' => 'Equipe Online', 'valor' => $pdo->query("SELECT COUNT(*) FROM usuario WHERE empresa_id = $empresaId AND ativo=1 AND ultima_atividade > (NOW() - INTERVAL 10 MINUTE)")->fetchColumn(), 'icone' => 'users', 'cor' => 'green'],
            ['titulo' => 'Entregas Hoje', 'valor' => $pdo->query("SELECT COUNT(*) FROM tarefa WHERE empresa_id = $empresaId AND status != 'CANCELADA' AND DATE(prazo) = CURDATE()")->fetchColumn(), 'icone' => 'alerta', 'cor' => 'orange'],
            ['titulo' => 'Tarefas Feitas', 'valor' => $pdo->query("SELECT COUNT(*) FROM tarefa WHERE empresa_id = $empresaId AND status = 'CONCLUIDA' AND MONTH(concluida_em) = MONTH(CURRENT_DATE())")->fetchColumn(), 'icone' => 'check', 'cor' => 'purple']
        ];

        // Lista de Projetos com Barra de Progresso
        $sqlProjetos = "
            SELECT p.id, p.nome, p.status, p.cliente_nome,
            (SELECT COUNT(*) FROM tarefa t WHERE t.projeto_id = p.id) as total_tarefas,
            (SELECT COUNT(*) FROM tarefa t WHERE t.projeto_id = p.id AND t.status = 'CONCLUIDA') as tarefas_feitas
            FROM projeto p 
            WHERE p.empresa_id = $empresaId AND p.ativo = 1 AND p.status != 'CONCLUIDO'
            ORDER BY p.atualizado_em DESC LIMIT 5
        ";
        $projetos = $pdo->query($sqlProjetos)->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($projetos as &$p) {
            $total = $p['total_tarefas'] > 0 ? $p['total_tarefas'] : 1;
            $p['progresso'] = round(($p['tarefas_feitas'] / $total) * 100);
        }
        $response['listas'] = $projetos;

        $response['online_users'] = $pdo->query("
            SELECT id, nome, cargo_detalhe FROM usuario 
            WHERE empresa_id = $empresaId AND ativo = 1 AND ultima_atividade > (NOW() - INTERVAL 30 MINUTE)
            ORDER BY ultima_atividade DESC LIMIT 8
        ")->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($papel === 'GESTOR') {
        // === VISÃO DE GESTOR ===
        $response['kpis'] = [
            ['titulo' => 'Meus Projetos', 'valor' => $pdo->query("SELECT COUNT(*) FROM projeto WHERE gestor_id = $usuarioId AND status = 'EM_ANDAMENTO'")->fetchColumn(), 'icone' => 'pasta', 'cor' => 'blue'],
            ['titulo' => 'Para Aprovar', 'valor' => $pdo->query("SELECT COUNT(*) FROM tarefa t JOIN projeto p ON t.projeto_id = p.id WHERE p.gestor_id = $usuarioId AND t.status = 'EM_REVISAO'")->fetchColumn(), 'icone' => 'olho', 'cor' => 'red'], 
            ['titulo' => 'Tarefas Equipe', 'valor' => $pdo->query("SELECT COUNT(*) FROM tarefa t JOIN projeto p ON t.projeto_id = p.id WHERE p.gestor_id = $usuarioId AND t.status IN ('PENDENTE','EM_ANDAMENTO')")->fetchColumn(), 'icone' => 'users', 'cor' => 'orange'],
            ['titulo' => 'Minhas Ativas', 'valor' => count($response['minhas_tarefas']), 'icone' => 'task', 'cor' => 'purple']
        ];

        $response['pendencias'] = $pdo->query("
            SELECT t.id, t.titulo, u.nome as responsavel, t.status, t.prazo 
            FROM tarefa t JOIN projeto p ON t.projeto_id = p.id JOIN usuario u ON t.responsavel_id = u.id 
            WHERE p.gestor_id = $usuarioId AND t.status = 'EM_REVISAO' LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        $response['listas'] = $pdo->query("
            SELECT t.id, t.titulo, t.prioridade, t.prazo, u.nome as responsavel, t.status
            FROM tarefa t JOIN projeto p ON t.projeto_id = p.id JOIN usuario u ON t.responsavel_id = u.id
            WHERE p.gestor_id = $usuarioId AND t.status = 'EM_ANDAMENTO' AND t.responsavel_id != $usuarioId
            ORDER BY t.prazo ASC LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // === VISÃO DE COLABORADOR (OPERACIONAL) ===
        // KPIs focados em produtividade pessoal
        $response['kpis'] = [
            ['titulo' => 'A Fazer', 'valor' => count($response['minhas_tarefas']), 'icone' => 'task', 'cor' => 'blue'],
            ['titulo' => 'Entregues (Mês)', 'valor' => $pdo->query("SELECT COUNT(*) FROM tarefa WHERE responsavel_id = $usuarioId AND status = 'CONCLUIDA' AND MONTH(concluida_em) = MONTH(CURRENT_DATE())")->fetchColumn(), 'icone' => 'check', 'cor' => 'green'],
            ['titulo' => 'Urgentes', 'valor' => $pdo->query("SELECT COUNT(*) FROM tarefa WHERE responsavel_id = $usuarioId AND status != 'CONCLUIDA' AND prioridade = 'URGENTE'")->fetchColumn(), 'icone' => 'alerta', 'cor' => 'red']
        ];
        
        // Busca projetos onde o colaborador é membro (para a barra lateral)
        $sqlMeusProjetos = "SELECT DISTINCT p.id, p.nome 
                            FROM projeto p
                            JOIN projeto_equipe pe ON p.id = pe.projeto_id
                            JOIN usuario u ON u.equipe_id = pe.equipe_id
                            WHERE u.id = ? AND p.ativo = 1 AND p.status != 'CONCLUIDO' 
                            ORDER BY p.criado_em DESC LIMIT 5";
        $stmtMP = $pdo->prepare($sqlMeusProjetos);
        $stmtMP->execute([$usuarioId]);
        $response['meus_projetos'] = $stmtMP->fetchAll(PDO::FETCH_ASSOC);
        
        // O colaborador não usa $response['listas'], ele usa $response['minhas_tarefas'] diretamente no JS
    }

    // --- 3. GRÁFICO (PARA GESTORES/DONOS) ---
    // Se for colaborador, o gráfico pode ser omitido ou mostrar performance pessoal
    $filtroGrafico = ($papel === 'COLABORADOR' || $papel === 'FUNCIONARIO') ? "AND responsavel_id = $usuarioId" : "";
    
    $grafico = [];
    $diasPT = ['Sun'=>'Dom', 'Mon'=>'Seg', 'Tue'=>'Ter', 'Wed'=>'Qua', 'Thu'=>'Qui', 'Fri'=>'Sex', 'Sat'=>'Sáb'];

    for ($i = 6; $i >= 0; $i--) {
        $data = date('Y-m-d', strtotime("-$i days"));
        $diaSemana = date('D', strtotime("-$i days"));
        
        $sqlG = "SELECT COUNT(*) FROM tarefa WHERE empresa_id = $empresaId $filtroGrafico AND status = 'CONCLUIDA' AND DATE(concluida_em) = '$data'";
        $qtd = $pdo->query($sqlG)->fetchColumn();
        
        $grafico['labels'][] = $diasPT[$diaSemana];
        $grafico['data'][] = $qtd;
    }
    $response['grafico'] = $grafico;

    // LIMPA BUFFER E ENVIA JSON LIMPO
    ob_clean(); 
    echo json_encode($response);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
exit;
?>