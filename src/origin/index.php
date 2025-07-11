<?php
//FILE: index.php


if (isset($_GET['DELETE'])) {
	
	//var_dump( glob("uploads/TEMP/" . str_replace(".", "_", $_GET['DELETE']).'---'));
	if (file_exists("uploads/TEMP/" . str_replace(".", "_", $_GET['DELETE']).'/')){
		//echo "OK";
		
		// remove parts files and dir
		$dir = "uploads/TEMP/" . str_replace(".", "_", $_GET['DELETE']).'/';
		$files = array_diff(scandir($dir), array('.','..'));
		foreach($files as $file) {
			unlink("$dir/$file");
		}
		rmdir($dir);
		
		
		@rmdir("uploads/TEMP/"); // if empty -> remove parts dir
		
	} else {
		//echo "ERROR";
	}
	
	header('Location: ./');
	die();
}
?>


<script type="text/javascript">
// https://github.com/mailopl/html5-xhr2-chunked-file-upload-slice/tree/master
// https://superuser.com/questions/1351785/what-could-be-limiting-my-maximum-upload-file-size-on-my-raspberry-pi-lamp-stack
// /etc/php5/apache2/php.ini
// /var/log/apache2/error.log
// /var/log/apache2/access.log
// cp /var/www/html/xhr2/uploads/LegfileChunkEnd_cz.mp4 /media/magorpi/Hudba/Filmy/LegfileChunkEnd_cz.mp4
// https://stackoverflow.com/questions/45953/php-execute-a-background-process

//https://github.com/ZiTAL/html5-file-upload-chunk

// check if file exists and has same size -> ask if override
// check if file fileChunkStarted upload -> ask if to continue or override


const BYTES_PER_CHUNK = 1024 * 1024 * 3; // 10MB chunk sizes.
var fileSlices = 0; // fileSlices, value that gets decremented
var fileSlicesTotal = 0; // total amount of fileSlices, constant once calculated

var fileSizeTotal = 0;
var fileSizeUploaded = 0;

var fileUploadThreadsAvailable = 20;
var fileUploadStartTime = null;
var fileProcessingStartTime = null;





async function uploadCheck(fileData)
{
	var percentageDiv = document.getElementById("percent");  
	var progressBar = document.getElementById("progressBar");
	percentageDiv.innerHTML = 'Upload started...';
	progressBar.value = 0;
	
	const formData = new FormData();
	formData.append("FileName", fileData.name);
	formData.append("FileSize", fileData.size);

	try {
		fetch("check.php", {
			method: "POST",
			body: formData
		}).then((response) => {
			if (!response.ok) {
				console.error("HTTP error! Status: ${response.status}");
				throw new Error("HTTP error! Status: ${response.status}");
			}
			return response.json();
		}).then((response) => {
			if (response.fileExists) {
				if (!confirm("File with the same Name and Size already exists!\nDo you wanna upload it again and override?")) {
					return;
				}
			}
			
			if (response.chunksExist && confirm(response.chunksSize+"s of file with the same name was already uploaded!\nDo you wanna continue in upload?")) {
				// continue
				uploadFile(fileData, response.uploadedChunks);
			
			} else {
				// new upload
				uploadFile(fileData, []);
			}
		});
	} catch (error) {
		console.error("There has been a problem with your fetch operation:", error);
	}
}



function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function included(array, object) {
	for (var i = 0; i < array.length; i++) {
		if (array[i] == object) {
			return true;
		}
	}
	return false;
}
async function calculateFileHash(file, algorithm = 'SHA-1') {
	try {
		// Read file as ArrayBuffer
		const arrayBuffer = await file.arrayBuffer();
		
		// Calculate hash using Web Crypto API
		const hashBuffer = await crypto.subtle.digest(algorithm, arrayBuffer);
		
		// Convert hash to hex string
		const hashArray = Array.from(new Uint8Array(hashBuffer));
		const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
		
		return hashHex;
	} catch (error) {
		throw new Error(`Hash calculation failed: ${error.message}`);
	}
}

