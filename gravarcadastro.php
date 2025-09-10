<?php
 include('conexao.php');

 $nome = $_POST['nome'];
 $email = $_POST['email'];
 $senha = $_POST['senha'];
 $nasc = $_POST['nasc'];
 $tel = $_POST['telefone'];

 $result = mysqli_query($conn, "INSERT INTO login(nome, email, senha, nasc, telefone)
values ('$nome','$email','$senha','$nasc'   , '$tel')");

$stmt = $mysqli->prepare("INSERT INTO CountryLanguage VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('ssssi', $nome, $email, $senha, $nasc, $tel);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="profile-card">
            <h1>Perfil do Usuário</h1>
            <div class="profile-info">
                <div><strong>Nome:</strong> <?php echo htmlspecialchars($nome); ?></div>
                <div><strong>E-mail:</strong> <?php echo htmlspecialchars($email); ?></div>
                <div><strong>Senha:</strong> ********</div>
                <div><strong>Data de Nascimento:</strong> <?php echo date("d/m/Y", strtotime($nasc)); ?></div>
                <div><strong>Telefone:</strong> <?php echo htmlspecialchars($tel); ?></div>
            </div>
            <a href="index.html"><button> Voltar</button></a> <br>
        </div>   
    </div>

</body>
</html>
