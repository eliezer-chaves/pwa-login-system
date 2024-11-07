<?php
require_once 'session_config.php';
require_once 'db.php';

// Inicia a sessão
session_start();

// Nome da página atual
$current_page = basename($_SERVER['PHP_SELF']);

// Verifica se a sessão está definida corretamente
if (!(isset($_SESSION['user_id'])) || !(isset($_SESSION['role']))) {
    // Se a sessão não está definida, permite que o usuário permaneça na página de registro
    if ($current_page !== 'register_page.php' && $current_page !== 'index.php') {
        header("Location: index.php");
        exit();
    }
} else if ($_SESSION['role'] == 'user') {
    // Redireciona o usuário 'user' para welcome.php
    if ($current_page !== 'welcome.php') {
        header("Location: welcome.php");
        exit();
    }
} else if ($_SESSION['role'] == 'admin') {
    // Redireciona o usuário 'admin' para dashboard.php
    if ($current_page !== 'dashboard.php') {
        header("Location: dashboard.php");
        exit();
    }
}


// Verifica se houve erro na conexão
if ($conn->connect_error) {
    error_log("Erro de conexão: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados.']);
    exit();
}


// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    //$senha = $_POST['password'];
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

    // Verifica se o email é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Formato de email inválido.']);
        exit();
    }

    // Hash da senha
    $senhaHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Verifica se o email já existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Se o email já existe, retorna erro com mensagem
        echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado.']);
        exit();
    } else {
        // Inserir o novo usuário
        $stmt = $conn->prepare("INSERT INTO users (nome, email, senha, telefone, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $nome, $email, $senhaHash, $telefone, $role);

        if ($stmt->execute()) {
            // Armazena o ID do usuário recém-criado
            $user_id = $stmt->insert_id;

            // Armazena o user_id e a role na sessão
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;

            // Define a URL de redirecionamento com base na role
            $redirect_url = ($role === 'admin') ? 'dashboard.php' : 'welcome.php';
            // Retorna a resposta JSON com a URL para redirecionamento
            echo json_encode(['success' => true, 'redirect' => $redirect_url]);
            exit();
        } else {
            // Se houver erro ao registrar o usuário
            error_log("Erro ao registrar o usuário: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar o usuário. Verifique os dados inseridos.']);
            exit();
        }
    }

    // Fecha a declaração
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #8BC6EC;
            background-image: linear-gradient(135deg, #8BC6EC 0%, #9599E2 100%);
        }

        .glass {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            padding: 30px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        .feedback {
            font-size: 0.9em;
            color: red;
            display: none;
        }

        .valid-feedback {
            color: green;
        }
        
    </style>
</head>

<body>
    <div class="glass my-5">
        <h2 class="text-center">Registrar-se</h2>
        <form id="registrationForm" class="mt-5" method="post">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="tel" class="form-control" id="telefone" name="telefone">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required oninput="validatePassword()">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword" onclick="togglePasswordVisibility()">
                        <i id="eyeIcon" class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="passwordFeedback" class="feedback">A senha deve ter pelo menos 6 caracteres.</div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmar Senha</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm_password" required oninput="checkPasswordsMatch()">
                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword" onclick="toggleConfirmPasswordVisibility()">
                        <i id="eyeIconConfirm" class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="confirmPasswordFeedback" class="feedback">As senhas não coincidem.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Função (Role)</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="role" id="roleUser" value="user" checked required>
                    <label class="form-check-label" for="roleUser">Usuário</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="role" id="roleAdmin" value="admin" required>
                    <label class="form-check-label" for="roleAdmin">Admin</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="submitButton" disabled>Registrar</button>
        </form>
    </div>

    <!-- Modal de Erro -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Erro de Validação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorMessage"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordFeedback = document.getElementById('passwordFeedback');
        const confirmPasswordFeedback = document.getElementById('confirmPasswordFeedback');
        const submitButton = document.getElementById('submitButton');
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeIconConfirm = document.getElementById('eyeIconConfirm');

        // Função para alternar a visibilidade da senha
        function togglePasswordVisibility() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            eyeIcon.classList.toggle('bi-eye-slash');
        }

        // Função para alternar a visibilidade da confirmação de senha
        function toggleConfirmPasswordVisibility() {
            const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
            confirmPasswordInput.type = type;
            eyeIconConfirm.classList.toggle('bi-eye-slash');
        }

        // Função para validar a senha
        function validatePassword() {
            if (passwordInput.value.length < 6) {
                passwordFeedback.style.display = 'block';
                submitButton.disabled = true;
            } else {
                passwordFeedback.style.display = 'none';
                submitButton.disabled = false;
            }
        }

        // Função para verificar se as senhas coincidem
        function checkPasswordsMatch() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordFeedback.style.display = 'block';
                submitButton.disabled = true;
            } else {
                confirmPasswordFeedback.style.display = 'none';
                submitButton.disabled = false;
            }
        }

        // Função para exibir o modal de erro
        function showErrorModal(message) {
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.textContent = message;
            const modal = new bootstrap.Modal(document.getElementById('errorModal'));
            modal.show();
        }



        $('#registrationForm').on('submit', function(e) {
            e.preventDefault(); // Impede o envio normal do formulário

            $.post("register_page.php", $(this).serialize(), function(data) {
                try {
                    const response = JSON.parse(data);
                    console.log(response); // Verifique a resposta no console

                    if (response.success === false) {
                        // Exibe a mensagem de erro se houver falha
                        showErrorModal(response.message);
                    } else if (response.success === true) {
                        // Redireciona após o sucesso sem exibir o modal
                        window.location.href = response.redirect;
                    }
                } catch (e) {
                    console.error('Erro ao processar resposta JSON:', e);
                    showErrorModal('Erro inesperado ao processar a resposta.');
                }
            }).fail(function(xhr, status, error) {
                // Caso haja um erro na requisição AJAX
                console.error("Erro na requisição:", error);
                showErrorModal('Erro inesperado ao processar a requisição.');
            });
        });
    </script>
</body>

</html>