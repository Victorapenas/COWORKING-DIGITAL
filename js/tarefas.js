// ARQUIVO: js/tarefas.js (CORRIGIDO E SIMPLIFICADO)

document.addEventListener('DOMContentLoaded', () => {
    const modalTarefa = document.getElementById('modalTarefa');
    const formTarefa = document.getElementById('formCriarTarefa');
    
    // Função global para abrir o modal de tarefa (Criação ou Edição)
    window.openTarefaModal = function(projetoId, tarefaId) {
        if (!modalTarefa) return;
        
        // Reset e Configurações base
        formTarefa.reset();
        document.getElementById('tarefaProjetoId').value = projetoId;
        document.getElementById('tarefaId').value = ""; // Limpa para nova tarefa
        document.getElementById('modalTarefaTitle').innerText = "Nova Tarefa";

        if (tarefaId) {
            // Se for edição: busca os dados da tarefa e preenche o formulário
            document.getElementById('modalTarefaTitle').innerText = "Editar Tarefa";
            document.getElementById('tarefaId').value = tarefaId;
            
            // **API para BUSCAR detalhes**
            fetch('../api/tarefa_buscar.php?id=' + tarefaId)
                .then(resp => resp.json())
                .then(data => {
                    if (data.ok && data.tarefa) {
                        const t = data.tarefa;
                        document.getElementById('nomeTarefa').value = t.titulo;
                        document.getElementById('descricaoTarefa').value = t.descricao;
                        document.getElementById('responsavelTarefa').value = t.responsavel_id;
                        
                        // CORREÇÃO APLICADA AQUI: ID mudado de 'prioridade' para 'prioridadeTarefa'
                        document.getElementById('prioridadeTarefa').value = t.prioridade; 
                        
                        document.getElementById('prazoTarefa').value = t.prazo;
                        document.getElementById('statusTarefa').value = t.status;
                        
                        // **NOTA:** A lógica do Checklist está faltando nesta versão.
                        // Use a versão de tarefa.php ou complete a lógica aqui.
                        
                    } else {
                        alert("Erro ao carregar detalhes da tarefa: " + (data.erro || "Desconhecido"));
                        closeModal('modalTarefa'); // Fecha em caso de erro
                    }
                })
                .catch(err => {
                    // MANTÉM A MENSAGEM DE ERRO (Erro de conexão ao buscar tarefa)
                    alert("Erro de conexão ao buscar tarefa: " + err.message);
                    closeModal('modalTarefa'); // Fecha em caso de erro
                });
        }
        
        modalTarefa.style.display = 'flex';
    };

    // Função auxiliar para fechar qualquer modal (Deixa aqui caso seja usada em outros lugares)
    window.closeModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    };
});