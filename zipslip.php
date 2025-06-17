<?php

$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip','txt'];
$allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/zip', 'text/plain'];
$upload_dir = __DIR__ . '/uploads/';

if (!is_dir($upload_dir)){
    mkdir($upload_dir);
};

$finfo = finfo_open(FILEINFO_MIME_TYPE);


function checkFileExt($filename){
    $file_type = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return $file_type;
}


if (isset($_FILES["file_to_upload"]) && $_FILES["file_to_upload"]["error"] == 0) {

    $target_file = $upload_dir . basename($_FILES["file_to_upload"]["name"]);
    
    echo "<h3>" . $target_file . "</h3>";

    $file_type = checkFileExt($target_file);

    if (!in_array($file_type, $allowedExt)){
        die("Unallowed file extension");
    }

    // check mime type
    $fileMimeType = finfo_file($finfo,$_FILES['file_to_upload']['tmp_name']);
    if(!in_array($fileMimeType, $allowedMime)){
        die("Unallowed mime type");
    }

    // nếu mime type là zip
    if ($file_type == "zip" || $fileMimeType == "application/zip") {

        $isValid = true;
        $zip = new ZipArchive;

        $res = $zip->open($_FILES['file_to_upload']['tmp_name']);
        
        if ($res === TRUE) {

            // kiểm tra file entry
            for ($i = 0 ; $i < $zip -> numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                
                // Bỏ qua các thư mục
                if (substr($filename, -1) === '/') {
                    continue;
                };

                // kiểm tra extension của file entry
                $entry_ext = checkFileExt($filename);
                if(!in_array($entry_ext, $allowedExt)){
                    die("Zip have unallowed file entry: " . $entry_ext);         // có thể thay đổi phần để an toàn hơn
                }

                // kiểm tra mimetype của file entry
                $stream = $zip->getStream($filename);
                if ($stream) {

                    $buffer = fread($stream, 1024);
                    fclose($stream);
                    $entryMimeType = $finfo->buffer($buffer);

                    if ( !in_array($entryMimeType, $allowedMime)) {
                        die("Zipfile have file entry with inallowed MIME type: " . $filename);
                    }

                } else {
                    die("Unable to check the mime type of zip's file entries");
  
                }

            }

            $zip->close();
            
            if(!system('7z e ' . escapeshellarg($_FILES['file_to_upload']['tmp_name']) . ' -o' . escapeshellarg($upload_dir))){
                die("error urcur when extract file with 7z x");
            };
            unlink($_FILES['file_to_upload']['tmp_name']); 


        } else {
            echo 'Failed to extract zip archive.';
        }
    } else {
        move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $target_file);
    }

}

// Chuyển hướng người dùng trở lại trang chính
header("location: index.php");
exit();
?>