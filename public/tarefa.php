<?php
// ARQUIVO: public/tarefa.php
// =============================================================================
// CONTEÚDO DO MODAL DE CRIAÇÃO/EDIÇÃO DE TAREFAS
// Este arquivo é incluído em public/projeto_detalhes.php
// As variáveis $id (id do projeto) e $membrosProjeto devem estar disponíveis no escopo de inclusão.
// =============================================================================

// Verifica se as variáveis essenciais estão definidas
if (!isset($id) || !isset($membrosProjeto)) {
    // Para um arquivo de inclusão, um aviso é mais adequado que um 'die'
    // Mas para garantir a execução segura no contexto esperado:
    echo '<p style="color:red; font-weight:bold;">Erro: Variáveis essenciais para o modal de tarefas não foram definidas.</p>';
    return; // Interrompe a inclusão
}
?>

<div id="modalTarefa" class="modal">
    <div class="modal-content" style="width: 500px;">
        <span class="close-btn" onclick="closeModal('modalTarefa')">&times;</span>
        <h3 id="modalTarefaTitle" style="margin-top: 0;">Nova Tarefa</h3>

        <form id="formCriarTarefa">
            <input type="hidden" name="projeto_id" id="tarefaProjetoId" value="<?php echo $id; ?>">
            <input type="hidden" name="id" id="tarefaId" value="">

            <div class="form-group">
                <label for="nomeTarefa">Nome da Tarefa:</label>
                <input type="text" id="nomeTarefa" name="nome" required>
            </div>

            <div class="form-group">
                <label for="descricaoTarefa">Descrição:</label>
                <textarea id="descricaoTarefa" name="descricao" rows="3"></textarea>
            </div>
            
            <div style="display:flex; gap: 20px;">
                <div style="flex:1;">
                    <div class="form-group">
                        <label for="responsavelTarefa">Responsável:</label>
                        <select id="responsavelTarefa" name="responsavel_id" required>
                            <option value="">Selecione um membro...</option>
                            <?php 
                            // Lista apenas membros que não são CEO ou Gestor para serem responsáveis por tarefas
                            foreach ($membrosProjeto as $membro): 
                                if(!in_array($membro['cargo_detalhe'], ['CEO', 'Gestor'])): // Filtro mantido
                            ?>
                                <option value="<?= $membro['id'] ?>"><?= htmlspecialchars($membro['nome']) ?></option>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </select>
                    </div>
                </div>
                <div style="flex:1;">
                    <div class="form-group">      
                        <label for="prioridade">Prioridade:</label>
                            <select id="prioridade" name="prioridade" required>
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
                        <input type="date" id="prazoTarefa" name="prazo">
                    </div>
                </div>
                <div style="flex:1;">
                    <div class="form-group">
                        <label for="statusTarefa">Status:</label>
                        <select id="statusTarefa" name="status" required>
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