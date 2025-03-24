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

include_once '../Elementos/Recursos/Alerta.php';
include_once '../Server/Server.php';

// Obtém o caminho da pasta do usuário
$pasta_user = User_pasta($_SESSION['ID'], $_SESSION['Certificado']);
$caminhoCompras = $pasta_user . 'Compras.json';

// Função para ler o JSON de compras
function lerCompras($caminho) {
    if (!file_exists($caminho)) {
        file_put_contents($caminho, json_encode([]));
        return [];
    }
    return json_decode(file_get_contents($caminho), true);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho</title>
    <link rel="stylesheet" href="../Elementos/CSS/Carrinho.css">
</head>
<body>
    <div class="Container">
        <div class="Carrinho">
            <h1>Carrinho</h1>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                        <th>Remover</th>
                        <th>Adicionar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $compras = lerCompras($caminhoCompras);
                    $total = 0;
                    foreach ($compras as $compra) {
                        $preco_formatado = $compra['preco'] / 100; // Ajusta o preço, considerando que está em centavos
                        $total += $preco_formatado * $compra['quantidade']; // Calcula o total corretamente

                        echo "<tr>";
                        echo "<td>{$compra['nome']}</td>";
                        echo "<td>" . number_format($preco_formatado, 2, ',', '.') . "</td>";
                        echo "<td>{$compra['quantidade']}</td>";
                        echo "<td><a href='Carrinho/Remov.php?nome={$compra['nome']}'>Remover</a></td>";
                        echo "<td><a href='Carrinho/Add.php?nome={$compra['nome']}'>Adicionar</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <p>Total: R$ <?= number_format($total, 2, ',', '.') ?></p>
            <button onclick="location.href='Carrinho/Finalizar.php'">Finalizar compra</button>
            <button onclick="location.href='Home.php'">Continuar comprando</button>
            <button onclick="location.href='Carrinho/Limpar.php'">Limpar carrinho</button>
        </div>
    </div>
</body>
</html>

