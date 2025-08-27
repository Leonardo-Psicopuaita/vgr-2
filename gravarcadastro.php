<?php
 include('conexao.php');

 $nome = $_POST['nome'];
 $email = $_POST['email'];
 $senha = $_POST['senha'];
 $nasc = $_POST['nasc'];

 $result = mysqli_query($conn, "INSERT INTO usuario (nome, email, senha, nasc)
values ('$nome','$email','$senha','$nasc')");
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

            </div>
            <a href="index.html"><button> Voltar</button></a> <br>
        </div>   
    </div>

</body>
</html>
