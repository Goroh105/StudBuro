<?php
include __DIR__ . '/conect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// --- ПОЛУЧАЕМ ПАРАМЕТРЫ СОРТИРОВКИ И ПОИСКА ---
$sort = $_GET['sort'] ?? 'date_asc';
$search = trim($_GET['search'] ?? '');

// Белый список сортировок
$allowedSorts = [
    'date_asc'   => ['field' => 'date_k',     'order' => 'ASC'],
    'date_desc'  => ['field' => 'date_k',     'order' => 'DESC'],
    'type_asc'   => ['field' => 'type',       'order' => 'ASC'],
    'title_asc'  => ['field' => 'title',      'order' => 'ASC'],
    'updated_asc'=> ['field' => 'date_create','order' => 'ASC'],
    'updated_desc'=>['field' => 'date_create','order' => 'DESC'],
];
if (!isset($allowedSorts[$sort])) {
    $sort = 'date_asc';
}
$orderBy = $allowedSorts[$sort]['field'] . ' ' . $allowedSorts[$sort]['order'];

// --- ФОРМИРУЕМ ЗАПРОС С ПОИСКОМ ---
if (!empty($search)) {
    // Поиск по названию (частичное совпадение, без учёта регистра)
    $stmt = $mysqli->prepare("SELECT * FROM konkurs WHERE title LIKE CONCAT('%', ?, '%') ORDER BY $orderBy");
    $stmt->bind_param('s', $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query("SELECT * FROM konkurs ORDER BY $orderBy");
}

if (!$result) {
    die('Ошибка запроса: ' . $mysqli->error);
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Конкурсы</title>
    <link rel="stylesheet" type="text/css" href="Css/Css.css">
</head>
<body>

<?php include __DIR__. '/header.html'; ?>

<div class="container">
    <div class="header">
        <h1>🏆 Конкурсы и мероприятия</h1>
        <a href="+konkurs.php" class="btn-add">➕ Добавить конкурс</a>
        <a href="export_konkurs.php" class="btn-export">📎 Выгрузить отчёт</a>
    </div>

    <!-- Блок поиска и сортировки -->
    <div class="search-sort-panel">
        <form method="get" action="" id="filterForm">
            <div class="search-wrap">
                <input type="text" name="search" placeholder="🔍 Поиск по названию..." 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                <button type="submit" class="search-btn">Найти</button>
                <?php if (!empty($search)): ?>
                    <a href="?sort=<?= $sort ?>" class="reset-btn">✖ Сбросить</a>
                <?php endif; ?>
            </div>
            <div class="sort-wrap">
                <label for="sort">Сортировать:</label>
                <select name="sort" id="sort" onchange="this.form.submit()">
                    <option value="date_asc"   <?= $sort == 'date_asc'    ? 'selected' : '' ?>>📅 Дата (сначала ближайшие)</option>
                    <option value="date_desc"  <?= $sort == 'date_desc'   ? 'selected' : '' ?>>📅 Дата (сначала дальние)</option>
                    <option value="type_asc"   <?= $sort == 'type_asc'    ? 'selected' : '' ?>>🏷️ Тип (А-Я)</option>
                    <option value="title_asc"  <?= $sort == 'title_asc'   ? 'selected' : '' ?>>📝 Название (А-Я)</option>
                    <option value="updated_desc"<?= $sort == 'updated_desc'? 'selected' : '' ?>>🕒 Недавно изменённые</option>
                    <option value="updated_asc" <?= $sort == 'updated_asc' ? 'selected' : '' ?>>🕒 Давно изменённые</option>
                </select>
            </div>
        </form>
    </div>

    <?php if ($result->num_rows === 0): ?>
        <div class="empty-state">Пока нет ни одного конкурса. Добавьте первый!</div>
    <?php else: ?>
        <div class="grid">
            <?php while ($contest = $result->fetch_assoc()): ?>
                <?php
                // Получаем файлы для текущего конкурса
                $stmt = $mysqli->prepare("SELECT * FROM file_konkurs WHERE key_id = ?");
                $stmt->bind_param('i', $contest['id']);
                $stmt->execute();
                $filesResult = $stmt->get_result();
                $files = [];
                while ($row = $filesResult->fetch_assoc()) {
                    $files[] = $row;
                }
                $stmt->close();
                ?>
                <div class="contest-card">
                    <div class="card-type-badge type-<?= htmlspecialchars($contest['type']) ?>"></div>
                    <div class="card-body">
                        <div class="contest-title"><?= htmlspecialchars($contest['title']) ?></div>
                        <div class="contest-meta">
                            <span class="meta-item">📅 <?= date('d.m.Y H:i', strtotime($contest['date_k'])) ?></span>
                            <span class="type-label"><?= htmlspecialchars($contest['type']) ?></span>
                            <span class="meta-item">🕒 обновлён: <?= date('d.m.Y H:i', strtotime($contest['date_create'])) ?></span>
                        </div>
                        <div class="contest-desc">
                            <?= nl2br(htmlspecialchars($contest['text_k'])) ?>
                        </div>
                        <?php if (count($files) > 0): ?>
                            <div class="files-section">
                                <div class="files-title">📎 Прикреплённые файлы (<?= count($files) ?>):</div>
                                <ul class="file-list">
                                    <?php foreach ($files as $file): ?>
                                        <li>
                                            <a href="<?= htmlspecialchars($file['file_put']) ?>" download>
                                                📄 <?= htmlspecialchars($file['file_name']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="files-section" style="background: none; padding-left: 0;">
                                <span style="font-size: 0.8rem; color: #94a3b8;">📂 Нет файлов</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-actions">
                        <a href="edit_konkurs.php?id=<?= $contest['id'] ?>" class="btn-edit">✏️ Редактировать</a>
                        <a href="delete_konkurs.php?id=<?= $contest['id'] ?>" class="btn-delete" 
                           onclick="return confirm('Удалить конкурс «<?= htmlspecialchars($contest['title']) ?>» и все файлы?')">🗑️ Удалить</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>