<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['Certificado'])) {
    header('Location: Home_open.php');
    exit();
}
include_once '../Elementos/Recursos/Alerta.php';
include_once '../Server/Server.php';

$user = Pesquisar($_SESSION['ID'], $_SESSION['Certificado']);

// Exibe o alerta, se houver
if (isset($_SESSION['Alerta'])) {
    Alerta($_SESSION['Alerta']['Tipo'], $_SESSION['Alerta']['Mensagem']);
    unset($_SESSION['Alerta']);
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Elementos/IMGS/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../Elementos/CSS/Home.css?v=<?php echo time(); ?>">
    <title>Inicio - <?php echo htmlspecialchars($user['Nick_name'], ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        .barra {
            width: 100%;
            height: 20px;
            background-color: #ddd;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        .consumido {
            height: 100%;
            background-color: #FF4500;
            width: <?php echo max(1, $porcentagem_consumido); ?>%; /* Garante que a barra sempre tenha no mínimo 1% */
            transition: width 0.5s ease-in-out;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const prevButton = document.querySelector('.carousel-prev');
            const nextButton = document.querySelector('.carousel-next');
            const carouselSlide = document.querySelector('.carousel-slide');
            const slides = document.querySelectorAll('.carousel-slide div');
            let counter = 0;
            const totalSlides = 3; // Número total de slides (Infor1, Infor2, Infor3)

            // Função para mudar os slides
            function changeSlide() {
                carouselSlide.style.transform = `translateX(-${counter * 100}%)`;
            }

            // Ação do botão "Anterior"
            prevButton.addEventListener('click', () => {
                if (counter > 0) {
                    counter--;
                } else {
                    counter = totalSlides - 1; // Volta para o último slide se estiver no primeiro
                }
                changeSlide();
            });

            // Ação do botão "Próximo"
            nextButton.addEventListener('click', () => {
                if (counter < totalSlides - 1) {
                    counter++;
                } else {
                    counter = 0; // Volta para o primeiro slide se estiver no último
                }
                changeSlide();
            });
        });
    </script>
</head>
<body>
    <div class="menu">
        <a href="Home.php">Home</a>
        <a href="../Server/Porteiro.php?sair=true" onclick="sair()">Sair</a>
        <a href="Porteiro/Perfil.php">Perfil</a>
    </div>
    <div class="Container">
        <div class="inicio">
            <h1>Central do Usuario!<br>
            <?php echo $user['Nome']; ?></h1>
            <div class="carousel-container">
                <div class="carousel-slide">
                    <div class="Infor1">
                        <h2>Compras</h2>
                        <p>Nome: <?php echo $user['Nome'] ?> </p>
                        <p>Limite: <?php echo $user["Limite"] ?></p>
                        <p>Consumo: <?php echo $user['Consumido'] ?> </p>
                        <?php
                            // Evitar divisão por zero
                            $consumido = isset($user['Consumido']) ? $user['Consumido'] : 0;
                            $limite = isset($user['Limite']) && $user['Limite'] > 0 ? $user['Limite'] : 1;

                            // Cálculo da porcentagem
                            $porcentagem_consumido = ($consumido / $limite) * 100;
                            //se aporcentagem for maior que 100% adiciona um h3 com a mensagem de alerta
                            if ($porcentagem_consumido > 100) {
                                echo "<h3 style='color: red;'>Atenção! O consumo ultrapassou o limite</h3>";
                            }
                        ?>
                        <div class="barra">
                            <div class="consumido" style="width: <?php echo $porcentagem_consumido; ?>%;"></div>
                            <div class="disponivel" style="width: <?php echo $porcentagem_disponivel; ?>%;"></div>
                        </div>
                    </div>
                    <div class="Infor2">
                        <h2>Informações</h2>
                        <p>Nome: <?php echo $user['Nome'] ?> </p>
                        <p>Sexo: <?php echo $user['Sexo'] ?> </p>
                        <p>Data de nascimento: <?php echo $user['Data_nasc'] ?> </p>
                        <p>IMG: <?php echo $user['IMG'] ?> </p>
                    </div>
                    <div class="Infor3">
                        <h2>Contato</h2>
                        <p>Email: <?php echo $user['Email'] ?> </p>
                        <p>E assim vai</p>
                    </div>
                </div>
            </div>
            <button class="carousel-prev">❮</button>
            <button class="carousel-next">❯</button>
        </div>
        <div class="cardapio">
            <h1>Cardápio</h1>
            <div class="Cardapio-Container">
                <?php
                $cardapio = cardapio_exibir();
                // Debug para ver a estrutura
                /*
                echo "<pre>";
                print_r($cardapio);
                echo "</pre>";
                */

                foreach ($cardapio as $dia => $detalhes) {
                    // Se o restaurante estiver fechado neste dia
                    if (isset($detalhes['Fechados'])) {
                        echo "<div class='Cardapio-Item'>
                                <div class='card-inner'>
                                    <div class='card-front'>
                                        <h3>Fechado</h3>
                                        <p>{$detalhes['Fechados']}</p>
                                    </div>
                                    <div class='card-back'>
                                        <h3>Horários de Funcionamento</h3>
                                        <p>{$detalhes['Horarios']}</p>
                                    </div>
                                </div>
                            </div>";
                    }
                    // Se tiver pratos para o dia
                    else if (isset($detalhes['Pratos'])) {
                        echo "<div class='dia-section'><h2>$dia</h2><div class='pratos-container'>";
                        foreach ($detalhes['Pratos'] as $pratoKey => $prato) {
                            $nome = $prato['Nome'];
                            $preco = $prato['Preco'];
                            $ingre = $prato['Ingredientes'];
                            $acomp = $prato['Acompanhamentos'];
                            echo "<div class='Cardapio-Item'>
                                    <div class='card-inner'>
                                        <div class='card-front'>
                                            <h3>$nome</h3>
                                            <p>Preço: $preco</p>
                                        </div>
                                        <div class='card-back'>
                                            <h3>$nome</h3>
                                            <p style='background-color: #FF8C00; color: white; border-radius: 15px;'>Preço: $preco</p>
                                            <p style='background-color: #FF8C00; color: white; border-radius: 15px;'>Ingredientes: $ingre</p>
                                            <p style='background-color: #FF8C00; color: white; border-radius: 15px;'>Acompanhamentos: $acomp</p>
                                            <a href=\"Compras.php?nome=".urlencode($nome)."&preco=".urlencode($preco)."\" style=\"background-color:rgb(34, 120, 205); color: white; border-radius: 15px; display: block; text-align: center; margin: 5px auto;\">Compara</a>
                                        </div>
                                    </div>
                                </div>";
                        }
                        echo "</div></div>";
                    }
                }
                ?>
            <a href="Carrinho.php">Ver carrinho!</a>
            </div>

            <script>
                // Função para virar o cartão quando clicado
                document.addEventListener('DOMContentLoaded', () => {
                    const cards = document.querySelectorAll('.Cardapio-Item');

                    cards.forEach(card => {
                        card.addEventListener('click', () => {
                            card.classList.toggle('flipped');
                        });
                    });
                });
            </script>
        </div>
        <div class="Compras">
            <h1>Compras</h1>
            <div class="Compras-Container">
                <?php
                // Defina o ID do usuário e o certificado (ajuste conforme necessário)
                $ID = $_SESSION['ID'] ?? null;  // Exemplo pegando da sessão
                $Certificado = $_SESSION['Certificado'] ?? null;  // Exemplo pegando da sessão
                $FILE_Compras = "../Elementos/Users/" . $user['Nick_name'] . "/";
                // Verifica se o diretório existe antes de tentar escanear
                if (is_dir($FILE_Compras)) {
                    //lista os json exceto o Compras.json
                    $files = array_map(function($file) {
                        return pathinfo($file, PATHINFO_FILENAME);
                    }, array_diff(scandir($FILE_Compras), array('..', '.', 'Compras.json')));

                    if (empty($files)) {
                        echo '<p>Sem compras finalizadas.</p>';
                    }
                } else {
                    echo '<p>Sem compras finalizadas.</p>';
                }
                if ($ID && $Certificado) {
                    $compras = compras_exibir($ID, $Certificado);
                    if (!empty($compras)) {
                        foreach ($compras as $indice => $compra) {
                            echo "<div class='compra-container'>";
                            echo "<h2>Compra #" . ($indice + 1) . " - " . htmlspecialchars($files[$indice + 2]) . "</h2>";
                            echo "<div class='pratos-container'>";

                            foreach ($compra as $prato) {
                                $nome = htmlspecialchars($prato['nome']);
                                $preco = number_format($prato['preco'] / 100, 2, ',', '.'); // Convertendo centavos para reais
                                $quantidade = $prato['quantidade'];

                                echo "<div class='Cardapio-Item'>
                                        <div class='card-inner'>
                                            <div class='card-front'>
                                                <h3>$nome</h3>
                                                <p>Preço: R$ $preco</p>
                                                <p>Quantidade: $quantidade</p>
                                            </div>
                                            <div class='card-back'>
                                                <h3>$nome</h3>
                                                <p style='background-color: #FF8C00; color: white; border-radius: 15px;'>Preço: R$ $preco</p>
                                                <p style='background-color: #FF8C00; color: white; border-radius: 15px;'>Quantidade: $quantidade</p>
                                                <a href=\"Compras.php?nome=".urlencode($nome)."&preco=".urlencode($preco)."\" style=\"background-color:rgb(34, 120, 205); color: white; border-radius: 15px; display: block; text-align: center; margin: 5px auto;\">Comprar novamente</a>
                                            </div>
                                        </div>
                                    </div>";
                            }

                            echo "</div>"; // Fecha pratos-container
                            echo "</div>"; // Fecha compra-container
                        }
                    } else {
                        echo '<p>Nenhuma compra encontrada.</p>';
                    }
                } else {
                    echo '<p>Erro: ID do usuário ou certificado ausente.</p>';
                }
                ?>
            </div>
        </div>
          
    </div>
</body>
</html>
