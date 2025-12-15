// --- DADOS PADRÃO (Substituindo o que o PHP fazia) ---
const DADOS_PADRAO = {
    rotulos: ["Perigoso", "Aberração", "Salvador", "Superior", "Mundano"],
    playbooks: ["A Janus", "O Delinquente", "O Nova", "A Discípula", "O Transformado", "A Forasteira", "O Legado", "A Amaldiçoada", "O Farol", "A Bruta"],
    condicoes: {
        "amedrontado": "ASSUSTADE (-2 p/ Engajar)",
        "irritado": "IRRITADE (-2 p/ Provocar/Avaliar)",
        "culpado": "CULPADE (-2 p/ Provocar)",
        "desesperado": "DESESPERANÇOSE (-2 p/ Defender)",
        "inseguro": "INSEGURE (-2 p/ Rejeitar)"
    },
    movimentos: [
        { titulo: "ENFRENTAR DIRETAMENTE A AMEAÇA (Role + Perigoso)", descricao: "Num sucesso, vocês trocam golpes. Num 10+, escolha dois. Num 7-9, escolha um:\n- resistir ou evitar os golpes\n- tirar algo do oponente\n- criar uma oportunidade para os aliados\n- impressionar, surpreender ou amedrontar a oposição" },
        { titulo: "LIBERAR SEUS PODERES (Role + Estranho)", descricao: "Para superar um obstáculo, remodelar o ambiente ou ampliar os sentidos. Num sucesso, você consegue. Num 7-9, marque uma condição ou o MJ dirá como o efeito é instável ou temporário." },
        { titulo: "DEFENDER (Role + Salvador)", descricao: "Defender algo ou alguém de uma ameaça iminente. PNJ: 10+ mantém seguras e escolhe uma. 7-9 expõe-se ao perigo ou intensifica. Opções: adicionar ponto à Equipe, ganhar Influência sobre protegido, remover uma condição.\nPJ: 10+ dê -2 na jogada dele. 7-9 expõe-se a custo ou retribuição." },
        { titulo: "AVALIAR A SITUAÇÃO (Role + Superior)", descricao: "Num 10+, pergunte duas. Num 7-9, pergunte uma. Ganhe +1 ao agir conforme as respostas.\n- o que posso usar aqui para _____?\n- qual é a maior ameaça aqui?\n- o que aqui está em maior perigo?\n- quem aqui é mais vulnerável a mim?\n- como acabar logo com isso?" },
        { titulo: "PROVOCAR (Role + Superior)", descricao: "Diga o que quer que a pessoa faça. PNJ: 10+ eles mordem a isca e fazem, ou vacilam. 7-9 podem escolher um.\nPJ: 10+ ambos. 7-9 escolha um: se morderem a isca ganham +1 adiante contra você, se não morderem marcam uma condição." },
        { titulo: "CONFORTAR OU APOIAR (Role + Mundano)", descricao: "Para confortar ou apoiar alguém. Num sucesso, a pessoa ouve: marca potencial, limpa uma condição ou muda Rótulos se se abrir. Num 10+, você também pode adicionar ponto à Equipe ou limpar uma condição sua." },
        { titulo: "PERFURAR A MÁSCARA (Role + Mundano)", descricao: "Para ver a pessoa por trás. 10+ pergunte três. 7-9 pergunte uma.\n- o que você está planejando de verdade?\n- o que você quer que eu faça?\n- o que você pretende fazer?\n- como eu poderia levar você a _____?\n- como eu poderia ganhar Influência sobre você?" },
        { titulo: "TOMAR UM GOLPE PODEROSO (Role + Condições)", descricao: "Role + condições marcadas. 10+ escolha um:\n- afastar-se da situação\n- perder o controle dos poderes\n- duas opções da lista de 7-9.\nNum 7-9 escolha um: \n- você ataca verbalmente: provoca um companheiro de equipe a agir de forma imprudente ou tira proveito de sua Influência para infligir uma condição.\n- você cede terreno; a oposição ganha uma oportunidade.\n- você supera a dor; marque duas condições.\n- Numa falha, você se mantém firme. Marque potencial normalmente e diga como você resistiu ao golpe.." }
    ]
};

// --- GERENCIAMENTO DE ESTADO ---
let nomeFichaAtual = localStorage.getItem('maskrpg_ultima_ficha') || 'Herói Padrão';
let dadosFicha = {};

// --- INICIALIZAÇÃO ---
window.addEventListener('DOMContentLoaded', () => {
    gerarInterfaceEstatica();
    carregarListaFichas();
    carregarFicha(nomeFichaAtual);
});

