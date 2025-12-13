// ARQUIVO: js/projetos.js
//atualização

document.addEventListener('DOMContentLoaded', () => {
    const modalProjeto = document.getElementById('modalProjeto');
    const modalDel = document.getElementById('modalExcluir');

    // --- FUNÇÃO PARA ABRIR MODAL DE NOVO PROJETO ---
    window.openModal = function() { 
        if (modalProjeto) {
            modalProjeto.style.display = 'flex';
            
            // Reset do Formulário
            const form = document.getElementById('formCriarProjeto');
            if(form) form.reset();
            
            // Limpa inputs ocultos de remoção de arquivo (se houver de edições anteriores)
            document.querySelectorAll('.input-remove-file').forEach(e => e.remove());
            
            // Título e ID
            const title = document.getElementById('modalTitle');
            if(title) title.innerText = "Novo Projeto";
            
            const pId = document.getElementById('projId');
            if(pId) pId.value = ""; 
            
            // Limpa prévia de logo
            const logoPrev = document.getElementById('logo_preview');
            if(logoPrev) logoPrev.innerText = "";

            // Limpa links públicos e container de arquivos
            const linkPub = document.getElementById('containerLinksPublicos');
            if(linkPub) linkPub.innerHTML = "";
            const arqPub = document.getElementById('arquivosAtuaisPublicos');
            if(arqPub) arqPub.innerHTML = "";
            
            // Limpa links privados e container de arquivos privados
            const linkPriv = document.getElementById('containerLinksPrivados');
            if(linkPriv) linkPriv.innerHTML = "";
            const arqPriv = document.getElementById('arquivosAtuaisPrivados');
            if(arqPriv) arqPriv.innerHTML = "";

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

            // Reseta abas para a primeira
            const firstTab = document.querySelector('.modal-tab');
            if(firstTab) switchFormTab('info', firstTab);
        }
    }

    // --- FUNÇÃO PARA ABRIR MODAL DE EDIÇÃO ---
    // Adicionado parâmetro opcional abaInicial e tituloContexto
    window.abrirModalEditarProjeto = function(proj, abaInicial = 'info', tituloContexto = 'editar') {
        if (modalProjeto) {
            modalProjeto.style.display = 'flex';
            const title = document.getElementById('modalTitle');
            
            // Define o título do modal com base no contexto
            if (title) {
                if (tituloContexto === 'adicionar_arquivos') {
                    title.innerText = "Adicionar Arquivos/Links";
                } else {
                    title.innerText = "Editar Projeto";
                }
            }
            
            // Preencher Campos
            const setVal = (id, val) => { const el = document.getElementById(id); if(el) el.value = val; };
            setVal('projId', proj.id);
            setVal('projNome', proj.nome);
            setVal('projCliente', proj.cliente_nome || '');
            setVal('projDesc', proj.descricao || '');
            setVal('projInicio', proj.data_inicio || '');
            setVal('projFim', proj.data_fim || '');
            setVal('projStatus', proj.status);

            // Resetar inputs de remoção de arquivos antigos
            document.querySelectorAll('.input-remove-file').forEach(e => e.remove());

            // --- PREENCHER EQUIPES (DROPDOWN) ---
            const hiddenInputs = document.getElementById('hiddenEquipesInputs');
            if(hiddenInputs) hiddenInputs.innerHTML = "";
            document.querySelectorAll('.custom-option').forEach(op => op.classList.remove('selected'));
            
            if (proj.equipes && Array.isArray(proj.equipes)) {
                proj.equipes.forEach(eq => {
                    // O backend pode retornar objeto {id, nome} ou apenas ID
                    const idBusca = (typeof eq === 'object') ? eq.id : eq;
                    const option = document.querySelector(`.custom-option[data-value="${idBusca}"]`);
                    if(option) option.classList.add('selected');
                });
                if(typeof atualizarInputsEquipe === 'function') atualizarInputsEquipe(); 
            }

            // --- RENDERIZAR ARQUIVOS EXISTENTES (PÚBLICOS) ---
            renderizarArquivosExistentes(proj.links, 'arquivosAtuaisPublicos');

            // --- RENDERIZAR ARQUIVOS EXISTENTES (PRIVADOS) ---
            renderizarArquivosExistentes(proj.privados, 'arquivosAtuaisPrivados');

            // --- RENDERIZAR LINKS DE TEXTO (PÚBLICOS) ---
            const containerPub = document.getElementById('containerLinksPublicos');
            if(containerPub) {
                containerPub.innerHTML = "";
                if (proj.links && Array.isArray(proj.links)) {
                    proj.links.forEach(l => {
                        if (l.tipo === 'link') addLinkInput('containerLinksPublicos', false, l.titulo, l.url);
                    });
                }
            }

            // --- RENDERIZAR LINKS DE TEXTO (PRIVADOS) ---
            const containerPriv = document.getElementById('containerLinksPrivados');
            if (containerPriv) {
                containerPriv.innerHTML = "";
                if (proj.privados && Array.isArray(proj.privados)) {
                    proj.privados.forEach(l => {
                        if (l.tipo === 'link') addLinkInput('containerLinksPrivados', true, l.titulo, l.url);
                    });
                }
            }
            
            // Selecionar a aba inicial
            const targetTabElement = document.querySelector(`#modalProjeto .modal-tab[onclick*="switchFormTab('${abaInicial}'"]`);
            if (targetTabElement) {
                // Chama a função global definida em projeto_detalhes.php
                switchFormTab(abaInicial, targetTabElement); 
            } else {
                // Fallback para a primeira aba (info)
                const firstTab = document.querySelector('.modal-tab');
                if(firstTab) switchFormTab('info', firstTab);
            }
        }
    }

    // --- NOVA FUNÇÃO AUXILIAR: LISTAR ARQUIVOS PARA EDIÇÃO/EXCLUSÃO ---
    window.renderizarArquivosExistentes = function(lista, containerId) {
        const container = document.getElementById(containerId);
        if(!container) return;
        
        container.innerHTML = ''; // Limpa lista anterior
        
        if (lista && Array.isArray(lista)) {
            let filesFound = false;
            lista.forEach(item => {
                // Filtra apenas o que é arquivo físico (ignora links e logos se necessário)
                if (item.tipo === 'arquivo' || item.tipo === 'logo') {
                    filesFound = true;
                    
                    const div = document.createElement('div');
                    div.style.cssText = 'display:flex; justify-content:space-between; align-items:center; background:white; padding:8px; margin-bottom:5px; border:1px solid #eee; border-radius:6px; font-size:0.85rem;';
                    
                    div.innerHTML = `
                        <div style="display:flex; align-items:center; gap:8px; overflow:hidden;">
                            <span style="color:#6A66FF;">📄</span>
                            <a href="${item.url}" target="_blank" style="text-decoration:none; color:#333; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:250px;">
                                ${item.titulo}
                            </a>
                        </div>
                        <button type="button" class="btn-remove-file" style="color:#e74c3c; background:none; border:none; cursor:pointer; font-weight:bold; font-size:0.8rem;">Excluir</button>
                    `;

                    // Lógica do botão excluir
                    const btn = div.querySelector('.btn-remove-file');
                    btn.onclick = function() {
                        // Efeito visual de riscado
                        div.style.opacity = '0.5';
                        div.style.textDecoration = 'line-through';
                        btn.remove(); // Remove o botão para não clicar de novo
                        
                        // Cria input hidden para avisar o PHP para remover este arquivo
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'remover_arquivos[]';
                        input.className = 'input-remove-file';
                        input.value = item.url; // O caminho do arquivo é a chave para exclusão
                        document.getElementById('formCriarProjeto').appendChild(input);
                    };

                    container.appendChild(div);
                }
            });
            
            if(!filesFound) {
                container.innerHTML = '<small style="color:#aaa;">Nenhum arquivo anexado.</small>';
            }
        }
    }

    // --- FUNÇÕES AUXILIARES DE UI ---
    window.closeModal = function() { if (modalProjeto) modalProjeto.style.display = 'none'; }
    window.fecharModalExcluir = function() { if (modalDel) modalDel.style.display = 'none'; }

    window.switchMainTab = function(tab, btn) {
        document.querySelectorAll('.main-tab-content').forEach(d => d.style.display = 'none');
        const view = document.getElementById('view-'+tab);
        if(view) view.style.display = 'block';
        document.querySelectorAll('.tabs-header .tab-btn').forEach(b => b.classList.remove('active'));
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
        if(!container) return; 

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

    // --- SUBMISSÃO DO FORMULÁRIO ---
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
                
                // Tenta decodificar o JSON com tratamento de erro
                let json;
                const textResp = await resp.text();
                try {
                    json = JSON.parse(textResp);
                } catch (parseErr) {
                    console.error("Resposta bruta do servidor:", textResp);
                    throw new Error("Resposta inválida do servidor. Verifique o console.");
                }

                if (json.ok) {
                    window.location.reload();
                } else {
                    alert(json.erro || "Erro desconhecido ao salvar projeto.");
                }
            } catch (err) { 
                alert("Erro: " + err.message); 
            } finally {
                btn.disabled = false; btn.textContent = txtOriginal;
            }
        });
    }
});