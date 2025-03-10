<?php 
// verifica se tem uma sessão aberta
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// verifica se o usuário está logado
if (!isset($_SESSION['Certificado'])) {
    header('Location: Porteiro/Cadastro.php');
    exit();
} else {
    header('Location: Porteiro/Login.php');
    exit();
}

?>