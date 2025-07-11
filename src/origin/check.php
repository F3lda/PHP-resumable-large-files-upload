<?php
//FILE: check.php




/*error_reporting(E_ALL);
ini_set('display_errors', 'On');*/

function human_filesize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . @$sz[$factor] . "B";
}


$upload_destination = '';




// name must be in proper format
if (!isset($_REQUEST['FileName'])) {
    throw new Exception('Name required');
}
/*if (!preg_match('/^[-a-z0-9_][-a-z0-9_.]*$/i', $_REQUEST['name'])) {
    throw new Exception('Name error');
}*/


// size must be set, and number
if (!isset($_REQUEST['FileSize'])) {
    throw new Exception('Size required');
}
if (!preg_match('/^[0-9]+$/', $_REQUEST['FileSize'])) {
    throw new Exception('Size error');
}




$result = ["fileExists" => false, "chunksExist" => false, "chunksSize" => "0 B", "uploadedChunks" => []];




$target = $upload_destination."uploads/" . $_REQUEST['FileName'];
$target_temp_dir = $upload_destination."uploads/TEMP/" . str_replace(".", "_", $_REQUEST['FileName']).'---'. $_REQUEST['FileSize'] .'/';


if (file_exists($target) && filesize($target) == (int)$_REQUEST['FileSize']){
	$result["fileExists"] = true;

}

if (is_dir($target_temp_dir)) {
	$chunksExist = [];
	$chunksSize = 0;
	
	$files = array_diff(scandir($target_temp_dir), array('.','..'));
	foreach($files as $file) {
		$index = substr($file, strrpos($file, '-')+1);
		if(is_numeric($index) && is_int((int)$index)) {
			array_push($chunksExist, (int)$index);
			$chunksSize += filesize($target_temp_dir."/".$file);
		}
    }
	
	if($chunksExist != []) {
		$result["chunksExist"] = true;
		$result["chunksSize"] = human_filesize($chunksSize, 2);
		$result["uploadedChunks"] = $chunksExist;
	}
}





echo json_encode($result);

// check if file exists -> override or not?
// check if chunks were uploaded -> dont override
// send uploaded chunks


/*
$upload_file = false;
$parts = file_get_contents($upload_destination."uploads/TEMP/" . str_replace(".", "_", $filename) . '/' . str_replace(".", "_", $filename) . '-parts.txt');
if($parts === false) {
	$upload_file = true;
} else {
	$upload_file = true;
	$parts = explode(",", $parts);
	foreach ($parts as $index) {
		if((int)$index == $_SERVER['HTTP_X_INDEX']) {
			$upload_file = false;
			break;
		}
	}
}
*/