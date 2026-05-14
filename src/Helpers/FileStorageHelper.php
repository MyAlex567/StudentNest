<?php
namespace App\Helpers;

class FileStorageHelper{

    public function deleteFile($filePath){
    
        try{
            $filesFailedToDelete = [];
            $missingFiles = [];
            $successCount = 0;

            foreach($filePath as $path){

                if (!file_exists($path['file_path'])) {
                    $missingFiles[] = basename($path['file_path']);
                    continue;
                }

                if (unlink($path['file_path'])) {
                    $successCount++;
                } else {
                    $filesFailedToDelete[] = basename($path['file_path']);
                }
            }

            return [
                'successCount' => $successCount,
                'filesFailedToDelete' => $filesFailedToDelete
            ];
        }catch(\Throwable $error){
            return $error->getMessage();
        }

    }

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
                    $paths[] = [
                        'file_path' => $targetPath,
                        'file_name' => $fileName
                    ];
                }else{
                    $uploadFailCount++;
                    $uploadMessages[] = ['type' => 'error', 'msg' => "✗ Failed to upload {$fileName}"];
                }
                
            } else {
                $uploadFailCount++;
                $uploadMessages[] = ['type' => 'error', 'msg' => "✗ {$errorMessage}"];
            }
        }

        return [
            'path' => $paths,
            'uploadSuccessCount' => $uploadSuccessCount,
            'uploadFailCount' => $uploadFailCount,
            'uploadMessage' => $uploadMessages
        ];

    }
}

?>