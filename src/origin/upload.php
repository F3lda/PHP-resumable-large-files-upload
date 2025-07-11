<?php
//FILE: upload.php


/*error_reporting(E_ALL);
ini_set('display_errors', 'On');*/

ignore_user_abort(true);


$upload_destination = '';



$filename   = urldecode($_SERVER['HTTP_X_FILE_NAME']);
$filesize   = $_SERVER['HTTP_X_FILE_SIZE'];
$chunksize   = $_SERVER['HTTP_X_CHUNK_SIZE'];
$chunkindex      = $_SERVER['HTTP_X_CHUNK_INDEX'];

// name must be in proper format
if (!isset($_SERVER['HTTP_X_FILE_NAME'])) {
    http_response_code(400);
	die("Name required");
	//throw new Exception('Name required');
}
/*if (!preg_match('/^[-a-z0-9_][-a-z0-9_.]*$/i', $_SERVER['HTTP_X_FILE_NAME'])) {
    throw new Exception('Name error');
}*/

// index must be set, and number
if (!isset($_SERVER['HTTP_X_CHUNK_INDEX'])) {
    http_response_code(400);
	die("Index required");
	//throw new Exception('Index required');
}
if (!preg_match('/^[0-9]+$/', $_SERVER['HTTP_X_CHUNK_INDEX'])) {
	http_response_code(400);
	die("Index error");
	//echo "Index error";
    //throw new Exception('Index error');
}

// size must be set, and number
if (!isset($_SERVER['HTTP_X_CHUNK_SIZE'])) {
	http_response_code(400);
	die("Size required");
	//echo "Size required";
    //throw new Exception('Size required');
}
if (!preg_match('/^[0-9]+$/', $_SERVER['HTTP_X_CHUNK_SIZE'])) {
	http_response_code(400);
	die("Size error");
	//echo "Size error";
    //throw new Exception('Size error');
}

// size must be set, and number
if (!isset($_SERVER['HTTP_X_FILE_SIZE'])) {
	http_response_code(400);
	die("Size required");
	//echo "Size required";
    //throw new Exception('Size required');
}
if (!preg_match('/^[0-9]+$/', $_SERVER['HTTP_X_FILE_SIZE'])) {
	http_response_code(400);
	die("Size error");
	//echo "Size error";
    //throw new Exception('Size error');
}




if (!file_exists($upload_destination."uploads/")){
	mkdir($upload_destination."uploads/");
	chmod($upload_destination."uploads/", octdec(777));
}

if (!file_exists($upload_destination."uploads/TEMP/")){
	mkdir($upload_destination."uploads/TEMP/");
	chmod($upload_destination."uploads/TEMP/", octdec(777));
}


// we store chunks in directory named after filename
if (!file_exists($upload_destination."uploads/TEMP/" . str_replace(".", "_", $filename).'---'. $filesize .'/')){
	mkdir($upload_destination."uploads/TEMP/" . str_replace(".", "_", $filename).'---'. $filesize .'/');
	chmod($upload_destination."uploads/TEMP/" . str_replace(".", "_", $filename).'---'. $filesize .'/', octdec(777));
}

$target = $upload_destination."uploads/TEMP/" . str_replace(".", "_", $filename).'---'. $filesize .'/'.$filename . '-' . $chunkindex;

unlink($target);


$putdata = fopen("php://input", "r");
$fp = fopen($target, "w");
$file_size_uploaded = 0;
while ($data = fread($putdata, 1024)) {
	$file_size_uploaded += fwrite($fp, $data);
}
fclose($fp);
fclose($putdata);
chmod($target, octdec(777));


if($file_size_uploaded != (int)$chunksize) {
	unlink($target);
} else {
	//file_put_contents($upload_destination."uploads/TEMP/" . str_replace(".", "_", $filename) . '/' . str_replace(".", "_", $filename) . '-parts.txt', $_SERVER['HTTP_X_INDEX'].",", FILE_APPEND | LOCK_EX);
	//chmod($upload_destination."uploads/TEMP/" . str_replace(".", "_", $filename) . '/' . str_replace(".", "_", $filename) . '-parts.txt', octdec(777));
}

