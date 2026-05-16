<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="row g-4">

            <!-- LEFT SIDE: Grade Form -->
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
                            <input type="text" class="form-control" value="Juan Dela Cruz" readonly>
                        </div>

                        <!-- Activity Title -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Activity</label>
                            <input type="text" class="form-control" value="Activity Title" readonly>
                        </div>

                        <!-- Student Answer -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Student Answer</label>
                            <textarea class="form-control" rows="5" readonly>This is the student's answer.</textarea>
                        </div>

                        <!-- Grade Form -->
                        <form action="#" method="POST">
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

                                <button type="submit" class="btn btn-dark rounded-pill px-4">
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

                        <!-- File 1 -->
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <h6 class="fw-semibold mb-1">second.jpg</h6>
                                    <small class="text-muted">Image file</small>
                                </div>

                                <a href="../../Assets/documents/dexter/submitted_activity/1778912304_7357_second.jpg" 
                                class="btn btn-outline-dark btn-sm rounded-pill"
                                target="_blank">
                                    View
                                </a>
                            </div>
                        </div>

                        <!-- File 2 -->
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <h6 class="fw-semibold mb-1">third.jpg</h6>
                                    <small class="text-muted">Image file</small>
                                </div>

                                <a href="../../Assets/documents/dexter/submitted_activity/1778912304_3220_third.jpg" 
                                class="btn btn-outline-dark btn-sm rounded-pill"
                                target="_blank">
                                    View
                                </a>
                            </div>
                        </div>

                        <!-- No files example -->
                        <!--
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-folder2-open" style="font-size: 3rem;"></i>
                            <p class="mt-3 mb-0">No files submitted.</p>
                        </div>
                        -->

                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>