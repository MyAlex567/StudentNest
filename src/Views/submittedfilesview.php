<?php
session_start();
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

$subId = $_GET['submission_id'] ?? null;
$submissionData = [];
$submissionFiles = [];             

if (empty($_SESSION['userData']) || $subId === null) {
    ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <div class="class-header-gradient p-5 mb-4 shadow-sm">
            <div class="position-relative" style="z-index: 2;">
                <h1 class="display-5 fw-bold mb-1">Page Not Found</h1>
                <p class="lead mb-0">
                    The Page you’re looking for doesn’t exist.
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

$submissionData = $ClassController->getSubmissiondata($subId);
$submissionFiles = $ClassController->getSubmissionFilePaths($subId);


if(!$submissionData['success']){
    $submissionData = [
        'success' => false,
        'message' => 'No Data Found'
    ];
}

if(isset($_POST['submitGrade']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $details = [
        'username' => $_SESSION['userData']['username'],
        'grade' => $_POST['grade'],
        'submission_id' => $subId
    ];

    var_dump($details);

    $result = $ClassController->submitGrade($details);

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

    header('Location: ' . $_SERVER['PHP_SELF'] . '?submission_id=' . $subId);
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

<body>
    <div class="container py-4">
        <div class="row g-4 justify-content-centerz">
            <!-- RIGHT SIDE: Submitted Files -->
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow text-dark h-100">
                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-1">Submitted Files</h5>
                        <p class="text-muted small mb-4">
                            Files uploaded by the student.
                        </p>

                        <?php if($submissionFiles['success']): ?>
                            <?php foreach($submissionFiles['data'] as $file): ?>

                                <div class="border rounded-3 p-3 mb-3">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div>
                                            <h6 class="fw-semibold mb-1"><?php echo $file['file_name'] ?></h6>
                                            <small class="text-muted"><?php echo preg_replace('/^\d+_\d+_/', '', $file['file_name']); ?></small>
                                        </div>

                                        <a  onclick="viewDocument('<?php echo $file['file_path']; ?>', '<?php echo strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION)) ?>')" 
                                            class="btn btn-outline-dark btn-sm rounded-pill"
                                            target="_blank">
                                            View
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="text-center text-muted py-5">
                                <i class="bi bi-folder2-open" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No files submitted.</p>
                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>

    <script src="../../Assets/JavaScript/gradingForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>