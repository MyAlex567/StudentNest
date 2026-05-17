<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '../../../vendor/autoload.php';

use App\Models\ClassModel;
use App\Helpers\Database;
use App\Controllers\ClassController;

$database = Database::getInstance();
$classModel = new ClassModel($database);
$ClassController = new ClassController($classModel);


$classCode = $_GET['class_code'] ?? '';
$_SESSION['class_id'] = $classCode;
$classResult = $ClassController->getClassData($classCode);
$classData = $classResult['data'] ?? '';
$classMembers = $ClassController->selectAllClass($classCode);
$class_post_details = [];
$classRole = '';
$classPost = [];
$classActivity = [];
$getAct = [];

if(isset($_POST['updateCanSubmit']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $details = [
        'username' => $_SESSION['userData']['username'],
        'activity_id' => $_POST['activity_id'],
        'can_submit' => isset($_POST['can_submit']) ? 1 : 0,
        'post_id' => $_POST['post_id']
    ];

    $result = $ClassController->updateCanSubmit($details);

    if($result['success']){
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => $result['message']
        ];
    }else{
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => implode(', ', $result['message'])
        ];        
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . "?class_code={$classCode}");
    exit();
    
}

if (!$classResult['success'] && empty($_SESSION['userData'])) {
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

if(isset($_POST['submit_user_activity']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $_SESSION['activity_id'] = $_POST['submit_user_activity'];
    header('Location: '. './submitActivity.php');
    exit;
}

// Handle posting

if(isset($_POST['submit_activity']) && $_SERVER['REQUEST_METHOD'] === "POST"){
    $postData = [
        'username' => $_SESSION['userData']['username'],
        'class_code' => $classCode,
        'post_type' => 'post_activity',
        'activity_file' => $_FILES['activity_file'] ?? '',
        'due_date' => $_POST['due_date'],
        'activity_title' => $_POST['activity_title'] ?? '',
        'activity_description' => $_POST['activity_description'] ?? ''
    ];

    $result = $ClassController->createActivity($postData);

    if($result['success']){
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Upload Success'
        ];
    }else{
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => implode(', ', $result['message'])
        ];        
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . "?class_code={$classCode}");
    exit();
}

if(isset($_POST['post']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $postData = [
        'username' => $_SESSION['userData']['username'],
        'class_code' => $classCode,
        'post_type' => $_POST['post_type'] ?? '',
        'file' => $_FILES['file'] ?? '',
        'post_title' => $_POST['post_title'] ?? '',
        'post_description' => $_POST['post_description'] ?? ''
    ];

    $result = $ClassController->createPost($postData);

    if(!$result['success']){
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => implode(', ', $result['message'])
        ];
    }else{
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Upload Success'
        ];
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . "?class_code={$classCode}");
    exit();
}

try{

    $isSuccess = $ClassController->getPost($classCode);
    $getAct = $ClassController->getActivityPost($classCode);
    
    if($isSuccess['success']){
        $classPost = $isSuccess['class_post'];
    }

    $userClassInfo = [
        'username' => $_SESSION['userData']['username'],
        'class_code' => $classCode
    ];
        
    $result = $ClassController->getClassRole($userClassInfo);

    if($result['success']){
        $classRole = $result['role'];
    }
    
}catch(PDOException $e){
    echo $e->getMessage();
}       