// Gera o HTML que antes era feito pelo PHP (Rótulos, Playbooks, Checkboxes)
function gerarInterfaceEstatica() {
    // 1. Rótulos
    const tbodyRotulos = document.querySelector('#tabela-rotulos tbody');
    DADOS_PADRAO.rotulos.forEach(rotulo => {
        let tr = `<tr><td class="nome-rotulo">${rotulo.toUpperCase()}</td>`;
        for(let i = -2; i <= 3; i++) {
            tr += `<td><label class="seletor-valor">
                <input type="radio" name="rotulos[${rotulo}]" value="${i}">
                <span>${i}</span>
            </label></td>`;
        }
        tr += '</tr>';
        tbodyRotulos.insertAdjacentHTML('beforeend', tr);
    });

    // 2. Playbooks
    const selArquetipo = document.getElementById('select-arquetipo');
    DADOS_PADRAO.playbooks.forEach(pb => {
        selArquetipo.insertAdjacentHTML('beforeend', `<option value="${pb}">${pb}</option>`);
    });

    // 3. Potencial
    const divPotencial = document.getElementById('container-potencial');
    for(let i=1; i<=5; i++) {
        divPotencial.insertAdjacentHTML('beforeend', 
            `<label class="checkbox-box"><input type="checkbox" name="potencial[${i}]" value="1"><span></span></label>`
        );
    }

    // 4. Condições
    const divCondicoes = document.getElementById('lista-condicoes');
    for (const [key, texto] of Object.entries(DADOS_PADRAO.condicoes)) {
        divCondicoes.insertAdjacentHTML('beforeend', `
            <label class="checkbox-texto">
                <input type="checkbox" name="condicoes[${key}]" value="1">
                <span class="marcador"></span>
                <span class="texto">${texto}</span>
            </label>
        `);
    }
}

// --- SISTEMA DE SALVAMENTO (LOCALSTORAGE) ---
function salvarDados() {
    const form = document.getElementById('ficha-form');
    const formData = new FormData(form);
    
    // Converte FormData para Objeto JSON
    const objetoDados = {};
    
    // Tratamento especial para inputs normais
    formData.forEach((value, key) => {
        // Verifica se é um array (ex: habilidades[id][titulo])
        if (key.includes('[')) {
            // Lógica simplificada: salvar no objetoDados de forma plana ou estruturada
            // Para simplicidade no LocalStorage, vamos recriar a estrutura de listas separadamente
        } else {
            objetoDados[key] = value;
        }
    });

    // Salvar Rótulos
    objetoDados.rotulos = {};
    document.querySelectorAll('input[name^="rotulos"]:checked').forEach(radio => {
        const nomeRotulo = radio.name.match(/\[(.*?)\]/)[1];
        objetoDados.rotulos[nomeRotulo] = radio.value;
    });

    // Salvar Checkboxes (Potencial, Condições, Momento)
    objetoDados.potencial = {};
    document.querySelectorAll('input[name^="potencial"]:checked').forEach(chk => {
        const idx = chk.name.match(/\[(.*?)\]/)[1];
        objetoDados.potencial[idx] = 1;
    });

    objetoDados.condicoes = {};
    document.querySelectorAll('input[name^="condicoes"]:checked').forEach(chk => {
        const key = chk.name.match(/\[(.*?)\]/)[1];
        objetoDados.condicoes[key] = 1;
    });
    
    // Momento da Verdade
    const mvChk = document.querySelector('input[name="momento_verdade_usado"]');
    if(mvChk.checked) objetoDados.momento_verdade_usado = 1;

    // Salvar Listas (Movimentos e Habilidades)
    objetoDados.movimentos = lerListaDinamica('lista-movimentos', 'movimentos');
    objetoDados.habilidades = lerListaDinamica('lista-habilidades', 'habilidades');

    // Salvar Imagem
    objetoDados.imagem = document.getElementById('base64-imagem').value;

    // GRAVAR NO NAVEGADOR
    const chave = `maskrpg_ficha_${nomeFichaAtual}`;
    localStorage.setItem(chave, JSON.stringify(objetoDados));
    
    mostrarStatusSalvo();
}

function lerListaDinamica(idContainer, tipo) {
    const container = document.getElementById(idContainer);
    const itens = [];
    container.querySelectorAll(tipo === 'movimentos' ? '.movimento-item' : '.habilidade-item').forEach(div => {
        const titulo = div.querySelector('input').value;
        const descricao = div.querySelector('textarea').value;
        itens.push({ titulo, descricao });
    });
    return itens;
}

