<?php
require_once '../functions/session_config.php';
require_once '../db/db.php';


// Inicia a sessão
session_start();

// Verificar se a sessão já foi iniciada e se o tempo de expiração já passou
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
    // Sessão expirada após 30 minutos
    session_unset(); // Limpar as variáveis de sessão
    session_destroy(); // Destruir a sessão
    header("Location: index.php"); // Redirecionar para a página de login
    exit();
}

// Atualizar o timestamp da última atividade
$_SESSION['last_activity'] = time();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: index.php");
    exit();
}

// Verifica se o usuário tem a role 'user'
if ($_SESSION['role'] !== 'user') {
    // Se o usuário não for da role 'user', redireciona para outra página (ex: dashboard)
    header("Location: dashboard.php");
    exit();
}

try {
    $conexao = criarConexao();
} catch (Exception $e) {
    echo '{ "Exceção_capturada": "' . $e->getMessage() . '"}';
}

// Buscar o usuário logado diretamente pelo ID (presumindo que o ID seja passado por GET ou POST)
$user_id = $_SESSION['user_id']; // Agora pegamos o ID diretamente da sessão

// Prepara a consulta para buscar nome, email e telefone do usuário pelo ID usando PDO
$sql = "SELECT nome, email, telefone FROM users WHERE id = :id";
$stmt = $conexao->prepare($sql);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();

// Verifica se o usuário foi encontrado
if ($stmt->rowCount() > 0) {
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    echo "Usuário não encontrado.";
    exit();
}

