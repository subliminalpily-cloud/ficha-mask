<?php
// Recebe os dados
$dados = $_POST;

// Pega o nome do arquivo (ou usa 'padrao' se não tiver)
$nomeArquivo = isset($_POST['nome_arquivo']) ? $_POST['nome_arquivo'] : 'padrao';

// Segurança: Remove caracteres estranhos do nome do arquivo (só permite letras, números e underline)
$nomeArquivo = preg_replace('/[^a-zA-Z0-9_-]/', '', $nomeArquivo);
if (empty($nomeArquivo)) { $nomeArquivo = 'padrao'; }

// Garante que a pasta 'fichas' existe
if (!is_dir('fichas')) {
    mkdir('fichas');
}

// Converte para JSON
$json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Salva na pasta 'fichas'
file_put_contents("fichas/{$nomeArquivo}.json", $json);

echo "Sucesso: $nomeArquivo";
?>