<?php

// Configurações do banco de dados
//$servername = "db4free.net";
//$username = "admin_pwa_app"; // Substitua pelo seu usuário do banco
//$password = "Senai@301"; // Substitua pela sua senha do banco
//$dbname = "pwa_app";

// Conexão com o banco
//$conn = new mysqli($servername, $username, $password, $dbname);
?>

<?php 	
	function criarConexao() {
		$host = 'db4free.net'; // 200.145.23.2
		$db   = 'pwa_app';
		$user = 'admin_pwa_app';
		$pass = 'Senai@301';
		$charset = 'utf8mb4';
		$options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
		$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=$charset";
		try {
			return new PDO($dsn, $user, $pass, $options);
		} catch (\PDOException $e) {
			throw new \PDOException($e->getMessage(), (int)$e->getCode());
		}
	}
	
	function executarQuery($conexao, $sql) {
		try {
			return $conexao->query($sql);
		} catch (\PDOException $e) {
			throw new \PDOException($e->getMessage(), (int)$e->getCode());
		}
	}
    function closeConn($conexao){
         // Check if it's a PDO connection
    if ($conexao instanceof PDO) {
        $conexao = null; // Close the PDO connection
    }
    }
?>