<?php
// ARQUIVO: api/projeto_editar.php (CORREÇÃO FINAL DE DUPLICAÇÃO)
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

    $pdo = conectar_db();
    
    // 1. Busca dados antigos do projeto
    $stmt = $pdo->prepare("SELECT nome, cliente_nome, descricao, data_inicio, data_fim, status, links_externos, arquivos_privados FROM projeto WHERE id = ?");
    $stmt->execute([$id]);
    $antigo = $stmt->fetch();
    
    if (!$antigo) throw new Exception("Projeto não encontrado.");
    
    // Decodifica JSONs antigos
    $listaAtualPublica = !empty($antigo['links_externos']) ? json_decode($antigo['links_externos'], true) : [];
    $listaAtualPrivada = !empty($antigo['arquivos_privados']) ? json_decode($antigo['arquivos_privados'], true) : [];

    // Busca equipes atuais para FALLBACK
    $stmtEqAntigas = $pdo->prepare("SELECT equipe_id FROM projeto_equipe WHERE projeto_id = ?");
    $stmtEqAntigas->execute([$id]);
    $equipesAntigas = $stmtEqAntigas->fetchAll(PDO::FETCH_COLUMN);

    // --- OBTENÇÃO DOS NOVOS DADOS com FALLBACK (Proteção contra perda de dados) ---
    $nome_post = trim($_POST['nome'] ?? '');
    $nome = !empty($nome_post) ? $nome_post : $antigo['nome'];
    if (empty($nome)) throw new Exception("O nome do projeto não pode ser vazio.");
    
    $cliente_post = trim($_POST['cliente'] ?? '');
    $cliente = !empty($cliente_post) ? $cliente_post : $antigo['cliente_nome'];

    $desc_post = trim($_POST['descricao'] ?? '');
    $desc = !empty($desc_post) ? $desc_post : $antigo['descricao'];

    $inicio_post = trim($_POST['data_inicio'] ?? '');
    $inicio = !empty($inicio_post) ? $inicio_post : $antigo['data_inicio'];
    
    $fim_post = trim($_POST['data_fim'] ?? '');
    $fim = !empty($fim_post) ? $fim_post : $antigo['data_fim'];

    $status_post = $_POST['status'] ?? '';
    $status = !empty($status_post) ? $status_post : $antigo['status'];
    // -----------------------------------------------------------------

    // --- LÓGICA DE EQUIPES PARA SALVAR ---
    $equipesInput = $_POST['equipes'] ?? [];
    $equipesParaSalvar = [];

    if (!empty($equipesInput)) {
        if (is_string($equipesInput)) {
            $equipesParaSalvar = explode(',', $equipesInput);
        } else {
            $equipesParaSalvar = $equipesInput;
        }
    } else {
        $equipesParaSalvar = $equipesAntigas; // FALLBACK para equipes
    }
    $equipesParaSalvar = array_filter($equipesParaSalvar, 'intval');
    // --------------------------------------

    // 2. Lógica de Remoção de Arquivos e Filtro
    $arquivosParaRemover = $_POST['remover_arquivos'] ?? [];
    
    // **APENAS MANTÉM ARQUIVOS EXISTENTES NÃO MARCADOS PARA REMOÇÃO.**
    // **LINKS DE TEXTO (tipo 'link') SÃO DESPREZADOS AQUI.**
    $filtrarMantidos = function($lista) use ($arquivosParaRemover) {
        $novaLista = [];
        foreach ($lista as $item) {
            // Apenas processa o que é um arquivo físico
            if ($item['tipo'] === 'arquivo') {
                if (!in_array($item['url'], $arquivosParaRemover)) {
                    $novaLista[] = $item; // Mantém o arquivo
                } else {
                    // Deleta arquivo físico
                    $caminhoFisico = __DIR__ . '/../public/' . $item['url'];
                    if (file_exists($caminhoFisico)) @unlink($caminhoFisico);
                }
            } 
            // LINKS DE TEXTO (tipo 'link') NÃO SÃO ADICIONADOS AQUI.
        }
        return $novaLista;
    };

    // A lista AGORA só contém arquivos físicos que foram mantidos
    $novosLinksPublicos = $filtrarMantidos($listaAtualPublica);
    $novosLinksPrivados = $filtrarMantidos($listaAtualPrivada);

    // 3. Adiciona Novos Links de Texto (Vindos do Form)
    // ESTA ETAPA AGORA ADICIONA TANTO OS LINKS NOVOS QUANTO OS ANTIGOS (que vieram no POST)
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
    
    // 4. Upload de NOVOS Arquivos (adicionados à lista que JÁ contém os arquivos antigos)
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

    // 5. Salva no Banco (Dados Principais e Arquivos)
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
    if (!empty($equipesParaSalvar)) { 
        $stmtEq = $pdo->prepare("INSERT INTO projeto_equipe (projeto_id, equipe_id) VALUES (?, ?)");
        foreach ($equipesParaSalvar as $eqId) {
            if($eqId) $stmtEq->execute([$id, $eqId]);
        }
    }

    echo json_encode(['ok' => true, 'mensagem' => 'Projeto atualizado com sucesso!']);

} catch (Exception $e) {
    http_response_code(400); 
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
?>