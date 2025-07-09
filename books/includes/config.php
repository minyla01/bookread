<?php
// កំណត់រចនាសម្ព័ន្ធ
session_start();

define('BOOKS_DIR', __DIR__ . '/../uploads/');
define('ALLOWED_TYPES', ['pdf', 'epub']);

// តភ្ជាប់មូលដ្ឋានទិន្នន័យ
$db = new PDO('mysql:host=localhost;dbname=ebook_reader', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// បង្កើតតារាងបើមិនទាន់មាន
$db->exec("CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS reading_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    last_page INT NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY user_book (user_id, book_id)
)");
?>