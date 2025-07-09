<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$book_id = $_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // សម្រាប់អ្នកប្រើប្រាស់ដែលបានចូល

// ទាញយកព័ត៌មានសៀវភៅ
$stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    die('សៀវភៅមិនត្រូវបានរកឃើញ។');
}

$file_path = BOOKS_DIR . $book['filename'];
$file_type = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// រក្សាទុកទំព័រដែលបានអាន
if (isset($_POST['current_page']) && $user_id) {
    saveReadingProgress($user_id, $book_id, $_POST['current_page']);
}

// ទាញយកទំព័រចុងក្រោយដែលបានអាន
$last_page = $user_id ? getLastReadPage($user_id, $book_id) : 0;

$dark_mode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true';
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - អ្នកអានសៀវភៅ</title>
    <link rel="stylesheet" href="css/style.css<?php echo $dark_mode ? '?dark=true' : ''; ?>">
    <link rel="stylesheet" href="css/dark.css" id="dark-mode" <?php echo $dark_mode ? '' : 'disabled'; ?>>
    
    <?php if ($file_type === 'pdf'): ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
        <style>
            #pdf-viewer {
                width: 100%;
                height: 80vh;
                border: 1px solid #ccc;
                margin-bottom: 10px;
            }
            #page-controls {
                margin-bottom: 20px;
            }
        </style>
    <?php endif; ?>
</head>
<body class="<?php echo $dark_mode ? 'dark-mode' : ''; ?>">
    <header>
        <h1><?php echo htmlspecialchars($book['title']); ?></h1>
        <button id="toggle-dark-mode"><?php echo $dark_mode ? 'ពន្លឺ' : 'ងងឹត'; ?></button>
        <a href="index.php" class="back-btn">ត្រលប់ក្រោយ</a>
    </header>

    <main>
        <?php if ($file_type === 'pdf'): ?>
            <div id="page-controls">
                <button id="prev-page">ទំព័រមុន</button>
                <span>ទំព័រ: <span id="page-num">1</span> / <span id="page-count">0</span></span>
                <button id="next-page">ទំព័របន្ទាប់</button>
                <input type="number" id="page-input" min="1" value="1">
                <button id="go-to-page">ទៅកាន់ទំព័រ</button>
            </div>
            
            <canvas id="pdf-viewer"></canvas>
            
            <script>
                // Initialize PDF.js
                pdfjsLib.getDocument('<?php echo "uploads/" . htmlspecialchars($book['filename']); ?>').promise.then(function(pdf) {
                    const viewer = document.getElementById('pdf-viewer');
                    const pageNum = document.getElementById('page-num');
                    const pageCount = document.getElementById('page-count');
                    let currentPage = <?php echo $last_page ?: 1; ?>;
                    let pdfDoc = null;
                    let pageRendering = false;
                    let pageNumPending = null;
                    
                    pdfDoc = pdf;
                    pageCount.textContent = pdf.numPages;
                    
                    function renderPage(num) {
                        pageRendering = true;
                        pdfDoc.getPage(num).then(function(page) {
                            const viewport = page.getViewport({ scale: 1.5 });
                            const canvas = document.getElementById('pdf-viewer');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            
                            const renderContext = {
                                canvasContext: context,
                                viewport: viewport
                            };
                            
                            const renderTask = page.render(renderContext);
                            
                            renderTask.promise.then(function() {
                                pageRendering = false;
                                if (pageNumPending !== null) {
                                    renderPage(pageNumPending);
                                    pageNumPending = null;
                                }
                            });
                        });
                        
                        pageNum.textContent = num;
                        document.getElementById('page-input').value = num;
                        
                        // Save reading progress
                        if (<?php echo $user_id ? 'true' : 'false'; ?>) {
                            saveProgress(num);
                        }
                    }
                    
                    function queueRenderPage(num) {
                        if (pageRendering) {
                            pageNumPending = num;
                        } else {
                            renderPage(num);
                        }
                    }
                    
                    function saveProgress(page) {
                        fetch('reader.php?id=<?php echo $book_id; ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `current_page=${page}`
                        });
                    }
                    
                    // Navigation
                    document.getElementById('prev-page').addEventListener('click', function() {
                        if (currentPage > 1) {
                            currentPage--;
                            queueRenderPage(currentPage);
                        }
                    });
                    
                    document.getElementById('next-page').addEventListener('click', function() {
                        if (currentPage < pdfDoc.numPages) {
                            currentPage++;
                            queueRenderPage(currentPage);
                        }
                    });
                    
                    document.getElementById('go-to-page').addEventListener('click', function() {
                        const page = parseInt(document.getElementById('page-input').value);
                        if (page >= 1 && page <= pdfDoc.numPages) {
                            currentPage = page;
                            queueRenderPage(currentPage);
                        }
                    });
                    
                    // Start with the first page or last read page
                    renderPage(currentPage);
                });
            </script>
            
        <?php elseif ($file_type === 'epub'): ?>
            <div id="epub-container" style="width: 100%; height: 80vh;"></div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/epub.js/0.3.93/epub.min.js"></script>
            <script>
                // Initialize EPUB.js
                const book = ePub("<?php echo 'uploads/' . htmlspecialchars($book['filename']); ?>");
                const rendition = book.renderTo("epub-container", {
                    width: "100%",
                    height: "100%"
                });
                
                rendition.display(<?php echo $last_page ?: 1; ?>);
                
                // Save progress when page changes
                rendition.on("relocated", function(location) {
                    if (<?php echo $user_id ? 'true' : 'false'; ?>) {
                        const currentPage = location.start.cfi;
                        fetch('reader.php?id=<?php echo $book_id; ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `current_page=${currentPage}`
                        });
                    }
                });
            </script>
        <?php else: ?>
            <p>ប្រភេទឯកសារមិនត្រូវបានគាំទ្រទេ។ សូមផ្ទុកឡើងជាឯកសារ PDF ឬ EPUB។</p>
        <?php endif; ?>
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