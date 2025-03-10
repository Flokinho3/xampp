<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * bd projetos_local
 * tabela users
#	Nome	Tipo	Colação	Atributos	Nulo	Padrão	Comentários	Extra	Ação
	1	ID Primária	int(11)			Não	Nenhum		AUTO_INCREMENT	Alterar Alterar	Eliminar Eliminar	
	2	Nome	text	utf8mb4_general_ci		Não	Nenhum			Alterar Alterar	Eliminar Eliminar	
	3	Email	text	utf8mb4_general_ci		Não	Nenhum			Alterar Alterar	Eliminar Eliminar	
	4	Senha	text	utf8mb4_general_ci		Não	Nenhum			Alterar Alterar	Eliminar Eliminar	
	5	Sexo	text	utf8mb4_general_ci		Não	Nenhum			Alterar Alterar	Eliminar Eliminar	
	6	Certificado Índice	int(11)			Não	Nenhum			Alterar Alterar	Eliminar Eliminar	
	7	IMG	text	utf8mb4_general_ci		Não	'Elementos\\IMGS\\undefined_image.png'			Alterar Alterar	Eliminar Eliminar	
	8	Niki	text	utf8mb4_general_ci		Não	Nenhum			Alterar Alterar	Eliminar Eliminar	
	9	Data_C	datetime			Não	Nenhum			Alterar Alterar	Eliminar Eliminar	
	10	Datan_naci	date			Não	Nenhum			Alterar Alterar	Eliminar Eliminar	

 * 
 * tabela conta
 * 	#	Nome	Tipo	Colação	Atributos	Nulo	Padrão	Comentários	Extra	Ação
	1	ID Primária	int(11)			Não	Nenhum		AUTO_INCREMENT	Alterar Alterar	Eliminar Eliminar	
	2	Limite	float			Não	150			Alterar Alterar	Eliminar Eliminar	
	3	Consumido	float			Não	0			Alterar Alterar	Eliminar Eliminar	
	4	Livre	tinyint(1)			Não	1			Alterar Alterar	Eliminar Eliminar	

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
        $_SESSION['Alerta'] = [
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
        $_SESSION['Alerta'] = [
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
        $_SESSION['Alerta'] = [
            'Tipo' => 'sucesso',
            'Mensagem' => 'Cadastro realizado com sucesso'
        ];
        header('Location: ../Porteiro/Login.php');
        exit;
    } else {
        $_SESSION['Alerta'] = [
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
        $_SESSION['Alerta'] = [
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
        $_SESSION['Alerta'] = [
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
        $_SESSION['Alerta'] = [
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
        $_SESSION['Alerta'] = [
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
        $_SESSION['Alerta'] = [
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
        $_SESSION['Alerta'] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Usuário não autenticado'
        ];
        header('Location: ../Porteiro/Login.php');
        exit;
    }

    $sql = "SELECT 
                users.Nome, 
                users.Niki, 
                users.Email, 
                users.Sexo, 
                users.Datan_naci, 
                users.IMG, 
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
    $stmt->bind_result($Nome, $Nick_name, $Email, $Sexo, $Data_nasc, $IMG, $Limite, $Consumido, $Livre);
    $stmt->fetch();

    return [
        'Nome' => $Nome,
        'Nick_name' => $Nick_name,
        'Email' => $Email,
        'Sexo' => $Sexo,
        'Data_nasc' => $Data_nasc,
        'IMG' => $IMG,
        'Limite' => $Limite,
        'Consumido' => $Consumido,
        'Livre' => $Livre
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
    // $dia_semana_pt = $dias_traduzidos[date('l')]; // Usar em produção para dia atual
    
    // Caminho do arquivo JSON com o cardápio
    $json_file = __DIR__ . '/Cardapio/Cardapio.json';
    
    if (!file_exists($json_file)) {
        $_SESSION['Alerta'] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Cardápio não encontrado'
        ];
        return [];
    }
    
    $cardapio = json_decode(file_get_contents($json_file), true);
    if ($cardapio === null) {
        $_SESSION['Alerta'] = [
            'Tipo' => 'erro',
            'Mensagem' => 'Erro ao ler o cardápio'
        ];
        return [];
    }
    
    // Retorna o cardápio do dia atual ou vazio caso não encontre
    return isset($cardapio[$dia_semana_pt]) ? [$dia_semana_pt => $cardapio[$dia_semana_pt]] : [];
}

?>
