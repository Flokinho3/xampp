<?php

session_start();
if (!isset($_SESSION['Certificado'])) {
    header("Location: ../Entrada/Entrada.html");
    exit();
}

echo "Bem-vindo ao Home!";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <a href="../../.Privado/Sair.php">Sair</a>
    
</body>
</html>