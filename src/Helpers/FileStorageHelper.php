<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * Handles file upload storage and file deletion.
 *
 * @author lisayAlex <202401-00307@dwc-legazpi.edu>
 */
class FileStorageHelper{

    /**
     * Deletes a user folder including all files and subfolders inside it.
     *
     * @param string $username The username used as the folder name.
     *
     * @return bool Returns true if the folder was deleted or does not exist, otherwise false.
     */
    public function deleteUserFolder(string $username): bool{
        try{
            $path = __DIR__ . "/../../Assets/documents/{$username}";

            if (!file_exists($path)) {
                return true;
            }

            if (!is_dir($path)) {
                return false;
            }

            return $this->deleteFolderRecursive($path);

        }catch(\Throwable){
            return false;
        }
    }

    /**
     * Recursively deletes a folder and everything inside it.
     *
     * @param string $folderPath The folder path to delete.
     *
     * @return bool Returns true if the folder was deleted successfully, otherwise false.
     */
    private function deleteFolderRecursive(string $folderPath): bool{
        $items = array_diff(scandir($folderPath), ['.', '..']);

        foreach ($items as $item) {
            $itemPath = $folderPath . DIRECTORY_SEPARATOR . $item;

            if (is_dir($itemPath)) {
                $this->deleteFolderRecursive($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        return rmdir($folderPath);
    }

    /**
     * Deletes uploaded files from storage.
     *
     * @param array $filePath List of file paths to delete.
     * @return array|string Delete result array or error message.
     */
    public function deleteFile(array $filePath): array|string{
    
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

    /**
     * Stores uploaded files in the document directory.
     *
     * @param string $username The username used for the upload folder.
     * @param string $postType The post type used for the upload folder.
     * @param array $fileUpload The uploaded file data.
     * @return array Upload result data.
     */
    public function store(string $username, string $postType, array $fileUpload): array{
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