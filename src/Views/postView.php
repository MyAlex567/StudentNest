<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
require_once __DIR__ . '../../../vendor/autoload.php';    

$_SESSION['post_id'] = $_GET['post_id'];

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

    <?php $documents = $_SESSION['class_post_data'][$_GET['post_id']] ?>

    <div class="documentContainer">
        <div class="document-grid">
            <?php if (count($documents) > 0): ?>
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
                    <h3>No documents yet</h3>
                    <p>Upload files or use FTP to add documents</p>
                </div>
            <?php endif; ?>
        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../Assets/JavaScript/classPage.js"></script>
<script src="../../Assets/JavaScript/postView.js"></script>
</body>
</html>