<?php
require_once 'session_config.php';

session_start();

// Verifica se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conecta ao banco de dados
    $servername = "localhost";
    $username = "root"; // Substitua pelo seu usuário do banco
    $password = ""; // Substitua pela sua senha do banco
    $dbname = "wpa_app";

    // Conexão com o banco
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }

    // Recebe a senha informada no modal
    $input_password = json_decode(file_get_contents('php://input'), true)['password'];
    $user_id = $_SESSION['user_id'];

    // Busca o usuário no banco de dados
    $sql = "SELECT senha FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        
        // Verifica se a senha informada é a mesma que está no banco
        if (password_verify($input_password, $user_data['senha'])) {
            // Deleta o usuário
            $delete_sql = "DELETE FROM users WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $user_id);
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
    $conn->close();
}
?>
