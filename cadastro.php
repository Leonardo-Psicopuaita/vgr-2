<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
<form action = "gravarcadastro.php" method = "POST">

    <div class="logo"><img src ="imagens fodas/logocortada.png" alt="">
    </div>

    <p>Faça seu cadastro</p>
    <label for="">Insira seu o nome</label>
    <input type ="text" name="nome" id=""><br>       

    <label for="">Insira o seu Email</label>
    <input type ="text" name="email" id=""><br>   

    <label for="">Insira a sua senha</label>
    <input type ="password" name="senha" id=""><br>  

    <label for="">Insira sua data de nascimento </label>
    <input type ="date" name="nasc" id=""><br>      

    <input type="submit" name="" id="" value="Mandar informações" ><br>
    <input type="reset" name="" id="" value="Limpar"><br>
    </form>

    <a href="index.html"><button> Voltar</button></a> <br>
</body>
</html>

