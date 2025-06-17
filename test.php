<?php
$zip = new ZipArchive();
$filename = "concat.php";
echo $filename;

echo '<br>';
if ($zip->open($filename) === TRUE) {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entryName = $zip->getNameIndex($i);
        echo "<p>Name: " . htmlspecialchars($entryName) . "<br>";

        // Bỏ qua thư mục (kết thúc bằng dấu '/')
        if (substr($entryName, -1) === '/') {
            echo "Skipped directory.<br></p>";
            continue;
        }

        $fileContents = $zip->getFromIndex($i);
        echo "File Contents:<br>";
        echo nl2br(htmlspecialchars($fileContents)) . "<br></p>";
    }
    $zip->close();
} else {
    echo "Failed to open ZIP file.";
}
?>
