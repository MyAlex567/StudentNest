<?php
session_start();
// Mock data for Rommel's StudentNest preview
$userData = $_SESSION['userData'] ?? ['username' => 'Charles'];
$className = "Web Development 101"; 
$classSection = "IT-3A";
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
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            --sidebar-width: 250px;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* Sidebar Styles */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: white;
            border-right: 1px solid #dee2e6;
            position: fixed;
            transition: all 0.3s;
        }

        .nav-link {
            color: #495057;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin: 0 10px;
        }

        .nav-link.active {
            background-color: #f0f4ff;
            color: #4e73df;
            font-weight: 600;
        }

        /* Main Content */
        #content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
        }

        /* Class Header */
        .class-header-gradient {
            background: var(--primary-gradient);
            border-radius: 15px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            padding: 1rem 1.5rem;
        }

        .nav-tabs .nav-link.active {
            color: #4e73df !important;
            border-bottom: 3px solid #4e73df !important;
            background: none !important;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
            #content { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <nav id="sidebar" class="d-none d-md-block">
        <div class="p-4">
            <h4 class="fw-bold text-primary">StudentNest</h4>
        </div>
        <ul class="nav flex-column gap-1">
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-grid-fill me-2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-book me-2"></i> My Class</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-bar-chart me-2"></i> Progress</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-file-earmark-text me-2"></i> Assignments</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
        </ul>
    </nav>

    <!-- Content Area -->
    <div id="content">
        <!-- Top Navbar -->
        <header class="navbar navbar-light bg-white border-bottom px-4 py-3 mb-4 sticky-top">
            <div class="container-fluid p-0">
                <h5 class="mb-0 fw-bold">Viewing Class</h5>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <i class="bi bi-bell text-muted fs-5"></i>
                    <img src="https://ui-avatars.com/api/?name=<?php echo $userData['username']; ?>&background=random" class="rounded-circle" width="35">
                </div>
            </div>
        </header>

        <div class="container-fluid px-4">
            <!-- Class Banner -->
            <div class="class-header-gradient p-5 mb-4 shadow-sm">
                <div class="position-relative" style="z-index: 2;">
                    <h1 class="display-5 fw-bold mb-1"><?php echo $className; ?></h1>
                    <p class="lead mb-0">Section: <?php echo $classSection; ?> | Room: Lab 2</p>
                </div>
                <i class="bi bi-code-slash position-absolute" style="right: 20px; bottom: -20px; font-size: 120px; opacity: 0.1;"></i>
            </div>

            <!-- Tab Navigation -->
            <div class="card border-0 shadow-sm mb-4">
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

            <div class="row">
                <!-- Left Column: Announcements -->
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm mb-4 p-3 d-flex flex-row align-items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle" width="40">
                        <div class="bg-light flex-grow-1 p-2 rounded-pill px-3 text-muted border" style="cursor: pointer;">
                            Share something with your class...
                        </div>
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

                <!-- Right Column: Info -->
                <div class="col-lg-3">
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
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>