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


$classCode = $_GET['class_code'] ?? '';
$classResult = $ClassController->getClassData($classCode);

if (!$classResult['success']) {
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


$classData = $classResult['data'];
$classMembers = $ClassController->selectAllClass($classCode);

if(isset($_POST['post']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $postType = $_POST['post_type'] ?? '';

    switch($postType){
        case 'post_material':
        case 'post_activity':

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
                    'message' => 'failed to upload'
                ];
            }else{
                $_SESSION['toast'] = [
                    'type' => 'success',
                    'message' => 'Upload Success'
                ];
            }
            break;

        case 'announcement':
            break;
            
        default:
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => 'failed to upload'
            ];
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . "?class_code={$classCode}");
    exit();
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

                                <!-- Post Card -->
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <img src="https://ui-avatars.com/api/?name=Instructor&background=0d6efd&color=fff" class="rounded-circle" width="45">
                                            <div>
                                                <h6 class="mb-0 fw-bold">Prof. Rommel</h6>
                                                <small class="text-muted">Posted 2 hours ago</small>
                                            </div>
                                        </div>
                                        <p class="card-text">Please ensure your 3NF database normalization assignment is submitted by Friday. Check the 'Classwork' tab for the ERD rubric.</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top py-3">
                                        <input type="text" class="form-control form-control-sm border-0 bg-light rounded-pill px-3" placeholder="Add class comment...">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Info (Hidden on Mobile for cleaner stream) -->
                            <div class="col-lg-3 d-none d-lg-block">
                                <div class="card border-0 shadow-sm p-3 mb-4">
                                    <h6 class="fw-bold mb-3">Upcoming Tasks</h6>
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        <i class="bi bi-file-earmark-text text-primary"></i>
                                        <div>
                                            <p class="small mb-0 fw-bold">ERD Normalization</p>
                                            <small class="text-muted">Due: Tomorrow, 11:59 PM</small>
                                        </div>
                                    </div>
                                </div>

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
                        <div class="card border-0 shadow-sm p-5 text-center">
                            <i class="bi bi-journal-text display-1 text-muted mb-3"></i>
                            <h4>Classwork Content</h4>
                            <p class="text-muted">Assignments and materials will appear here.</p>
                        </div>
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
                                <option value="post_activity">Activity</option>
                                <option value="post_material">Material</option>
                            </select>

                            <p class="mb-0" id="filename_display" style="font-style: italic;">No file Choosen</p>

                        </div>
                    
                        <!-- Editor Section -->
                        <div class="rounded-top bg-light p-3 border-bottom border-secondary-subtle" id="editor_section">
                            <textarea class="form-control border-0 bg-transparent shadow-none" 
                                rows="5" 
                                name="announcement"
                                placeholder="Announce something to your class"
                                style="resize: none;"></textarea>
                            
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
                                    disabled>
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