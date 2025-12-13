<?php
// --- LÓGICA DE GERENCIAMENTO DE ARQUIVOS ---
$nome_atual = isset($_GET['nome']) ? $_GET['nome'] : 'padrao';
$nome_atual = preg_replace('/[^a-zA-Z0-9_-]/', '', $nome_atual);
if (empty($nome_atual)) { $nome_atual = 'padrao'; }

$caminho_arquivo = "fichas/{$nome_atual}.json";

$dados = [];
if (file_exists($caminho_arquivo)) {
    $conteudo = file_get_contents($caminho_arquivo);
    $dados = json_decode($conteudo, true);
}

$lista_fichas = [];
if (is_dir('fichas')) {
    $arquivos = scandir('fichas');
    foreach($arquivos as $arq){
        if($arq !== '.' && $arq !== '..') {
            $lista_fichas[] = str_replace('.json', '', $arq);
        }
    }
}

// --- CONFIGURAÇÃO DOS MOVIMENTOS BÁSICOS (PADRÃO) ---
if (!isset($dados['movimentos'])) {
    $dados['movimentos'] = [
        [ "titulo" => "ENFRENTAR DIRETAMENTE A AMEAÇA (Role + Perigoso)", "descricao" => "Num sucesso, vocês trocam golpes. Num 10+, escolha dois. Num 7-9, escolha um:\n- resistir ou evitar os golpes\n- tirar algo do oponente\n- criar uma oportunidade para os aliados\n- impressionar, surpreender ou amedrontar a oposição" ],
        [ "titulo" => "LIBERAR SEUS PODERES (Role + Estranho)", "descricao" => "Para superar um obstáculo, remodelar o ambiente ou ampliar os sentidos. Num sucesso, você consegue. Num 7-9, marque uma condição ou o MJ dirá como o efeito é instável ou temporário." ],
        [ "titulo" => "DEFENDER (Role + Salvador)", "descricao" => "Defender algo ou alguém de uma ameaça iminente. PNJ: 10+ mantém seguras e escolhe uma. 7-9 expõe-se ao perigo ou intensifica. Opções: adicionar ponto à Equipe, ganhar Influência sobre protegido, remover uma condição.\nPJ: 10+ dê -2 na jogada dele. 7-9 expõe-se a custo ou retribuição." ],
        [ "titulo" => "AVALIAR A SITUAÇÃO (Role + Superior)", "descricao" => "Num 10+, pergunte duas. Num 7-9, pergunte uma. Ganhe +1 ao agir conforme as respostas.\n- o que posso usar aqui para _____?\n- qual é a maior ameaça aqui?\n- o que aqui está em maior perigo?\n- quem aqui é mais vulnerável a mim?\n- como acabar logo com isso?" ],
        [ "titulo" => "PROVOCAR (Role + Superior)", "descricao" => "Diga o que quer que a pessoa faça. PNJ: 10+ eles mordem a isca e fazem, ou vacilam. 7-9 podem escolher um.\nPJ: 10+ ambos. 7-9 escolha um: se morderem a isca ganham +1 adiante contra você, se não morderem marcam uma condição." ],
        [ "titulo" => "CONFORTAR OU APOIAR (Role + Mundano)", "descricao" => "Para confortar ou apoiar alguém. Num sucesso, a pessoa ouve: marca potencial, limpa uma condição ou muda Rótulos se se abrir. Num 10+, você também pode adicionar ponto à Equipe ou limpar uma condição sua." ],
        [ "titulo" => "PERFURAR A MÁSCARA (Role + Mundano)", "descricao" => "Para ver a pessoa por trás. 10+ pergunte três. 7-9 pergunte uma.\n- o que você está planejando de verdade?\n- o que você quer que eu faça?\n- o que você pretende fazer?\n- como eu poderia levar você a _____?\n- como eu poderia ganhar Influência sobre você?" ],
        [ "titulo" => "TOMAR UM GOLPE PODEROSO (Role + Condições)", "descricao" => "Role + condições marcadas. 10+ escolha um:\n- afastar-se da situação\n- perder o controle dos poderes\n- duas opções da lista de 7-9.\nNum 7-9 escolha um: \n- você ataca verbalmente: provoca um companheiro de equipe a agir de forma imprudente ou tira proveito de sua Influência para infligir uma condição.\n- você cede terreno; a oposição ganha uma oportunidade.\n- você supera a dor; marque duas condições.\n- Numa falha, você se mantém firme. Marque potencial normalmente e diga como você resistiu ao golpe.." ]
    ];
}

