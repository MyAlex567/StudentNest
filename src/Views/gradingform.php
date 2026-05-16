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
    <div class="container py-4">
        <div class="row g-4">

            <!-- LEFT SIDE: Grade Form -->
            <?php if($submissionData['success']): ?>

                <div class="col-12 col-lg-7"> 
                    <div class="card border-0 shadow text-dark h-100">
                        <div class="card-body p-4">

                            <h4 class="fw-bold mb-1">Grade Submission</h4>
                            <p class="text-muted small mb-4">
                                Review the student's answer and submit a grade.
                            </p>

                            <!-- Student Info -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Submitted By</label>
                                <input type="text" class="form-control" value="<?php echo $submissionData['data']['submitted_by_name'] ?>" readonly>
                            </div>

                            <!-- Activity Title -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Activity</label>
                                <input type="text" class="form-control" value="<?php echo $submissionData['data']['title'] ?>" readonly>
                            </div>

                            <!-- Student Answer -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Student Answer</label>
                                <textarea class="form-control" rows="5" readonly><?php echo $submissionData['data']['answer_text'] ?></textarea>
                            </div>

                            <!-- Grade Form -->
                            <form action="<?php echo $_SERVER['PHP_SELF'] . '?submission_id=' . $subId ?>" method="POST">
                                <input type="hidden" name="submission_id" value="1">

                                <div class="mb-3">
                                    <label for="grade" class="form-label fw-semibold">Grade</label>
                                    <input type="number" 
                                        class="form-control" 
                                        id="grade" 
                                        name="grade" 
                                        min="0" 
                                        max="100" 
                                        placeholder="Enter grade"
                                        required>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="javascript:history.back()" class="btn btn-outline-secondary rounded-pill px-4">
                                        Cancel
                                    </a>

                                    <button type="submit" class="btn btn-dark rounded-pill px-4" name="submitGrade">
                                        Submit Grade
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>



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

            <?php else: ?>
                <h1>HYS TANGLAY NA AKO MAG KAAG ERROR PWEDE NA INI</h1>
            <?php endif; ?>


        </div>
    </div>

    <script src="../../Assets/JavaScript/gradingForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>