// ARQUIVO: js/tarefas.js
// ATUALIZADO: Funções Globais de Modal e Correção de Horário

document.addEventListener('DOMContentLoaded', () => {
    // Garante que o form de tarefas seja interceptado
    const formTarefa = document.getElementById('formCriarTarefa');
    // Verifica se a função handleTarefaSubmit (definida em tarefa.php) existe antes de anexar
    if (formTarefa && typeof window.handleTarefaSubmit === 'function') {
        formTarefa.addEventListener('submit', window.handleTarefaSubmit); 
    }
});

// --- FUNÇÕES GLOBAIS (window.) ---

// Função para fechar QUALQUER modal pelo ID
window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        // Remove hash da URL se houver (opcional, para limpar URL)
        // history.pushState("", document.title, window.location.pathname + window.location.search);
    } else {
        console.error("Modal não encontrado: " + modalId);
    }
};

// Função para abrir o modal de Tarefa (Criação/Edição)
window.openTarefaModal = function(projetoId, tarefaId) {
    const modal = document.getElementById('modalTarefa');
    if (!modal) {
        console.error("Modal de tarefa não encontrado no DOM.");
        return;
    }
    
    const form = document.getElementById('formCriarTarefa');
    if (form) form.reset();
    
    // Limpa checklist anterior (função definida em tarefa.php)
    if(typeof window.resetChecklist === 'function') window.resetChecklist();

    document.getElementById('tarefaProjetoId').value = projetoId || '';
    document.getElementById('tarefaId').value = tarefaId || "";
    document.getElementById('modalTarefaTitle').innerText = tarefaId ? "Editar Tarefa" : "Nova Tarefa";

    if (tarefaId) {
        // MODO EDIÇÃO: Busca dados
        fetch('../api/tarefa_buscar.php?id=' + tarefaId)
            .then(resp => resp.json())
            .then(data => {
                if (data.ok && data.tarefa) {
                    const t = data.tarefa;
                    document.getElementById('nomeTarefa').value = t.titulo;
                    document.getElementById('descricaoTarefa').value = t.descricao;
                    document.getElementById('responsavelTarefa').value = t.responsavel_id;
                    
                    // Ajuste para o ID correto do select de prioridade
                    const elPrioridade = document.getElementById('prioridadeTarefa');
                    if(elPrioridade) elPrioridade.value = t.prioridade;
                    
                    // CORREÇÃO DATA: Formata para o input datetime-local (YYYY-MM-DDTHH:MM)
                    if(t.prazo) {
                        const dateObj = new Date(t.prazo);
                        // Ajuste manual de fuso horário para garantir exibição correta no input local
                        dateObj.setMinutes(dateObj.getMinutes() - dateObj.getTimezoneOffset());
                        document.getElementById('prazoTarefa').value = dateObj.toISOString().slice(0,16);
                    }
                    
                    document.getElementById('statusTarefa').value = t.status === 'A_FAZER' ? 'PENDENTE' : t.status;

                    // Carrega Checklist
                    try {
                        const list = JSON.parse(t.checklist);
                        if(Array.isArray(list) && list.length > 0) {
                            if(typeof window.adicionarItemChecklist === 'function') {
                                list.forEach(item => window.adicionarItemChecklist(item));
                            }
                        } else {
                            if(typeof window.adicionarItemChecklist === 'function') window.adicionarItemChecklist();
                        }
                    } catch(e) {
                        if(typeof window.adicionarItemChecklist === 'function') window.adicionarItemChecklist();
                    }
                } else {
                    alert("Erro ao carregar detalhes: " + (data.erro || "Erro desconhecido"));
                }
            })
            .catch(err => {
                console.error("Erro ao buscar tarefa", err);
                alert("Erro de conexão ao buscar tarefa.");
            });
    } else {
        // MODO CRIAÇÃO: Adiciona um item vazio no checklist
        if(typeof window.adicionarItemChecklist === 'function') window.adicionarItemChecklist();
    }
    
    modal.style.display = 'flex';
};