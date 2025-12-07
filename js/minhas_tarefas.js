// ARQUIVO: js/minhas_tarefas.js

function abrirModalExecucao(tarefa) {
    const modal = document.getElementById('modalExecucao');
    if (!modal) return;

    // Preenche dados para execução (Visualização do Colaborador)
    document.getElementById('execId').value = tarefa.id;
    document.getElementById('execTitulo').innerText = tarefa.titulo;
    document.getElementById('execDesc').innerText = tarefa.descricao || "Sem descrição detalhada.";
    
    document.getElementById('execStatus').value = tarefa.status;
    document.getElementById('execProgresso').value = tarefa.progresso;
    document.getElementById('lblProg').innerText = tarefa.progresso + "%";

    // Configura Badge de Status
    const badge = document.getElementById('execBadge');
    badge.innerText = tarefa.status.replace('_', ' ');
    // Remove classes antigas
    badge.classList.remove('bg-pendente', 'bg-progresso', 'bg-concluido', 'bg-atrasada');
    
    // Adiciona classe nova baseada no status
    let cls = 'bg-pendente';
    if(tarefa.status === 'EM_ANDAMENTO') cls = 'bg-progresso';
    if(tarefa.status === 'CONCLUIDA') cls = 'bg-concluido';
    badge.classList.add(cls);

    // Reset upload UI
    document.getElementById('uploadText').innerText = "Clique para anexar arquivo";
    document.getElementById('fileInput').value = "";

    modal.style.display = 'flex';
}

function previewUpload(input) {
    if (input.files && input.files[0]) {
        document.getElementById('uploadText').innerHTML = 
            `<strong>Arquivo: ${input.files[0].name}</strong>`;
    }
}

// Submissão do Progresso (Trabalho na Tarefa)
document.getElementById('formEntrega').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const txt = btn.innerText;
    btn.disabled = true; btn.innerText = "Salvando...";

    try {
        const formData = new FormData(this);
        const resp = await fetch('../api/tarefa_entregar.php', { method: 'POST', body: formData });
        const json = await resp.json();

        if (json.ok) {
            alert("Progresso salvo com sucesso!");
            window.location.reload();
        } else {
            alert(json.erro || "Erro ao salvar.");
        }
    } catch (err) {
        alert("Erro de conexão.");
    } finally {
        btn.disabled = false; btn.innerText = txt;
    }
});

// Fechar modal ao clicar fora
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(m => {
        if (event.target == m) m.style.display = "none";
    });
}