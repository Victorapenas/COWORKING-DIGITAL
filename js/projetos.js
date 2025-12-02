// ARQUIVO: js/projetos.js

document.addEventListener('DOMContentLoaded', () => {
    const modalProjeto = document.getElementById('modalProjeto');
    const modalDel = document.getElementById('modalExcluir');

    // --- FUNÇÃO SEGURA (CORRIGIDA) ---
    window.openModal = function() { 
        if (modalProjeto) {
            modalProjeto.style.display = 'flex';
            
            // Reset do Formulário
            const form = document.getElementById('formCriarProjeto');
            if(form) form.reset();
            
            // Título e ID
            const title = document.getElementById('modalTitle');
            if(title) title.innerText = "Novo Projeto";
            
            const pId = document.getElementById('projId');
            if(pId) pId.value = ""; 
            
            // Limpa prévia de logo (SE EXISTIR)
            const logoPrev = document.getElementById('logo_preview');
            if(logoPrev) logoPrev.innerText = "";

            // Limpa links públicos (SE EXISTIR)
            const linkPub = document.getElementById('containerLinksPublicos');
            if(linkPub) linkPub.innerHTML = "";
            
            // --- CORREÇÃO DO ERRO ---
            // Só tenta limpar se o elemento existir (Gestores não têm esse elemento)
            const linkPriv = document.getElementById('containerLinksPrivados');
            if(linkPriv) linkPriv.innerHTML = "";

            // Limpa Dropdown de Equipes
            const hiddenInputs = document.getElementById('hiddenEquipesInputs');
            if(hiddenInputs) hiddenInputs.innerHTML = ""; 
            
            const triggerText = document.getElementById('equipeTriggerText');
            if(triggerText) {
                triggerText.textContent = "Selecione as equipes...";
                triggerText.style.color = "#999";
                triggerText.style.fontWeight = "400";
            }

            // Remove classe 'selected' das opções
            document.querySelectorAll('.custom-option').forEach(op => op.classList.remove('selected'));

            // Reseta abas
            const firstTab = document.querySelector('.modal-tab');
            if(firstTab) switchFormTab('info', firstTab);
        }
    }

    window.abrirModalEditarProjeto = function(proj) {
        if (modalProjeto) {
            modalProjeto.style.display = 'flex';
            const title = document.getElementById('modalTitle');
            if(title) title.innerText = "Editar Projeto";
            
            // Preencher Campos (com verificação de existência)
            const setVal = (id, val) => { const el = document.getElementById(id); if(el) el.value = val; };
            
            setVal('projId', proj.id);
            setVal('projNome', proj.nome);
            setVal('projCliente', proj.cliente_nome || '');
            setVal('projDesc', proj.descricao || '');
            setVal('projInicio', proj.data_inicio || '');
            setVal('projFim', proj.data_fim || '');
            setVal('projStatus', proj.status);

            // --- LÓGICA DO DROPDOWN NA EDIÇÃO ---
            const hiddenInputs = document.getElementById('hiddenEquipesInputs');
            if(hiddenInputs) hiddenInputs.innerHTML = "";
            document.querySelectorAll('.custom-option').forEach(op => op.classList.remove('selected'));
            
            if (proj.equipes && Array.isArray(proj.equipes)) {
                proj.equipes.forEach(eq => {
                    const idBusca = (typeof eq === 'object') ? eq.id : eq;
                    const option = document.querySelector(`.custom-option[data-value="${idBusca}"]`);
                    if(option) option.classList.add('selected');
                });
                if(typeof atualizarInputsEquipe === 'function') atualizarInputsEquipe(); 
            }

            // Links Públicos
            const containerPub = document.getElementById('containerLinksPublicos');
            if(containerPub) {
                containerPub.innerHTML = "";
                if (proj.links && Array.isArray(proj.links)) {
                    proj.links.forEach(l => {
                        if (l.tipo === 'link') addLinkInput('containerLinksPublicos', false, l.titulo, l.url);
                    });
                }
            }

            // Links Privados (Só limpa se existir para o usuário logado)
            const containerPriv = document.getElementById('containerLinksPrivados');
            if (containerPriv) {
                containerPriv.innerHTML = "";
                if (proj.privados && Array.isArray(proj.privados)) {
                    proj.privados.forEach(l => {
                        if (l.tipo === 'link') addLinkInput('containerLinksPrivados', true, l.titulo, l.url);
                    });
                }
            }
            
            const firstTab = document.querySelector('.modal-tab');
            if(firstTab) switchFormTab('info', firstTab);
        }
    }

    // Restante das funções auxiliares
    window.closeModal = function() { if (modalProjeto) modalProjeto.style.display = 'none'; }
    window.fecharModalExcluir = function() { if (modalDel) modalDel.style.display = 'none'; }

    window.switchMainTab = function(tab, btn) {
        document.querySelectorAll('.main-tab-content').forEach(d => d.style.display = 'none');
        const view = document.getElementById('view-'+tab);
        if(view) view.style.display = 'block';
        document.querySelectorAll('.tabs-header .tab-btn').forEach(b => b.classList.remove('active'));
        if(btn) btn.classList.add('active');
    }

    window.switchFormTab = function(tab, btn) {
        document.querySelectorAll('#formCriarProjeto .tab-panel').forEach(p => p.classList.remove('active'));
        const target = document.getElementById('tab-'+tab);
        if(target) target.classList.add('active');
        document.querySelectorAll('#formCriarProjeto .modal-tab').forEach(b => b.classList.remove('active'));
        if(btn) btn.classList.add('active');
    }

    window.previewFile = function(input) {
        const preview = document.getElementById('logo_preview');
        if(preview && input.files && input.files[0]) preview.innerText = "Arquivo: " + input.files[0].name;
    }

    window.filtrarProjetos = function() {
        const term = document.getElementById('searchBox').value.toLowerCase();
        document.querySelectorAll('.proj-card').forEach(card => {
            card.style.display = card.innerText.toLowerCase().includes(term) ? 'flex' : 'none';
        });
    }

    window.abrirModalAcao = function(id, nome, acao) {
        if (modalDel) {
            document.getElementById('idProjetoExcluir').value = id;
            document.getElementById('msgExcluir').innerText = `O que deseja fazer com o projeto "${nome}"?`;
            
            const btnSoft = document.getElementById('btnSoftDelete');
            const btnHard = document.getElementById('btnHardDelete');
            
            if (acao === 'active_context') {
                if(btnSoft) btnSoft.style.display = 'flex';
                if(btnHard) btnHard.style.display = 'flex'; 
            } 
            modalDel.style.display = 'flex';
        }
    }

    window.confirmarAcaoProjeto = async function(id, tipo) {
        if (tipo === 'hard' && !confirm("ATENÇÃO: A exclusão definitiva apagará tudo. Tem certeza?")) return;
        if (!id) id = document.getElementById('idProjetoExcluir').value;

        try {
            const resp = await fetch('../api/projeto_excluir.php', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id, tipo: tipo })
            });
            const json = await resp.json();
            if (json.ok) window.location.reload();
            else alert(json.erro || "Erro ao processar.");
        } catch (err) { alert("Erro de conexão."); } finally { window.fecharModalExcluir(); }
    }

    window.addLinkInput = function(containerId, isPrivado = false, valTitulo = '', valUrl = '') {
        const container = document.getElementById(containerId);
        if(!container) return; // Segurança extra

        const nameTit = isPrivado ? 'link_priv_titulo[]' : 'link_titulo[]';
        const nameUrl = isPrivado ? 'link_priv_url[]' : 'link_url[]';
        const div = document.createElement('div');
        div.style.cssText = 'display:flex; gap:10px; margin-bottom:5px; align-items:center;';
        div.innerHTML = `
            <input type="text" name="${nameTit}" value="${valTitulo}" placeholder="Título" style="flex:1; padding:10px; border:1px solid #eee; border-radius:8px;">
            <input type="text" name="${nameUrl}" value="${valUrl}" placeholder="URL" style="flex:2; padding:10px; border:1px solid #eee; border-radius:8px;">
            <button type="button" onclick="this.parentElement.remove()" style="border:none; background:none; cursor:pointer; color:#e74c3c; font-size:1.2rem;">&times;</button>
        `;
        container.appendChild(div);
    }

    const formProj = document.getElementById('formCriarProjeto');
    if (formProj) {
        formProj.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = formProj.querySelector('button[type="submit"]');
            const txtOriginal = btn.textContent;
            btn.disabled = true; btn.textContent = "Salvando...";

            try {
                const formData = new FormData(formProj);
                const id = document.getElementById('projId').value;
                const endpoint = id ? '../api/projeto_editar.php' : '../api/projeto_criar.php';

                const resp = await fetch(endpoint, { method: 'POST', body: formData });
                let json;
                try { json = await resp.json(); } catch (parseErr) { throw new Error("Resposta inválida do servidor (JSON)."); }

                if (json.ok) window.location.reload();
                else alert(json.erro || "Erro ao salvar projeto.");
            } catch (err) { alert("Erro: " + err.message); } finally {
                btn.disabled = false; btn.textContent = txtOriginal;
            }
        });
    }
});