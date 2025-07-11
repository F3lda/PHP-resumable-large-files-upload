# Resumable Large Files Upload

A PHP-based chunked file upload system with resume capability, progress tracking, and integrity verification.

## Features

- **Chunked Upload**: Configurable chunk size (default 30MB)
- **Resume Capability**: Continues interrupted uploads
- **Progress Tracking**: Real-time speed and time estimates
- **Integrity Check**: SHA-1 checksum verification
- **Modular Design**: Easy integration

## Quick Start

```php
<?php
$upload_destination = './'; // Optional: custom upload path
include('./src/resumable_upload.php');

if (isset($_GET['UPLOAD'])) {
    useResumableUpload();
} else {
    echo '<a href="./?UPLOAD">UPLOAD</a>';
}
?>
```

## Configuration

```php
$upload_destination = './uploads/'; // Upload directory
$bytes_per_chunk = 10;              // Chunk size in MB (default: 30)
```

## File Structure

```
your-project/
├── index.php
├── src/resumable_upload.php
└── uploads/
    ├── TEMP/           # Temporary chunks
    └── file.ext        # Completed files
```

## URL Parameters

- `?UPLOAD` - Show upload interface
- `?action=upload` - Handle chunk upload
- `?action=check` - Check existing files
- `?action=merge` - Merge chunks
- `?DELETE=filename` - Remove incomplete upload

## Requirements

- PHP 5.6+
- Write permissions on upload directory
- Modern browser with File API support

## How It Works

1. Check for existing files/chunks
2. Upload file in chunks with progress
3. Merge chunks into final file
4. Verify with SHA-1 checksum
5. Clean up temporary files

### Notes

Based on these repos:
- https://github.com/ZiTAL/html5-file-upload-chunk
- https://github.com/mailopl/html5-xhr2-chunked-file-upload-slice/
