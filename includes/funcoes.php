<?php
// ARQUIVO: includes/funcoes.php
require_once __DIR__ . '/../config/conexao.php';

// =============================================================================
// 1. CENTRAL DE ÍCONES SVG
// =============================================================================
function getIcone(string $nome): string {
    $icons = [
        'lixo' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>',
        'editar' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
        'olho' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
        'user' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
        'users' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
        'calendario' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
        'pasta' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>',
        'arquivo' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>',
        'check' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        'clock' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
        'task' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>',
        'online' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21a8 8 0 0 1 13.292-6"></path><circle cx="10" cy="8" r="5"></circle><path d="M22 19l-3 3l-3-3"></path><path d="M22 16v6"></path></svg>',
        'config' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
        'mail' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
        'restaurar' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>',
        'coroa' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"></path></svg>',
        'nuvem' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>',
        'documento' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
        'cadeado' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
        'imagem' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
        'link' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>',
        'seta_voltar' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>'
    ];
    return $icons[$nome] ?? '';
}

// =============================================================================
// 2. UTILITÁRIOS E VALIDAÇÃO
// =============================================================================

function validar_email(string $email): string|false {
    $email = strtolower(trim($email));
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

function validar_senha(string $senha): bool {
    return strlen($senha) >= 8;
}

function validar_codigo_recuperacao(string $codigo): bool {
    return is_numeric($codigo) && strlen($codigo) === 4;
}

function aplicar_rate_limit() {
    if (defined('RATE_LIMIT_DELAY_MS')) usleep(RATE_LIMIT_DELAY_MS * 1000);
}

function responder_erro(string $mensagem, int $http_code = 400) {
    http_response_code($http_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'erro' => $mensagem], JSON_UNESCAPED_UNICODE);
    exit;
}

function redirecionar(string $url) {
    if (!headers_sent()) header('Location: ' . $url);
    else echo "<script>window.location.href='$url';</script>";
    exit;
}

// =============================================================================
// 3. CONTEXTO DO USUÁRIO
// =============================================================================

