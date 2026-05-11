<?php
include __DIR__ . '/conect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Неверный запрос.');
}

$id = (int)$_GET['id'];

$stmt = $mysqli->prepare("SELECT file_put FROM file_konkurs WHERE key_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row['file_put'];
}
$stmt->close();

$delFilesStmt = $mysqli->prepare("DELETE FROM file_konkurs WHERE key_id = ?");
$delFilesStmt->bind_param('i', $id);
$delFilesStmt->execute();
$delFilesStmt->close();

$deleteStmt = $mysqli->prepare("DELETE FROM konkurs WHERE id = ?");
$deleteStmt->bind_param('i', $id);
$deleteStmt->execute();

if ($deleteStmt->affected_rows > 0) {
    
    foreach ($files as $filePath) {
        $fullPath = __DIR__ . '/' . $filePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}

$deleteStmt->close();

header('Location: konkursall.php');
exit;
?>