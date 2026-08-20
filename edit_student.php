<?php
require_once __DIR__ . '/init/init.php';

// Ensure user is logged in
check_login();

$page_title = 'Edit Student';
$error = '';
$student_id = (int)($_GET['id'] ?? 0);

// Fetch existing student record
$stmt = $db->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    set_flash_message('danger', 'Student record not found.');
    redirect('students.php');
}

$student = $result->fetch_assoc();
$stmt->close();

// Handle Form Submission (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_code = sanitize($_POST['student_code'] ?? '');
    $full_name    = sanitize($_POST['full_name'] ?? '');
    $email        = sanitize($_POST['email'] ?? '');
    $gender       = sanitize($_POST['gender'] ?? 'Male');
    $class_name   = sanitize($_POST['class_name'] ?? '');

    // Validation
    if (empty($student_code) || empty($full_name) || empty($class_name)) {
        $error = 'Student Code, Full Name, and Class Name are required.';
    } else {
        // Check if new code conflicts with another existing student ID
        $check_stmt = $db->prepare("SELECT id FROM students WHERE student_code = ? AND id != ? LIMIT 1");
        $check_stmt->bind_param("si", $student_code, $student_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "Student Code '$student_code' is already taken by another student.";
        } else {
            // Update student in Database
            $update_stmt = $db->prepare("UPDATE students SET student_code = ?, full_name = ?, email = ?, gender = ?, class_name = ? WHERE id = ?");
            $update_stmt->bind_param("sssssi", $student_code, $full_name, $email, $gender, $class_name, $student_id);

            if ($update_stmt->execute()) {
                set_flash_message('success', "Student record for '$full_name' updated successfully!");
                redirect('students.php');
            } else {
                $error = 'Error updating student record.';
            }
            $update_stmt->close();
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
                <h3 class="fw-bold mb-1">Edit Student Information</h3>
                <p class="text-muted mb-0">Update information for ID: <?php echo htmlspecialchars($student['student_code']); ?></p>
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
                <form method="POST" action="edit_student.php?id=<?php echo $student_id; ?>">
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Student Code / ID <span class="text-danger">*</span></label>
                        <input type="text" name="student_code" class="form-control bg-light" required value="<?php echo htmlspecialchars($_POST['student_code'] ?? $student['student_code']); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control bg-light" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? $student['full_name']); ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">Class Name / Grade <span class="text-danger">*</span></label>
                            <input type="text" name="class_name" class="form-control bg-light" required value="<?php echo htmlspecialchars($_POST['class_name'] ?? $student['class_name']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">Gender</label>
                            <select name="gender" class="form-select bg-light">
                                <?php $g = $_POST['gender'] ?? $student['gender']; ?>
                                <option value="Male" <?php echo ($g === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($g === 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($g === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-muted fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control bg-light" value="<?php echo htmlspecialchars($_POST['email'] ?? $student['email']); ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 rounded-3 fw-semibold">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Update Student
                        </button>
                        <a href="students.php" class="btn btn-light border px-4 rounded-3 text-muted">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
