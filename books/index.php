<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$books = getAllBooks();
$dark_mode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true';
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ប្រព័ន្ធអានសៀវភៅអនឡាញ</title>
    <link rel="stylesheet" href="css/style.css<?php echo $dark_mode ? '?dark=true' : ''; ?>">
    <link rel="stylesheet" href="css/dark.css" id="dark-mode" <?php echo $dark_mode ? '' : 'disabled'; ?>>
</head>
<body class="<?php echo $dark_mode ? 'dark-mode' : ''; ?>">
    <header>
        <h1>សៀវភៅអេឡិចត្រូនិក</h1>
        <button id="toggle-dark-mode"><?php echo $dark_mode ? 'ពន្លឺ' : 'ងងឹត'; ?></button>
        <a href="upload.php" class="upload-btn">ផ្ទុកសៀវភៅឡើង</a>
    </header>

    <main>
        <div class="book-list">
            <?php foreach ($books as $book): ?>
                <div class="book-item">
                    <a href="reader.php?id=<?php echo $book['id']; ?>">
                        <div class="book-cover"><?php echo substr($book['title'], 0, 2); ?></div>
                        <h3><?php echo $book['title']; ?></h3>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
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