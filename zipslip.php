<?php
// thư mục upload có file .htaccess , không có cơ chế kiểm tra file ext hay mime type
// kẻ tấn công upload file ra ngoài thư mục upload

session_start();

$allowedExt = ['jpg', 'jpeg', 'png', 'gif',  'zip'];
$allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'application/zip'];

$upload_dir = __DIR__ . '/uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);

function checkFileExt($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function traversalCheck($filename) {
    $filename = str_replace(['../', '..\\'], '', $filename);
    return $filename;
}

if (isset($_FILES["file_to_upload"]) && $_FILES["file_to_upload"]["error"] == 0) {

    $target_file = $upload_dir . basename($_FILES["file_to_upload"]["name"]);

    // kiểm tra file upload
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

    
    // xử lí file zip
    if ($file_type === "zip" || $fileMimeType === "application/zip") {
        $zip = new ZipArchive;
        $res = $zip->open($_FILES['file_to_upload']['tmp_name']);
        if ($res === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {

                // không có cơ chế kiểm tra file extension và mimetype thay vào đó bảo vệ bằng config .htaccess
                $entryName = $zip->getNameIndex($i);

                // kiểm tra pathtraversal nhưng chưa đủ an toàn
                $entryName = traversalCheck($entryName);

                // sử dụng cơ chế giải nén thủ công thay vì ZipArchive để mô phỏng zipslip
                $contents = $zip->getFromIndex($i);
                
                // kiểm tra chữ kí đầu tệp
                $buffer = substr($content, 0 , 1024);
                $entryMimeType = $finfo->buffer($buffer);
                if(!in_array($entryMimeType, $allowedMime)) {
                     $_SESSION['message'] = "File trong zip có MIME không hợp lệ: " . htmlspecialchars($entryMimeType) . " cho " . htmlspecialchars($entryName);
                    header("Location: index.php");
                    exit();
                }
                
                $dest = $upload_dir . $entryName;

                file_put_contents($dest, $contents);
            }

            $_SESSION['message'] = "Giải nén thành công với Zip Archive!" . $fileEntries . "with content: " . $contents;
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
