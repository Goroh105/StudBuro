<?php
include __DIR__ . '/conect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) die('Неверный ID');

$stmt = $mysqli->prepare("SELECT * FROM konkurs WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$contest = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$contest) die('Конкурс не найден');

$stmt = $mysqli->prepare("SELECT * FROM file_konkurs WHERE key_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$date_k = date('Y-m-d', strtotime($contest['date_k']));
$time_k = date('H:i', strtotime($contest['date_k']));
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование конкурса</title>
<link rel="stylesheet" type="text/css" href="Css/Css.css">
</head>
<body>
<?php 
include __DIR__. '/header.html';
?>
<div class="form-container">
    <h2>✏️ Редактирование конкурса</h2>
    <form action="update_konkurs.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="contest_id" value="<?= $contest['id'] ?>">

        <label>Название конкурса</label>
        <input type="text" name="title" value="<?= htmlspecialchars($contest['title']) ?>" required>

        <label>Описание</label>
        <textarea name="text_k" rows="4"><?= htmlspecialchars($contest['text_k']) ?></textarea>

        <label>Дата проведения</label>
        <input type="date" name="event_date" value="<?= $date_k ?>" required>

        <label>Время проведения</label>
        <input type="time" name="event_time" value="<?= $time_k ?>" required>

        <label>Тип конкурса</label>
        <select name="type" required>
            <option value="вузовский" <?= $contest['type'] == 'вузовский' ? 'selected' : '' ?>>Вузовский</option>
            <option value="межвузовский" <?= $contest['type'] == 'межвузовский' ? 'selected' : '' ?>>Межвузовский</option>
            <option value="региональный" <?= $contest['type'] == 'региональный' ? 'selected' : '' ?>>Региональный</option>
            <option value="всероссийский" <?= $contest['type'] == 'всероссийский' ? 'selected' : '' ?>>Всероссийский</option>
        </select>

        <h3>📎 Текущие файлы</h3>
        <?php if (count($files) > 0): ?>
            <?php foreach ($files as $file): ?>
                <div class="file-item">
                    <span><?= htmlspecialchars($file['file_name']) ?></span>
                    <a href="delete_file.php?id=<?= $file['id'] ?>&contest_id=<?= $contest['id'] ?>" 
                       class="delete-file" 
                       onclick="return confirm('Удалить этот файл?')">🗑️ Удалить</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Файлов нет</p>
        <?php endif; ?>

        <h3>➕ Добавить новые файлы</h3>
        <input type="file" name="files[]" multiple>

        <button type="submit">Сохранить изменения</button>
    </form>
    <a href="konkursall.php" class="back">← Назад к списку</a>
</div>
</body>
</html>