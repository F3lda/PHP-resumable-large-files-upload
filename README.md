# Resumable Large Files Upload

A PHP class for uploading large files with resume capability, chunked transfer, and file management.

## Features

- Resumable uploads with chunked transfer
- Real-time progress tracking and speed calculation
- SHA-1 checksum validation
- Delete incomplete/completed uploads
- Configurable parameter prefixes
- Security protections

## Quick Start

```php
<?php
require_once('./resumable_upload.php');

// Initialize with parameter prefix, upload directory, chunk size, and threads
$ResUpload = new ResumableLargeFilesUpload('TEFIS_', './PRIVATE/', 30, 5);

// Handle upload requests
if(isset($_GET["LARGE_UPLOAD"]) && $ResUpload->isRequested()) {
    $ResUpload->handleRequest();
    die();
}

// Show upload interface
if(isset($_GET["LARGE_UPLOAD"])){
    $ResUpload->show();
    echo '<hr><button onclick="window.location.href = \'./\';">< Home</button>';
}
?>
```

## Constructor

```php
new ResumableLargeFilesUpload($param_prefix, $upload_destination, $bytes_per_chunk, $upload_threads)
```

- **`$param_prefix`** (string) - URL parameter prefix (default: '', e.g., 'APP_')
- **`$upload_destination`** (string) - Upload directory (default: './uploads/')
- **`$bytes_per_chunk`** (int) - Chunk size in MB (default: 30)
- **`$upload_threads`** (int) - Number of upload threads (default: 3)

## URL Parameters

With prefix `'TEFIS_'`:
- `TEFIS_action` - Action type (upload, check, merge, delete, delete_completed)
- `TEFIS_data` - Filename for delete operations

## File Structure

```
uploads/
├── TEMP/                    # Temporary chunks
│   └── filename_ext---123/  # Chunk directory
├── completed_file1.pdf      # Finished uploads
└── completed_file2.zip
```

## Server Configuration

```php
ini_set('upload_max_filesize', '1G');
ini_set('post_max_size', '1G');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');
```

## Key Methods

```php
$uploader->handleRequest();     // Process upload requests
$uploader->isRequested();       // Check if request is for uploader
$uploader->show();              // Display upload interface
$uploader->getParamVariableNames(); // Get parameter names
```

## Notes

Based on these repos:
- https://github.com/ZiTAL/html5-file-upload-chunk
- https://github.com/mailopl/html5-xhr2-chunked-file-upload-slice/
