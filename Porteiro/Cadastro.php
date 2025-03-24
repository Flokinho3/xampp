<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

include_once '../Elementos/Recursos/Alerta.php';

// Exibe o alerta, se houver
if (isset($_SESSION['Alerta'])) {
    Alerta($_SESSION['Alerta']['Tipo'], $_SESSION['Alerta']['Mensagem']);
    unset($_SESSION['Alerta']);
}

//verifica se o usuário já está logado
if (isset($_SESSION['ID'])) {
    header('Location: ../Home/Home.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="icon" href="../Elementos/IMGS/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../Elementos/CSS/Cadastro.css">
</head>
<body>
    <div class="Alerta" style="position: fixed; top: 0; width: 100%; background-color: red; color: white; text-align: center; padding: 10px;">
        <h2>Projeto De um Estudante!</h2>
        <p>Nao forneça informaçoes reais ou importantes</p>
    </div>
    
    <div class="Container">
        <div class="Vidro_cadastro">
            <form action="../Server/Porteiro.php" method="POST">
                <h1>Cadastro</h1>
                <div class="elemento">
                    <label for="Nome">Nome</label>
                    <input type="text" id="Nome" name="Nome" placeholder="Nome" required>

                    <label for="Email">Email</label>
                    <input type="email" id="Email" name="Email" placeholder="Email" required>

                    <label for="Senha">Senha</label>
                    <input type="password" id="Senha" name="Senha" placeholder="Senha" required>

                    <label for="Nick_name">Apelido</label>
                    <input type="text" id="Nick_name" name="Nick_name" placeholder="Apelido" required>

                    <label for="Data_nasc">Data de Nascimento</label>
                    <input type="date" id="Data_nasc" name="Data_nasc" required>

                    <input type="hidden" name="Tipo" value="Cadastrar">
                    <input type="submit" value="Cadastrar">
                </div>
            </form>
            <a href="Login.php">Ja é cadastrado?</a>
            <a href="#">Recupere sua conta</a>
        </div>
    </div>
</body>
</html>
