<?php
include __DIR__ . '/conect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Студенческое бюро</title>
    <link rel="stylesheet" type="text/css" href="Css/Css.css">
</head>
<body>
    <div class="form-container">
    <h2>➕ Новый конкурс / мероприятие</h2>
    <form action="save.php" method="post" enctype="multipart/form-data">
        <label>Название конкурса *</label>
        <input type="text" name="title" required>

        <label>Описание</label>
        <textarea name="text_k" rows="4"></textarea>

        <label>Дата проведения *</label>
        <input type="date" name="event_date" required>

        <label>Время проведения *</label>
        <input type="time" name="event_time" required>
        <small>Например: 14:30</small>

        <label>Тип конкурса</label>
        <select name="type" required>
            <option value="вузовский">Вузовский</option>
            <option value="межвузовский">Межвузовский</option>
            <option value="региональный">Региональный</option>
            <option value="всероссийский">Всероссийский</option>
        </select>

        <label>Файлы (можно выбрать несколько)</label>
        <input type="file" name="files[]" multiple>

        <button type="submit">Сохранить конкурс</button>
    </form>
    <a href="konkursall.php" class="back">← Назад к списку</a>
</div>
</body>
</html>