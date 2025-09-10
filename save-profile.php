<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo 'Método inválido';
    exit;
}

$user_name = trim($_POST['user_name'] ?? '');
if (empty($user_name)) {
    http_response_code(400);
    echo 'Nome é obrigatório';
    exit;
}

// Salvar nome na sessão
$_SESSION['user_name'] = htmlspecialchars($user_name);

// Lidar com upload de avatar
if (isset($_POST['avatar']) && !empty($_POST['avatar'])) {
    $avatarBase64 = $_POST['avatar'];
    if (preg_match('/^data:image\/(\w+);base64,/', $avatarBase64, $type)) {
        $avatarBase64 = substr($avatarBase64, strpos($avatarBase64, ',') + 1);
        $type = strtolower($type[1]);
        if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
            http_response_code(400);
            echo 'Formato inválido (use JPG, PNG ou GIF)';
            exit;
        }
        $avatarData = base64_decode($avatarBase64);
        if ($avatarData === false) {
            http_response_code(400);
            echo 'Falha na decodificação';
            exit;
        }
        $avatarDir = __DIR__ . '/avatars/';
        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }
        $filename = 'avatar_' . session_id() . '_' . time() . '.' . $type;
        $filepath = $avatarDir . $filename;
        if (file_put_contents($filepath, $avatarData)) {
            $_SESSION['user_avatar'] = 'avatars/' . $filename;
        } else {
            http_response_code(500);
            echo 'Erro ao salvar imagem';
            exit;
        }
    } else {
        http_response_code(400);
        echo 'Formato inválido da imagem';
        exit;
    }
}

echo 'OK';
?>