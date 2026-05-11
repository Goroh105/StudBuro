<?php
include __DIR__ . '/conect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$year = date('Y');
$month = date('m');
$daysInMonth = date('t', mktime(0,0,0,$month,1,$year));
$firstDayWeek = date('N', mktime(0,0,0,$month,1,$year)); // 1-7 (пн-вс)

$startDate = "$year-$month-01";
$endDate = date('Y-m-t', strtotime($startDate));
$stmt = $mysqli->prepare("SELECT id, title, date_k FROM konkurs WHERE date_k BETWEEN ? AND ? ORDER BY date_k");
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$eventsByDay = [];
foreach ($events as $e) {
    $day = (int)date('j', strtotime($e['date_k']));
    $eventsByDay[$day][] = $e;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная – Календарь</title>
    <link rel="stylesheet" type="text/css" href="Css/Css.css">
</head>
<body>
<?php include 'header.html'; ?>
<div class="container">
    <h1>🏠 Календарь мероприятий</h1>
    <div class="dashboard">
        
        <div class="mini-calendar">
            <div class="month-name"><?= date('F Y', strtotime($startDate)) ?></div>
            <div class="cal-weekdays">
                <span>Пн</span><span>Вт</span><span>Ср</span><span>Чт</span><span>Пт</span><span>Сб</span><span>Вс</span>
            </div>
            <div class="cal-days">
                <?php
                $day = 1;
                for ($i = 1; $i < $firstDayWeek; $i++) {
                    echo '<div class="cal-day empty-day"></div>';
                }
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $hasEvent = isset($eventsByDay[$d]);
                    $class = $hasEvent ? 'cal-day event' : 'cal-day';
                    echo "<div class='$class'>$d</div>";
                }
                ?>
            </div>
        </div>
        <div class="events-list">
            <h3>📅 Мероприятия на <?= date('F Y', strtotime($startDate)) ?></h3>
            <?php if (count($events) === 0): ?>
                <div class="no-events">В этом месяце нет запланированных конкурсов.</div>
            <?php else: ?>
                <?php foreach ($events as $ev): ?>
                    <div class="event-item">
                        <span class="event-date"><?= date('d.m H:i', strtotime($ev['date_k'])) ?></span>
                        <span class="event-title"><?= htmlspecialchars($ev['title']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>