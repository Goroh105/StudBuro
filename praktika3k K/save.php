<?php
include __DIR__. '/conect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: +konkurs.php');
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

$title = trim($_POST['title']);
$text_k = trim($_POST['text_k']);
$type = $_POST['type'];


$event_date = $_POST['event_date'];
$event_time = $_POST['event_time'];
if (empty($event_date) || empty($event_time)) {
    die('Ошибка: дата и время обязательны.');
}
$event_datetime = $event_date . ' ' . $event_time . ':00';
$timestamp = strtotime($event_datetime);
if (!$timestamp) {
    die('Неверный формат даты или времени.');
}
$event_datetime = date('Y-m-d H:i:s', $timestamp);

$mysqli->begin_transaction();

try {
  
    $sql = "INSERT INTO konkurs (title, text_k, date_k, type) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception('Ошибка подготовки запроса: ' . $mysqli->error);
    }
    $stmt->bind_param('ssss', $title, $text_k, $event_datetime, $type);
    $stmt->execute();
    $contestId = $stmt->insert_id;
    $stmt->close();

    $uploadDir = __DIR__ . '/dok/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filesCount = 0;
    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        $fileSql = "INSERT INTO file_konkurs (key_id, file_name, file_put) VALUES (?, ?, ?)";
        $fileStmt = $mysqli->prepare($fileSql);
        if (!$fileStmt) {
            throw new Exception('Ошибка подготовки запроса для файлов: ' . $mysqli->error);
        }

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
                    $fileStmt->bind_param('iss', $contestId, $safeName, $webPath);
                    $fileStmt->execute();
                    $filesCount++;
                }
            }
        }
        $fileStmt->close();
    }

    $mysqli->commit();
    header('Location: index.php?msg=added&files=' . $filesCount);
    exit;

} catch (Exception $e) {
    $mysqli->rollback();
    die('Ошибка при сохранении: ' . $e->getMessage());
}
?>