<?php
function criarConexao()
{
	$host = 'db4free.net'; // 200.145.23.2
	$db = '';
	$user = '';
	$pass = '';
	$charset = 'utf8mb4';
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=$charset";
	try {
		return new PDO($dsn, $user, $pass, $options);
	} catch (\PDOException $e) {
		throw new \PDOException($e->getMessage(), (int) $e->getCode());
	}
}

function executarQuery($conexao, $sql)
{
	try {
		return $conexao->query($sql);
	} catch (\PDOException $e) {
		throw new \PDOException($e->getMessage(), (int) $e->getCode());
	}
}
function closeConn($conexao)
{
	// Check if it's a PDO connection
	if ($conexao instanceof PDO) {
		$conexao = null; // Close the PDO connection
	}
}
?>