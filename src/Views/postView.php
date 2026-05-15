<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
require_once __DIR__ . '../../../vendor/autoload.php';    

$_SESSION['post_id'] = $_GET['post_id'];
$documents = $_SESSION['class_post_data'][$_GET['post_id']] ?? '';

if (empty($documents)) {
    ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <div class="class-header-gradient p-5 mb-4 shadow-sm">
            <div class="position-relative" style="z-index: 2;">
                <h1 class="display-5 fw-bold mb-1">Post Not Found</h1>
                <p class="lead mb-0">
                    The Post you’re looking for doesn’t exist or the post code is invalid.
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentNest - Viewing Post</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../Assets/CssDesign/viewPage.css">
</head>

<body>
    <header class="navbar navbar-light bg-white border-bottom px-4 py-3 mb-4 sticky-top">
        <div class="container-fluid p-0 gap-3">
            <a href="./classPage.php?class_code=<?php echo $_SESSION['class_id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-house-door me-1"></i> Go back to class</a>
            <h5 class="mb-0 fw-bold">Viewing Post</h5>
            <div class="ms-auto d-flex align-items-center gap-3">
                <i class="bi bi-bell text-muted fs-5"></i>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['userData']['username'] ?? 'Not Available'; ?>&background=random" class="rounded-circle" width="35">
            </div>
        </div>
    </header>

    <div class="documentContainer">
        <div class="document-grid">
            <?php if (count($documents) > 0 && $documents['post_data']['file_paths'] && $documents['file_preview_details']): ?>

                <?php for($index = 1; $index <= count($documents['file_preview_details']); $index++): ?>

                    <div class="document-card" onclick="viewDocument('<?php echo $documents['file_preview_details']['file_detail'.$index]['file_path']; ?>', '<?php echo $documents['file_preview_details']['file_detail'.$index]['ext']; ?>')">
                        <?php if($documents['file_preview_details']['file_detail'.$index]['class_role'] == "teacher"): ?>
                            <button class="delete-btn" onclick="deleteFile(event, '<?php echo $documents['file_preview_details']['file_detail'.$index]['filename']; ?>')">×</button>
                        <?php endif; ?>
                        <div class="document-preview">
                            <?php echo $documents['file_preview_details']['file_detail'.$index]['previewContent']; ?>
                        </div>
                        <div class="document-info">
                            <div class="document-name"><?php echo htmlspecialchars($documents['file_preview_details']['file_detail'.$index]['displayName']); ?></div>
                            <div class="document-meta">
                                <span class="document-type type-<?php echo $documents['file_preview_details']['file_detail'.$index]['ext']; ?>"><?php echo strtoupper($documents['file_preview_details']['file_detail'.$index]['ext']); ?></span>
                                <span class="file-size">📏 <?php echo $documents['file_preview_details']['file_detail'.$index]['fileSizeFormatted']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            <?php else: ?>
                <div class="empty-state" style="text-align: center; padding: 60px; background: white; border-radius: 12px;">
                    <div style="font-size: 64px;">📭</div>
                    <h3>No documents To see Here Bro</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../Assets/JavaScript/classPage.js"></script>
<script src="../../Assets/JavaScript/postView.js"></script>
</body>
</html>