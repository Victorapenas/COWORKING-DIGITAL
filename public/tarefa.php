<?php
// ARQUIVO: public/tarefa.php
// =============================================================================
// CONTEÚDO DO MODAL DE CRIAÇÃO/EDIÇÃO DE TAREFAS
// Adaptado para funcionar tanto em Projetos quanto no Dashboard
// =============================================================================

// PREVENÇÃO DE ERRO:
// Se as variáveis não existirem (uso no Dashboard), definimos valores vazios padrão
$idProjetoContexto = isset($id) ? $id : '';
$listaMembrosContexto = isset($membrosProjeto) ? $membrosProjeto : [];
?>

<div id="modalTarefa" class="modal">
    <div class="modal-content" style="width: 500px;">
        <span class="close-btn" onclick="closeModal('modalTarefa')">&times;</span>
        <h3 id="modalTarefaTitle" style="margin-top: 0; color: #2b3674;">Nova Tarefa</h3>

        <form id="formCriarTarefa">
            <input type="hidden" name="projeto_id" id="tarefaProjetoId" value="<?= $idProjetoContexto ?>">
            <input type="hidden" name="id" id="tarefaId" value="">

            <div class="form-group">
                <label for="nomeTarefa">Nome da Tarefa:</label>
                <input type="text" id="nomeTarefa" name="nome" required class="campo-padrao">
            </div>

            <div class="form-group">
                <label for="descricaoTarefa">Descrição:</label>
                <textarea id="descricaoTarefa" name="descricao" rows="3" class="campo-padrao"></textarea>
            </div>
            
            <div style="display:flex; gap: 20px;">
                <div style="flex:1;">
                    <div class="form-group">
                        <label for="responsavelTarefa">Responsável:</label>
                        <select id="responsavelTarefa" name="responsavel_id" required class="campo-padrao">
                            <option value="">Selecione...</option>
                            <?php 
                            // LÓGICA CORRIGIDA (HIERARQUIA HÍBRIDA):
                            // Removemos o 'if' que bloqueava CEO/Gestor.
                            // Agora todos aparecem, pois líderes também podem executar tarefas.
                            if (!empty($listaMembrosContexto)) {
                                foreach ($listaMembrosContexto as $membro): 
                                    // Adiciona o cargo visualmente para facilitar a escolha
                                    $cargoLabel = !empty($membro['cargo_detalhe']) ? " (" . $membro['cargo_detalhe'] . ")" : "";
                            ?>
                                <option value="<?= $membro['id'] ?>">
                                    <?= htmlspecialchars($membro['nome']) . htmlspecialchars($cargoLabel) ?>
                                </option>
                            <?php 
                                endforeach; 
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div style="flex:1;">
                    <div class="form-group">      
                        <label for="prioridade">Prioridade:</label>
                            <select id="prioridade" name="prioridade" required class="campo-padrao">
                                <option value="NORMAL">Normal</option>
                                <option value="IMPORTANTE">Importante</option>
                                <option value="URGENTE">Urgente</option>
                            </select>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap: 20px;">
                <div style="flex:1;">
                    <div class="form-group">
                        <label for="prazoTarefa">Prazo:</label>
                        <input type="date" id="prazoTarefa" name="prazo" class="campo-padrao">
                    </div>
                </div>
                <div style="flex:1;">
                    <div class="form-group">
                        <label for="statusTarefa">Status:</label>
                        <select id="statusTarefa" name="status" required class="campo-padrao">
                            <option value="ABERTO">Em Aberto</option>
                            <option value="EM_ANDAMENTO">Em Andamento</option>
                            <option value="CONCLUIDA">Concluída</option>
                            <option value="CANCELADA">Cancelada</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="botao-secundario" onclick="closeModal('modalTarefa')">Cancelar</button>
                <button type="submit" class="botao-primario">Salvar Tarefa</button>
            </div>
        </form>
    </div>
</div>