if(isset($_POST['leave_class']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = [
        'username' => $_SESSION['userData']['username'],
        'class_role' => $classRole,
        'class_code' => $classCode
    ];

    $result = $ClassController->leaveClass($data);

    if($result['success']){
        header('Location: ' . '../../index.php');
        exit;
    }
}

if(isset($_POST['delete_class']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = [
        'username' => $_SESSION['userData']['username'],
        'class_role' => $classRole,
        'class_code' => $classCode
    ];

    $result = $ClassController->deleteClass($data);

    if($result['success']){
        header('Location: ' . '../../index.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentNest - Viewing Class</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../Assets/CssDesign/classPage.css">
</head>

<body>
    <header class="navbar navbar-light bg-white border-bottom px-4 py-3 mb-4 sticky-top">
        <div class="container-fluid p-0 gap-3">
            <a href="../../index.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-house-door me-1"></i> Home</a>
            <h5 class="mb-0 fw-bold">Viewing Class</h5>
            <div class="ms-auto d-flex align-items-center gap-3">
                <i class="bi bi-bell text-muted fs-5"></i>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['userData']['username'] ?? 'Not Available'; ?>&background=random" class="rounded-circle" width="35">
            </div>
        </div>
    </header>

    <div class="d-flex justify-content-center">
        <!-- Content Area -->
        <div id="content">
            <div class="container-fluid px-4">
                <!-- Class Banner -->
                <div class="class-header-gradient p-5 mb-4 shadow-sm">
                    <div class="position-relative" style="z-index: 2;">
                        <h1 class="display-5 fw-bold mb-1"><?php echo $classData['class_name'] ?></h1>
                        <p class="lead mb-0">
                            Section: <?php echo !empty($classData['section']) ? $classData['section'] : 'Not Available'; ?> | 
                            Room: <?php echo !empty($classData['room']) ? $classData['room'] : 'Not Available'; ?>
                        </p>
                    </div>
                    <i class="bi bi-code-slash position-absolute" style="right: 20px; bottom: -20px; font-size: 120px; opacity: 0.1;"></i>
                </div>

                <!-- Desktop Tab Navigation (Hidden on Mobile) -->
                <div class="card border-0 shadow-sm mb-4 desktop-tabs-container d-none d-md-block">
                    <ul class="nav nav-tabs px-3" id="classTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="stream-tab" data-bs-toggle="tab" data-bs-target="#stream" type="button">Stream</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="classwork-tab" data-bs-toggle="tab" data-bs-target="#classwork" type="button">Classwork</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="people-tab" data-bs-toggle="tab" data-bs-target="#people" type="button">People</button>
                        </li>
                    </ul>
                </div>

                <!-- Tab Content Container -->
                <div class="tab-content" id="classTabContent">
                    <!-- Stream Tab -->
                    <div class="tab-pane fade show active" id="stream" role="tabpanel">
                        <div class="row">
                            <!-- Left Column: Announcements -->
                            <div class="col-lg-9">
                                <div class="card border-0 shadow-sm mb-4 p-3 d-flex flex-row align-items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle" width="40">
                                    <button type="button" class="bg-light p-2 rounded-pill px-3 text-muted border"
                                            style="cursor: pointer;"
                                            data-bs-toggle="modal" data-bs-target="#announcementModal">
                                            Share Something with your class...
                                    </button>
                                </div>

                                <!-- POST CARD -->

                                <?php if(empty($classPost)): ?>
                                    <p>No post</p>
                                <?php else: ?>
                                    <?php $class_post_details = [] ?>
                                    <?php foreach ($classPost as $post): ?>
                                    
                                        <?php
                                            $class_post_details[$post['post_id']] = [
                                                'post_data' => $post
                                            ];
                                        ?>

                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-body p-4 position-relative">

                                                <?php if($classRole === 'teacher'): ?>
                                                    <div class="dropdown position-absolute top-0 end-0 m-3">
                                                        <form action="<?php echo $_SERVER['PHP_SELF'] . '?class_code=' . $classCode ?>" method="POST">

                                                            <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown">
                                                                <i class="bi bi-three-dots-vertical fs-5"></i>
                                                            </button>

                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a class="dropdown-item text-danger"
                                                                    href="#"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deletePostModal<?php echo $post['post_id']; ?>">
                                                                        Delete
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="d-flex align-items-center gap-3 mb-3">
                                                    <img src="https://ui-avatars.com/api/?name=<?php echo $post['author'] ?>&background=0d6efd&color=fff" class="rounded-circle" width="45">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?php echo $post['author'] ?></h6>
                                                        <small class="text-muted">Posted <?php 
                                                        echo (floor((strtotime($post['post_date']) - time()) / 86400) * -1) == 0 
                                                                ? 'today' 
                                                                : (floor((strtotime($post['post_date']) - time()) / 86400) * -1) . " days ago";
                                                        ?></small>
                                                    </div>
                                                </div>

                                                <span class="badge <?php echo $post['post_type'] === 'material' ? 'bg-dark' : 'bg-danger'; ?>">
                                                    <?php echo $post['post_type']; ?>
                                                </span>
                                                <a href="../Views/postView.php?post_id=<?php echo $post['post_id'] ?>" class="link-dark">
                                                    <?php echo $post['title'] ?>
                                                </a>

                                                <p class="card-text"><?php echo $post['description'] ?></p>

                                            </div>

                                            <?php $files = $post['file_paths'] ? explode('[[FILE_SEPARATOR]]', $post['file_paths']) : []?>

                                                <?php if (count($files) > 0): ?>
                                                    <?php $file_order = 1; ?>
                                                    <?php foreach ($files as $doc): 
                                                        $filename = basename($doc);
                                                        $displayName = preg_replace('/^\d+_/', '', $filename);
                                                        $ext = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
                                                        $fileSize = filesize($doc);
                                                        $fileSizeFormatted = ($fileSize > 1024 * 1024) ? 
                                                            round($fileSize / (1024 * 1024), 2) . ' MB' : 
                                                            round($fileSize / 1024, 2) . ' KB';
                                                        
                                                        if ($ext == 'pdf') {
                                                            $previewContent = '<div class="document-icon">📕</div>';

                                                            $class_post_details[$post['post_id']]["file_preview_details"]["file_detail{$file_order}"] = [
                                                                'class_role' => $classRole,
                                                                'file_path' => $doc,
                                                                'filename' => $filename,
                                                                'displayName' => $displayName,
                                                                'ext' => $ext,
                                                                'fileSize' => $fileSize,
                                                                'fileSizeFormatted' => $fileSizeFormatted,
                                                                'previewContent' => $previewContent
                                                            ];

                                                        } elseif ($ext == 'docx') {
                                                            $previewContent = '<div class="document-icon">📘</div>';
                                                            $class_post_details[$post['post_id']]["file_preview_details"]["file_detail{$file_order}"] = [
                                                                'class_role' => $classRole,
                                                                'file_path' => $doc,
                                                                'filename' => $filename,
                                                                'displayName' => $displayName,
                                                                'ext' => $ext,
                                                                'fileSize' => $fileSize,
                                                                'fileSizeFormatted' => $fileSizeFormatted,
                                                                'previewContent' => $previewContent
                                                            ];
                                                        } else {
                                                            $previewContent = '<img src="' . $doc . '" alt="Preview" class="image-preview" onerror="this.style.display=\'none\'; this.parentElement.innerHTML=\'<div class=document-icon>🖼️</div>\'">';
                                                            $class_post_details[$post['post_id']]["file_preview_details"]["file_detail{$file_order}"] = [
                                                                'class_role' => $classRole,
                                                                'file_path' => $doc,
                                                                'filename' => $filename,
                                                                'displayName' => $displayName,
                                                                'ext' => $ext,
                                                                'fileSize' => $fileSize,
                                                                'fileSizeFormatted' => $fileSizeFormatted,
                                                                'previewContent' => $previewContent
                                                            ];
                                                        }
                                                
                                                        $file_order++;
                                                    ?>
                                                    <?php endforeach; ?>
                                                    <?php $_SESSION['class_post_data'] = $class_post_details?>
                                                <?php endif; ?>
                                        </div>
                                        

                                        <!-- DELET NOTIFICATION -->
                                        <div class="modal fade" id="deletePostModal<?php echo $post['post_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Delete Post</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        Are you sure you want to delete this post?
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            Cancel
                                                        </button>

                                                        <button type="button" class="btn btn-danger delete_post" data-bs-dismiss="modal"
                                                                data-post-id="<?php echo $post['post_id']; ?>"
                                                                data-class-code="<?php echo $classCode; ?>">
                                                            Delete
                                                        </button>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                            </div>

                            <!-- Right Column: Info (Hidden on Mobile for cleaner stream) -->
                            <div class="col-lg-3 d-none d-lg-block">
                                <?php if($classRole === 'student'): ?>
                                    <form action="<?php echo $_SERVER['PHP_SELF'] . '?class_code=' . $classCode ?>" method="POST" class="card border-0 shadow-sm p-3 mb-4">
                                        <h6 class="fw-bold mb-3 text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>
                                            Leave Class
                                        </h6>

                                        <p class="small text-muted mb-3">
                                            You are currently enrolled in this class. If you leave, you will no longer see its posts, assignments, and files.
                                        </p>

                                        <button 
                                            type="submit" 
                                            name="leave_class"
                                            class="btn btn-outline-danger rounded-pill fw-semibold"
                                            onclick="return confirm('Are you sure you want to leave this class?');"
                                        >
                                            <i class="bi bi-door-open me-2"></i>
                                            Leave Class
                                        </button>
                                    </form>
                                <?php elseif($classRole === 'teacher'): ?>
                                    <form action="<?php echo $_SERVER['PHP_SELF'] . '?class_code=' . $classCode ?>" method="POST" class="card border-0 shadow-sm p-3 mb-4">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 45px; height: 45px; min-width: 45px;">
                                                <i class="bi bi-trash3 fs-5"></i>
                                            </div>

                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-1 text-danger">
                                                    Delete Class
                                                </h6>

                                                <p class="small text-muted mb-3">
                                                    This will permanently delete this class, including its students, posts, assignments, and files.
                                                    This action cannot be undone.
                                                </p>

                                                <button 
                                                    type="submit" 
                                                    name="delete_class"
                                                    class="btn btn-danger rounded-pill fw-semibold px-4"
                                                    onclick="return confirm('Delete this class permanently?')"
                                                >
                                                    <i class="bi bi-trash3 me-2"></i>
                                                    Delete Class
                                                </button>
                                            </div>
                                        </div>
                                    </form>                                    
                                <?php endif; ?>
                                <!-- Class Code here -->
                                <div class="card border-0 shadow-sm p-3 mb-4">
                                    <h6 class="fw-bold mb-3">Class Code</h6>
                                    <div class="d-flex align-items-start justify-content-center gap-2 mb-2">
                                        <div>
                                            <h1 class="text-align-center classCode"><?php echo $classCode ?></h1>
                                        </div>
                                    </div>
                                </div>     
                                
                                
                            </div>
                        </div>
                    </div>

                    <!-- Classwork Tab Placeholder -->
                    <div class="tab-pane fade" id="classwork" role="tabpanel">
                        <div class="col-lg-9">

                            <?php if($classRole === "teacher"): ?>
                                <div class="card border-0 shadow-sm mb-4 p-3 d-flex flex-row align-items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['userData']['username'] ?>&background=random" class="rounded-circle" width="40">
                                    <button type="button" class="bg-light p-2 rounded-pill px-3 text-muted border"
                                            style="cursor: pointer;"
                                            data-bs-toggle="modal" data-bs-target="#activityModal">
                                            post activity
                                    </button>
                                </div>
                            <?php endif ?>
                        </div>


                        <?php if(!$getAct['success']): ?>
                            <div class="card border-0 shadow-sm p-5 text-center">
                                <i class="bi bi-journal-text display-1 text-muted mb-3"></i>
                                <h4>Classwork Content</h4>
                                <p class="text-muted">Assignments and materials will appear here.</p>
                            </div>
                        <?php else: ?>       
                            <?php foreach($getAct['class_post'] as $post): ?>
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body p-4 position-relative">

                                        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-3">

                                            <div class="d-flex align-items-center gap-3">
                                                <img src="https://ui-avatars.com/api/?name=<?php echo $post['author'] ?>&background=0d6efd&color=fff"
                                                    class="rounded-circle"
                                                    width="45">

                                                <div>
                                                    <h6 class="mb-0 fw-bold">
                                                        <?php echo $post['author'] ?>
                                                    </h6>

                                                    <small class="text-muted">
                                                        Posted
                                                        <?php
                                                        echo (floor((strtotime($post['post_date']) - time()) / 86400) * -1) == 0
                                                            ? 'today'
                                                            : (floor((strtotime($post['post_date']) - time()) / 86400) * -1) . " days ago";
                                                        ?>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="text-md-end">
                                                <h6 class="mb-0 fw-bold text-danger">
                                                    Due Date
                                                </h6>

                                                <?php
                                                    $dueDate = strtotime($post['due_date']);
                                                    $isToday = date('Y-m-d', $dueDate) === date('Y-m-d');
                                                    $checkIfLate = date('Y-m-d', $dueDate) < date('Y-m-d H:i:s');
                                                ?>

                                                <small class="<?php echo $isToday ? 'text-danger fw-bold' : 'fw-bold text-muted'; ?>">
                                                    <?php
                                                        echo $isToday
                                                            ? 'Due Today • ' . date('h:i A', $dueDate)
                                                            : 'Due • ' .date('F d, Y h:i A', $dueDate);
                                                    ?>
                                                </small>
                                                                                                                                             
                                            </div>

                                        </div>

                                        <?php if($classRole === 'student'): ?>
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                                                <span class="badge <?php echo $post['post_type'] === 'material' ? 'bg-dark' : 'bg-danger'; ?>">
                                                    <?php echo ucfirst($post['post_type']); ?>
                                                </span>

                                                <?php if($post['can_submit']): ?>
                                                    <form action="<?php echo $_SERVER['PHP_SELF'] . '?class_code=' . $classCode ?>" method="POST">
                                                        <button class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm submitActivity" value="<?php echo $post['activity_id'] ?>"
                                                                name="submit_user_activity">
                                                            <i class="bi bi-upload me-2"></i>
                                                            <?php echo $checkIfLate ? 'Submit Late Bro' : 'Submit Activity' ?>
                                                        </button>                                                    
                                                    </form>
                                                <?php else: ?>
                                                        <span 
                                                            class="btn btn-secondary rounded-pill px-4 fw-semibold shadow-sm">
                                                            <i class="bi bi-lock-fill me-2"></i>
                                                            Submission Closed
                                                        </span>
                                                <?php endif; ?>

                                            </div>
                                        <?php endif; ?>

                                        <h4><?php echo $post['title'] ?></h4>

                                        <p class="card-text mb-0">
                                            <?php echo $post['description'] ?>
                                        </p>

                                        <?php if($classRole === 'teacher'): ?>
                                            <!-- Control if the activity are still accepting activitys -->
                                            <form action="<?php echo $_SERVER['PHP_SELF'] . '?class_code=' . $classCode ?>" method="POST">
                                                <input type="hidden" name="activity_id" value="<?php echo $post['activity_id']; ?>">
                                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">

                                                <div class="form-check form-switch mb-3">
                                                    <input 
                                                        class="form-check-input" 
                                                        type="checkbox" 
                                                        role="switch"
                                                        id="canSubmitSwitch"
                                                        name="can_submit"
                                                        value="1"
                                                        <?php echo $post['can_submit'] == 1 ? 'checked' : ''; ?>
                                                    >

                                                    <label class="form-check-label fw-semibold" for="canSubmitSwitch">
                                                        Allow students to submit
                                                    </label>
                                                </div>

                                                <button type="submit" name="updateCanSubmit" class="btn btn-dark rounded-pill px-4">
                                                    Save
                                                </button>
                                            </form>                                            
                                        <?php endif; ?>
                                    </div>
                                </div>            
                                                                                    
                            <?php endforeach; ?>                                                        
                        <?php endif; ?>
                        
                    </div>

                    <!-- People Tab Placeholder -->
                    <div class="tab-pane fade" id="people" role="tabpanel">
                        <div class="card border-0 shadow-sm p-5 text-center">

                            <?php if (!$classMembers['success']): ?>
                                <i class="bi bi-people display-1 text-muted mb-3"></i>
                                <h4><?php echo $classMembers['message']; ?></h4>
                                <h4>People Content</h4>
                                <p class="text-muted">Teachers and classmates will appear here.</p>
                            <?php else: ?>
                                <?php foreach ($classMembers['class'] as $user): ?>

                                    <div class="container py-3">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-3 bg-white shadow rounded">
                                                    <img src="https://ui-avatars.com/api/?name=<?php echo $user['full_name'] ?>"class="rounded-circle me-3" width="50" height="50">
                                                    <div>
                                                        <p><?php echo $user['full_name'] . ' : ' . $user['role'] ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                <?php endforeach; ?>
                            <?php endif; ?>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
        
        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content border-0 shadow-lg" style="border-radius: 8px;">
                 

                <div class="modal-body p-4">
                    <h5 class="modal-title mb-4 fw-normal" id="announcementModalLabel" style="font-size: 1.4rem;">Activity</h5>  
                    <form action="<?php echo $_SERVER['PHP_SELF'] . '?class_code=' . $classCode ?>" enctype="multipart/form-data" method="POST">

                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>

                                <input 
                                    type="datetime-local" 
                                    class="form-control"
                                    id="due_date"
                                    name="due_date">
                            </div>
                        </div>
                        
                        <!-- Text Section dito --> 
                        <div class="rounded-top bg-light p-3 border-bottom border-secondary-subtle">
                            <label for="activity_title">Title: </label>
                            <input type="text" name="activity_title" id="activity_title" class="bg-light p-2 rounded-pill px-3 text-muted border" placeholder="title here...">
                            <textarea class="form-control border-0 bg-transparent shadow-none" 
                                rows="5" 
                                name="activity_description"
                                placeholder="Describe your activity"
                                style="resize: none;"></textarea>
                        </div>

                        <div id="activity_file_display" class="activity_file_display">
                            <p id="activity_file_display" class="mb-0">No file selected</p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex gap-2">
                                <label class="btn btn-outline-secondary rounded-circle border-light-subtle circle-icon" for="file_activity" title="Upload Files"><i class="bi bi-upload"></i></label>
                                <input 
                                    type="file" 
                                    name="activity_file[]" 
                                    accept=".pdf,.docx,.jpg,.jpeg" 
                                    id="file_activity" 
                                    hidden multiple>
                            </div>
                            
                            <div class="d-flex align-items-center gap-3">
                                <button type="button" 
                                        class="btn btn-link text-dark text-decoration-none fw-medium" 
                                        style="color: black" 
                                        data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <div class="btn-group shadow-none">
                                    <button class="btn btn-dark px-4" type="submit" name="submit_activity">Post</button>
                                </div>
                            </div>
                        </div>


                    </form>

                </div>

            </div>

        </div>

    </div>



    <!-- This is where you posting shit -->
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 8px;">
                <div class="modal-body p-4">
                    
                    
                    <h5 class="modal-title mb-4 fw-normal" id="announcementModalLabel" style="font-size: 1.4rem;">Announcement</h5>

                    <form action="<?php echo $_SERVER['PHP_SELF'] . '?class_code=' . $classCode ?>" enctype="multipart/form-data" method="POST">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            
                            <select class="form-select btn-outline-secondary rounded-pill w-25 px-3 py-1 d-flex align-items-center gap-2 text-primary border-light-subtle"
                                    id="post_type"
                                    name="post_type">
                                <option selected value="post_announcement">Announcement</option>
                                <option value="post_material">Material</option>
                            </select>
                        </div>
                    
                        <!-- Editor Section -->
                        <div class="rounded-top bg-light p-3 border-bottom border-secondary-subtle" id="editor_section">
                            <label for="material_title">Title: </label>
                            <input type="text" name="post_title" id="post_title" class="bg-light p-2 rounded-pill px-3 text-muted border" placeholder="title here...">                             
                            <textarea class="form-control border-0 bg-transparent shadow-none" 
                                rows="5" 
                                name="announcement"
                                placeholder="Announce something to your class"
                                style="resize: none;"></textarea>
                        </div>
                    
                        <div id="attach_file_container" class="attach_file_container">
                            <p id="fileName" class="mb-0">No file selected</p>
                        </div>

                        <!-- Footer Actions -->
                    
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex gap-2">
                                <label class="btn btn-outline-secondary rounded-circle border-light-subtle circle-icon" for="fileInput" title="Upload Files"><i class="bi bi-upload"></i></label>
                                <input 
                                    type="file" 
                                    name="file[]" 
                                    accept=".pdf,.docx,.jpg,.jpeg" 
                                    id="fileInput" 
                                    hidden multiple
                                    >
                                <button class="btn btn-outline-secondary rounded-circle border-light-subtle circle-icon"><i class="bi bi-link-45deg"></i></button>
                            </div>
                            
                            <div class="d-flex align-items-center gap-3">
                                <button type="button" 
                                        class="btn btn-link text-dark text-decoration-none fw-medium" 
                                        style="color: black" 
                                        data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <div class="btn-group shadow-none">
                                    <button class="btn btn-dark px-4" type="submit" name="post">Post</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation (Visible only on small screens) -->
    <nav class="navbar fixed-bottom mobile-bottom-nav d-md-none">
        <div class="container-fluid">
            <div class="nav nav-justified w-100" role="tablist">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#stream" type="button">
                    <i class="bi bi-chat-left-text"></i>
                    <span>Stream</span>
                </button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#classwork" type="button">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Classwork</span>
                </button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#people" type="button">
                    <i class="bi bi-people"></i>
                    <span>People</span>
                </button>
            </div>
        </div>
    </nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script to sync active state between desktop tabs and mobile bottom nav
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {  
            const targetId = event.target.getAttribute('data-bs-target');
            document.querySelectorAll(`[data-bs-target="${targetId}"]`).forEach(el => {
                const tab = new bootstrap.Tab(el);
                // Manually manage active class to keep both navs in sync
                el.classList.add('active');
                el.parentElement.querySelectorAll('.nav-link').forEach(sibling => {
                    if(sibling !== el) sibling.classList.remove('active');
                });
            });
        });
    });
</script>

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

<script src="../../Assets/JavaScript/classPage.js"></script>
</body>
</html>