// Fecha a conexão
$conexao = null;
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            padding-top: 60px;
            /* Compensa o header fixo */
        }

        .header {
            background-color: rgba(255, 255, 255, 0.9);

            position: fixed;
            top: 0;
            width: 100%;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header a {
            text-decoration: none;
            color: #007bff;
            min-height: 50px;

        }

        .welcome {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
            margin: auto;
        }

        .feedback {
            display: none;
            font-size: 0.9em;
        }

        .valid-feedback {
            color: green;
        }

        .invalid-feedback {
            color: red;
        }

        label {
            text-align: left;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">

</head>

<body class="bg-dark">
    <!-- Header fixo -->
    <div class="header d-flex justify-content-between align-items-center px-4">
        <span>Bem-vindo, <?php echo htmlspecialchars($user_data['nome']); ?>!</span>
        <a href="../functions/logout.php" class="d-flex justify-content-between align-items-center">Sair</a>
    </div>

    <!-- Formulário com dados do usuário -->
    <div class="welcome my-4">
        <h2 class="mb-3">Meus Dados</h2>
        <form id="updateForm" onsubmit="event.preventDefault(); updateUserData();">

            <div class="mb-3 text-start">
                <label for="nome" class="form-label ">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome"
                    value="<?php echo htmlspecialchars($user_data['nome']); ?>" required>
            </div>
            <div class="mb-3 text-start">
                <label for="email" class="form-label text-start">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
            </div>
            <div class="mb-3 text-start">
                <label for="telefone" class="form-label text-start">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone"
                    value="<?php echo htmlspecialchars($user_data['telefone']); ?>" required>
            </div>
            <div class="mb-1 text-start">
                <label class="form-label text-danger">Para atualizar os dados é necessário confirmar a senha ou criar
                    uma nova.</label>
            </div>
            <div class="mb-3 text-start">
                <label for="senha" class="form-label">Senha</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="senha" name="senha" minlength="6"
                        onkeyup="validatePassword()" placeholder="Mínimo 6 caracteres">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="togglePasswordVisibility('senha', 'eyeIconSenha')">
                        <i id="eyeIconSenha" class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="passwordFeedback" class="feedback"></div>
            </div>

            <div class="mb-3 text-start">
                <label for="confirmarSenha" class="form-label">Confirmar Senha</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirmarSenha" name="confirmarSenha"
                        onkeyup="confirmPassword()" placeholder="Repita a senha">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="togglePasswordVisibility('confirmarSenha', 'eyeIconConfirmarSenha')">
                        <i id="eyeIconConfirmarSenha" class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="confirmPasswordFeedback" class="feedback"></div>
            </div>
            <script>
                function togglePasswordVisibility(inputId, iconId) {
                    const passwordInput = document.getElementById(inputId);
                    const eyeIcon = document.getElementById(iconId);

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


            <div class="d-flex justify-content-between">
                <!-- Botão para excluir o usuário -->
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                    data-bs-target="#deleteModal">Excluir Conta</button>

                <button type="submit" id="updateButton" class="btn btn-primary" disabled>Atualizar Dados</button>
            </div>

        </form>
    </div>

    <!-- Modal de sucesso -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Sucesso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="successModalBody">
                    <!-- A mensagem de sucesso será inserida aqui -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de erro -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Erro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorModalBody">
                    <!-- A mensagem de erro será inserida aqui -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal de confirmação para excluir a conta -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Para excluir sua conta, confirme sua senha.</p>
                    <input type="password" class="form-control" id="deletePassword" placeholder="Digite sua senha">
                    <div id="deletePasswordFeedback" class="feedback text-danger"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="deleteConfirmButton"
                        onclick="deleteUser()">Confirmar Exclusão</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        // Função para excluir o usuário após a confirmação da senha
        function deleteUser() {
            const password = document.getElementById("deletePassword").value;
            const passwordFeedback = document.getElementById("deletePasswordFeedback");

            // Valida a senha
            if (password.length < 6) {
                passwordFeedback.textContent = "A senha deve ter pelo menos 6 caracteres.";
                passwordFeedback.style.display = "block";
                return;
            }

            // Se a senha for válida, envia a requisição para deletar o usuário
            fetch('../functions/delete_user.php', {
                method: 'POST',
                body: JSON.stringify({
                    password: password
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Exclui os dados da sessão e redireciona para o login
                        window.location.href = '../index.php';
                    } else {
                        passwordFeedback.textContent = data.message;
                        passwordFeedback.style.display = "block";
                    }
                })
                .catch(error => {
                    console.error('Erro ao excluir usuário:', error);
                });
        }

        // Funções JavaScript para validação de senha e atualização
        function validatePassword() {
            const password = document.getElementById("senha").value;
            const passwordFeedback = document.getElementById("passwordFeedback");
            if (password.length >= 6) {
                passwordFeedback.textContent = "Senha válida.";
                passwordFeedback.classList.add("valid-feedback");
                passwordFeedback.classList.remove("invalid-feedback");
                passwordFeedback.style.display = "block";
                document.getElementById("updateButton").disabled = false;
            } else {
                passwordFeedback.textContent = "A senha deve ter pelo menos 6 caracteres.";
                passwordFeedback.classList.add("invalid-feedback");
                passwordFeedback.classList.remove("valid-feedback");
                passwordFeedback.style.display = "block";
                document.getElementById("updateButton").disabled = true;
            }
        }

        function confirmPassword() {
            const password = document.getElementById("senha").value;
            const confirmPassword = document.getElementById("confirmarSenha").value;
            const confirmPasswordFeedback = document.getElementById("confirmPasswordFeedback");
            if (password === confirmPassword) {
                confirmPasswordFeedback.textContent = "As senhas coincidem.";
                confirmPasswordFeedback.classList.add("valid-feedback");
                confirmPasswordFeedback.classList.remove("invalid-feedback");
                document.getElementById("updateButton").disabled = false;
            } else {
                confirmPasswordFeedback.textContent = "As senhas não coincidem.";
                confirmPasswordFeedback.classList.add("invalid-feedback");
                confirmPasswordFeedback.classList.remove("valid-feedback");
                document.getElementById("updateButton").disabled = true;
            }
            confirmPasswordFeedback.style.display = "block";
        }

        function clearFields() {
            document.getElementById("senha").value = "";
            document.getElementById("confirmarSenha").value = "";
            document.getElementById("passwordFeedback").style.display = "none";
            document.getElementById("confirmPasswordFeedback").style.display = "none";
            document.getElementById("updateButton").disabled = true;
        }


        function showSuccessModal(message) {
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            document.getElementById('successModalBody').innerText = message;
            successModal.show();
        }

        function showErrorModal(message) {
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            document.getElementById('errorModalBody').innerText = message;
            errorModal.show();
        }


        function updateUserData() {
            var formData = $('#updateForm').serializeArray(); // Obtém os dados do formulário como array
            formData.push({
                name: "user_id",
                value: "<?php echo $user_id; ?>"
            }); // Adiciona o user_id

            $.ajax({
                url: '../functions/update_user.php',
                type: 'POST',
                dataType: 'json',
                data: $.param(formData), // Converte de volta para string de consulta
                success: function (response) {
                    if (response.status === 'success') {
                        showSuccessModal(response.message)
                        //alert('Dados atualizados com sucesso!');
                    } else {
                        showErrorModal(response.message)
                        //alert('Erro: ' + response.message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    showErrorModal(jqXHR.responseText)
                    //console.log("Erro na requisição:", textStatus, errorThrown);
                    //console.log("Resposta do servidor:", jqXHR.responseText);
                    //alert('Erro na requisição. Tente novamente.');
                }
            });
        }
        // Detecta o fechamento do modal de sucesso e atualiza a página
        $('#successModal').on('hidden.bs.modal', function () {
            location.reload(); // Atualiza a página
        });
    </script>
</body>

</html>