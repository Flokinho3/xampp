<?php

include_once 'Server.php';

//verifica ose o metodo e post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['Tipo'];
    //verifica se a acao e cadastrar
    if ($acao == "Cadastrar") {
        $Nome = $_POST['Nome'];
        $Email = $_POST['Email'];
        $Senha = $_POST['Senha'];
        $Nick_name = $_POST['Nick_name'];
        $Data_nasc = $_POST['Data_nasc'];
        Cadastro($Nome, $Email, $Senha, $Nick_name, $Data_nasc);        
    }
    //verifica se a acao e login
    else if ($acao == "Login") {
        $Email = $_POST['Email'];
        $Senha = $_POST['Senha'];
        Login($Email, $Senha);
    }
    
}

if (isset($_GET['sair'])) {
    session_start();
    session_destroy();
    header("Location: ../Porteiro/Login.php"); // Redireciona para a página inicial
    exit;
}

?>