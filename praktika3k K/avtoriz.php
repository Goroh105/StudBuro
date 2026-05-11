<?php
session_start();
include __DIR__ . '/conect.php';

$login = trim($_POST['login']);
$pass = trim($_POST['pass']);

if (empty($login) || empty($pass)) {
    die('Заполните все поля');
}

$stmt = $mysqli->prepare("SELECT id, login, pass FROM users WHERE login = ?");
if (!$stmt) {
    die('Ошибка подготовки запроса: ' . $mysqli->error);
}

$stmt->bind_param('s', $login);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
  
    $passwordValid = false;
    
    if (password_verify($pass, $user['pass'])) {
        $passwordValid = true;
    }
  
    elseif (md5($pass) === $user['pass']) {
        $passwordValid = true;
    
        $newHash = password_hash($pass, PASSWORD_DEFAULT);
        $updateStmt = $mysqli->prepare("UPDATE users SET pass = ? WHERE id = ?");
        $updateStmt->bind_param('si', $newHash, $user['id']);
        $updateStmt->execute();
        $updateStmt->close();
    }
    
    if ($passwordValid) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_login'] = $user['login'];
        header('Location: index.php');
        exit;
    }
}


header('Location: login.php?error=1');
exit;
?>