<?php

session_start();

if (isset($_SESSION['Certificado'])) {
    header('Location: .Publico/Home/Home.php');
    exit();
} else {
    header('Location: .Publico/Entrada/Entrada.html');
    exit();
}

?>