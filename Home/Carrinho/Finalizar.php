<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


if (!isset($_SESSION['Certificado'])) {
    $_SESSION['Alerta'] [] = [
        'Tipo' => 'erro',
        'Mensagem' => 'Usuário não autenticado.'
    ];
    header('Location: Home_open.php');
    exit();
}

include_once '../../Server/Server.php';

// Obtém o caminho da pasta do usuário e define o caminho do arquivo de compras
$pasta_user = User_pasta($_SESSION['ID'], $_SESSION['Certificado']);
$caminhoCompras = $pasta_user . 'Compras.json';

// Verifica se o arquivo de compras existe
if (file_exists($caminhoCompras)) {
    // Lê o conteúdo do arquivo para verificar se há itens no carrinho
    $user = Pesquisar($_SESSION['ID'], $_SESSION['Certificado']);
    $FILE_Compras = "../../Elementos/Users/" . $user['Nick_name'] . "/Compras.json";
    $compras = json_decode(file_get_contents($FILE_Compras), true);
    
    if (empty($compras)) {
        $_SESSION['Alerta'][] = [
            'Tipo' => 'erro',
            'Mensagem' => 'O carrinho está vazio.'
        ];
    }
    
    try {
        $res = finalizar_compra($user['ID'], $user['Certificado'], $FILE_Compras);
        if ($res) {
            $_SESSION['Alerta'][] = [
                'Tipo' => 'sucesso',
                'Mensagem' => 'Compra finalizada com sucesso.'
            ];
        } else {
            $_SESSION['Alerta'][] = [
                'Tipo' => 'erro',
                'Mensagem' => 'Erro ao finalizar a compra.'
            ];
        }
        header('Location: ../Carrinho.php');

    } catch (Exception $e) {
        $_SESSION['Alerta'][] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Erro: ' . $e->getMessage()
        ];
        header('Location: ../Carrinho.php');
    }
} else {
    $_SESSION['Alerta'][] = [
        'Tipo' => 'erro',
        'Mensagem' => 'Não há produtos no carrinho.'
    ];
    header('Location: ../Carrinho.php');
}

?>