function mostrarStatusSalvo() {
    const status = document.getElementById('status-salvamento');
    status.style.display = 'block';
    setTimeout(() => { status.style.display = 'none'; }, 2000);
}

// Autosave Trigger
document.getElementById('ficha-form').addEventListener('input', () => {
    clearTimeout(window.saveTimeout);
    window.saveTimeout = setTimeout(salvarDados, 1000);
});

// --- CARREGAMENTO DE DADOS ---
function carregarFicha(nome) {
    nomeFichaAtual = nome;
    localStorage.setItem('maskrpg_ultima_ficha', nome); // Lembrar qual estava aberta
    document.getElementById('select-ficha').value = nome;
    
    const chave = `maskrpg_ficha_${nome}`;
    const json = localStorage.getItem(chave);
    
    if (json) {
        dadosFicha = JSON.parse(json);
    } else {
        // Se a ficha não existe, cria uma nova com base nos padrões
        dadosFicha = { movimentos: JSON.parse(JSON.stringify(DADOS_PADRAO.movimentos)) }; 
    }
    
    preencherFormulario();
}

function preencherFormulario() {
    const d = dadosFicha;
    const f = document.getElementById('ficha-form');

    // Campos de Texto Simples
    ['nome_real', 'nome_heroi', 'aparencia', 'influencia_sobre_mim', 'influencia_eu_tenho', 'texto_momento_verdade', 'anotacoes'].forEach(campo => {
        if(f.elements[campo]) f.elements[campo].value = d[campo] || '';
    });

    // Select
    if(d.arquetipo) f.elements['arquetipo'].value = d.arquetipo;

    // Rótulos (Radio)
    if(d.rotulos) {
        for(const [rotulo, valor] of Object.entries(d.rotulos)) {
            const radio = f.querySelector(`input[name="rotulos[${rotulo}]"][value="${valor}"]`);
            if(radio) radio.checked = true;
        }
    }

    // Checkboxes
    f.querySelectorAll('input[type="checkbox"]').forEach(chk => chk.checked = false); // Limpa tudo antes
    
    if(d.potencial) {
        for(const idx in d.potencial) {
            const chk = f.querySelector(`input[name="potencial[${idx}]"]`);
            if(chk) chk.checked = true;
        }
    }
    if(d.condicoes) {
        for(const key in d.condicoes) {
            const chk = f.querySelector(`input[name="condicoes[${key}]"]`);
            if(chk) chk.checked = true;
        }
    }
    if(d.momento_verdade_usado) {
        f.querySelector('input[name="momento_verdade_usado"]').checked = true;
    }

    // Listas Dinâmicas
    renderizarLista('lista-movimentos', d.movimentos || [], 'movimentos');
    renderizarLista('lista-habilidades', d.habilidades || [], 'habilidades');

    // Imagem
    const imgBase64 = d.imagem || '';
    document.getElementById('base64-imagem').value = imgBase64;
    const preview = document.getElementById('preview-img');
    const aviso = document.getElementById('aviso-foto');
    if(imgBase64) {
        preview.src = imgBase64;
        preview.style.display = 'block';
        aviso.style.display = 'none';
    } else {
        preview.style.display = 'none';
        aviso.style.display = 'block';
    }
}

