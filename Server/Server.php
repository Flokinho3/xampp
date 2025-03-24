<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Base path for JSON storage
define('JSON_STORAGE_PATH', __DIR__ . '/../Elementos/JSON/');

// Ensure JSON storage directory exists
if (!is_dir(JSON_STORAGE_PATH)) {
    mkdir(JSON_STORAGE_PATH, 0755, true);
}

// Function to read JSON file
function readJsonFile($filename) {
    $filepath = JSON_STORAGE_PATH . $filename;
    if (!file_exists($filepath)) {
        return [];
    }
    $content = file_get_contents($filepath);
    return json_decode($content, true) ?: [];
}


// Função de Cadastro
function Cadastro($Nome, $Email, $Senha, $Nick_name, $Data_nasc) {
    $users = readJsonFile('users.json');
    
    // Verifica se o email já está cadastrado
    $emailExists = array_filter($users, function($user) use ($Email) {
        return $user['Email'] === $Email;
    });
    if (!empty($emailExists)) {
        $_SESSION['Alerta'][] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Email já cadastrado'
        ];
        header('Location: ../Porteiro/Cadastro.php');
        exit;
    }

    // Verifica se o apelido já está cadastrado
    $nickExists = array_filter($users, function($user) use ($Nick_name) {
        return $user['Niki'] === $Nick_name;
    });
    if (!empty($nickExists)) {
        $_SESSION['Alerta'][] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Apelido já cadastrado'
        ];
        header('Location: ../Porteiro/Cadastro.php');
        exit;
    }

    // Gerar certificado único
    $Certificado = Gerar_certificado($users);

    // Preparar novo usuário
    $newUser = [
        'ID' => count($users) + 1,
        'Nome' => $Nome,
        'Email' => $Email,
        'Senha' => password_hash($Senha, PASSWORD_DEFAULT),
        'Niki' => $Nick_name,
        'Data_C' => date('Y-m-d H:i:s'),
        'Datan_naci' => $Data_nasc,
        'Certificado' => $Certificado,
        'IMG' => 'Elementos\\IMGS\\undefined_image.png'
    ];

    // Preparar conta
    $contas = readJsonFile('contas.json');
    $newConta = [
        'ID' => $Certificado,
        'Limite' => 150,
        'Consumido' => 0,
        'Livre' => 1
    ];

    // Salvar dados
    $users[] = $newUser;
    $contas[] = $newConta;
    
    writeJsonFile('users.json', $users);
    writeJsonFile('contas.json', $contas);

    $_SESSION['Alerta'][] = [
        'Tipo' => 'sucesso',
        'Mensagem' => 'Cadastro realizado com sucesso'
    ];
    header('Location: ../Porteiro/Login.php');
    exit;
}

// Função de Login
function Login($Email, $Senha) {
    $users = readJsonFile('users.json');
    
    // Encontrar usuário pelo email
    $user = array_filter($users, function($u) use ($Email) {
        return $u['Email'] === $Email;
    });
    $user = reset($user);

    if (!$user) {
        $_SESSION['Alerta'][] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Email não cadastrado'
        ];
        header('Location: ../Porteiro/Login.php');
        exit;
    }

    // Verificar senha
    if (password_verify($Senha, $user['Senha'])) {
        $_SESSION['ID'] = $user['ID'];
        $_SESSION['Certificado'] = $user['Certificado'];
        header('Location: ../Home/Home.php');
        exit;
    } else {
        $_SESSION['Alerta'][] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Senha incorreta'
        ];
        header('Location: ../Porteiro/Login.php');
        exit;
    }
}

// Gera um certificado de 4 dígitos único
function Gerar_certificado($users) {
    do {
        $certificado = rand(1000, 9999);
        $exists = array_filter($users, function($user) use ($certificado) {
            return $user['Certificado'] == $certificado;
        });
    } while (!empty($exists));

    return $certificado;
}

