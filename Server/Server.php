<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
 * bd projetos_local
 * tabela users
    #	Nome	Tipo	Colação	Atributos	Nulo	Padrão	Comentários	Extra	Ação
	1	ID Primária	int(11)				AUTO_INCREMENT		
	2	Nome	text	utf8mb4_general_ci					
	3	Email	text	utf8mb4_general_ci					
	4	Senha	text	utf8mb4_general_ci					
	5	Sexo	text	utf8mb4_general_ci					
	6	Certificado Índice	int(11)						
	7	IMG	text	utf8mb4_general_ci		Não	'Elementos\\IMGS\\undefined_image.png'				
	8	Niki	text	utf8mb4_general_ci					
	9	Data_C	datetime						
	10	Datan_naci	date						

 * 
 * tabela conta
 * 	#	Nome	Tipo	Colação	Atributos	Nulo	Padrão	Comentários	Extra	Ação
	1	ID Primária	int(11)				AUTO_INCREMENT		
	2	Limite	float			Não	150				
	3	Consumido	float			Não	0				
	4	Livre	tinyint(1)			Não	1				

 */

// Conecta ao banco de dados
function conectar() {
    $servidor = "localhost";
    $usuario = "root";
    $senha = "";
    $banco = "projetos_local";

    $conexao = new mysqli($servidor, $usuario, $senha, $banco);

    if ($conexao->connect_error) {
        die("Erro na conexão: " . $conexao->connect_error);
    }

    return $conexao;
}

// Função de Cadastro
function Cadastro($Nome, $Email, $Senha, $Nick_name, $Data_nasc) {
    $conexao = conectar();

    // Verifica se o email já está cadastrado
    $stmt = $conexao->prepare("SELECT ID FROM users WHERE Email = ?");
    $stmt->bind_param("s", $Email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Email já cadastrado'
        ];
        header('Location: ../Porteiro/Cadastro.php');
        exit;
    }

    // Verifica se o apelido já está cadastrado
    $stmt->prepare("SELECT ID FROM users WHERE Niki = ?");
    $stmt->bind_param("s", $Nick_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Apelido já cadastrado'
        ];
        header('Location: ../Porteiro/Cadastro.php');
        exit;
    }

    // Gerar certificado único
    $Certificado = Gerar_certificado($conexao);

    // Inserir registro na tabela conta
    $stmt = $conexao->prepare("INSERT INTO conta (Limite, Consumido, Livre) VALUES (150, 0, 1)");
    $stmt->execute();
    $Certificado = $conexao->insert_id;

    // Cadastro do usuário
    $senha_criptografada = password_hash($Senha, PASSWORD_DEFAULT);
    $stmt = $conexao->prepare("INSERT INTO users (Nome, Email, Senha, Niki, Data_C, Datan_naci, Certificado) VALUES (?, ?, ?, ?, NOW(), ?, ?)");
    $stmt->bind_param("ssssss", $Nome, $Email, $senha_criptografada, $Nick_name, $Data_nasc, $Certificado);

    if ($stmt->execute()) {
        $_SESSION['Alerta'] []=[
            'Tipo' => 'sucesso',
            'Mensagem' => 'Cadastro realizado com sucesso'
        ];
        header('Location: ../Porteiro/Login.php');
        exit;
    } else {
        $_SESSION['Alerta'] []=[
            'Tipo' => 'erro',
            'Mensagem' => 'Erro ao cadastrar'
        ];
        header('Location: ../Porteiro/Cadastro.php');
        exit;
    }

    $stmt->close();
    $conexao->close();
}

// Função de Login
function Login($Email, $Senha) {
    $conexao = conectar();

    // Verifica se o email existe
    $stmt = $conexao->prepare("SELECT Certificado, Senha, ID FROM users WHERE Email = ?");
    $stmt->bind_param("s", $Email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Email não cadastrado'
        ];
        header('Location: ../Porteiro/Login.php');
        exit;
    }

    $stmt->bind_result($Certificado, $Senha_hash, $ID);
    $stmt->fetch();

    // Verifica a senha
    if (password_verify($Senha, $Senha_hash)) {
        $_SESSION['ID'] = $ID;
        $_SESSION['Certificado'] = $Certificado;
        header('Location: ../Home/Home.php');
        exit;
    } else {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Senha incorreta'
        ];
        header('Location: ../Porteiro/Login.php');
        exit;
    }
}

// Gera um certificado de 4 dígitos único
function Gerar_certificado($conexao) {
    do {
        $certificado = rand(1000, 9999);
        $stmt = $conexao->prepare("SELECT ID FROM users WHERE Certificado = ?");
        $stmt->bind_param("s", $certificado);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);

    return $certificado;
}

