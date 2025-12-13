const form = document.getElementById('ficha-form');
const statusDiv = document.getElementById('status-salvamento');
let timeoutId;

// --- SISTEMA DE AUTOSAVE ---
function salvarDados() {
    statusDiv.style.display = 'block';
    statusDiv.textContent = "SALVANDO...";

    const formData = new FormData(form);

    fetch('salvar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        statusDiv.textContent = "DADOS GRAVADOS.";
        setTimeout(() => { statusDiv.style.display = 'none'; }, 2000);
    })
    .catch(error => {
        console.error('Erro:', error);
        statusDiv.textContent = "ERRO DE CONEXÃO";
        statusDiv.style.color = "red";
    });
}

// Escuta alterações no formulário (agora usa delegação de evento para funcionar com campos novos)
form.addEventListener('input', () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(salvarDados, 1000);
});

// --- SISTEMA DE ITENS DINÂMICOS (HABILIDADES E MOVIMENTOS) ---

// Função Genérica de Remover (serve para ambos)
function removerItem(botao) {
    if(!confirm("Tem certeza que deseja apagar este item?")) return;
    const item = botao.parentElement; // Pega o pai (seja .habilidade-item ou .movimento-item)
    item.remove();
    salvarDados();
}

// Adicionar Habilidade (Mantive igual)
function adicionarHabilidade() {
    const container = document.getElementById('lista-habilidades');
    const idUnico = Date.now(); 
    
    const html = `
        <div class="habilidade-item">
            <button type="button" class="btn-remove" onclick="removerItem(this)">X</button>
            <label>Título:</label>
            <input type="text" name="habilidades[${idUnico}][titulo]" placeholder="Nome da Habilidade">
            <label style="margin-top:5px;">Descrição:</label>
            <textarea name="habilidades[${idUnico}][descricao]" style="height:120px;" placeholder="O que ela faz?"></textarea>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// NOVA FUNÇÃO: Adicionar Movimento
function adicionarMovimento() {
    const container = document.getElementById('lista-movimentos');
    const idUnico = Date.now(); 
    
    const html = `
        <div class="movimento-item">
            <button type="button" class="btn-remove" onclick="removerItem(this)">X</button>
            <label>Movimento:</label>
            <input type="text" name="movimentos[${idUnico}][titulo]" placeholder="Nome do Movimento (ex: Ataque Especial)">
            <label style="margin-top:5px;">Regra/Efeito:</label>
            <textarea name="movimentos[${idUnico}][descricao]" style="height:140px;" placeholder="Como funciona a rolagem?"></textarea>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}
// --- SISTEMA DE UPLOAD DE IMAGEM ---

const inputArquivo = document.getElementById('input-arquivo');
const previewImg = document.getElementById('preview-img');
const avisoFoto = document.getElementById('aviso-foto');
const inputBase64 = document.getElementById('base64-imagem');

inputArquivo.addEventListener('change', function(evento) {
    const arquivo = evento.target.files[0];

    if (arquivo) {
        // Cria um leitor de arquivos
        const reader = new FileReader();

        reader.onload = function(e) {
            // Pega o resultado (o código da imagem)
            const resultado = e.target.result;

            // Mostra a imagem na tela
            previewImg.src = resultado;
            previewImg.style.display = 'block';
            avisoFoto.style.display = 'none';

            // Joga o código no input escondido para ser salvo
            inputBase64.value = resultado;

            // Força o salvamento
            salvarDados();
        }

        // Lê o arquivo como URL de dados (Base64)
        reader.readAsDataURL(arquivo);
    }
});