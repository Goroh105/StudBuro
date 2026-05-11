<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в систему</title>
    <link rel="stylesheet" type="text/css" href="Css/Css.css">
</head>
<body>
<div class="login-container">
    <h2>🔐 Вход в систему</h2>
    <form action="avtoriz.php" method="post">
        <label>Логин</label>
        <input type="text" name="login" required autofocus>

        <br> <label>Пароль</label>
        <input type="password" name="pass" required>

        <button type="submit">Войти</button>
    </form>
</div>
</body>
</html>