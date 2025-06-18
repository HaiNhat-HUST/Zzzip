<?php
session_start();

$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip', 'txt'];
$allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/zip', 'text/plain'];
$upload_dir = __DIR__ . '/uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);

function checkFileExt($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

if (isset($_FILES["file_to_upload"]) && $_FILES["file_to_upload"]["error"] == 0) {

    $target_file = $upload_dir . basename($_FILES["file_to_upload"]["name"]);
    $file_type = checkFileExt($target_file);

    if (!in_array($file_type, $allowedExt)) {
        $_SESSION['message'] = "Không cho phép phần mở rộng file: " . htmlspecialchars($file_type);
        header("Location: index.php");
        exit();
    }

    $fileMimeType = finfo_file($finfo, $_FILES['file_to_upload']['tmp_name']);
    if (!in_array($fileMimeType, $allowedMime)) {
        $_SESSION['message'] = "Không cho phép MIME type: " . htmlspecialchars($fileMimeType);
        header("Location: index.php");
        exit();
    }

    if ($file_type === "zip" || $fileMimeType === "application/zip") {
        $zip = new ZipArchive;
        $res = $zip->open($_FILES['file_to_upload']['tmp_name']);

        if ($res === TRUE) {
            $fileEntries = 'File entry ';
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                $fileEntries = $fileEntries . $entry;
            }
            $zip->extractTo($upload_dir);  
            $zip->close();

            $_SESSION['message'] = "Giải nén thành công với Zip Archive!" . $fileEntries;
        } else {
            $_SESSION['message'] = "Không thể mở file zip.";
        }
    } else {
        if (move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $target_file)) {
            $_SESSION['message'] = "Upload file thành công!";
        } else {
            $_SESSION['message'] = "Không thể lưu file.";
        }
    }

} else {
    $_SESSION['message'] = "Không có file nào được gửi hoặc có lỗi khi upload.";
}

header("Location: index.php");
exit();
