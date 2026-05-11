<?php
include __DIR__ . '/conect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$contest_id = isset($_GET['contest_id']) ? (int)$_GET['contest_id'] : 0;

if (!$file_id || !$contest_id) {
    die('Неверный запрос.');
}

$stmt = $mysqli->prepare("SELECT file_put FROM file_konkurs WHERE id = ? AND key_id = ?");
$stmt->bind_param('ii', $file_id, $contest_id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($file) {
    
    $delStmt = $mysqli->prepare("DELETE FROM file_konkurs WHERE id = ?");
    $delStmt->bind_param('i', $file_id);
    $delStmt->execute();
    $delStmt->close();

    
    $fullPath = __DIR__ . '/' . $file['file_put'];
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

header('Location: edit_konkurs.php?id=' . $contest_id);
exit;
?>