<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
require_once './vendor/autoload.php';

use App\Models\UserModel;
use App\Models\ClassModel;
use App\Helpers\Database;
use App\Controllers\AuthController;
use App\Controllers\ClassController;

$database = Database::getInstance();
$usermodel = new UserModel($database);
$classModel = new ClassModel($database);
$AuthController = new AuthController($usermodel);
$ClassController = new ClassController($classModel);

$userData = $_SESSION['userData'] ?? '';



// For creating class
if(isset($_POST['Create_class']) && $_SERVER['REQUEST_METHOD'] === "POST"){

    $class_data = [
        'class_name' => $_POST['class_name'] ?? '',
        'class_section' => $_POST['class_section'] ?? '',
        'class_room' => $_POST['class_room'] ?? '',
        'class_subject' => $_POST['class_subject'] ?? ''
    ];

    $result = $ClassController->store($class_data);

    if($result['success']){
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => $result['message']
        ];
    }else{
        $message = '';
        if(is_array($result['message'])){
            $message = implode(' ', $result['message']);

            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => $message
            ];      
        }else{
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => $result['message']
            ];      
        } 
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}


// Joining a class
if(isset($_POST['Join_class']) && $_SERVER['REQUEST_METHOD'] === "POST"){
    $code = $_POST['class_code'] ?? '';

    $result = $ClassController->join($code);

    if($result['success']){
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => $result['message']
        ];
    }else{
        $message = '';
        if(is_array($result['message'])){
            $message = implode(' ', $result['message']);

            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => $message
            ];      
        }else{
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => $result['message']
            ];      
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if(isset($_POST['logout']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $_SESSION = [];
    session_destroy();
    header('Location: ' . './src/Views/login.php');
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentNest</title>
    <link rel="stylesheet" href="./Assets/Vendor/css/bootstrap.min.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./Assets/CssDesign/newHome.css">
</head>
<body>

<div id="sidebar-overlay"></div>

<div class="d-flex">
    <nav id="sidebar" class="p-3 d-block position-md-sticky top-md-0 vh-md-100">
        <div class="px-2 mb-4">
            <h4 class="fw-bold mb-0">StudentNest</h4>
            <?php 
                // $bites = bin2hex(random_bytes(3));
                // echo strtoupper(substr($bites, 0, 6));
                // echo "<br>". $bites;
                // var_dump($_SESSION['userData']);
            ?>
        </div>
        <ul class="nav flex-column gap-2">
            <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-grid-fill me-2"></i> <span class="nav-text">Dashboard</span></a></li>
            <li class="nav-item" id="class_toggle"><a class="nav-link" href="#"><i class="bi bi-book me-2"></i> <span class="nav-text">My Class</span></a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-bar-chart me-2"></i> <span class="nav-text">Progress</span></a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-file-earmark-text me-2"></i> <span class="nav-text">Assignments</span></a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-patch-check me-2"></i> <span class="nav-text">Certificates</span></a></li>
            <li class="nav-item" id="settings_toggle"><a class="nav-link" href="#"><i class="bi bi-gear me-2"></i> <span class="nav-text">Settings</span></a></li>
        </ul>
    </nav>

    <div id="content" class="w-100 flex-grow-1">
        <header class="navbar navbar-light bg-white border-bottom px-4 py-3 mb-4 sticky-top">
            <div class="d-flex align-items-center gap-3">
                <button type="button" id="sidebarCollapse" class="hamburger-btn">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="mb-0 fw-bold d-none d-md-block">Dashboard</h5>
            </div>

            
            <!-- TOP NAVBAR -->
            <div class="ms-auto d-flex align-items-center gap-3">


                <!-- Toggle Join or Create Class -->
                <div class="dropdown dropstart">
                    <button class="btn btn-light" data-bs-toggle="dropdown" title="Join or Create Class">
                        <i class="fa-solid fa-plus"></i>
                    </button>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#Join-Class-Modal">Join Class</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#Create-Class-Modal">Create Class</a></li>
                    </ul>
                    
                </div>

                <i class="bi bi-bell text-muted fs-5"></i>
                <div class="dropdown dropstart">

                    <img src="https://ui-avatars.com/api/?name=User&background=random"
                        class="rounded-circle"
                        width="35"
                        role="button"
                        data-bs-toggle="dropdown"
                        style="cursor:pointer;">

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Profile</a></li><li>
                        
                        </li>
                        <hr class="dropdown-divider">
                        <li>
                            <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>

        </header>

            <!-- Create Class Modal -->
            <div class="modal fade" id="Create-Class-Modal" tabindex="-1" aria-labelledby="createClassLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen-sm-down">
                    <div class="modal-content dark-modal">
                        
                        <div class="modal-header custom-modal-header">
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn text-white me-3 p-0" data-bs-dismiss="modal">
                                    <i class="fa-solid fa-xmark fa-lg"></i>
                                </button>
                                <h4 class="modal-title mb-0" id="createClassLabel" style="color: black">Create class</h4>
                            </div>

                        </div>

                        <div class="modal-body p-4">
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="create-class-form">
                                
                                <div class="floating-input-container">
                                    <input type="text" id="class-name" class="custom-input" placeholder=" " autofocus name="class_name">
                                    <label for="class-name" class="floating-label">Class name (required)</label>
                                </div>

                                <div class="floating-input-container mt-4">
                                    <input type="text" id="section" class="custom-input" placeholder=" " name="class_section">
                                    <label for="section" class="floating-label">Section</label>
                                </div>

                                <div class="floating-input-container mt-4">
                                    <input type="text" id="room" class="custom-input" placeholder=" " name="class_room">
                                    <label for="room" class="floating-label">Room</label>
                                </div>

                                <div class="floating-input-container mt-4">
                                    <input type="text" id="subject" class="custom-input" placeholder=" " name="class_subject">
                                    <label for="subject" class="floating-label">Subject</label>
                                </div>

                                <div class="d-flex align-items-center gap-3 mt-2">
                                    <button type="submit" class="btn btn-secondary" name="Create_class">Create</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>


            
            <!-- Logout Modal -->
            <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Are you sure you want to logout?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body d-flex gap-3 justify-content-center">
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" name="logout">Yes, Logout</button>
                        </form>
                    </div>

                    </div>
                </div>
            </div>

            <!-- JOIN CLASS MODAL -->
            <div class="modal fade" id="Join-Class-Modal" tabindex="-1" aria-labelledby="JoinClassLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen-sm-down">
                    <div class="modal-content dark-modal">
                        
                        <div class="modal-header custom-modal-header">
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn text-white me-3 p-0" data-bs-dismiss="modal">
                                    <i class="fa-solid fa-xmark fa-lg"></i>
                                </button>
                                <h4 class="modal-title mb-0" id="createClassLabel" style="color: black">Join class</h4>
                            </div>

                        </div>

                        <div class="modal-body p-4">
                            <form action="" class="create-class-form gap-3" method="POST">

                                <p class="text-body fw-normal fs-6">
                                    <span class="text-body fw-bold fs-6">Class Code</span><br>
                                    Ask your teacher for the class code, then enter it here.
                                </p>
                                
                                <div class="floating-input-container">
                                    <input type="text" id="class-name" class="custom-input" placeholder=" " autofocus name="class_code">
                                    <label for="class-name" class="floating-label">Class Code</label>
                                </div>

                                <div class="d-flex align-items-center gap-3 mt-2">
                                    <button type="submit" class="btn btn-secondary" name="Join_class">Join</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <div class="px-4">
            <div class="card p-5 mb-4 bg-white border">
                <h2 class="fw-bold">Welcome Back 👋, <?php echo $userData['username'] ?? 'Unknown' ?></h2>
                <p class="text-muted">Labels are now restored for mobile, while keeping the mini-sidebar for desktop!</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card p-3 d-flex flex-row align-items-center justify-content-between border">
                        <div><p class="text-muted mb-0 small text-nowrap">Current Class</p><h4 class="mb-0 fw-bold">6</h4></div>
                        <i class="bi bi-briefcase stat-icon d-none d-sm-block"></i>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card p-3 d-flex flex-row align-items-center justify-content-between border">
                        <div><p class="text-muted mb-0 small text-nowrap">Avg Progress</p><h4 class="mb-0 fw-bold">78%</h4></div>
                        <i class="bi bi-speedometer2 stat-icon d-none d-sm-block"></i>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card p-3 d-flex flex-row align-items-center justify-content-between border text-nowrap">
                        <div><p class="text-muted mb-0 small">Assignments</p><h4 class="mb-0 fw-bold">12</h4></div>
                        <i class="bi bi-journal-text stat-icon d-none d-sm-block"></i>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card p-3 d-flex flex-row align-items-center justify-content-between border">
                        <div><p class="text-muted mb-0 small">Certificates</p><h4 class="mb-0 fw-bold">3</h4></div>
                        <i class="bi bi-award stat-icon d-none d-sm-block"></i>
                    </div>
                </div>
            </div>

            <!-- MAIN CONTENT -->
            <div class="card border mb-5 p-3" id="Main_user_content">

                <!-- CONTENT HERE -->

            </div>
        </div>
    </div>
</div>

    <script src="./Assets/Vendor/js/bootstrap.min.js"></script>
    <script type="module" src="./Assets/JavaScript/home.js"></script>

    <!-- Toast Notification -->

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