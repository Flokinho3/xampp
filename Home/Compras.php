<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['Certificado'])) {
    $_SESSION['Alerta'] [] = [
        'Tipo' => 'erro',
        'Mensagem' => 'Usuário não autenticado.'
    ];
    header('Location: Home_open.php');
    exit();
}

include_once '../Elementos/Recursos/Alerta.php';
include_once '../Server/Server.php';

// Obtém o caminho da pasta do usuário
$pasta_user = User_pasta($_SESSION['ID'], $_SESSION['Certificado']);
$caminhoCompras = $pasta_user . 'Compras.json';


// Função para ler o JSON de compras
function lerCompras($caminho) {
    if (!file_exists($caminho)) {
        file_put_contents($caminho, json_encode([]));
        return [];
    }
    return json_decode(file_get_contents($caminho), true);
}

// Função para salvar as compras no JSON
function salvarCompras($caminho, $compras) {
    file_put_contents($caminho, json_encode($compras));
}

function Add_item($nome, $preco, $compras) {
    static $processados = [];
    if (isset($processados[$nome])) {
        return $compras;
    }
    $processados[$nome] = true;

    // Sanitiza e converte o preço corretamente
    $preco = floatval(str_replace(',', '.', filter_var($preco, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)));

    foreach ($compras as $key => $compra) {
        if ($compra['nome'] === $nome) {
            // Atualiza preço e quantidade
            $compras[$key]['preco'] += $preco;
            $compras[$key]['quantidade']++;
            return $compras;
        }
    }

    // Adiciona um novo item
    $compras[] = [
        'nome' => $nome,
        'preco' => $preco,
        'quantidade' => 1
    ];
    return $compras;
}

// Verifica se os parâmetros necessários foram passados
if (isset($_GET['nome']) && isset($_GET['preco'])) {
    $nome = trim($_GET['nome']);
    $preco = trim($_GET['preco']);

    $preco = filter_var($_GET['preco'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    if (!is_numeric($preco) || $preco <= 0) {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Preço inválido.'
        ];
        header('Location: Home.php');
        exit();
    }


    if (empty($nome) || !is_numeric($preco)) {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Nome ou preço inválido.'
        ];
        header('Location: Home.php');
        exit();
    }

    $compras = lerCompras($caminhoCompras);
    $compras = Add_item($nome, $preco, $compras);
    salvarCompras($caminhoCompras, $compras);

    $_SESSION['Alerta'] [] = [
        'Tipo' => 'sucesso',
        'Mensagem' => 'Item adicionado ao carrinho!'
    ];
    header('Location: Home.php');
    exit();
} else {
    $_SESSION['Alerta'] [] = [
        'Tipo' => 'aviso',
        'Mensagem' => 'Nenhum item foi especificado.'
    ];
    header('Location: Home.php');
    exit();
}
?>
