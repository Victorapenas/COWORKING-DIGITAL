<?php
//atualização
// ARQUIVO: api/calendario_eventos.php
require_once __DIR__ . '/../includes/seguranca.php';
require_once __DIR__ . '/funcoes_api.php';

if (!esta_logado()) responder_erro("Sessão inválida");

$pdo = conectar_db();
$sessao = $_SESSION[SESSAO_USUARIO_KEY];
$empresaId = getEmpresaIdLogado($sessao);
$papel = $sessao['papel'];

// Permissão de Líder
$isLeader = ($papel === 'DONO' || $papel === 'LIDER');

$filtroTipo = $_GET['tipo'] ?? 'todos';
$filtroStatus = $_GET['status'] ?? 'pendente'; 

$eventos = [];

// 1. TAREFAS (Têm horário real, usa o que está no banco)
if ($filtroTipo === 'todos' || $filtroTipo === 'tarefa') {
    $sql = "SELECT id, titulo, prazo, status FROM tarefa WHERE empresa_id = ? AND prazo IS NOT NULL";
    
    if ($filtroStatus === 'pendente') {
        $sql .= " AND status IN ('PENDENTE', 'EM_ANDAMENTO', 'EM_REVISAO', 'ABERTO')";
    } elseif ($filtroStatus === 'concluido') {
        $sql .= " AND status = 'CONCLUIDA'";
    } else {
        $sql .= " AND status != 'CANCELADA'";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$empresaId]);
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dataIso = date('c', strtotime($row['prazo']));
        
        $eventos[] = [
            'id' => 't_'.$row['id'],
            'title' => $row['titulo'],
            'start' => $dataIso, 
            'className' => 'evt-tarefa',
            'allDay' => false
        ];
    }
}

// 2. PROJETOS (Datas finais)
// TRUQUE: Definimos horário 08:00 para aparecer no começo do dia na grade horária
if ($filtroTipo === 'todos' || $filtroTipo === 'projeto') {
    $sql = "SELECT id, nome, data_fim FROM projeto WHERE empresa_id = ? AND data_fim IS NOT NULL AND ativo = 1";
    
    if ($filtroStatus === 'pendente') $sql .= " AND status != 'CONCLUIDO'";
    elseif ($filtroStatus === 'concluido') $sql .= " AND status = 'CONCLUIDO'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$empresaId]);
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $eventos[] = [
            'id' => 'p_'.$row['id'],
            'title' => 'Entrega: ' . $row['nome'],
            // Adiciona hora fixa 08:00
            'start' => $row['data_fim'] . 'T08:00:00', 
            'className' => 'evt-projeto',
            'allDay' => false 
        ];
    }
}

// 3. CONTRATOS (Só Líder vê)
// TRUQUE: Definimos horário 09:00 para não sobrepor visualmente o projeto
if ($isLeader && ($filtroTipo === 'todos' || $filtroTipo === 'contrato')) {
    $stmt = $pdo->prepare("SELECT nome, data_fim FROM projeto WHERE empresa_id = ? AND data_fim IS NOT NULL");
    $stmt->execute([$empresaId]);
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $eventos[] = [
            'title' => 'VENCIMENTO: ' . $row['nome'],
            // Adiciona hora fixa 09:00
            'start' => $row['data_fim'] . 'T09:00:00',
            'className' => 'evt-contrato',
            'allDay' => false
        ];
    }
}

echo json_encode($eventos);
?>