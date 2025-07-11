<?php
//FILE: merge.php



/*error_reporting(E_ALL);
ini_set('display_errors', 'On');*/

ignore_user_abort(true);


$upload_destination = '';



// name must be in proper format
if (!isset($_REQUEST['name'])) {
    throw new Exception('Name required');
}
/*if (!preg_match('/^[-a-z0-9_][-a-z0-9_.]*$/i', $_REQUEST['name'])) {
    throw new Exception('Name error');
}*/

// indexes must be set, and number
if (!isset($_REQUEST['indexes'])) {
    throw new Exception('Indexes required');
}

if (!preg_match('/^[0-9]+$/', $_REQUEST['indexes'])) {
    throw new Exception('Indexes error');
}

// size must be set, and number
if (!isset($_REQUEST['size'])) {
    throw new Exception('Size required');
}
if (!preg_match('/^[0-9]+$/', $_REQUEST['size'])) {
    throw new Exception('Size error');
}

$filesize = $_REQUEST['size'];

$target = $upload_destination."uploads/full_" . $_REQUEST['name'];
$dst = fopen($target, 'wb');

for ($i = 0; $i < $_REQUEST['indexes']; $i++) {
    $slice = $upload_destination.'uploads/TEMP/' . str_replace(".", "_", $_REQUEST['name']) .'---'. $filesize . '/' . $_REQUEST['name'] . '-' . $i;
    $src = fopen($slice, 'rb');
    stream_copy_to_stream($src, $dst);
    fclose($src);
    //unlink($slice);
}

fclose($dst);
chmod($target, octdec(777));

if($_REQUEST['size'] == filesize($target)) {
	@unlink($upload_destination."uploads/" . $_REQUEST['name']); // remove if overriding
	rename($target, $upload_destination."uploads/" . $_REQUEST['name']);
	
	// remove parts files and dir
	$dir = $upload_destination."uploads/TEMP/" . str_replace(".", "_", $_REQUEST['name']) .'---'. $filesize;
	$files = array_diff(scandir($dir), array('.','..'));
	foreach($files as $file) {
		unlink("$dir/$file");
    }
	rmdir($dir);
	
	
	@rmdir($upload_destination."uploads/TEMP/"); // if empty -> remove parts dir
} else {
	unlink($target);
}
echo hash_file('sha1', $upload_destination."uploads/" . $_REQUEST['name']);