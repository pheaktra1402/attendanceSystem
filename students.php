<?php
require_once __DIR__ . '/init/init.php';

// Ensure user is logged in
check_login();

$page_title = 'Student Management';

// Handle Search Query
$search = sanitize($_GET['search'] ?? '');

if (!empty($search)) {
    // Search query matching code, name, or class
    $sql = "SELECT * FROM students 
            WHERE student_code LIKE '%$search%' 
               OR full_name LIKE '%$search%' 
               OR class_name LIKE '%$search%' 
            ORDER BY id DESC";
} else {
    // Default fetch all students
    $sql = "SELECT * FROM students ORDER BY id DESC";
}

$students_result = $db->query($sql);
$total_count = ($students_result) ? $students_result->num_rows : 0;

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Students List</h3>
        <p class="text-muted mb-0">Manage student records (Create, Read, Update, Delete)</p>
    </div>
    <div>
        <a href="add_student.php" class="btn btn-success rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Add New Student
        </a>
    </div>
</div>

<!-- Search Bar & Filters -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="students.php" class="row g-2 align-items-center">
            <div class="col-md-9 col-lg-10">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" 
                           placeholder="Search by student code, name, or class..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="students.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Students Data Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-secondary">
            <i class="fa-solid fa-list me-2"></i>Registered Students 
            <span class="badge bg-primary rounded-pill ms-1"><?php echo $total_count; ?></span>
        </h6>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Code</th>
                    <th>Full Name</th>
                    <th>Class</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th class="text-end" style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students_result && $students_result->num_rows > 0): ?>
                    <?php $count = 1; ?>
                    <?php while ($student = $students_result->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold text-muted"><?php echo $count++; ?></td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                    <?php echo htmlspecialchars($student['student_code']); ?>
                                </span>
                            </td>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td><span class="badge bg-info-subtle text-info-emphasis px-3 py-1 rounded-pill"><?php echo htmlspecialchars($student['class_name']); ?></span></td>
                            <td>
                                <?php if ($student['gender'] === 'Male'): ?>
                                    <span class="text-primary"><i class="fa-solid fa-mars me-1"></i>Male</span>
                                <?php elseif ($student['gender'] === 'Female'): ?>
                                    <span class="text-danger"><i class="fa-solid fa-venus me-1"></i>Female</span>
                                <?php else: ?>
                                    <span class="text-secondary"><i class="fa-solid fa-genderless me-1"></i>Other</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?php echo htmlspecialchars($student['email'] ?: 'N/A'); ?></td>
                            <td class="text-end">
                                <a href="edit_student.php?id=<?php echo $student['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary me-1" 
                                   title="Edit Student">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="delete_student.php?id=<?php echo $student['id']; ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   title="Delete Student" 
                                   onclick="return confirm('Are you sure you want to delete <?php echo addslashes(htmlspecialchars($student['full_name'])); ?>? This will also remove all their attendance history.');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-slash display-5 text-secondary mb-3 d-block"></i>
                            No student records found. <a href="add_student.php">Click here to add one</a>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
