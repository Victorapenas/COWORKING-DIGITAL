<?php
// ARQUIVO: api/projeto_editar.php
ob_start(); 
ini_set('display_errors', 0); 
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php'; 
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    
    if (!in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR'])) {
        throw new Exception("Permissão negada.");
    }

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) throw new Exception("ID inválido.");

    // --- CORREÇÃO DO ERRO 500 (EQUIPES) ---
    // O JS envia como array, mas garantimos que funciona se vier como string também
    $equipesInput = $_POST['equipes'] ?? [];
    if (is_string($equipesInput)) {
        $equipes = explode(',', $equipesInput);
    } else {
        $equipes = $equipesInput; // Já é um array
    }

    $nome = trim($_POST['nome'] ?? '');
    $cliente = trim($_POST['cliente'] ?? '');
    $desc = trim($_POST['descricao'] ?? '');
    $inicio = !empty($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
    $fim = !empty($_POST['data_fim']) ? $_POST['data_fim'] : null;
    $status = $_POST['status'] ?? 'PLANEJADO';

    $pdo = conectar_db();
    
    // 1. Busca dados antigos para preservar arquivos não excluídos
    $stmt = $pdo->prepare("SELECT links_externos, arquivos_privados FROM projeto WHERE id = ?");
    $stmt->execute([$id]);
    $antigo = $stmt->fetch();
    
    // Decodifica JSONs antigos
    $listaAtualPublica = !empty($antigo['links_externos']) ? json_decode($antigo['links_externos'], true) : [];
    $listaAtualPrivada = !empty($antigo['arquivos_privados']) ? json_decode($antigo['arquivos_privados'], true) : [];

    // 2. Lógica de Remoção de Arquivos (Baseado no que o usuário clicou 'Excluir' no modal)
    $arquivosParaRemover = $_POST['remover_arquivos'] ?? [];
    
    // Função auxiliar para filtrar
    $filtrarMantidos = function($lista) use ($arquivosParaRemover) {
        $novaLista = [];
        foreach ($lista as $item) {
            // Se for arquivo e estiver na lista de remoção, ignora (apaga do array)
            // Se for link de texto, vamos recriá-los depois pelos inputs, então ignoramos aqui também
            if ($item['tipo'] === 'arquivo') {
                if (!in_array($item['url'], $arquivosParaRemover)) {
                    $novaLista[] = $item; // Mantém o arquivo
                } else {
                    // Opcional: Deletar arquivo físico
                    $caminhoFisico = __DIR__ . '/../public/' . $item['url'];
                    if (file_exists($caminhoFisico)) @unlink($caminhoFisico);
                }
            }
        }
        return $novaLista;
    };

    $novosLinksPublicos = $filtrarMantidos($listaAtualPublica);
    $novosLinksPrivados = $filtrarMantidos($listaAtualPrivada);

    // 3. Adiciona Novos Links de Texto (Vindos do Form)
    if (isset($_POST['link_titulo'])) {
        foreach ($_POST['link_titulo'] as $i => $titulo) {
            if (!empty($titulo)) {
                $novosLinksPublicos[] = [
                    'titulo' => $titulo, 
                    'url' => $_POST['link_url'][$i], 
                    'tipo' => 'link'
                ];
            }
        }
    }
    if (isset($_POST['link_priv_titulo'])) {
        foreach ($_POST['link_priv_titulo'] as $i => $titulo) {
            if (!empty($titulo)) {
                $novosLinksPrivados[] = [
                    'titulo' => $titulo, 
                    'url' => $_POST['link_priv_url'][$i], 
                    'tipo' => 'link'
                ];
            }
        }
    }
    
    // 4. Upload de NOVOS Arquivos
    $uploadDir = __DIR__ . '/../public/uploads/projetos/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    // Públicos
    if (isset($_FILES['docs_publicos'])) {
        foreach ($_FILES['docs_publicos']['name'] as $i => $name) {
            if ($_FILES['docs_publicos']['error'][$i] === 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $novoNome = 'doc_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['docs_publicos']['tmp_name'][$i], $uploadDir . $novoNome)) {
                    $novosLinksPublicos[] = [
                        'titulo' => $name, 
                        'url' => 'uploads/projetos/' . $novoNome, 
                        'tipo' => 'arquivo'
                    ];
                }
            }
        }
    }
    
    // Privados
    if (isset($_FILES['docs_privados'])) {
        foreach ($_FILES['docs_privados']['name'] as $i => $name) {
            if ($_FILES['docs_privados']['error'][$i] === 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $novoNome = 'priv_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['docs_privados']['tmp_name'][$i], $uploadDir . $novoNome)) {
                    $novosLinksPrivados[] = [
                        'titulo' => $name, 
                        'url' => 'uploads/projetos/' . $novoNome, 
                        'tipo' => 'arquivo'
                    ];
                }
            }
        }
    }

    // 5. Salva no Banco
    $sql = "UPDATE projeto SET nome=?, cliente_nome=?, descricao=?, data_inicio=?, data_fim=?, status=?, links_externos=?, arquivos_privados=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nome, $cliente, $desc, $inicio, $fim, $status, 
        json_encode(array_values($novosLinksPublicos)), 
        json_encode(array_values($novosLinksPrivados)), 
        $id
    ]);

    // 6. Atualiza Equipes
    $pdo->prepare("DELETE FROM projeto_equipe WHERE projeto_id=?")->execute([$id]);
    if (!empty($equipes)) {
        $stmtEq = $pdo->prepare("INSERT INTO projeto_equipe (projeto_id, equipe_id) VALUES (?, ?)");
        foreach ($equipes as $eqId) {
            if($eqId) $stmtEq->execute([$id, $eqId]);
        }
    }

    echo json_encode(['ok' => true, 'mensagem' => 'Projeto atualizado com sucesso!']);

} catch (Exception $e) {
    http_response_code(400); // Bad Request em vez de 500 para erros lógicos
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
?>