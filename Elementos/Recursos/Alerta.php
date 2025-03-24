<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Exemplo de uso:
 * 
 * include_once 'Alerta.php';
 * 
 * // Adiciona múltiplos alertas
 * $_SESSION['Alerta'][] = ['Tipo' => 'erro', 'Mensagem' => 'Email já cadastrado'];
 * $_SESSION['Alerta'][] = ['Tipo' => 'sucesso', 'Mensagem' => 'Cadastro realizado com sucesso'];
 * 
 * Tipos válidos: erro, sucesso, aviso, info
 */

function css_alert() {
    echo '<style>
    .alerta {
        color: white;
        padding: 10px 20px;
        margin: 10px;
        border-radius: 5px;
        opacity: 1;
        transition: opacity 1s ease-in-out;
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        top: 20px;
        z-index: 1000;
        min-width: 250px;
        text-align: center;
        font-weight: bold;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
    }
    .alerta.erro { background-color: rgba(255, 0, 0, 0.8); }
    .alerta.sucesso { background-color: rgba(0, 255, 0, 0.8); }
    .alerta.aviso { background-color: rgba(255, 255, 0, 0.8); color: black; }
    .alerta.info { background-color: rgba(0, 0, 255, 0.8); }
    </style>';
}

function exibir_alertas() {
    if (!isset($_SESSION['Alerta']) || empty($_SESSION['Alerta'])) {
        return;
    }

    css_alert();

    $alertas = $_SESSION['Alerta'];
    unset($_SESSION['Alerta']); // Remove os alertas após exibição

    foreach ($alertas as $index => $alerta) {
        if (!is_array($alerta) || !isset($alerta['Tipo']) || !isset($alerta['Mensagem'])) {
            continue;
        }
        $tipo = strtolower($alerta['Tipo']);
        $mensagem = htmlspecialchars($alerta['Mensagem']);

        echo '<div class="alerta ' . $tipo . '" style="top:' . (20 + ($index * 60)) . 'px;">' . $mensagem . '</div>';
    }

    echo '<script>
    setTimeout(function(){
        var alerts = document.querySelectorAll(".alerta");
        alerts.forEach(function(alert, index) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 1000);
        });
    }, 5000);
    </script>';
}

// Exibe os alertas na página
exibir_alertas();

?>
