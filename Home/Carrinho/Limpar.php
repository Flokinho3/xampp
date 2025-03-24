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

// se o arquivo de compras existir, ele será excluído
if (file_exists($caminhoCompras)) {
    unlink($caminhoCompras);
}

$_SESSION['Alerta'] [] = [
    'Tipo' => 'sucesso',
    'Mensagem' => 'Carrinho limpo com sucesso.'
];
header('Location: ../Home.php');
exit();

?>