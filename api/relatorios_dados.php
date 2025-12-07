<?php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/funcoes_api.php';
if (!esta_logado()) responder_erro("Sessão expirada");

$pdo = conectar_db();
$empresaId = getEmpresaIdLogado($_SESSION[SESSAO_USUARIO_KEY]);

// Dados Membros
$stmt = $pdo->prepare("SELECT u.nome, (SELECT COUNT(*) FROM tarefa t WHERE t.responsavel_id = u.id AND t.status='CONCLUIDA') as concluidas FROM usuario u WHERE empresa_id = ? AND ativo = 1");
$stmt->execute([$empresaId]);
$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dados Projetos
$res = $pdo->query("SELECT 
    SUM(CASE WHEN status='PLANEJADO' THEN 1 ELSE 0 END) as planejado,
    SUM(CASE WHEN status='EM_ANDAMENTO' THEN 1 ELSE 0 END) as andamento,
    SUM(CASE WHEN status='CONCLUIDO' THEN 1 ELSE 0 END) as concluido
    FROM projeto WHERE empresa_id = $empresaId AND ativo = 1")->fetch(PDO::FETCH_ASSOC);

echo json_encode(['ok'=>true, 'membros'=>$membros, 'status'=>$res]);
?>