async function uploadFile(fileData, uploadedChunks)
{
	var percentageDiv = document.getElementById("percent");
	percentageDiv.innerHTML = 'Calculating checksum...';
	
	const fileHash = await calculateFileHash(fileData);
	
    var fileChunkStart = 0;
    var fileChunkEnd = 0;
    var fileChunkIndex = 0;

    // calculate the number of fileSlices
    fileSlices = Math.ceil(fileData.size / BYTES_PER_CHUNK);
    filefileSlicesTotal = fileSlices;
	
	fileSizeTotal = fileData.size;
	fileSizeUploaded = 0;
	
	fileUploadStartTime = performance.now();

    while(fileChunkStart < fileData.size && fileSlices > 0) {
        fileChunkEnd = fileChunkStart + BYTES_PER_CHUNK;
        if(fileChunkEnd > fileData.size) {
            fileChunkEnd = fileData.size;
        }
		
		
		if (!included(uploadedChunks, fileChunkIndex)) {
			var fileChunk = null;
		    if (fileData.webkitSlice) {
				fileChunk = fileData.webkitSlice(fileChunkStart, fileChunkEnd);
			} else if (fileData.mozSlice) {
				fileChunk = fileData.mozSlice(fileChunkStart, fileChunkEnd);
			} else {
				fileChunk = fileData.slice(fileChunkStart, fileChunkEnd); 
			}
		
			fileUploadThreadsAvailable--;
			uploadChunk(fileData.name, fileChunk, fileChunkIndex, fileSizeTotal, fileHash);
		} else {
			fileSizeUploaded += fileChunkEnd-fileChunkStart;
			
			
			uploadShowInfo(fileSizeUploaded, fileSizeTotal);
			
			
			fileSlices--;

			// if we have finished all fileSlices
			if(fileSlices == 0) {
				uploadMerge(fileData.name, fileHash);
			}
		}
		
		
		while(fileUploadThreadsAvailable == 0) {await sleep(10);}

        fileChunkStart = fileChunkEnd;
        fileChunkIndex++;
    }
	
	if (fileSlices < 0) {
		widow.stop(); // should cancel any pending image or script requests.
	}
}


/**
 * Blob to ArrayBuffer (needed ex. on Android 4.0.4)
**/
var str2ab_blobreader = function(str, callback) {
    var blob;
    BlobBuilder = window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder;
    if (typeof(BlobBuilder) !== 'undefined') {
      var bb = new BlobBuilder();
      bb.append(str);
      blob = bb.getBlob();
    } else {
      blob = new Blob([str]);
    }
    var f = new FileReader();
    f.onload = function(e) {
        callback(e.target.result)
    }
    f.readAsArrayBuffer(blob);
}
/**
 * Performs actual upload, adjustes progress bars
 *
 * @param blob
 * @param fileChunkIndex
 * @param fileChunkStart
 * @param fileChunkEnd
 */
function uploadChunk(fileName, fileChunk, fileChunkIndex, fileSizeTotal, fileHash) {
//function uploadChunk(blob, fileChunkIndex, fileChunkStart, fileChunkEnd) {
    var xhr;
    var fileChunkEnd;
    var chunk;
	var prevfileSizeUploaded = 0;

    xhr = new XMLHttpRequest();

    /*xhr.onreadystatechange = function() {
	console.log(xhr.readyState);
        if(xhr.readyState == 4) {
            if(xhr.responseText) {
                alert(xhr.responseText);
            }
        }
    };*/

/*    if (blob.webkitSlice) {
        chunk = blob.webkitSlice(fileChunkStart, fileChunkEnd);
    } else if (blob.mozSlice) {
        chunk = blob.mozSlice(fileChunkStart, fileChunkEnd);
    } else {
		chunk = blob.slice(fileChunkStart, fileChunkEnd); 
    }*/

    xhr.addEventListener("load",  function (evt) {
		if(evt.target.status != 200) {
			fileSlices = -1;
			window.stop(); // should cancel any pending image or script requests.
			alert("ERROR: " + evt.target.status + " - " + evt.target.response);
			
			var percentageDiv = document.getElementById("percent");  
			percentageDiv.innerHTML = "ERROR: " + evt.target.status + " - " + evt.target.response;
		} else {
			//var percentageDiv = document.getElementById("percent");  
			//var progressBar = document.getElementById("progressBar"); 

			//progressBar.max = progressBar.value = 100;  
			//percentageDiv.innerHTML = "100%";  
			console.log(evt);
			
			
			fileUploadThreadsAvailable++;
			
			fileSlices--;

			// if we have finished all fileSlices
			if(fileSlices == 0) {
				uploadMerge(fileName, fileHash);
			}
		}
    }, false);

	xhr.upload.addEventListener("progress", function (evt) {

		if (evt.lengthComputable) {
			//fileSizeUploaded -= prevfileSizeUploaded;
			fileSizeUploaded += evt.loaded - prevfileSizeUploaded;
			prevfileSizeUploaded = evt.loaded;
			
            uploadShowInfo(fileSizeUploaded, fileSizeTotal);
		}
		//console.log(evt);
		//console.log(xhr.getAllResponseHeaders())
	}, false);


    xhr.open("post", "upload.php", true);
    xhr.setRequestHeader("X-File-Name", encodeURIComponent(fileName));             // custom header with filename and full size
	xhr.setRequestHeader("X-File-Size", fileSizeTotal);// decodeURIComponent(atob(btoa(encodeURIComponent("%%čšřšřžášsdSDFas56sd31.3m2,-)ú4irae+ěšžš")))) 
	xhr.setRequestHeader("X-Chunk-Size", fileChunk.size);// decodeURIComponent(atob(btoa(encodeURIComponent("%%čšřšřžášsdSDFas56sd31.3m2,-)ú4irae+ěšžš")))) 
	xhr.setRequestHeader("X-Chunk-Index", fileChunkIndex);                     // part identifier
    
    /*if (blob.webkitSlice) {                                     // android default browser in version 4.0.4 has webkitSlice instead of slice()
    	var buffer = str2ab_blobreader(fileChunk, function(buf) {   // we cannot send a blob, because body payload will be empty
       		xhr.send(buf);                                      // thats why we send an ArrayBuffer
    	});	
    } else {*/
    	xhr.send(fileChunk);                                        // but if we support slice() everything should be ok
    //}
}


