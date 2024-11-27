<?php
session_start();

// Inicializa as sessões para tarefas, pilha e fila, se não existirem
if (!isset($_SESSION['tarefas'])) {
    $_SESSION['tarefas'] = [];
}
if (!isset($_SESSION['pilha'])) {
    $_SESSION['pilha'] = [];
}
if (!isset($_SESSION['fila'])) {
    $_SESSION['fila'] = [];
}


function adicionarTarefa($tarefa)
{
    $_SESSION['tarefas'][] = $tarefa;
    $_SESSION['fila'][] = $tarefa; 
    $_SESSION['pilha'][] = "Adicionar $tarefa"; 
}


function removerTarefa($indice)
{
    if (isset($_SESSION['tarefas'][$indice])) { 
        $tarefaRemovida = $_SESSION['tarefas'][$indice];
        unset($_SESSION['tarefas'][$indice]);
        $_SESSION['tarefas'] = array_values($_SESSION['tarefas']); 
        
        // Remover da fila, se presente
        $indiceFila = array_search($tarefaRemovida, $_SESSION['fila']);
        if ($indiceFila !== false) {
            unset($_SESSION['fila'][$indiceFila]);
            $_SESSION['fila'] = array_values($_SESSION['fila']); 
        }

        $_SESSION['pilha'][] = "Remover $tarefaRemovida"; 
    }
}



function desfazerAcao()
{
    if (empty($_SESSION['pilha'])) {
        return;
    }

    $ultimaAcao = array_pop($_SESSION['pilha']); 
    $acaoPartes = explode(' ', $ultimaAcao, 2); 

    if (count($acaoPartes) < 2) {
        return; 
    }

    $acao = $acaoPartes[0];
    $tarefa = $acaoPartes[1];

    if ($acao == 'Adicionar') {
        // Desfaz a adição
        $indice = array_search($tarefa, $_SESSION['tarefas']);
        if ($indice !== false) {
            unset($_SESSION['tarefas'][$indice]);
            $_SESSION['tarefas'] = array_values($_SESSION['tarefas']); 
        }

        // Remove da fila
        $indiceFila = array_search($tarefa, $_SESSION['fila']);
        if ($indiceFila !== false) {
            unset($_SESSION['fila'][$indiceFila]);
            $_SESSION['fila'] = array_values($_SESSION['fila']); 
        }
    } elseif ($acao == 'Remover') {
        // Desfaz a remoção
        $_SESSION['tarefas'][] = $tarefa;
        $_SESSION['fila'][] = $tarefa; 
    } elseif ($acao == 'Executar') {
        // Desfaz a execução, adicionando a tarefa de volta à fila
        $_SESSION['fila'][] = $tarefa; 
    }
}

// executar a tarefa mais antiga
function executarTarefa()
{
    if (!empty($_SESSION['fila'])) {
        $tarefaExecutada = array_shift($_SESSION['fila']); 
        $_SESSION['pilha'][] = "Executar $tarefaExecutada"; 
        return $tarefaExecutada;
    }
    return null;
}

// Adicionar tarefa
if (isset($_POST['adicionar'])) {
    $novaTarefa = trim($_POST['tarefa']);
    if ($novaTarefa != "") {
        adicionarTarefa($novaTarefa);
    }
}

// Remover tarefa
if (isset($_POST['remover'])) {
    $indice = $_POST['remover'];
    removerTarefa($indice);
}

// Desfazer ação
if (isset($_POST['desfazer'])) {
    desfazerAcao();
}

// Executar tarefa
if (isset($_POST['executar'])) {
    $tarefaExecutada = executarTarefa();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Tarefas</title>
    <link rel="stylesheet" href="css/novo.css">,
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/resposivo.css">
    <script src="script.js"></script>

</head>

<body>


    <div class="container">
        <h1 >Gerenciamento de Tarefas</h1>
        <form method="POST">

            <input type="text" id="tarefa" name="tarefa" placeholder="Nova Tarefa.." required>
            <button class="btn" type="submit" name="adicionar">Adicionar Tarefa</button>
        </form>


        <h2>Lista de Tarefas:</h2>
        <?php if (count($_SESSION['tarefas']) > 0): ?>
            <ul>
                <?php foreach ($_SESSION['tarefas'] as $index => $tarefa): ?>
                    <li>
                        <?php echo htmlspecialchars($tarefa); ?>
                        <form method="POST" style="display:inline;">
                        
                            <button id="btn-remove" type="submit" name="remover" value="<?php echo $index; ?>">Remover</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Não há tarefas na lista.</p>
        <?php endif; ?>


        <h2>Fila de Tarefas Pendentes:</h2>
        <?php if (count($_SESSION['fila']) > 0): ?>
            <ul>
                <?php foreach ($_SESSION['fila'] as $tarefa): ?>
                    <li><?php echo htmlspecialchars($tarefa); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Não há tarefas pendentes.</p>
        <?php endif; ?>


        <div class="form">
            <form method="POST">
                <button class="desfazerbtn" type="submit" name="desfazer">Desfazer Última Ação</button>
                <button class="executarbtn" type="submit" name="executar">Executar Tarefa Mais Antiga</button>
            </form>
        </div>
    </div>
    <!-- Exibir tarefa executada -->
    <?php if (isset($tarefaExecutada)): ?>
        <p class="teste"><span>Tarefa executada</span class="span_resultado"> <span class="conteudoparabaixo"><?php echo htmlspecialchars($tarefaExecutada); ?></span>
    </p>
    <?php endif; ?>








<!--<footer class="footer">
    <img src="imagem/m (1).gif" alt="Logo da Empresa" class="footer-logo">
    <p  class="pfooter" class="footer-text">Tech Tasks</p>

    <p class="pfooter">Digital Technology</p>
</footer>-->



</body>

</html>