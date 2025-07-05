<?php
require_once 'session_config.php';
require_once 'db.php';

session_start();

// Verifica se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexao = criarConexao();
    } catch (Exception $e) {
        echo '{ "Exceção_capturada": "' . $e->getMessage() . '"}';
    }

    // Recebe a senha informada no modal
    $input_password = json_decode(file_get_contents('php://input'), true)['password'];
    $user_id = $_SESSION['user_id'];

    // Busca o usuário no banco de dados
    $sql = "SELECT senha FROM users WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Verifica se a senha informada é a mesma que está no banco
        if (password_verify($input_password, $result['senha'])) {
            // Deleta o usuário
            $delete_sql = "DELETE FROM users WHERE id = :id";
            $delete_stmt = $conexao->prepare($delete_sql);
            $delete_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $delete_stmt->execute();

            // Apaga os dados da sessão
            session_destroy();

            // Retorna sucesso
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Senha incorreta.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado.']);
    }

    // Fecha a conexão
    $conexao = null;
}