function uploadShowInfo(fileSizeUploaded, fileSizeTotal)
{
	var percentageDiv = document.getElementById("percent");  
	var progressBar = document.getElementById("progressBar");
	
	
	progressBar.max = fileSizeTotal;  
	progressBar.value = fileSizeUploaded;
	
	
	var uploadTime = performance.now() - fileUploadStartTime;
	
	const uploadInSeconds = Math.floor(uploadTime/1000);
	const uploadInMinutes = Math.floor((uploadTime/1000/60)*100) / 100;
	
	var MBs = Math.round(((fileSizeUploaded/uploadInSeconds) / 1024 / 1024) * 100) / 100;
	var MBm = Math.round(((fileSizeUploaded/uploadInMinutes) / 1024 / 1024) * 100) / 100;

	
	var REMInMinutes = "...";
	if (MBs === Infinity) {
		MBs = "...";
	}
	if (MBm !== Infinity) {
		REMInMinutes = Math.ceil((((fileSizeTotal-fileSizeUploaded) / 1024 / 1024)/MBm)*100)/100;
	}
	
	const uploadedMBs = Math.round((fileSizeUploaded / 1024 / 1024) * 100) / 100;
	const totalMBs = Math.round((fileSizeTotal / 1024 / 1024) * 100) / 100;
	
	
	percentageDiv.innerHTML = Math.round(fileSizeUploaded/fileSizeTotal * 100) + "% [" + decimalTrailingZeros(uploadedMBs, 2) + "/" + decimalTrailingZeros(totalMBs, 2) + " MB] (" + decimalTrailingZeros(MBs, 2) + " MB/s) [ELA: "+getTimefromMillis(uploadTime)+"] (REM: " + decimalTrailingZeros(REMInMinutes, 2) + " min)";

	if (fileSizeUploaded == fileSizeTotal) {
		percentageDiv.innerHTML = "Processing...";
		fileProcessingStartTime = performance.now();
	}
}

/**
 *  Function executed once all of the fileSlices has been sent, "TO MERGE THEM ALL!"
**/
function uploadMerge(fileName, fileHash) {
    var xhr;
    var fd;

    xhr = new XMLHttpRequest();

    fd = new FormData();
    fd.append("name", fileName);
    fd.append("indexes", filefileSlicesTotal);
    fd.append("size", fileSizeTotal);
	
	xhr.addEventListener("load",  function (evt) {
		console.log(evt.target.responseText);
		console.log(fileHash);
		const checksum = (evt.target.responseText == fileHash) ? 'OK' : 'Invalid';
		
		
		
    	var percentageDiv = document.getElementById("percent");  
		var progressBar = document.getElementById("progressBar"); 

	    //progressBar.max = progressBar.value = 100;
		
		
		var uploadTime = performance.now() - fileUploadStartTime;
		var processingTime = performance.now() - fileProcessingStartTime;
		
	    percentageDiv.innerHTML = "Done - checksum: "+checksum+"! ("+Math.round((fileSizeTotal / 1024 / 1024) * 100) / 100+" MB) [All: "+getTimefromMillis(uploadTime)+"] (Processing: "+getTimefromMillis(processingTime)+")";  
    }, false);

    xhr.open("POST", "merge.php", true);
    xhr.send(fd);
}

