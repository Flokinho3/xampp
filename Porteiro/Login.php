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
    <title>Login</title>
    <link rel="stylesheet" href="../Elementos/CSS/Login.css">
    <link rel="icon" href="../Elementos/IMGS/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="container">
        <div class="Login">
            <h1>Login</h1>
            <div class="vidro_trasparente">
                <form action="../Server/Porteiro.php" method="POST">
                    <input type="hidden" name="Tipo" value="Login">
                    <label for="Email">Email</label>
                    <input type="email" name="Email" id="Email" required>
                    <label for="Senha">Senha</label>
                    <input type="password" name="Senha" id="Senha" required>
                    <button type="submit">Entrar</button>
                </form>
            </div>
            <a href="../Porteiro/Cadastro.php">Cadastrar</a>
            <a href="#">Recuperar senha</a>
        </div>
</body>
</html>