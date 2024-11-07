<?php
require_once 'session_config.php';
require_once 'db.php';

// Inicia a sessão
session_start();

// Verifica se o usuário está logado e se o ID da sessão corresponde ao ID do usuário
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== (int)$_POST['user_id']) {
    echo json_encode(['status' => 'error', 'message' => 'Você precisa estar logado para atualizar seus dados.']);
    exit();
}

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Erro de conexão com o banco de dados.']);
    exit();
}

// Verifica se a requisição é do tipo POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];  // Recebe o user_id diretamente do formulário ou requisição
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $senha = $_POST['senha'];
    $confirmarSenha = $_POST['confirmarSenha'];

    // Verifica se a senha foi fornecida e se as senhas correspondem
    if (!empty($senha)) {
        if ($senha !== $confirmarSenha) {
            echo json_encode(['status' => 'error', 'message' => 'As senhas não correspondem.']);
            exit();
        }
        // Hash da nova senha
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET nome = ?, email = ?, telefone = ?, senha = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $email, $telefone, $senhaHash, $user_id);
    } else {
        $sql = "UPDATE users SET nome = ?, email = ?, telefone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nome, $email, $telefone, $user_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Dados atualizados com sucesso.']);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar os dados. Tente novamente.']);
        exit();
    }

    // Fecha a declaração
    $stmt->close();
}

// Fecha a conexão com o banco de dados
$conn->close();
