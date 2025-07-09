<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$dark_mode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['book_file'])) {
    $result = uploadBook($_FILES['book_file']);
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ផ្ទុកសៀវភៅឡើង</title>
    <link rel="stylesheet" href="css/style.css<?php echo $dark_mode ? '?dark=true' : ''; ?>">
    <link rel="stylesheet" href="css/dark.css" id="dark-mode" <?php echo $dark_mode ? '' : 'disabled'; ?>>
</head>
<body class="<?php echo $dark_mode ? 'dark-mode' : ''; ?>">
    <header>
        <h1>ផ្ទុកសៀវភៅឡើង</h1>
        <button id="toggle-dark-mode"><?php echo $dark_mode ? 'ពន្លឺ' : 'ងងឹត'; ?></button>
        <a href="index.php" class="back-btn">ត្រលប់ក្រោយ</a>
    </header>

    <main>
        <?php if (isset($result)): ?>
            <div class="alert <?php echo $result['success'] ? 'success' : 'error'; ?>">
                <?php echo $result['message']; ?>
            </div>
        <?php endif; ?>
        
        <form action="upload.php" method="post" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label for="book_file">ជ្រើសរើសឯកសារសៀវភៅ (PDF ឬ EPUB):</label>
                <input type="file" name="book_file" id="book_file" accept=".pdf,.epub" required>
            </div>
            <button type="submit" class="submit-btn">ផ្ទុកឡើង</button>
        </form>
    </main>

    <script>
        // Toggle dark mode
        document.getElementById('toggle-dark-mode').addEventListener('click', function() {
            const darkModeEnabled = document.getElementById('dark-mode').disabled;
            document.getElementById('dark-mode').disabled = !darkModeEnabled;
            document.body.classList.toggle('dark-mode');
            
            // Save preference in cookie for 30 days
            document.cookie = `dark_mode=${!darkModeEnabled}; path=/; max-age=${30 * 24 * 60 * 60}`;
            
            // Update button text
            this.textContent = darkModeEnabled ? 'ពន្លឺ' : 'ងងឹត';
        });
    </script>
</body>
</html>