
<?php
if($_SERVER ['REQUEST_METHOD'] == 'POST') {

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $nasc = $_POST['nasc'];
    $tel = $_POST['telefone'];
    
}

$host = "localhost"; // xampp instalado e ativado
$bd = "vgr"; // criar uma base com um nome qualquer
$usuario = "root"; // nome do usario para acesso ao banco de dados
$senha = ""; // senha do usario para o acesso ao banco de dados

$conn = mysqli_connect($host, $usuario, $senha, $bd);

