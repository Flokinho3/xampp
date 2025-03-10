<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * exemplo de uso:
 * 
 * include_once 'Alerta.php';
 * 
 * // Exibe um alerta de erro
 * $_SESSION['Alerta'] = [
 *     'Tipo' => 'erro',
 *    'Mensagem' => 'Email já cadastrado'
 * ];
 * 
 * tipos validos: erro, sucesso, aviso, info
 */

function css_alert() {
    echo '<style>
    .alerta {
        color: white;
        padding: 10px;
        margin: 10px;
        border-radius: 5px;
        opacity: 1;
        transition: opacity 1s ease-in-out;
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        top: 20px;
        z-index: 1000;
    }
    .alerta.erro { background-color: rgba(255, 0, 0, 0.8); }
    .alerta.sucesso { background-color: rgba(0, 255, 0, 0.8); }
    .alerta.aviso { background-color: rgba(255, 255, 0, 0.8); color: black; }
    .alerta.info { background-color: rgba(0, 0, 255, 0.8); }
    </style>';
}

function Alerta($tipo, $mensagem) {
    css_alert();
    $classe = strtolower($tipo); // Converte "Erro" para "erro", etc.
    echo '<div class="alerta ' . $classe . '">' . htmlspecialchars($mensagem) . '</div>';
    echo '<script>
    setTimeout(function(){
        var alerts = document.querySelectorAll(".alerta");
        alerts.forEach(function(alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 1000);
        });
    }, 5000);
    </script>';
}
?>