// Verifica se o certificado é válido
function Verificar_certificado($Certificado) {
    if (!isset($_SESSION['ID']) || !isset($_SESSION['Certificado'])) {
        $_SESSION['Alerta'][] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Usuário não autenticado'
        ];
        return false;
    }

    $users = readJsonFile('users.json');
    $user = array_filter($users, function($u) use ($Certificado) {
        return $u['Certificado'] == $Certificado;
    });
    $user = reset($user);

    if (!$user) {
        session_destroy();
        $_SESSION['Alerta'][] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Certificado não cadastrado'
        ];
        return false;
    }

    if ($user['ID'] == $_SESSION['ID'] && $Certificado == $_SESSION['Certificado']) {
        return true;
    } else {
        $_SESSION['Alerta'][] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Certificado inválido'
        ];
        return false;
    }
}

// Função para pesquisar as informações do usuário
function Pesquisar($ID, $Certificado) {
    $users = readJsonFile('users.json');
    $contas = readJsonFile('contas.json');

    $user = array_filter($users, function($u) use ($ID, $Certificado) {
        return $u['ID'] == $ID && $u['Certificado'] == $Certificado;
    });
    $user = reset($user);

    $conta = array_filter($contas, function($c) use ($Certificado) {
        return $c['ID'] == $Certificado;
    });
    $conta = reset($conta);

    if (!$user || !$conta) {
        $_SESSION['Alerta'][] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Usuário não autenticado'
        ];
        header('Location: ../Porteiro/Login.php');
        exit;
    }

    return [
        'ID' => $user['ID'],
        'Nome' => $user['Nome'],
        'Nick_name' => $user['Niki'],
        'Email' => $user['Email'],
        'Sexo' => $user['Sexo'] ?? '',
        'Data_nasc' => $user['Datan_naci'],
        'IMG' => $user['IMG'],
        'Limite' => $conta['Limite'],
        'Consumido' => $conta['Consumido'],
        'Livre' => $conta['Livre'],
        'Certificado' => $user['Certificado']
    ];
}

// Função para exibir o cardápio de acordo com o dia da semana
function cardapio_exibir() {
    // Mapeamento dos dias da semana para corresponder ao JSON
    $dias_traduzidos = [
        "Sunday" => "Domingo",
        "Monday" => "Segunda",
        "Tuesday" => "Terça",
        "Wednesday" => "Quarta",
        "Thursday" => "Quinta",
        "Friday" => "Sexta",
        "Saturday" => "Sabado"
    ];
    
    // Para teste, força o dia como Quarta (ou use a linha comentada para dia atual)
    //$dia_semana_pt = 'Quarta';
    $dia_semana_pt = $dias_traduzidos[date('l')]; // Usar em produção para dia atual
    
    // Caminho do arquivo JSON com o cardápio
    $json_file = __DIR__ . '/Cardapio/Cardapio.json';
    
    if (!file_exists($json_file)) {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Cardápio não encontrado'
        ];
        return [];
    }
    
    $cardapio = json_decode(file_get_contents($json_file), true);
    if ($cardapio === null) {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Erro ao ler o cardápio'
        ];
        return [];
    }
    
    // Retorna o cardápio do dia atual ou vazio caso não encontre
    return isset($cardapio[$dia_semana_pt]) ? [$dia_semana_pt => $cardapio[$dia_semana_pt]] : [];
}

// Função para verificar/criar a pasta do usuário
function User_pasta($ID, $Certificado) {
    $user = Pesquisar($ID, $Certificado);
    //verifica se o usuario tem permiçao de compra
    if ($user['Livre'] == 0) {
        //verifica se o consumo e maior ou igual ao limite
        if ($user['Consumido'] >= $user['Limite']) {
            $_SESSION['Alerta'] [] = [
                'Tipo' => 'erro',
                'Mensagem' => 'Limite de compras excedido'
            ];
        }else{
            //libera o usuario
            $conexao = conectar();
            $stmt = $conexao->prepare("UPDATE conta SET Livre = 1 WHERE ID = ?");
            $stmt->bind_param("s", $user['Certificado']);
            $stmt->execute();
        }
    }
    // Sanitiza o nome da pasta para evitar caracteres inválidos
    $nomePasta = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user['Nick_name']);

    // Caminho da pasta do usuário
    $pasta = __DIR__ . "/../Elementos/Users/{$nomePasta}/";

    // Verifica e cria a pasta se necessário
    if (!is_dir($pasta)) {
        if (!mkdir($pasta, 0755, true) && !is_dir($pasta)) {
            $_SESSION['Alerta'] [] = [
                'Tipo' => 'erro',
                'Mensagem' => 'Erro ao criar a pasta do usuário'
            ];
            return '';
        }
    }

    return $pasta;
}

