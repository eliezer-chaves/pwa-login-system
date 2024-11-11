<?php
require_once 'session_config.php';
require_once 'db.php';

// Inicia a sessão para poder usar $_SESSION
session_start();


// Nome da página atual
$current_page = basename($_SERVER['PHP_SELF']);

// Verifica se a sessão está definida corretamente
if (!(isset($_SESSION['user_id'])) || !(isset($_SESSION['role']))) {
    // Redireciona para index.php apenas se não estiver na página index.php
    if ($current_page !== 'index.php') {
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
    // Redireciona o usuário 'admin' para deshboard.php
    if ($current_page !== 'dashboard.php') {
        header("Location: dashboard.php");
        exit();
    }
}

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
// Verifica se o método de requisição é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = trim($_POST['password']);
    
    // Prepara a consulta para buscar o usuário pelo email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifica se a senha está correta
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];  // Armazena o ID do usuário na sessão
            $_SESSION['role'] = $user['role'];   // Armazena a role do usuário
            echo $_SESSION['user_id'] . $_SESSION['role'];
            // Redireciona para a página apropriada com base na role
            if ($user['role'] === 'admin') {
                header('Location: dashboard.php');
                exit();
            } else {
                header('Location: welcome.php');
                exit();
            }
        } else {
            // Senha incorreta, redireciona com mensagem de erro
            header("Location: index.php?error=1");
            exit();
        }
    } else {
        // Usuário não encontrado, redireciona com mensagem de erro
        header("Location: index.php?error=1");
        exit();
    }
}

// Fecha a conexão com o banco de dados
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">

    <style>
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url("bg.jpg");
            /*background: linear-gradient(135deg, #8BC6EC 0%, #9599E2 100%), url('bg.jpg');
             Defini ambas as camadas corretamente */
            background-size: cover;
            /* Garante que a imagem de fundo cubra toda a tela */
            background-position: center;
            /* Centraliza a imagem de fundo */
        }

        .glass {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        .form-control:focus {
            border-color: #212529;
            box-shadow: none;
        }
       
        a:visited {
            color: #212529;
            text-decoration: none;
        }
        button:active{
            border-color: none;
        }
    </style>
</head>

<body>
    <div class="glass">
        <h2 class="text-center text-white"><b>Bem vindo!</b></h2>
        <form action="index.php" method="post">
            <div class="mb-3">
                <label for="email" class="form-label text-white">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-white">Senha</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required oninput="validatePassword()">
                    <button type="button" class="btn btn-secondary" id="togglePassword" onclick="togglePasswordVisibility()">
                        <i id="eyeIcon" class="bi bi-eye"></i>
                    </button>
                </div>

            </div>
            <script>
                function togglePasswordVisibility() {
                    const passwordInput = document.getElementById('password');
                    const eyeIcon = document.getElementById('eyeIcon');

                    // Alterna o tipo do campo de senha entre 'password' e 'text'
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        eyeIcon.classList.remove('bi-eye');
                        eyeIcon.classList.add('bi-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        eyeIcon.classList.remove('bi-eye-slash');
                        eyeIcon.classList.add('bi-eye');
                    }
                }
            </script>



            <button type="submit" class="btn btn-dark w-100">Entrar</button>
        </form>
        <div class="text-center mt-3">
            <p class="text-white">Não tem uma conta? <a href="register_page.php">Crie uma conta</a></p>
        </div>
    </div>

    <!-- Modal de erro -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Não foi possível o login!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Usuário não encontrado ou senha incorreta.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para verificar o parâmetro de erro na URL e exibir o modal
        function checkErrorModal() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('error')) {
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            }
        }

        // Executa a função ao carregar a página
        document.addEventListener("DOMContentLoaded", checkErrorModal);
    </script>
</body>

</html>