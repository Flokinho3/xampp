<?php

session_start();

if (!isset($_SESSION["certificado"])) {
    header("Location: .Publico/Entrada/Entrada.html");
    exit;
} else {
    header("Location: .Publico/Home/Home.php");
    exit;
}
?>