function getEmpresaIdLogado(array $usuario): int {
    $empresaId = (int) ($usuario['empresa_id'] ?? 0); 
    if ($empresaId === 0 && isset($usuario['id'])) {
        $pdo = conectar_db();
        $stmt = $pdo->prepare("SELECT empresa_id FROM usuario WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        $empresaId = (int) $stmt->fetchColumn(); 
        if ($empresaId > 0) $_SESSION[SESSAO_USUARIO_KEY]['empresa_id'] = $empresaId;
    }
    return $empresaId;
}

function listarEquipes(int $empresaId): array {
    $pdo = conectar_db();
    $stmt = $pdo->prepare("SELECT id, nome FROM equipe WHERE empresa_id = ? ORDER BY nome ASC");
    $stmt->execute([$empresaId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// =============================================================================
// 4. LÓGICA DE EQUIPES (COMPLETA)
// =============================================================================

// Helper para formatar dados de membro
function processarDadosMembro(&$m) {
    $m['data_entrada'] = isset($m['criado_em']) ? date('d/m/Y', strtotime($m['criado_em'])) : '-';
    
    if (!empty($m['ultima_atividade'])) {
        $diff = time() - strtotime($m['ultima_atividade']);
        if ($diff < 60) $m['atividade_txt'] = "Agora";
        elseif ($diff < 3600) $m['atividade_txt'] = floor($diff/60)." min atrás";
        elseif ($diff < 86400) $m['atividade_txt'] = floor($diff/3600)." h atrás";
        else $m['atividade_txt'] = floor($diff/86400)." dias atrás";
        $m['is_online'] = ($diff <= 300);
    } else {
        $m['atividade_txt'] = "Nunca";
        $m['is_online'] = false;
    }
    
    $m['visto'] = $m['atividade_txt']; // Chave de compatibilidade

    $role = $m['papel_sistema'] ?? 'FUNCIONARIO';
    $m['papel_formatado'] = match($role) { 
        'DONO', 'LIDER' => 'Sócio', 
        'GESTOR' => 'Gestor', 
        default => 'Colaborador' 
    };
    
    // Garante chaves numéricas
    if(!isset($m['total'])) $m['total'] = 0;
    if(!isset($m['concluidas'])) $m['concluidas'] = 0;
}

function getLideranca(int $empresaId): array {
    $pdo = conectar_db();
    $sql = "SELECT u.id, u.nome, u.email, u.cargo_detalhe, u.ultima_atividade, u.criado_em,
            p.nome as papel_sistema,
            (SELECT COUNT(*) FROM tarefa t WHERE t.responsavel_id = u.id) as total,
            (SELECT COUNT(*) FROM tarefa t WHERE t.responsavel_id = u.id AND t.status='CONCLUIDA') as concluidas
            FROM usuario u 
            JOIN papel p ON u.papel_id = p.id 
            WHERE u.empresa_id = ? AND (p.nome = 'DONO' OR p.nome = 'LIDER') AND u.ativo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$empresaId]);
    $lideres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($lideres as &$lider) {
        processarDadosMembro($lider);
    }
    return $lideres;
}

function getEquipesDetalhadas(int $empresaId): array {
    $pdo = conectar_db();
    $stmt = $pdo->prepare("SELECT * FROM equipe WHERE empresa_id = ? ORDER BY nome ASC");
    $stmt->execute([$empresaId]);
    $equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resultado = [];
    foreach ($equipes as $eq) {
        $sqlM = "SELECT u.id, u.nome, u.email, u.cargo_detalhe, u.criado_em, u.ultima_atividade, p.nome as papel_sistema,
                 (SELECT COUNT(*) FROM tarefa t WHERE t.responsavel_id = u.id AND t.status != 'CANCELADA') as total,
                 (SELECT COUNT(*) FROM tarefa t WHERE t.responsavel_id = u.id AND t.status = 'CONCLUIDA') as concluidas
                 FROM usuario u JOIN papel p ON u.papel_id = p.id 
                 WHERE u.equipe_id = ? AND u.ativo = 1 AND p.nome != 'DONO' AND p.nome != 'LIDER'
                 ORDER BY p.nivel_hierarquia DESC, u.nome ASC";
        
        $stM = $pdo->prepare($sqlM);
        $stM->execute([$eq['id']]);
        $membros = $stM->fetchAll(PDO::FETCH_ASSOC);
        
        $tTotal = 0; $tConcluidas = 0; $tAtivas = 0;
        foreach($membros as &$m) {
            processarDadosMembro($m);
            $tTotal += $m['total'];
            $tConcluidas += $m['concluidas'];
            $tAtivas += ($m['total'] - $m['concluidas']);
        }
        
        $resultado[] = [
            'info' => $eq,
            'stats' => ['total' => $tTotal, 'concluidas' => $tConcluidas, 'ativas' => $tAtivas],
            'membros' => $membros
        ];
    }
    return $resultado;
}

function getMembrosFiltrados(int $empresaId, string $filtro): array {
    $pdo = conectar_db();
    $sql = "SELECT u.id, u.nome, u.email, u.cargo_detalhe, u.criado_em, u.ultima_atividade, u.ativo, p.nome as papel_sistema, e.nome as nome_equipe,
            (SELECT COUNT(*) FROM tarefa t WHERE t.responsavel_id = u.id) as total,
            (SELECT COUNT(*) FROM tarefa t WHERE t.responsavel_id = u.id AND t.status='CONCLUIDA') as concluidas
            FROM usuario u
            JOIN papel p ON u.papel_id = p.id
            LEFT JOIN equipe e ON u.equipe_id = e.id
            WHERE u.empresa_id = :empresaId";
            
    if ($filtro === 'GESTOR') $sql .= " AND p.nome = 'GESTOR' AND u.ativo = 1";
    elseif ($filtro === 'COLABORADOR') $sql .= " AND (p.nome = 'FUNCIONARIO' OR p.nome = 'COLABORADOR') AND u.ativo = 1";
    elseif ($filtro === 'ARQUIVADO') $sql .= " AND u.ativo = 0";
    
    $sql .= " ORDER BY u.nome ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
    $stmt->execute();
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($membros as &$m) {
        processarDadosMembro($m);
    }
    return $membros;
}

function getMembrosDisponiveis(int $empresaId): array {
    $pdo = conectar_db();
    $sql = "SELECT u.id, u.nome, u.email, e.nome as nome_equipe_atual 
            FROM usuario u 
            LEFT JOIN equipe e ON u.equipe_id = e.id
            JOIN papel p ON u.papel_id = p.id
            WHERE u.empresa_id = ? AND u.ativo = 1 AND p.nome != 'DONO' AND p.nome != 'LIDER'
            ORDER BY (u.equipe_id IS NULL) DESC, u.nome ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$empresaId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStatsGeral(int $empresaId): array {
    $pdo = conectar_db();
    $m = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE empresa_id=? AND ativo=1"); $m->execute([$empresaId]); $tm=$m->fetchColumn();
    $o = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE empresa_id=? AND ativo=1 AND ultima_atividade > (NOW() - INTERVAL 5 MINUTE)"); $o->execute([$empresaId]); $to=$o->fetchColumn();
    $t = $pdo->prepare("SELECT SUM(CASE WHEN status='CONCLUIDA' THEN 1 ELSE 0 END) as c, SUM(CASE WHEN status IN ('PENDENTE','EM_ANDAMENTO') THEN 1 ELSE 0 END) as a FROM tarefa t JOIN usuario u ON t.responsavel_id=u.id WHERE u.empresa_id=?"); $t->execute([$empresaId]); $st=$t->fetch(PDO::FETCH_ASSOC);
    
    $totalT = ($st['c'] + $st['a']);
    $prog = ($totalT > 0) ? round(($st['c'] / $totalT) * 100) : 0;
    
    return ['total_membros'=>$tm, 'online'=>$to, 'ativas'=>$st['a']??0, 'concluidas'=>$st['c']??0, 'progresso_geral'=>$prog];
}

// =============================================================================
// 5. CONFIGURAÇÕES E PROJETOS
// =============================================================================
function buscarUsuarioPorId($id){ $pdo=conectar_db(); $st=$pdo->prepare("SELECT id, nome, email, cargo_detalhe, empresa_id FROM usuario WHERE id=?"); $st->execute([$id]); return $st->fetch(PDO::FETCH_ASSOC)?:null; }
function atualizarUsuarioBasico($id, $n, $c){ $pdo=conectar_db(); $st=$pdo->prepare("UPDATE usuario SET nome=?, cargo_detalhe=? WHERE id=?"); return $st->execute([$n, $c, $id]); }
function buscarEmpresaPorId($id){ $pdo=conectar_db(); $st=$pdo->prepare("SELECT id, nome FROM empresa WHERE id=?"); $st->execute([$id]); return $st->fetch(PDO::FETCH_ASSOC)?:null; }
function atualizarNomeEmpresa($id, $n){ $pdo=conectar_db(); $st=$pdo->prepare("UPDATE empresa SET nome=? WHERE id=?"); return $st->execute([$n, $id]); }

// FUNÇÃO NOVA: GET MEMBROS DO PROJETO PARA DETALHES
function getMembrosDoProjeto(int $projetoId): array {
    $pdo = conectar_db();
    $sql = "
        SELECT u.id, u.nome, u.cargo_detalhe, u.email, 
               COUNT(t.id) as tarefas_total,
               SUM(CASE WHEN t.status = 'CONCLUIDA' THEN 1 ELSE 0 END) as tarefas_feitas
        FROM usuario u
        JOIN projeto_equipe pe ON u.equipe_id = pe.equipe_id
        LEFT JOIN tarefa t ON t.responsavel_id = u.id AND t.projeto_id = ?
        WHERE pe.projeto_id = ? AND u.ativo = 1
        GROUP BY u.id
        ORDER BY u.nome ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$projetoId, $projetoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProjetos(int $empresaId, bool $apenasAtivos = true): array {
    $pdo = conectar_db();
    $filtro = $apenasAtivos ? "AND p.ativo = 1" : "AND p.ativo = 0";
    $sql = "SELECT p.*, u.nome as nome_gestor FROM projeto p LEFT JOIN usuario u ON p.gestor_id = u.id WHERE u.empresa_id = ? $filtro ORDER BY p.status ASC, p.criado_em DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$empresaId]);
    $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($projetos as &$proj) {
        $stmtEq = $pdo->prepare("SELECT e.id, e.nome FROM equipe e JOIN projeto_equipe pe ON e.id = pe.equipe_id WHERE pe.projeto_id = ?");
        $stmtEq->execute([$proj['id']]);
        $proj['equipes'] = $stmtEq->fetchAll(PDO::FETCH_ASSOC); // Traz IDs e Nomes
        
        $stmtTar = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'CONCLUIDA' THEN 1 ELSE 0 END) as feitas FROM tarefa WHERE projeto_id = ? AND status != 'CANCELADA'");
        $stmtTar->execute([$proj['id']]);
        $stats = $stmtTar->fetch(PDO::FETCH_ASSOC);
        
        $proj['progresso'] = ($stats['total']>0) ? round(($stats['feitas']/$stats['total'])*100) : 0;
        $proj['tarefas_total'] = $stats['total'];
        $proj['tarefas_feitas'] = $stats['feitas'];
        
        $proj['links'] = !empty($proj['links_externos']) ? json_decode($proj['links_externos'], true) : [];
        $proj['privados'] = !empty($proj['arquivos_privados']) ? json_decode($proj['arquivos_privados'], true) : [];
        
        $proj['logo_url'] = null;
        if(is_array($proj['links'])){
            foreach($proj['links'] as $l) {
                if(isset($l['tipo']) && $l['tipo'] === 'logo') {
                    $proj['logo_url'] = $l['url'];
                    break;
                }
            }
        }
    }
    return $projetos;
}

function getProjetoDetalhe(int $id): ?array {
    $pdo = conectar_db();
    $stmt = $pdo->prepare("SELECT p.*, u.nome as nome_gestor FROM projeto p LEFT JOIN usuario u ON p.gestor_id = u.id WHERE p.id = ?");
    $stmt->execute([$id]);
    $proj = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$proj) return null;
    
    $stmtEq = $pdo->prepare("SELECT e.id, e.nome FROM equipe e JOIN projeto_equipe pe ON e.id = pe.equipe_id WHERE pe.projeto_id = ?");
    $stmtEq->execute([$id]);
    $proj['equipes'] = $stmtEq->fetchAll(PDO::FETCH_ASSOC);
    
    $stmtTar = $pdo->prepare("SELECT t.*, u.nome as responsavel_nome FROM tarefa t LEFT JOIN usuario u ON t.responsavel_id = u.id WHERE t.projeto_id = ? AND t.status != 'CANCELADA' ORDER BY t.status ASC, t.prazo ASC");
    $stmtTar->execute([$id]);
    $proj['tarefas_lista'] = $stmtTar->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($proj['tarefas_lista']);
    $feitas = 0; 
    foreach($proj['tarefas_lista'] as $t) if($t['status'] === 'CONCLUIDA') $feitas++;
    
    $proj['progresso'] = ($total>0) ? round(($feitas/$total)*100) : 0;
    $proj['tarefas_total'] = $total;
    $proj['tarefas_feitas'] = $feitas;
    
    $proj['links'] = !empty($proj['links_externos']) ? json_decode($proj['links_externos'], true) : [];
    $proj['privados'] = !empty($proj['arquivos_privados']) ? json_decode($proj['arquivos_privados'], true) : [];
    
    return $proj;
}
?>