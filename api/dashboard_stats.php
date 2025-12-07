<?php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/funcoes_api.php';

if (!esta_logado()) responder_erro("Sessão expirada");

$pdo = conectar_db();
$empresaId = getEmpresaIdLogado($_SESSION[SESSAO_USUARIO_KEY]);

// KPIs
$kpis = [
    'projetos_ativos' => $pdo->query("SELECT COUNT(*) FROM projeto WHERE empresa_id = $empresaId AND status = 'EM_ANDAMENTO'")->fetchColumn(),
    'tarefas_pendentes' => $pdo->query("SELECT COUNT(*) FROM tarefa WHERE empresa_id = $empresaId AND status IN ('PENDENTE','EM_ANDAMENTO')")->fetchColumn(),
    'equipe_online' => $pdo->query("SELECT COUNT(*) FROM usuario WHERE empresa_id = $empresaId AND ativo=1 AND ultima_atividade > (NOW() - INTERVAL 10 MINUTE)")->fetchColumn(),
    'emergencias' => $pdo->query("SELECT COUNT(*) FROM emergencia WHERE empresa_id = $empresaId AND status = 'ABERTO'")->fetchColumn()
];

// Projetos Recentes
$projetos = $pdo->query("SELECT id, nome, status, progresso FROM projeto WHERE empresa_id = $empresaId AND ativo = 1 ORDER BY atualizado_em DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

// Emergencias
$emergencias = $pdo->query("SELECT titulo, prioridade FROM emergencia WHERE empresa_id = $empresaId AND status != 'RESOLVIDO' ORDER BY criado_em DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'ok' => true,
    'kpis' => $kpis,
    'projetos' => $projetos,
    'emergencias' => $emergencias
]);
?>