// Renderiza Habilidades e Movimentos
function renderizarLista(idContainer, itens, tipo) {
    const container = document.getElementById(idContainer);
    container.innerHTML = ''; // Limpa
    
    itens.forEach(item => {
        const idUnico = Date.now() + Math.random();
        const altura = tipo === 'movimentos' ? '140px' : '120px';
        const classe = tipo === 'movimentos' ? 'movimento-item' : 'habilidade-item';
        const labelTitulo = tipo === 'movimentos' ? 'Movimento:' : 'Título:';
        
        const html = `
            <div class="${classe}">
                <button type="button" class="btn-remove" onclick="removerItem(this)">X</button>
                <label>${labelTitulo}</label>
                <input type="text" value="${item.titulo || ''}" oninput="salvarDados()">
                <label style="margin-top:5px;">Descrição:</label>
                <textarea style="height:${altura};" oninput="salvarDados()">${item.descricao || ''}</textarea>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    });
}

function adicionarItem(tipo) {
    const containerId = tipo === 'movimentos' ? 'lista-movimentos' : 'lista-habilidades';
    const listaAtual = lerListaDinamica(containerId, tipo);
    listaAtual.push({ titulo: "", descricao: "" });
    renderizarLista(containerId, listaAtual, tipo);
    salvarDados();
}

function removerItem(btn) {
    if(!confirm('Apagar item?')) return;
    btn.parentElement.remove();
    salvarDados();
}

// --- GERENCIADOR DE FICHAS ---
function carregarListaFichas() {
    const select = document.getElementById('select-ficha');
    select.innerHTML = '';
    
    let encontrouFichas = false;
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key.startsWith('maskrpg_ficha_')) {
            const nome = key.replace('maskrpg_ficha_', '');
            select.insertAdjacentHTML('beforeend', `<option value="${nome}">${nome}</option>`);
            encontrouFichas = true;
        }
    }
    
    if(!encontrouFichas) {
        select.insertAdjacentHTML('beforeend', `<option value="Herói Padrão">Herói Padrão</option>`);
    }
    
    select.value = nomeFichaAtual;
}

function criarNovaFicha() {
    const nome = prompt("Nome do novo arquivo de ficha:");
    if(nome) {
        const nomeLimpo = nome.replace(/[^a-zA-Z0-9_-]/g, '');
        if(nomeLimpo) {
            carregarFicha(nomeLimpo);
            carregarListaFichas();
            salvarDados(); // Salva imediatamente para criar a chave
        } else {
            alert("Use apenas letras e números.");
        }
    }
}

function trocarFicha() {
    const nome = document.getElementById('select-ficha').value;
    carregarFicha(nome);
}

function apagarFichaAtual() {
    if(confirm(`Tem certeza que deseja apagar a ficha "${nomeFichaAtual}"?`)) {
        localStorage.removeItem(`maskrpg_ficha_${nomeFichaAtual}`);
        carregarListaFichas();
        // Carrega a primeira que sobrar ou cria padrão
        const primeira = document.getElementById('select-ficha').options[0].value;
        carregarFicha(primeira);
    }
}

// --- IMAGEM ---
document.getElementById('input-arquivo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if(file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
            document.getElementById('base64-imagem').value = evt.target.result;
            document.getElementById('preview-img').src = evt.target.result;
            document.getElementById('preview-img').style.display = 'block';
            document.getElementById('aviso-foto').style.display = 'none';
            salvarDados();
        };
        reader.readAsDataURL(file);
    }

});

// --- SISTEMA DE EXPORTAR E IMPORTAR (COMPARTILHAMENTO) ---

function baixarFichaAtual() {
    // 1. Pega os dados atuais da memória
    const chave = `maskrpg_ficha_${nomeFichaAtual}`;
    const dadosJson = localStorage.getItem(chave);

    if (!dadosJson) {
        alert("Erro: Ficha vazia ou não encontrada.");
        return;
    }

    // 2. Cria um arquivo "fantasma" no navegador
    const blob = new Blob([dadosJson], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    
    // 3. Cria um link invisível e clica nele para forçar o download
    const a = document.createElement('a');
    a.href = url;
    a.download = `${nomeFichaAtual}.json`; // Nome do arquivo: Batman.json
    document.body.appendChild(a);
    a.click();
    
    // 4. Limpa
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function importarFicha(input) {
    const arquivo = input.files[0];
    if (!arquivo) return;

    const leitor = new FileReader();
    leitor.onload = function(e) {
        try {
            // 1. Tenta ler o conteúdo do arquivo
            const conteudo = e.target.result;
            const dados = JSON.parse(conteudo);

            // 2. Descobre o nome da ficha pelo nome do arquivo (ex: "Batman.json" vira "Batman")
            let nomeImportado = arquivo.name.replace('.json', '');
            nomeImportado = nomeImportado.replace(/[^a-zA-Z0-9_-]/g, ''); // Limpa caracteres estranhos

            // 3. Pergunta se quer sobrescrever se já existir
            if (localStorage.getItem(`maskrpg_ficha_${nomeImportado}`)) {
                if (!confirm(`Já existe uma ficha chamada "${nomeImportado}". Deseja substituir?`)) {
                    input.value = ''; // Limpa o input para poder tentar de novo
                    return;
                }
            }

            // 4. Salva no LocalStorage e carrega
            localStorage.setItem(`maskrpg_ficha_${nomeImportado}`, JSON.stringify(dados));
            
            alert(`Ficha "${nomeImportado}" importada com sucesso!`);
            carregarListaFichas();
            carregarFicha(nomeImportado);
            
        } catch (erro) {
            alert("Erro ao ler o arquivo. Tem certeza que é um JSON válido?");
            console.error(erro);
        }
        
        // Limpa o input para permitir importar o mesmo arquivo novamente se precisar
        input.value = '';
    };
    leitor.readAsText(arquivo);
}
