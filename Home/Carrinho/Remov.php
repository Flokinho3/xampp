<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include_once '../../Server/Server.php';

// Obtém o caminho da pasta do usuário
$pasta_user = User_pasta($_SESSION['ID'], $_SESSION['Certificado']);
$caminhoCompras = $pasta_user . 'Compras.json';  // Corrigido para utilizar apenas uma variável

if (!isset($_SESSION['Certificado'])) {
    $_SESSION['Alerta'] [] = [
        'Tipo' => 'erro',
        'Mensagem' => 'Usuário não autenticado.'
    ];
    header('Location: Home_open.php');
    exit();
}

// Verifica se o parâmetro "nome" está presente na URL
if (!isset($_GET['nome'])) {
    $_SESSION['Alerta'] [] = [
        'Tipo' => 'erro',
        'Mensagem' => 'Item não especificado.'
    ];
    header('Location: ../Carrinho.php');
    exit();
}

$nome = $_GET['nome'];

// Verifica se o arquivo de compras existe
if (!file_exists($caminhoCompras)) {
    $_SESSION['Alerta'] [] = [
        'Tipo' => 'erro',
        'Mensagem' => 'Arquivo de compras não encontrado.'
    ];
    header('Location: ../Home.php');
    exit();
}

$compras = json_decode(file_get_contents($caminhoCompras), true);

// Verifica se o item está presente nas compras
$found = false;
foreach ($compras as $key => $compra) {
    if ($compra['nome'] === $nome) {
        $found = true;
        if ($compra['quantidade'] > 1) {
            // Se houver mais de uma unidade, diminui a quantidade
            $compras[$key]['quantidade']--;
        } else {
            // Se for o último item, remove o item
            unset($compras[$key]);
        }
        break;
    }
}

if (!$found) {
    $_SESSION['Alerta'] [] = [
        'Tipo' => 'erro',
        'Mensagem' => 'Item não encontrado no carrinho.'
    ];
    header('Location: ../Carrinho.php');
    exit();
}

// Salva as alterações no arquivo de compras
file_put_contents($caminhoCompras, json_encode($compras, JSON_PRETTY_PRINT));

$_SESSION['Alerta'] [] = [
    'Tipo' => 'sucesso',
    'Mensagem' => 'Item removido do carrinho.'
];

// Redireciona para o carrinho
header('Location: ../Carrinho.php');
exit();

?>