// Verifica se o certificado é válido
function Verificar_certificado($Certificado) {
    if (!isset($_SESSION['ID']) || !isset($_SESSION['Certificado'])) {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Usuário não autenticado'
        ];
        return false;
    }

    $conexao = conectar();
    $stmt = $conexao->prepare("SELECT ID FROM users WHERE Certificado = ?");
    $stmt->bind_param("s", $Certificado);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        //encerra a sessão evita loop infinito
        session_destroy();
        //redireciona para a página de login
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Certificado não cadastrado'
        ];
        return false;
    }

    $stmt->bind_result($ID);
    $stmt->fetch();

    if ($ID == $_SESSION['ID'] && $Certificado == $_SESSION['Certificado']) {
        return true;
    } else {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Certificado inválido'
        ];
        return false;
    }
}

// função para pesquisar as informaçoes do usuario econsumo
function Pesquisar($ID, $Certificado) {
    $conexao = conectar();
    $stmt = $conexao->prepare("SELECT Nome, Niki, Email, Sexo, Datan_naci, IMG FROM users WHERE ID = ? AND Certificado = ?");
    $stmt->bind_param("ss", $ID, $Certificado);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $_SESSION['Alerta'] [] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Usuário não autenticado'
        ];
        header('Location: ../Porteiro/Login.php');
        exit;
    }

    $sql = "SELECT 
                users.ID,
                users.Nome, 
                users.Niki, 
                users.Email, 
                users.Sexo, 
                users.Datan_naci, 
                users.IMG, 
                users.Certificado,
                conta.Limite, 
                conta.Consumido, 
                conta.Livre 
            FROM users 
            INNER JOIN conta ON users.Certificado = conta.ID 
            WHERE users.ID = ? AND users.Certificado = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ss", $ID, $Certificado);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($ID, $Nome, $Nick_name, $Email, $Sexo, $Data_nasc, $IMG, $Certificado ,$Limite, $Consumido, $Livre);
    $stmt->execute();
    $stmt->fetch();

    return [
        'ID' => $ID,
        'Nome' => $Nome,
        'Nick_name' => $Nick_name,
        'Email' => $Email,
        'Sexo' => $Sexo,
        'Data_nasc' => $Data_nasc,
        'IMG' => $IMG,
        'Limite' => $Limite,
        'Consumido' => $Consumido,
        'Livre' => $Livre,
        'Certificado' => $Certificado
    ];

    $stmt->close();
    $conexao->close();
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
    $dia_semana_pt = 'Quarta';
    //$dia_semana_pt = $dias_traduzidos[date('l')]; // Usar em produção para dia atual
    
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

function finalizar_compra($ID, $Certificado, $Caminho_Finalizar) {
    $conexao = conectar();
    $Caminho = $Caminho_Finalizar;
    
    // Carrega o JSON
    $compras = json_decode(file_get_contents($Caminho), true);
    
    // Soma o valor total da compra
    $total = 0;
    
    foreach ($compras as $item) {
        // Converte o preço para o formato decimal (centavos para reais) e multiplica pela quantidade
        $precoDecimal = $item['preco'] / 100; // divide por 100 para obter o valor em reais
        $total += $precoDecimal * $item['quantidade'];
    }

    // Não formate ainda o total, continue com o valor numérico para cálculos
    $totalFormatted = number_format($total, 2, ',', '.'); // Formate para exibição apenas
    
    // Pesquisa o usuário
    $user = Pesquisar($ID, $Certificado);

    // Verifica se o usuário tem permissão para realizar compras
    if ($user['Livre'] == 0) {
        throw new Exception('Usuário sem permissão para compras');
    }

    // Verifica se o total ultrapassa o limite de compras
    if ($total > $user['Limite']) {
        // Permite ultrapassar o limite até 50
        if ($total > $user['Limite'] + 50) {
            throw new Exception('Limite de compras ultrapassado');
        } else {
            $_SESSION['Alerta'] [] = [
                'Tipo' => 'aviso',
                'Mensagem' => 'Compra relizada! Limite exedido em R$ ' . ($total - $user['Limite'])
            ];
            // blokeia o usuario
            $conexao = conectar();
            $stmt = $conexao->prepare("UPDATE conta SET Livre = 0 WHERE ID = ?");
            $stmt->bind_param("s", $user['Certificado']);
            $stmt->execute();

        }
    }
    // Obtém o diretório onde o arquivo original está localizado
    $diretorio = pathinfo($Caminho, PATHINFO_DIRNAME);

    // Gera o novo nome do arquivo
    $Nova_pasta = "Finalizado - " . date('Y-m-d_H-i-s') . '.json';

    // Renomeia o arquivo no mesmo diretório
    rename($Caminho, $diretorio . DIRECTORY_SEPARATOR . $Nova_pasta);


    // Atualiza o total consumido
    $consumido = $user['Consumido'] + $total;
    $stmt = $conexao->prepare("UPDATE conta SET Consumido = ? WHERE ID = ?");
    $stmt->bind_param("ss", $consumido, $user['Certificado']);
    $stmt->execute();

    // Exibe mensagem de sucesso
    $_SESSION['Alerta'] [] = [
        'Tipo' => 'sucesso',
        'Mensagem' => 'Compra realizada com sucesso'
    ];
    return true;
    header('Location: ../Home/Carrinho.php');
}

?>
