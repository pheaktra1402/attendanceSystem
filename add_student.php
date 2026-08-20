<?php
require_once __DIR__ . '/init/init.php';

// Ensure user is logged in
check_login();

$page_title = 'Add Student';
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_code = sanitize($_POST['student_code'] ?? '');
    $full_name    = sanitize($_POST['full_name'] ?? '');
    $email        = sanitize($_POST['email'] ?? '');
    $gender       = sanitize($_POST['gender'] ?? 'Male');
    $class_name   = sanitize($_POST['class_name'] ?? '');

    // Validation
    if (empty($student_code) || empty($full_name) || empty($class_name)) {
        $error = 'Student Code, Full Name, and Class Name are required fields.';
    } else {
        // Check if student code is unique
        $check_stmt = $db->prepare("SELECT id FROM students WHERE student_code = ? LIMIT 1");
        $check_stmt->bind_param("s", $student_code);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "Student Code '$student_code' already exists! Please use a unique code.";
        } else {
            // Insert into Database using Prepared Statements
            $insert_stmt = $db->prepare("INSERT INTO students (student_code, full_name, email, gender, class_name) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $student_code, $full_name, $email, $gender, $class_name);

            if ($insert_stmt->execute()) {
                set_flash_message('success', "Student '$full_name' added successfully!");
                redirect('students.php');
            } else {
                $error = 'Error saving student record to database.';
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1">Add New Student</h3>
                <p class="text-muted mb-0">Fill in the details to enroll a new student</p>
            </div>
            <a href="students.php" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="add_student.php">
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Student Code / ID <span class="text-danger">*</span></label>
                        <input type="text" name="student_code" class="form-control bg-light" placeholder="e.g. STU-105" required value="<?php echo htmlspecialchars($_POST['student_code'] ?? ''); ?>">
                        <div class="form-text">Unique identification number for the student.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control bg-light" placeholder="e.g. Michael Scott" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">Class Name / Grade <span class="text-danger">*</span></label>
                            <input type="text" name="class_name" class="form-control bg-light" placeholder="e.g. Grade 10A" required value="<?php echo htmlspecialchars($_POST['class_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">Gender</label>
                            <select name="gender" class="form-select bg-light">
                                <option value="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo (($_POST['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-muted fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control bg-light" placeholder="e.g. michael@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success px-4 rounded-3 fw-semibold">
                            <i class="fa-solid fa-check me-1"></i> Save Student
                        </button>
                        <a href="students.php" class="btn btn-light border px-4 rounded-3 text-muted">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