function decimalTrailingZeros(number, decimals)
{
	number = ('' + number);
	if (!isNaN(number) && !isNaN(parseFloat(number))) {
		var decNumb = ('' + number).split('.');
		if (decNumb.length == 2) {
			return decNumb[0] + "." + decNumb[1].padEnd(decimals, '0');
		} else if (decNumb.length == 1) {
			return decNumb[0] + "." + "".padEnd(decimals, '0');
		}
	}
	return number;
}

function getTimefromMillis(timeMillis)
{
	//https://stackoverflow.com/a/69126766
	const d = new Date(Date.UTC(0,0,0,0,0,0,timeMillis)),
	  // Pull out parts of interest
	  parts = [
		d.getUTCHours(),
		d.getUTCMinutes(),
		d.getUTCSeconds()
	  ],
	  // Zero-pad
	  formatted = parts.map(s => String(s).padStart(2,'0')).join(':');
	 return formatted;
}


</script>

<h2>Resumable large files upload</h2>
<input type="file" name="file" id="fileToUpload">
<button onclick="uploadCheck(document.getElementById('fileToUpload').files[0])">Upload</button>
<br><br>
<progress id="progressBar" value="0" max="100" style="width: 270px;"></progress>
<br>
<span id="percent">Select file...</span>


<?php


function dirToArray($dir, $depth = -1) {
	
   $result = array();
   
   if (!file_exists($dir) || $depth === 0) {
	   return $result;
   }

   $cdir = scandir($dir);

   foreach ($cdir as $key => $value)
   {
      if (!in_array($value,array(".","..")))
      {
         if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
         {
			if ($depth === -1) {
				$result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
			} else if ($depth > 1){
				$result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value, $depth-1);
			} else {
				$result[] = $value;
			}
         }
         else
         {
            $result[] = $value;
         } 
      }
   }

   return $result;
}

function human_filesize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . @$sz[$factor] . "B";
}

function tempDirsSizes($temp_dir_tree, $dir_path) {
	$chunks_sizes = [];
	foreach($temp_dir_tree as $index => $dir) {
		//echo 'File Name: '.$index.'<br>';
		
		$pos = strrpos($index, "---");
		if ($pos === false) { // note: three equal signs
			// not found...
			$chunks_sizes[$index]['full_size'] = 0;
			$chunks_sizes[$index]['human_full_size'] = "0 B";
			$chunks_sizes[$index]['name'] = '';
		} else {
			
			$chunks_sizes[$index]['full_size'] = (int)substr($index, $pos+3);
			$chunks_sizes[$index]['human_full_size'] = human_filesize((int)substr($index, $pos+3));
			$chunks_sizes[$index]['name'] = substr($index, 0, $pos);
		}
		
		$chunks_sizes[$index]['full_name'] = $index;
		
		
		$chunks_sizes[$index]['size'] = 0;
		foreach($dir as $file) {
			//echo '<pre>';
			//var_dump(filesize($dir_path."/".$index.'/'.$file));
			//echo '</pre>';
			;
			$chunks_sizes[$index]['size'] += filesize(join('/', [trim($dir_path, '/'), trim($index, '/'), trim($file, '/')]));
		}
		
		$chunks_sizes[$index]['human_size'] = human_filesize($chunks_sizes[$index]['size']);
	}
	
	return $chunks_sizes;
}



$dir = realpath('./uploads/TEMP');
$temp_files_list = dirToArray($dir);
//$incomplete_files_list = array_keys($temp_files_list);
//var_dump($incomplete_files_list);

$temp_files_list = tempDirsSizes($temp_files_list, './uploads/TEMP');


$uploaded_list = dirToArray('./uploads', 1);



/*
echo '<pre>';
var_dump($temp_files_list);


var_dump($uploaded_list);
echo '</pre>';*/
?>



<div id="incompleted">
	<h2>incompleted:</h2>
	<ul>
	<?php
		
		foreach($temp_files_list as $fl):
	?>
		<li>
			<a href="./?DELETE=<?=urlencode($fl['full_name'])?>">X</a> - <span><?=$fl['name']?> (<?=$fl['human_size']?>/<?=$fl['human_full_size']?>)</span>
			
			<br>
			<progress id="progressBar" value="<?=$fl['size']?>" max="<?=$fl['full_size']?>" style="width: 270px;"></progress>
		</li>
	<?php
		endforeach;
	?>
	</ul>
</div>
<div id="completed">
	<h2>completed:</h2>
	<ul>
	<?php
	foreach($uploaded_list as $fl):
		if ($fl === "TEMP") {continue;}
	?>
		<li><a href="uploads/<?=$fl?>"><?=$fl?></a></li>
	<?php
	endforeach;
	?>
	</ul>
</div>