// Funções auxiliares
function valor($campo) { global $dados; return isset($dados[$campo]) ? htmlspecialchars($dados[$campo]) : ''; }
function checado($rotulo, $valor) { global $dados; if (isset($dados['rotulos'][$rotulo]) && $dados['rotulos'][$rotulo] == $valor) { return 'checked'; } return ''; }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha RPG: <?php echo $nome_atual; ?></title>
    <style>
        /* Estilos Gerais */
        body { font-family: 'Courier New', Courier, monospace; background-color: #1a1a1a; color: #0f0; padding: 20px; }
        .ficha-container { max-width: 95%; margin: 0 auto; background: #222; padding: 25px; border: 2px solid #0f0; box-shadow: 0 0 10px #0f0; }
        h1, h2 { border-bottom: 1px solid #0f0; padding-bottom: 5px; text-transform: uppercase; }
        
        .campo { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], textarea { width: 100%; padding: 10px; box-sizing: border-box; background: #000; border: 1px solid #0f0; color: #0f0; font-family: inherit; font-size: 1.1em; }
        textarea { height: 100px; resize: vertical; }

        /* Estilo dos Rótulos (Tabela) */
        .tabela-rotulos { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .tabela-rotulos td, .tabela-rotulos th { padding: 8px; text-align: center; border-bottom: 1px dashed #333; }
        .nome-rotulo { text-align: left; font-weight: bold; width: 150px; }
        .seletor-valor input[type="radio"] { display: none; }
        .seletor-valor span { display: inline-block; width: 30px; height: 30px; line-height: 30px; border: 1px solid #444; border-radius: 50%; cursor: pointer; color: #666; }
        .seletor-valor input[type="radio"]:checked + span { background-color: #0f0; color: #000; font-weight: bold; box-shadow: 0 0 8px #0f0; }

        /* Itens (Habilidades/Movimentos) */
        .habilidade-item { background: #111; padding: 15px; margin-bottom: 10px; border: 1px dashed #444; position: relative; }
        .movimento-item { background: #0a1a0a; padding: 15px; margin-bottom: 15px; border: 1px solid #0f0; position: relative; }
        .btn-add { background: #000; color: #0f0; border: 1px solid #0f0; padding: 10px 20px; cursor: pointer; margin-top: 10px; font-weight: bold; }
        .btn-add:hover { background: #0f0; color: #000; }
        .btn-remove { position: absolute; top: 5px; right: 5px; background: #f00; color: #fff; border: none; cursor: pointer; font-weight: bold; padding: 2px 8px; font-family: inherit; }
        .btn-remove:hover { background: #900; }

        /* Status */
        #status-salvamento { position: fixed; top: 10px; right: 10px; padding: 10px; background: #000; border: 1px solid #0f0; display: none; z-index: 1000; }

        /* Inputs e Checkboxes */
        select { width: 100%; padding: 10px; background: #000; border: 1px solid #0f0; color: #0f0; font-family: inherit; font-size: 1.1em; cursor: pointer; }
        .checkbox-box { display: inline-block; cursor: pointer; }
        .checkbox-box input { display: none; }
        .checkbox-box span { display: inline-block; width: 25px; height: 25px; border: 2px solid #0f0; background: #111; }
        .checkbox-box input:checked + span { background: #0f0; box-shadow: 0 0 10px #0f0; }

        /* Condições */
        .lista-condicoes { display: flex; flex-direction: column; gap: 10px; margin-top: 15px; }
        .checkbox-texto { display: flex; align-items: center; cursor: pointer; padding: 8px; background: rgba(50, 0, 0, 0.2); border: 1px solid transparent; transition: all 0.2s; }
        .checkbox-texto:hover { border-color: #f00; background: rgba(255, 0, 0, 0.1); }
        .checkbox-texto input { display: none; }
        .checkbox-texto .marcador { width: 20px; height: 20px; border: 2px solid #f00; margin-right: 15px; display: inline-block; flex-shrink: 0; position: relative; }
        .checkbox-texto input:checked + .marcador { background: #f00; box-shadow: 0 0 10px #f00; }
        .checkbox-texto .texto { color: #888; font-size: 1em; transition: color 0.2s; }
        .checkbox-texto input:checked ~ .texto { color: #f00; font-weight: bold; text-shadow: 0 0 5px #500; }

        /* Foto */
        .topo-ficha { display: flex; gap: 20px; margin-bottom: 20px; align-items: flex-start; }
        .container-foto { width: 250px; flex-shrink: 0; text-align: center; }
        .moldura-foto { width: 100%; height: 250px; border: 2px solid #0f0; background: #000; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; cursor: pointer; box-shadow: 0 0 15px rgba(0, 255, 0, 0.2); }
        .moldura-foto:hover { border-color: #fff; }
        .foto-img { width: 100%; height: 100%; object-fit: cover; }
        .texto-upload { position: absolute; color: #0f0; font-size: 0.8em; background: rgba(0,0,0,0.7); padding: 5px; pointer-events: none; }
        .dados-topo { flex-grow: 1; }

        /* Barra de Arquivos */
        .barra-arquivos { background: #333; padding: 10px; margin-bottom: 20px; border-bottom: 2px solid #0f0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .btn-arquivo { text-decoration: none; background: #000; color: #888; padding: 5px 10px; border: 1px solid #444; margin-right: 5px; font-size: 0.8em; }
        .btn-arquivo:hover { color: #fff; border-color: #fff; }
        .btn-arquivo.ativo { background: #0f0; color: #000; font-weight: bold; }
        .form-arquivo { display: flex; gap: 5px; }

        /* --- NOVO: LAYOUT DUAS COLUNAS --- */
        .layout-duas-colunas {
            display: flex;
            gap: 25px; /* Espaço entre as colunas */
            align-items: flex-start;
            flex-wrap: wrap; /* Para não quebrar em celular */
        }

        .coluna-esquerda, .coluna-direita {
            flex: 1; /* Ambas tentam ocupar o mesmo espaço */
            min-width: 400px; /* Largura mínima para não ficar muito espremido */
        }
    </style>
</head>
<body>

    <div id="status-salvamento">DADOS SALVOS...</div>

    <div class="ficha-container" style="margin-bottom: 20px;">
        <div class="barra-arquivos">
            <div style="flex-grow: 1;">
                <span style="color:#fff;">ARQUIVO ATUAL:</span> 
                <strong style="color:#0f0; font-size: 1.2em;"><?php echo strtoupper($nome_atual); ?></strong>
            </div>
            <form action="index.php" method="GET" class="form-arquivo">
                <input type="text" name="nome" placeholder="Novo nome..." style="width: 150px; padding: 5px; height: 30px;">
                <button type="submit" class="btn-add" style="margin:0; padding: 0 10px; height: 30px;">CARREGAR/CRIAR</button>
            </form>
        </div>
        <div style="font-size: 0.8em; color: #888;">
            FICHAS EXISTENTES:
            <?php 
            if(empty($lista_fichas)) echo "Nenhuma ficha encontrada.";
            foreach($lista_fichas as $f): 
                $classe = ($f == $nome_atual) ? 'ativo' : '';
            ?>
                <a href="index.php?nome=<?php echo $f; ?>" class="btn-arquivo <?php echo $classe; ?>"><?php echo $f; ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="ficha-container">
        <h1 style="text-align: center;">ARQUIVO DE HEROI</h1>
        
        <form id="ficha-form">
            <input type="hidden" name="nome_arquivo" value="<?php echo $nome_atual; ?>">

            <div class="layout-duas-colunas">

                <div class="coluna-esquerda">
                    
                    <div class="topo-ficha">
                        <div class="container-foto">
                            <label class="moldura-foto" for="input-arquivo">
                                <?php 
                                    $imgSrc = isset($dados['imagem']) && !empty($dados['imagem']) ? $dados['imagem'] : ''; 
                                    $displayTexto = $imgSrc ? 'none' : 'block';
                                    $displayImg = $imgSrc ? 'block' : 'none';
                                ?>
                                <span class="texto-upload" id="aviso-foto" style="display: <?php echo $displayTexto; ?>;">[CLIQUE PARA<br>CARREGAR ARQUIVO]</span>
                                <img src="<?php echo $imgSrc; ?>" id="preview-img" class="foto-img" style="display: <?php echo $displayImg; ?>;">
                            </label>
                            <input type="file" id="input-arquivo" accept="image/*" style="display: none;">
                            <input type="hidden" name="imagem" id="base64-imagem" value="<?php echo valor('imagem'); ?>">
                        </div>

                        <div class="dados-topo">
                            <div style="display: flex; gap: 20px;">
                                <div class="campo" style="flex: 1;">
                                    <label>NOME REAL:</label>
                                    <input type="text" name="nome_real" value="<?php echo valor('nome_real'); ?>">
                                </div>
                                <div class="campo" style="flex: 1;">
                                    <label>NOME DE HERÓI:</label>
                                    <input type="text" name="nome_heroi" value="<?php echo valor('nome_heroi'); ?>">
                                </div>
                            </div>
                            <div class="campo">
                                <label>APARÊNCIA:</label>
                                <textarea name="aparencia" style="height: 145px;"><?php echo valor('aparencia'); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="campo">
                        <h2>RÓTULOS</h2>
                        <table class="tabela-rotulos">
                            <thead><tr><th></th> <?php for($i=-2; $i<=3; $i++) echo "<th>$i</th>"; ?></tr></thead>
                            <tbody>
                                <?php 
                                $rotulos = ["PERIGOSO", "ABERRAÇÃO", "SALVADOR", "SUPERIOR", "MUNDANO"];
                                foreach ($rotulos as $rotulo): 
                                ?>
                                <tr>
                                    <td class="nome-rotulo"><?php echo strtoupper($rotulo); ?></td>
                                    <?php for($i=-2; $i<=3; $i++): ?>
                                    <td>
                                        <label class="seletor-valor">
                                            <input type="radio" name="rotulos[<?php echo $rotulo; ?>]" value="<?php echo $i; ?>" <?php echo checado($rotulo, $i); ?>>
                                            <span><?php echo $i; ?></span>
                                        </label>
                                    </td>
                                    <?php endfor; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="display: flex; gap: 20px; align-items: flex-start;">
                        <div class="campo" style="flex: 1;">
                            <label>ARQUÉTIPO:</label>
                            <select name="arquetipo">
                                <option value="">-- Selecione --</option>
                                <?php 
                                $playbooks = ["A Janus", "O Delinquente", "O Nova", "A Discípula", "O Transformado", "A Forasteira", "O Legado", "A Amaldiçoada", "O Farol", "A Bruta"];
                                foreach ($playbooks as $p) {
                                    $selected = (isset($dados['arquetipo']) && $dados['arquetipo'] == $p) ? 'selected' : '';
                                    echo "<option value='$p' $selected>$p</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="campo" style="flex: 1;">
                            <label>POTENCIAL (Marque 5 para evoluir):</label>
                            <div style="display: flex; gap: 10px; margin-top: 5px;">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <label class="checkbox-box">
                                        <input type="checkbox" name="potencial[<?php echo $i; ?>]" value="1" <?php echo (isset($dados['potencial'][$i])) ? 'checked' : ''; ?>>
                                        <span></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div class="campo">
                        <h2>INFLUÊNCIA</h2>
                        <div style="display: flex; gap: 20px;">
                            <div style="flex: 1;">
                                <label style="color: #f55;">TÊM INFLUÊNCIA SOBRE MIM:</label>
                                <textarea name="influencia_sobre_mim" style="height: 80px; border-color: #f55; color: #f55;" placeholder="Adultos, NPCs importantes..."><?php echo valor('influencia_sobre_mim'); ?></textarea>
                            </div>
                            <div style="flex: 1;">
                                <label style="color: #0ff;">TENHO INFLUÊNCIA SOBRE:</label>
                                <textarea name="influencia_eu_tenho" style="height: 80px; border-color: #0ff; color: #0ff;" placeholder="Fãs, companheiros..."><?php echo valor('influencia_eu_tenho'); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="campo" style="border: 1px solid #ff0; padding: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ff0; margin-bottom: 10px; padding-bottom: 5px;">
                            <h2 style="border:none; margin:0; color: #ff0;">MOMENTO DA VERDADE</h2>
                            <label class="checkbox-texto" style="margin:0; background:transparent; border:none;">
                                <input type="checkbox" name="momento_verdade_usado" value="1" <?php echo (isset($dados['momento_verdade_usado']) ? 'checked' : ''); ?>>
                                <span class="marcador" style="border-color: #ff0; border-radius: 50%; width: 15px; height: 15px;"></span>
                                <span class="texto" style="color: #ff0;">JÁ USADO?</span>
                            </label>
                        </div>
                        <textarea name="texto_momento_verdade" placeholder="Copie aqui o texto do seu Momento da Verdade..." style="height: 80px; font-style: italic; color: #ff0; border-color: #440; background: #110;"><?php echo valor('texto_momento_verdade'); ?></textarea>
                    </div>

                    <div class="campo" style="border: 2px solid #500; padding: 20px; position: relative; background: #0a0000;">
                        <label style="color: #f00; position: absolute; top: -12px; left: 15px; background: #1a1a1a; padding: 0 10px; border: 1px solid #500;">⚠ CONDIÇÕES</label>
                        <div class="lista-condicoes">
                            <?php 
                            $condicoes = [
                                "amedrontado" => "ASSUSTADO (-2 p/ Engajar)",
                                "irritado" => "IRRITADO (-2 p/ Provocar/Avaliar)",
                                "culpado" => "CULPADO (-2 p/ Provocar)",
                                "desesperado" => "DESESPERANÇOSO (-2 p/ Defender)",
                                "inseguro" => "INSEGURO (-2 p/ Rejeitar)"
                            ];
                            foreach ($condicoes as $key => $texto): 
                                $checked = (isset($dados['condicoes'][$key])) ? 'checked' : '';
                            ?>
                            <label class="checkbox-texto">
                                <input type="checkbox" name="condicoes[<?php echo $key; ?>]" value="1" <?php echo $checked; ?>>
                                <span class="marcador"></span>
                                <span class="texto"><?php echo $texto; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        
                    </div>
                                        <div class="campo">
                        <h2>HABILIDADES</h2>
                        <div id="lista-habilidades">
                            <?php 
                            if (isset($dados['habilidades']) && is_array($dados['habilidades'])) {
                                foreach ($dados['habilidades'] as $idx => $hab) {
                                    echo '<div class="habilidade-item">';
                                    echo '<button type="button" class="btn-remove" onclick="removerItem(this)">X</button>';
                                    echo '<label>Título:</label><input type="text" name="habilidades['.$idx.'][titulo]" value="'.htmlspecialchars($hab['titulo']).'">';
                                    echo '<label style="margin-top:5px;">Descrição:</label><textarea name="habilidades['.$idx.'][descricao]" style="height:120px;">'.htmlspecialchars($hab['descricao']).'</textarea>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="btn-add" onclick="adicionarHabilidade()">+ ADICIONAR HABILIDADE</button>
                    </div>

                    <div class="campo">
                        <h2>ANOTAÇÕES & OBSERVAÇÕES</h2>
                        <textarea name="anotacoes" style="height: 300px; font-family: 'Courier New', monospace; line-height: 1.5;" placeholder="Histórico, inventário extra, nomes de NPCs..."><?php echo valor('anotacoes'); ?></textarea>
                    </div>
                </div> <div class="coluna-direita">

                    <div class="campo">
                        <h2>MOVIMENTOS BÁSICOS</h2>
                        <div id="lista-movimentos">
                            <?php 
                            if (isset($dados['movimentos']) && is_array($dados['movimentos'])) {
                                foreach ($dados['movimentos'] as $idx => $mov) {
                                    echo '<div class="movimento-item">';
                                    echo '<button type="button" class="btn-remove" onclick="removerItem(this)">X</button>';
                                    echo '<label>Movimento:</label><input type="text" name="movimentos['.$idx.'][titulo]" value="'.htmlspecialchars($mov['titulo']).'" style="font-weight:bold; color:#0f0;">';
                                    echo '<label style="margin-top:5px;">Regra/Efeito:</label><textarea name="movimentos['.$idx.'][descricao]" style="height:140px; font-size:0.9em;">'.htmlspecialchars($mov['descricao']).'</textarea>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="btn-add" onclick="adicionarMovimento()">+ NOVO MOVIMENTO</button>
                    </div>



                </div> </div> </form>
    </div>

    <script src="script.js"></script>
</body>
</html>