// /js/equipes.js
//atualização

// === LÓGICA DE EXCLUSÃO/RESTAURAÇÃO DE MEMBROS ===
async function confirmarExclusaoMembro(tipo, idDireto = null) {
    let id = idDireto;
    if (!id) {
        id = document.getElementById('idMemDel').value;
    }

    if(!id) {
        alert("Erro: ID não encontrado."); 
        return;
    }

    if (idDireto) {
        let mensagem = "Tem certeza?";
        if (tipo === 'hard') mensagem = "ATENÇÃO: Isso excluirá o usuário permanentemente e é irreversível. Continuar?";
        if (tipo === 'restore') mensagem = "Deseja restaurar este membro para a equipe?";
        
        if (!confirm(mensagem)) return;
    }

    let btn = null;
    if (event && event.target) {
        btn = event.target.closest('button');
        if(btn) {
            btn.innerHTML = "..."; 
            btn.disabled = true;
        }
    }

    try {
        const resp = await fetch('../api/admin_excluir_membro.php', {
            method: 'POST', 
            headers: {'Content-Type':'application/json'}, 
            body: JSON.stringify({id, tipo}) 
        });
        const json = await resp.json();
        
        if(json.ok) {
            window.location.reload(); 
        } else { 
            alert(json.erro || "Erro ao processar"); 
            if(btn) btn.disabled = false; 
        }
    } catch(e) { 
        alert("Erro de conexão."); 
        if(btn) btn.disabled = false;
    } finally {
        fecharModais();
    }
}

// === MOVER MEMBRO ===
const formMove = document.getElementById('formMoveMember');
if(formMove) {
    formMove.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = formMove.querySelector('button[type="submit"]');
        btn.disabled = true; btn.textContent = "Movendo...";

        const dados = {
            usuario_id: document.getElementById('select_membro_existente').value,
            equipe_id: document.getElementById('select_equipe_destino_move').value
        };

        try {
            const resp = await fetch('../api/admin_mover_membro.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(dados)
            });
            const json = await resp.json();
            
            if(json.ok) {
                alert("Membro movido com sucesso!");
                window.location.reload();
            } else {
                alert(json.erro);
            }
        } catch(err) {
            alert("Erro ao conectar.");
        } finally {
            btn.disabled = false; btn.textContent = "Mover Membro";
        }
    });
}

// === CRIAÇÃO DE MEMBRO ===
const formMembro = document.getElementById('formAddMember');
if(formMembro) {
    formMembro.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = formMembro.querySelector('button[type="submit"]');
        btn.disabled = true; btn.textContent = "Salvando...";
        
        const dados = {
            nome: document.getElementById('novo_nome').value,
            email: document.getElementById('novo_email').value,
            funcao: document.getElementById('novo_cargo').value,
            equipe_id: document.getElementById('select_equipe_membro').value,
            papel: formMembro.querySelector('input[name="nivel_acesso"]:checked').value
        };
        
        try {
            const resp = await fetch('../api/admin_adicionar_membro.php', { 
                method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dados) 
            });
            const json = await resp.json();
            if(json.ok) { 
                alert(`Sucesso! Senha gerada: ${json.senha_gerada}`); 
                window.location.reload(); 
            } else { 
                alert(json.erro); 
            }
        } catch(err) { alert("Erro."); } finally { btn.disabled = false; btn.textContent = "Salvar Cadastro"; }
    });
}

// === EDIÇÃO DE MEMBRO ===
const formEditar = document.getElementById('formEditarMembro');
if(formEditar) {
    formEditar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const dados = {
            id: document.getElementById('edit_id').value,
            nome: document.getElementById('edit_nome').value,
            email: document.getElementById('edit_email').value,
            funcao: document.getElementById('edit_cargo').value,
            papel: formEditar.querySelector('input[name="edit_papel"]:checked').value
        };
        try {
            const resp = await fetch('../api/admin_editar_membro.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dados) });
            const json = await resp.json();
            if(json.ok) { window.location.reload(); } else { alert(json.erro); }
        } catch(err) { alert("Erro."); }
    });
}

// === CRIAÇÃO DE EQUIPE ===
const formEquipe = document.getElementById('formCriarEquipe');
if(formEquipe) {
    formEquipe.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = formEquipe.querySelector('button[type="submit"]');
        btn.disabled = true;
        const dados = {
            nome_equipe: document.getElementById('nome_nova_equipe').value,
            descricao: document.getElementById('desc_nova_equipe').value
        };
        try {
            const resp = await fetch('../api/admin_criar_equipe.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dados) });
            const json = await resp.json();
            if(json.ok) { window.location.reload(); } else { alert(json.erro); }
        } catch(e) { alert("Erro."); } finally { btn.disabled = false; }
    });
}

// === EXCLUSÃO DE EQUIPE (FUNÇÃO QUE ABRE O MODAL) ===
function confirmarExclusaoEquipe(id, nome) {
    const inputId = document.getElementById('idEquipeDel');
    const msg = document.getElementById('msgDelEq');
    const modal = document.getElementById('modalExcluirEquipe');
    
    if (inputId && msg && modal) {
        inputId.value = id;
        msg.innerText = "Você tem certeza que deseja excluir a equipe " + nome + "?";
        modal.style.display = 'flex';
    } else {
        console.error("Erro: Modal de excluir equipe não encontrado.");
    }
}

// === EXCLUSÃO DE EQUIPE (CONFIRMAÇÃO) ===
async function confirmarDelEquipe() {
    const id = document.getElementById('idEquipeDel').value;
    
    // Ignoramos o checkbox visualmente, pois a lógica agora é sempre "mover para geral" (NULL)
    // Mas mantemos a variavel caso queira usar no futuro
    const check = document.getElementById('checkDelMembros');
    const delMembros = check ? check.checked : false; 

    try {
        const resp = await fetch('../api/admin_excluir_equipe.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id, deletar_membros: delMembros }) // Mandamos false por padrão se não tiver checkbox
        });
        const json = await resp.json();
        if(json.ok) {
            window.location.reload(); 
        } else { 
            alert(json.erro); 
        }
    } catch(e) { 
        alert("Erro de conexão."); 
    }
}