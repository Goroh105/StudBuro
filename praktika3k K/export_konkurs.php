<?php
include __DIR__ . '/conect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="konkursy_export_' . date('Y-m-d') . '.xls"');

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Отчёт по конкурсам</title>
    <style>
        th, td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        table { border-collapse: collapse; }
    </style>
</head>
<body>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Название конкурса</th>
            <th>Описание</th>
            <th>Дата и время</th>
            <th>Тип конкурса</th>
            <th>Последнее изменение</th>
            <th>Файлы</th>
        </tr>
    </thead>
    <tbody>';

$result = $mysqli->query("SELECT * FROM konkurs ORDER BY date_k ASC");

while ($contest = $result->fetch_assoc()) {
    
    $stmt = $mysqli->prepare("SELECT file_name FROM file_konkurs WHERE key_id = ?");
    $stmt->bind_param('i', $contest['id']);
    $stmt->execute();
    $filesResult = $stmt->get_result();
    $files = [];
    while ($row = $filesResult->fetch_assoc()) {
        $files[] = htmlspecialchars($row['file_name']);
    }
    $stmt->close();
    
    $fileList = implode('<br>', $files); 
    
    echo '<tr>';
    echo '<td>' . $contest['id'] . '</td>';
    echo '<td>' . htmlspecialchars($contest['title']) . '</td>';
    echo '<td>' . nl2br(htmlspecialchars($contest['text_k'])) . '</td>';
    echo '<td>' . date('d.m.Y H:i', strtotime($contest['date_k'])) . '</td>';
    echo '<td>' . htmlspecialchars($contest['type']) . '</td>';
    echo '<td>' . date('d.m.Y H:i', strtotime($contest['date_create'])) . '</td>';
    echo '<td>' . $fileList . '</td>';
    echo '</tr>';
}

echo '</tbody>
</table>
</body>
</html>';
?>