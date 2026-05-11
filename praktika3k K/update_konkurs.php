<?php
include __DIR__ . '/conect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function sanitizeFilename($filename) {
  
    $filename = basename($filename);
    
    $translit = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i',
        'й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t',
        'у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shh','ъ'=>'','ы'=>'y','ь'=>'',
        'э'=>'e','ю'=>'yu','я'=>'ya',' '=>'_','['=>'',']'=>'','('=>'',')'=>'','{'=>'','}'=>''
    ];
    $filename = strtr(mb_strtolower($filename), $translit);
    
    $filename = preg_replace('/[^a-z0-9._-]/', '', $filename);
    return $filename;
}

$contest_id = (int)$_POST['contest_id'];
$title = trim($_POST['title']);
$text_k = trim($_POST['text_k']);
$event_date = $_POST['event_date'];
$event_time = $_POST['event_time'];
$type = $_POST['type'];

if (empty($title) || empty($event_date) || empty($event_time)) {
    die('Ошибка: название, дата и время обязательны.');
}

$event_datetime = $event_date . ' ' . $event_time . ':00';
$timestamp = strtotime($event_datetime);
if (!$timestamp) {
    die('Неверный формат даты/времени.');
}
$event_datetime = date('Y-m-d H:i:s', $timestamp);


$stmt = $mysqli->prepare("UPDATE konkurs SET title = ?, text_k = ?, date_k = ?, type = ?, date_create = NOW() WHERE id = ?");
$stmt->bind_param('ssssi', $title, $text_k, $event_datetime, $type, $contest_id);
$stmt->execute();
$stmt->close();


$uploadDir = __DIR__ . '/dok/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
    $fileStmt = $mysqli->prepare("INSERT INTO file_konkurs (key_id, file_name, file_put) VALUES (?, ?, ?)");
    for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
        if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
            $originalName = basename($_FILES['files']['name'][$i]);
                $safeName = sanitizeFilename($originalName);

                
                $counter = 1;
                $baseName = pathinfo($safeName, PATHINFO_FILENAME);
                $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                while (file_exists($uploadDir . $safeName)) {
                    $safeName = $baseName . '_' . $counter . '.' . $ext;
                    $counter++;
                }
            $destination = $uploadDir . $safeName;
            if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $destination)) {
                $webPath = 'dok/' . $safeName;
                $fileStmt->bind_param('iss', $contest_id, $safeName, $webPath);
                $fileStmt->execute();
            }
        }
    }
    $fileStmt->close();
}

header('Location: konkursall.php?msg=updated');
exit;
?>