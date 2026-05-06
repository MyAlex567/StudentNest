<?php
namespace App\Helpers;

class FileStorageHelper{

    public function store($username, $postType, $fileUpload){
        // No chmod operations - works on restricted servers
        $uploadDir = "../../Assets/documents/{$username}/{$postType}";
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        $allowedExts = ['pdf', 'docx', 'jpg', 'jpeg'];

        // Handle multiple file upload
        $uploadMessages = [];
        $uploadSuccessCount = 0;
        $uploadFailCount = 0;

        $paths = [];

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $files = $fileUpload;


        $validIndexes = array_keys(array_filter($files['error'], fn($e) => $e !== UPLOAD_ERR_NO_FILE));

        foreach($validIndexes as $keys){
            $fileName = basename($files['name'][$keys]);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileTmpName = $files['tmp_name'][$keys];
            $fileError = $files['error'][$keys];
            $fileSize = $files['size'][$keys];

            $isValid = true;
            $errorMessage = '';

            if ($fileError !== UPLOAD_ERR_OK) {
                $isValid = false;
                $errorMessage = "Error uploading {$fileName}";
            } elseif (!in_array($fileExt, $allowedExts)) {
                $isValid = false;
                $errorMessage = "{$fileName}: Invalid file type";
            } elseif ($fileSize > $maxFileSize) {
                $isValid = false;
                $errorMessage = "{$fileName}: Exceeds 10MB limit";
            }

            if($isValid){
                $uniqueFileName = time() . '_' . rand(1000, 9999) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
                $targetPath = $uploadDir . '/' . $uniqueFileName;

                if(move_uploaded_file($fileTmpName, $targetPath)) {
                    $uploadSuccessCount++;
                    $uploadMessages[] = ['type' => 'success', 'msg' => "✓ {$fileName} uploaded successfully"];
                    $paths[] = $targetPath;
                }else{
                    $uploadFailCount++;
                    $uploadMessages[] = ['type' => 'error', 'msg' => "✗ Failed to upload {$fileName}"];
                }
                
            } else {
                $uploadFailCount++;
                $uploadMessages[] = ['type' => 'error', 'msg' => "✗ {$errorMessage}"];
            }
        }

        return $paths;

    }
}

?>