<?php
require_once 'config.php';

// មុខងារផ្ទុកឯកសារ
function uploadBook($file) {
    $target_dir = BOOKS_DIR;
    $filename = basename($file['name']);
    $target_file = $target_dir . $filename;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // ពិនិត្យប្រភេទឯកសារ
    if (!in_array($fileType, ALLOWED_TYPES)) {
        return ['success' => false, 'message' => 'ប្រភេទឯកសារមិនត្រូវអនុញ្ញាត។'];
    }

    // ផ្ទុកឯកសារ
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        global $db;
        $stmt = $db->prepare("INSERT INTO books (filename, title) VALUES (?, ?)");
        $title = pathinfo($filename, PATHINFO_FILENAME);
        $stmt->execute([$filename, $title]);
        return ['success' => true, 'message' => 'ផ្ទុកឯកសារបានជោគជ័យ។'];
    } else {
        return ['success' => false, 'message' => 'មានបញ្ហាក្នុងការផ្ទុកឯកសារ។'];
    }
}

// មុខងារទាញយកសៀវភៅទាំងអស់
function getAllBooks() {
    global $db;
    $stmt = $db->query("SELECT * FROM books ORDER BY title");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// មុខងាររក្សាទុកទំព័រដែលបានអាន
function saveReadingProgress($user_id, $book_id, $page) {
    global $db;
    $stmt = $db->prepare("INSERT INTO reading_progress (user_id, book_id, last_page) 
                         VALUES (?, ?, ?) 
                         ON DUPLICATE KEY UPDATE last_page = ?, updated_at = NOW()");
    $stmt->execute([$user_id, $book_id, $page, $page]);
    return $stmt->rowCount();
}

// មុខងារទាញយកទំព័រចុងក្រោយដែលបានអាន
function getLastReadPage($user_id, $book_id) {
    global $db;
    $stmt = $db->prepare("SELECT last_page FROM reading_progress WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$user_id, $book_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['last_page'] : 0;
}
?>