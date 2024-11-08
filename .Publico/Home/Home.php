<?php
session_start();
if (!isset($_SESSION["certificado"])) {
    echo "<script>alert('Você não está logado.');</script>";
    header("Location: ../../.Publico/Entrada/Entrada.html");
    exit;
}

include_once("../../.Privado/Server.php");
$resposta = pesquisa_user($_SESSION["certificado"]);

$user = [
    "Nome" => htmlspecialchars($resposta["Nome"], ENT_QUOTES, 'UTF-8'),
    "Email" => htmlspecialchars($resposta["Email"], ENT_QUOTES, 'UTF-8'),
    "Niki" => htmlspecialchars($resposta["Niki"], ENT_QUOTES, 'UTF-8'),
    "ID" => (int)$resposta["ID"], // Ensure ID is integer
    "IMG" => "../../" . trim($resposta["Img"], "'")
];

$img2 = "../../.Publico/Imgs/Fundo.jpg"; // para diferenciar as imagens
// Verificar se a imagem do usuário existe
$img_path = !empty($user["IMG"]) && file_exists($user["IMG"]) ? $user["IMG"] : $img2;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user["Nome"]; ?> - Perfil</title>
    <link rel="icon" href="../Imgs/Logo.ico" type="image/png">
    <link rel="stylesheet" href="../CSS/Sidebar/Sidebar.css">
    <link rel="stylesheet" href="../CSS/Perfil.css?v=1">

</head>
<body>
    <div class="Sidebar">
        <div>
            <img src="../Imgs/Fundo.jpg" alt="Sidebar background image">
        </div>
        <div class="elementos">
            <a href="#">Home</a>
            <a href="../Perfil/Perfil.php">Perfil</a>
            <a href="Configuracoes.php">Configurações</a>
            <a href="../../.Privado/Sair.php">Sair</a>
        </div>
    </div>
    <div class="Container">
        
    </div>
    <script src="../CSS/Sidebar/sidebar.js"></script>
</body>
</html>
