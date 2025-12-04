// ARQUIVO: js/tarefas.js

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
                        document.getElementById('prioridade').value = t.prioridade;
                        document.getElementById('prazoTarefa').value = t.prazo;
                        document.getElementById('statusTarefa').value = t.status;
                    } else {
                        alert("Erro ao carregar detalhes da tarefa: " + (data.erro || "Desconhecido"));
                        closeModal('modalTarefa'); // Fecha em caso de erro
                    }
                })
                .catch(err => {
                    alert("Erro de conexão ao buscar tarefa: " + err.message);
                    closeModal('modalTarefa'); // Fecha em caso de erro
                });
        }
        
        modalTarefa.style.display = 'flex';
    };

    // Função auxiliar para fechar qualquer modal
    window.closeModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    };

    // Submissão do Formulário de Tarefa
    if (formTarefa) {
        formTarefa.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = formTarefa.querySelector('button[type="submit"]');
            const txtOriginal = btn.textContent;
            btn.disabled = true; btn.textContent = "Salvando...";

            try {
                const formData = new FormData(formTarefa);
                const id = document.getElementById('tarefaId').value;
                
                // Escolhe o endpoint: Criar se não houver ID, Editar se houver
                const endpoint = id ? '../api/tarefa_editar.php' : '../api/tarefa_criar.php';

                const resp = await fetch(endpoint, { method: 'POST', body: formData });
                let json;
                try { json = await resp.json(); } catch (parseErr) { throw new Error("Resposta inválida do servidor (JSON)."); }

                if (json.ok) {
                    alert(id ? "Tarefa atualizada com sucesso!" : "Tarefa criada com sucesso!");
                    window.location.reload(); // Recarrega para exibir a nova lista
                } else {
                    alert(json.erro || "Erro ao salvar tarefa.");
                }
            } catch (err) {
                alert("Erro: " + err.message);
            } finally {
                btn.disabled = false;
                btn.textContent = txtOriginal;
            }
        });
    }
});