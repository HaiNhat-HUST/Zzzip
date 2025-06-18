<?php
session_start(); // Luôn đặt lên đầu

// Thư mục upload chung
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

function format_bytes($bytes, $precision = 2) {
    if ($bytes == 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function get_icon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'zip':
            return '🤐';
        case 'ppt':
        case 'pptx':
            return '🖼';
        default:
            return '🗃';
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Files</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Viettel Digital Talent - Cyber Security</h1>
        </header>
        <div class="content-area">
            <h1>My Files</h1>
            <p>Browse, upload, and manage your files and folders.</p>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="upload-message" style="color: green; font-weight: bold;">
                    <?= htmlspecialchars($_SESSION['message']) ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <div class="action-bar">
                <span class="breadcrumb">Home</span>
                <div class="actions">
                    <div class="search-bar">
                        <input type="text" placeholder="Search files...">
                    </div>
                    <button class="btn-icon">☰</button>
                    <button class="btn-secondary" disabled>Create Folder</button>
                    
                    <form method="post" enctype="multipart/form-data" id="upload-form" style="display: inline-block;">
                        <select id="endpoint-selector" class="btn-secondary" style="padding: 4px; margin-right: 10px;">
                            <option value="ziparchive.php">Extract zip with PHP ZipArchive</option>
                            <option value="unzip.php">Extract zip with unzip tool</option>
                            <option value="7zipp.php">Extract zip with 7zip</option>
                            <option value="zipslip.php">ZipSlip</option>
                        </select>

                        <label for="file-upload" class="btn-primary">
                            ⇧ Upload File
                        </label>
                        <input id="file-upload" name="file_to_upload" type="file" style="display:none;">
                    </form>

                    <script>
                        document.getElementById('file-upload').addEventListener('change', function () {
                            const form = document.getElementById('upload-form');
                            const selectedEndpoint = document.getElementById('endpoint-selector').value;
                            form.action = selectedEndpoint;
                            form.submit();
                        });
                    </script>
                </div>
            </div>

            <div class="file-grid">
                <?php
                    $items = scandir($upload_dir);
                    foreach ($items as $item) {
                        if ($item == '.' || $item == '..') continue;
                        $item_path = $upload_dir . $item;

                        if (is_dir($item_path)):
                ?>
                        <div class="file-item">
                            <div class="file-icon">📁</div>
                            <div class="file-details">
                                <span class="file-name"><?= htmlspecialchars($item) ?></span>
                                <span class="file-meta">
                                    <?php
                                        $sub_items = count(scandir($item_path)) - 2;
                                        echo $sub_items . ($sub_items == 1 ? ' item' : ' items');
                                    ?>
                                </span>
                            </div>
                        </div>
                <?php else:
                        $file_url = $upload_dir . rawurlencode($item);
                ?>
                        <a href="<?= $file_url ?>" target="_blank" class="file-link">
                            <div class="file-item">
                                <div class="file-icon"><?= get_icon($item) ?></div>
                                <div class="file-details">
                                    <span class="file-name"><?= htmlspecialchars($item) ?></span>
                                    <span class="file-meta">
                                        <?= format_bytes(filesize($item_path)) ?> • <?= date("Y-m-d", filemtime($item_path)) ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                <?php
                        endif;
                    }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
