<?php

include_once "../../.Privado/Server.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["Nome"] ?? null;
    $email = $_POST["Email"] ?? null;
    $senha = $_POST["Senha"] ?? null;
    $niki = $_POST["Niki"] ?? null;
    $action = $_POST["action"] ?? null;

    // Verifique se os campos necessários não são nulos
    if ($action === "cadastro" && $nome && $email && $senha && $niki) {
        $resposta = cadastro_user($nome, $email, $senha, $niki);
        $resposta = htmlspecialchars($resposta); // Sanitização para o alert
        echo "<script>alert('$resposta');</script>";
        echo "<script>window.location.href = 'Entrada.html';</script>";
        exit;
        
    } elseif ($action === "login" && $email && $senha) {
        $resultado_login = logar_user($email, $senha);
        $lista_erros = ["Senha incorreta", "Email não cadastrado"];

        if (in_array($resultado_login, $lista_erros)) {
            echo "<script>alert('$resultado_login');</script>";
            echo "<script>window.location.href = 'Entrada.html';</script>";
        } else {
            session_start();
            $_SESSION["certificado"] = $resultado_login;
            echo "<script>alert('Login realizado com sucesso.');</script>";
            echo "<script>window.location.href = '../Home/Home.php';</script>";
        }
        exit;

    } else {
        echo "<script>alert('Ação inválida ou dados incompletos.');</script>";
        echo "<script>window.location.href = 'Entrada.html';</script>";
        exit;
    }
}
?>