function compras_exibir($ID, $Certificado) {
    $pasta = User_pasta($ID, $Certificado);
    
    // Garante que o caminho termina com uma barra
    if (substr($pasta, -1) !== DIRECTORY_SEPARATOR) {
        $pasta .= DIRECTORY_SEPARATOR;
    }

    // Obtém todos os arquivos JSON na pasta
    $compras = glob($pasta . '*.json', GLOB_NOSORT);
    
    /*
        // Obtém todos os arquivos JSON na pasta, exceto "Compras.json"
        $compras = glob($pasta . '*.json', GLOB_NOSORT);
    */
    if (!$compras) return []; // Retorna um array vazio se não houver arquivos
    

    // Lê o conteúdo de cada arquivo e decodifica JSON
    $compras = array_map(function ($arquivo) {
        $conteudo = file_get_contents($arquivo);
        return $conteudo ? json_decode($conteudo, true) : null;
    }, $compras);

    // Remove entradas nulas (arquivos JSON corrompidos)
    return array_filter($compras);
}

// Function to write JSON file
function writeJsonFile($filename, $data) {
    $filepath = JSON_STORAGE_PATH . $filename;
    return file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
}

function finalizar_compra($ID, $Certificado, $Caminho_Finalizar) {
    // Carrega o JSON da compra
    $compras = json_decode(file_get_contents($Caminho_Finalizar), true);
    
    // Soma o valor total da compra
    $total = 0;
    
    foreach ($compras as $item) {
        // Converte o preço para o formato decimal (centavos para reais) e multiplica pela quantidade
        $precoDecimal = $item['preco'] / 100; // divide por 100 para obter o valor em reais
        $total += $precoDecimal * $item['quantidade'];
    }

    $totalFormatted = number_format($total, 2, ',', '.'); // Formate para exibição
    
    // Pesquisa o usuário
    $user = Pesquisar($ID, $Certificado);

    // Carrega as contas
    $contas = readJsonFile('contas.json');

    // Encontra a conta específica do usuário
    $contaIndex = array_search($user['Certificado'], array_column($contas, 'ID'));

    // Verifica se o usuário tem permissão para realizar compras
    if ($contas[$contaIndex]['Livre'] == 0) {
        throw new Exception('Usuário sem permissão para compras');
    }

    // Verifica se o total ultrapassa o limite de compras
    if ($total > $contas[$contaIndex]['Limite']) {
        // Permite ultrapassar o limite até 50
        if ($total > $contas[$contaIndex]['Limite'] + 50) {
            throw new Exception('Limite de compras ultrapassado');
        } else {
            $_SESSION['Alerta'][] = [
                'Tipo' => 'aviso',
                'Mensagem' => 'Compra realizada! Limite excedido em R$ ' . ($total - $contas[$contaIndex]['Limite'])
            ];
            
            // Bloqueia o usuário
            $contas[$contaIndex]['Livre'] = 0;
        }
    }

    // Obtém o diretório onde o arquivo original está localizado
    $diretorio = pathinfo($Caminho_Finalizar, PATHINFO_DIRNAME);

    // Gera o novo nome do arquivo
    $Nova_pasta = "Finalizado - " . date('Y-m-d_H-i-s') . '.json';

    // Renomeia o arquivo no mesmo diretório
    rename($Caminho_Finalizar, $diretorio . DIRECTORY_SEPARATOR . $Nova_pasta);

    // Atualiza o total consumido
    $contas[$contaIndex]['Consumido'] += $total;

    // Salva as alterações na conta
    writeJsonFile('contas.json', $contas);

    // Exibe mensagem de sucesso
    $_SESSION['Alerta'][] = [
        'Tipo' => 'sucesso',
        'Mensagem' => 'Compra realizada com sucesso'
    ];

    return true;
}

?>
