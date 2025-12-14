<?php
// ARQUIVO: api/relatorios_dados.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/funcoes_api.php';

if (!esta_logado()) responder_erro("Sessão expirada");

$pdo = conectar_db();
$empresaId = getEmpresaIdLogado($_SESSION[SESSAO_USUARIO_KEY]);

// Filtros
$filtroEquipe = isset($_GET['equipe']) && $_GET['equipe'] != '' ? (int)$_GET['equipe'] : null;
$filtroMembro = isset($_GET['membro']) && $_GET['membro'] != '' ? (int)$_GET['membro'] : null;
$filtroMes = $_GET['mes'] ?? date('Y-m');

// 1. KPIs GERAIS
$sqlKPI = "SELECT 
    COUNT(t.id) as total_tarefas,
    COALESCE(SUM(CASE WHEN t.status = 'CONCLUIDA' THEN 1 ELSE 0 END), 0) as concluidas,
    COALESCE(SUM(CASE WHEN t.status = 'EM_ANDAMENTO' THEN 1 ELSE 0 END), 0) as andamento,
    COALESCE(SUM(CASE WHEN t.status != 'CONCLUIDA' AND t.status != 'CANCELADA' AND t.prazo < CURDATE() THEN 1 ELSE 0 END), 0) as atrasadas
    FROM tarefa t 
    JOIN usuario u ON t.responsavel_id = u.id
    WHERE t.empresa_id = ? AND t.status != 'CANCELADA'";

$paramsKPI = [$empresaId];

if ($filtroEquipe) { $sqlKPI .= " AND u.equipe_id = ?"; $paramsKPI[] = $filtroEquipe; }
if ($filtroMembro) { $sqlKPI .= " AND t.responsavel_id = ?"; $paramsKPI[] = $filtroMembro; }
if ($filtroMes) { $sqlKPI .= " AND DATE_FORMAT(t.prazo, '%Y-%m') = ?"; $paramsKPI[] = $filtroMes; }

$kpis = $pdo->prepare($sqlKPI);
$kpis->execute($paramsKPI);
$dadosKPI = $kpis->fetch(PDO::FETCH_ASSOC);

// GARANTIA DE INTEIROS (Corrige o erro do Loop)
$dadosKPI['total_tarefas'] = (int)$dadosKPI['total_tarefas'];
$dadosKPI['concluidas']    = (int)$dadosKPI['concluidas'];
$dadosKPI['andamento']     = (int)$dadosKPI['andamento'];
$dadosKPI['atrasadas']     = (int)$dadosKPI['atrasadas'];

// Cálculo Eficiência
$total = $dadosKPI['total_tarefas'] > 0 ? $dadosKPI['total_tarefas'] : 1;
$dadosKPI['eficiencia'] = round(($dadosKPI['concluidas'] / $total) * 100);

// 2. LISTA DE MEMBROS
$sqlMembros = "SELECT 
    u.id, u.nome, e.nome as nome_equipe,
    COUNT(t.id) as total,
    COALESCE(SUM(CASE WHEN t.status = 'CONCLUIDA' THEN 1 ELSE 0 END), 0) as concluidas,
    COALESCE(SUM(CASE WHEN t.status != 'CONCLUIDA' AND t.status != 'CANCELADA' AND t.prazo < CURDATE() THEN 1 ELSE 0 END), 0) as atrasadas
    FROM usuario u
    LEFT JOIN equipe e ON u.equipe_id = e.id
    LEFT JOIN tarefa t ON t.responsavel_id = u.id AND t.empresa_id = ? AND t.status != 'CANCELADA' ";

$paramsMembros = [$empresaId];
if ($filtroMes) { $sqlMembros .= " AND DATE_FORMAT(t.prazo, '%Y-%m') = ?"; $paramsMembros[] = $filtroMes; }

$sqlMembros .= " WHERE u.empresa_id = ? AND u.ativo = 1";
$paramsMembros[] = $empresaId;

if ($filtroEquipe) { $sqlMembros .= " AND u.equipe_id = ?"; $paramsMembros[] = $filtroEquipe; }
if ($filtroMembro) { $sqlMembros .= " AND u.id = ?"; $paramsMembros[] = $filtroMembro; }

$sqlMembros .= " GROUP BY u.id ORDER BY concluidas DESC";

$stmtM = $pdo->prepare($sqlMembros);
$stmtM->execute($paramsMembros);
$listaMembros = $stmtM->fetchAll(PDO::FETCH_ASSOC);

foreach($listaMembros as &$m) {
    $t = $m['total'] > 0 ? $m['total'] : 1;
    $m['taxa_sucesso'] = round(($m['concluidas'] / $t) * 100);
    $m['taxa_atraso'] = round(($m['atrasadas'] / $t) * 100);
}

// 3. GRÁFICO DE STATUS
$sqlStatus = "SELECT status, COUNT(*) as qtd FROM tarefa t JOIN usuario u ON t.responsavel_id = u.id WHERE t.empresa_id = ?";
$paramsStatus = [$empresaId];
if ($filtroEquipe) { $sqlStatus .= " AND u.equipe_id = ?"; $paramsStatus[] = $filtroEquipe; }
if ($filtroMembro) { $sqlStatus .= " AND t.responsavel_id = ?"; $paramsStatus[] = $filtroMembro; }
if ($filtroMes) { $sqlStatus .= " AND DATE_FORMAT(t.prazo, '%Y-%m') = ?"; $paramsStatus[] = $filtroMes; }
$sqlStatus .= " GROUP BY status";

$stmtS = $pdo->prepare($sqlStatus);
$stmtS->execute($paramsStatus);
$rawStatus = $stmtS->fetchAll(PDO::FETCH_KEY_PAIR);

$statusFormatado = [
    'Planejado' => (int)($rawStatus['PENDENTE'] ?? 0),
    'Execucao'  => (int)(($rawStatus['EM_ANDAMENTO'] ?? 0) + ($rawStatus['EM_REVISAO'] ?? 0)),
    'Concluido' => (int)($rawStatus['CONCLUIDA'] ?? 0),
    'Atrasado'  => $dadosKPI['atrasadas'] 
];

echo json_encode([
    'ok' => true,
    'kpis' => $dadosKPI,
    'membros' => $listaMembros,
    'status_chart' => $statusFormatado
]);
?>