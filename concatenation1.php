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

    // Kiểm tra phần mở rộng
    if (!in_array($file_type, $allowedExt)) {
        $_SESSION['message'] = "Không cho phép phần mở rộng file: " . htmlspecialchars($file_type);
        header("Location: index.php");
        exit();
    }

    // Kiểm tra MIME type
    $fileMimeType = finfo_file($finfo, $_FILES['file_to_upload']['tmp_name']);
    if (!in_array($fileMimeType, $allowedMime)) {
        $_SESSION['message'] = "Không cho phép MIME type: " . htmlspecialchars($fileMimeType);
        header("Location: index.php");
        exit();
    }

    // Nếu là file zip thì kiểm tra các file bên trong
    if ($file_type === "zip" || $fileMimeType === "application/zip") {
        $zip = new ZipArchive;
        $res = $zip->open($_FILES['file_to_upload']['tmp_name']);

        if ($res === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);

                if (substr($filename, -1) === '/') continue; // bỏ qua thư mục

                $entry_ext = checkFileExt($filename);
                if (!in_array($entry_ext, $allowedExt)) {
                    $_SESSION['message'] = "File trong zip có phần mở rộng không hợp lệ: " . htmlspecialchars($entry_ext);
                    header("Location: index.php");
                    exit();
                }

                $stream = $zip->getStream($filename);
                if ($stream) {
                    $buffer = fread($stream, 1024);
                    fclose($stream);
                    $entryMimeType = $finfo->buffer($buffer);

                    if (!in_array($entryMimeType, $allowedMime)) {
                        $_SESSION['message'] = "File trong zip có MIME không hợp lệ: " . htmlspecialchars($filename);
                        header("Location: index.php");
                        exit();
                    }

                } else {
                    $_SESSION['message'] = "Không thể kiểm tra MIME file trong zip.";
                    header("Location: index.php");
                    exit();
                }
            }

            $zip->close();

            // Extract bằng 7z
            $output = null;
            $retval = 0;
            exec('unzip ' . escapeshellarg($_FILES['file_to_upload']['tmp_name']) . escapeshellarg($upload_dir), $output, $retval);
            if ($retval !== 0) {
                $_SESSION['message'] = "Lỗi khi giải nén file zip bằng unzip.";
                header("Location: index.php");
                exit();
            }

            unlink($_FILES['file_to_upload']['tmp_name']);
            $_SESSION['message'] = "Upload và giải nén file zip thành công!";
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
