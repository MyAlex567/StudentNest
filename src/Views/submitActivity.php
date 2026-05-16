<?php
session_start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
require_once __DIR__ . '../../../vendor/autoload.php';

use App\Models\ClassModel;
use App\Helpers\Database;
use App\Controllers\ClassController;

$database = Database::getInstance();
$classModel = new ClassModel($database);
$ClassController = new ClassController($classModel);

if (empty($_SESSION['userData']) && empty($_SESSION['activity_id'])) {
    var_dump($_SESSION['userData']);
    ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <div class="class-header-gradient p-5 mb-4 shadow-sm">
            <div class="position-relative" style="z-index: 2;">
                <h1 class="display-5 fw-bold mb-1">Class Not Found</h1>
                <p class="lead mb-0">
                    The class you’re looking for doesn’t exist or the class code is invalid.
                </p>
                <a href="../../index.php" class="btn btn-primary px-4">
                    <i class="bi bi-house-door me-1"></i> Go Back Home
                </a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary px-4 ms-2">
                    Back
                </a>
            </div>
            <i class="bi bi-code-slash position-absolute" style="right: 20px; bottom: -20px; font-size: 120px; opacity: 0.1;"></i>
        </div>
    <?php
    exit;
}

$activity_id = $_SESSION['activity_id'];
if(isset($_POST['submit_activity_answer']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $submissionData = [
        'username' => $_SESSION['userData']['username'] ?? '',
        'post_type' => 'submitted_activity',
        'activity_id' => $_SESSION['activity_id'] ?? '',
        'answer_text' => $_POST['answer_text'] ?? '',
        'submission_file' => $_FILES['submission_file'] ?? '',
        'submitted_at' => date('Y-m-d H:i:s')
    ];

    $result = $ClassController->submitActivity($submissionData);

    if(!$result['success']){
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => implode(', ', $result['message'])
        ];
    }else{
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => $result['message']
        ];
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<style>
    .message.error{
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
</style>

<body>
    <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3 mb-4">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-primary"
                        onclick="history.back()">

                    <i class="bi bi-arrow-left"></i>
                    Back
                </button>
                <h5 class="mb-0 fw-bold">
                    Activity Submission
                </h5>
            </div>
        </div>
    </nav>

    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST" enctype="multipart/form-data" class="card border-0 shadow-sm p-4">
        <!-- Answer Section -->
        <div class="mb-4">
            <label class="form-label fw-bold">
                Your Answer
            </label>
            <textarea class="form-control"
                    name="answer_text"
                    rows="6"
                    placeholder="Write your answer here..."
                    required></textarea>
        </div>
        <p id="attach_file_container">
            No attach file
        </p>

        <!-- File Upload -->
        <div class="mb-4">

            <label class="form-label fw-bold">
                Upload Attachment
            </label>

            <input type="file"
                class="form-control"
                name="submission_file[]"
                id="submission_file"
                multiple>

            <small class="text-muted">
                You can upload PDF, DOCX, JPG, JPEG, etc.
            </small>
        </div>
        <!-- Submit Button -->
        <div class="d-flex justify-content-end">
            <button type="submit"
                    name="submit_activity_answer"
                    class="btn btn-primary px-4">

                <i class="bi bi-upload me-2"></i>
                Submit Activity
            </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Assets/JavaScript/submitActivity.js"></script>
    
    <?php if (isset($_SESSION['toast'])): ?>
        <div class="position-fixed start-50 translate-middle-x" style="top: 30%; z-index: 11;">
            <div id="myToast" class="toast message <?php echo $_SESSION['toast']['type']; ?> border-0">
            <div class="toast-body text-center fw-bold">
                <?php echo $_SESSION['toast']['message']; ?>
            </div>
            </div>
        </div>

        <script>
            const toast = new bootstrap.Toast(document.getElementById('myToast'), {
                delay: 3000
            });
            toast.show();
        </script>

    <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>
</form>
</body>
</html>