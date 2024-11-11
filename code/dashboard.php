<?php
require_once 'session_config.php';
require_once 'db.php';

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

// Verifica se o usuário está logado e se tem a role 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Se não estiver logado ou não for admin, redireciona para a página de login
    header("Location: index.php");
    exit();
}

try {
    $conexao = criarConexao();
} catch (Exception $e) {
    echo '{ "Exceção_capturada": "' . $e->getMessage() . '"}';
}

// Consulta para obter a lista de usuários
$sql = "SELECT nome, email, telefone, role, created_at FROM users";
$stmt = $conexao->prepare($sql);
$stmt->execute();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilo para o cabeçalho fixo */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #343a40;
            z-index: 1000;
            height: 75px;
        }
        .navbar .btn-logout {
            color: white;
        }
        .content {
            margin-top: 100px; /* Deixa espaço para o cabeçalho fixo */
        }
        .card-table {
            border-radius: 15px; /* Bordas arredondadas no card */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            margin-top: 20px;
        }
        /* Estilo para centralizar o conteúdo da tabela */
        .table th, .table td {
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Cabeçalho fixo com botão de logout -->
<nav class="navbar navbar-expand-lg navbar-dark mb-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin Dashboard</a>
        <a href="logout.php" class="btn btn-link btn-logout">Logout</a>
    </div>
</nav>

<!-- Conteúdo da página -->
<div class="container content">
    <h2 class="text-center">Lista de Usuários</h2>
    <div class="card card-table mt-2">
        <div class="card-header">
            <h5 class="mb-0">Usuários Registrados</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Função (Role)</th>
                        <th>Data de Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Nenhum usuário encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
