<?php
// In your main file (e.g., admin.php, tools.php, etc.)
$upload_destination = './'; // Optional: set custom upload path (relative from current dir)
include('./src/resumable_upload.php');


if (isset($_GET['UPLOAD'])) {
	useResumableUpload();
} else {
	echo '<a href="./?UPLOAD">UPLOAD</a>';
}

?>
