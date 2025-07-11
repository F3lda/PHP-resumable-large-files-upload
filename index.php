<?php
require_once('./src/resumable_upload.php');

// Initialize with parameter prefix and upload directory
$ResUpload = new ResumableLargeFilesUpload('TEFIS_', './PRIVATE/');

// Handle upload requests
if(isset($_GET["LARGE_UPLOAD"]) && $ResUpload->isRequested()) {
    $ResUpload->handleRequest();
    die();
}

// Show upload interface
if(isset($_GET["LARGE_UPLOAD"])){
    $ResUpload->show();
    echo '<hr><button onclick="window.location.href = \'./\';">< Home</button>';
} else {
	echo '<a href="./?LARGE_UPLOAD">UPLOAD</a>';
}
?>
