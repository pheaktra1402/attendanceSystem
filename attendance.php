<?php
require_once __DIR__ . '/init/init.php';

// Ensure user is logged in
check_login();

$page_title = 'Take Attendance';

// Selected Attendance Date (default to today)
$attendance_date = sanitize($_GET['date'] ?? date('Y-m-d'));
$class_filter    = sanitize($_GET['class'] ?? '');

// Fetch distinct class list for filtering dropdown
$classes_result = $db->query("SELECT DISTINCT class_name FROM students ORDER BY class_name ASC");

// Build SQL query to fetch students & existing attendance for selected date
$query = "SELECT s.id as student_id, s.student_code, s.full_name, s.class_name, s.gender,
                 a.id as attendance_id, a.status as existing_status, a.remarks as existing_remarks
          FROM students s
          LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date = '$attendance_date'";

if (!empty($class_filter)) {
    $query .= " WHERE s.class_name = '$class_filter'";
}

$query .= " ORDER BY s.class_name ASC, s.student_code ASC";
$students_result = $db->query($query);

// Handle Attendance Form Submission (Save / Batch Insert & Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $sub_date    = sanitize($_POST['attendance_date'] ?? date('Y-m-d'));
    $attendance  = $_POST['attendance'] ?? []; // Array: student_id => status ('Present'|'Absent'|'Late')
    $remarks_arr = $_POST['remarks'] ?? [];    // Array: student_id => remarks string

    if (!empty($attendance)) {
        // Prepared Statement with ON DUPLICATE KEY UPDATE to handle both create & update seamlessly
        $stmt = $db->prepare("INSERT INTO attendance (student_id, attendance_date, status, remarks) 
                              VALUES (?, ?, ?, ?) 
                              ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks)");

        foreach ($attendance as $student_id => $status) {
            $s_id    = (int)$student_id;
            $s_status = sanitize($status);
            $s_remark = sanitize($remarks_arr[$s_id] ?? '');

            $stmt->bind_param("isss", $s_id, $sub_date, $s_status, $s_remark);
            $stmt->execute();
        }
        $stmt->close();

        set_flash_message('success', 'Attendance for ' . date('M d, Y', strtotime($sub_date)) . ' saved successfully!');
        redirect('attendance_report.php?date=' . $sub_date);
    } else {
        set_flash_message('warning', 'No attendance status selected.');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header & Date Selector -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Take Attendance</h3>
        <p class="text-muted mb-0">Record or update daily attendance for students</p>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="attendance.php" class="d-flex gap-2">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-calendar text-muted"></i></span>
            <input type="date" name="date" class="form-control bg-white border-start-0" 
                   value="<?php echo htmlspecialchars($attendance_date); ?>" 
                   onchange="this.form.submit()">
        </div>

        <select name="class" class="form-select bg-white" onchange="this.form.submit()">
            <option value="">All Classes</option>
            <?php if ($classes_result): ?>
                <?php while ($c = $classes_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($c['class_name']); ?>" <?php echo ($class_filter === $c['class_name']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['class_name']); ?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
    </form>
</div>

<form method="POST" action="attendance.php">
    <input type="hidden" name="attendance_date" value="<?php echo htmlspecialchars($attendance_date); ?>">

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-secondary">
                    <i class="fa-solid fa-user-check me-2 text-success"></i>Mark Attendance for 
                    <span class="text-primary"><?php echo date('l, F j, Y', strtotime($attendance_date)); ?></span>
                </h6>
            </div>
            <div>
                <!-- Batch Select All Radio Buttons Helper JS -->
                <button type="button" class="btn btn-sm btn-outline-success me-1" onclick="markAll('Present')">
                    <i class="fa-solid fa-check-double me-1"></i>Mark All Present
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('Absent')">
                    <i class="fa-solid fa-xmark me-1"></i>Mark All Absent
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th class="text-center">Status</th>
                        <th>Remarks (Optional)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students_result && $students_result->num_rows > 0): ?>
                        <?php $num = 1; ?>
                        <?php while ($row = $students_result->fetch_assoc()): ?>
                            <?php 
                                // Pre-select status if exists, default to 'Present'
                                $current_status = $row['existing_status'] ?? 'Present'; 
                            ?>
                            <tr>
                                <td class="fw-bold text-muted"><?php echo $num++; ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                    <span class="text-muted small font-monospace"><?php echo htmlspecialchars($row['student_code']); ?></span>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['class_name']); ?></span></td>
                                <td>
                                    <!-- Radio Status Group -->
                                    <div class="btn-group w-100" role="group" aria-label="Status">
                                        <!-- Present -->
                                        <input type="radio" class="btn-check status-present" 
                                               name="attendance[<?php echo $row['student_id']; ?>]" 
                                               id="p_<?php echo $row['student_id']; ?>" 
                                               value="Present" <?php echo ($current_status === 'Present') ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-success btn-sm" for="p_<?php echo $row['student_id']; ?>">
                                            <i class="fa-solid fa-check me-1"></i>Present
                                        </label>

                                        <!-- Late -->
                                        <input type="radio" class="btn-check status-late" 
                                               name="attendance[<?php echo $row['student_id']; ?>]" 
                                               id="l_<?php echo $row['student_id']; ?>" 
                                               value="Late" <?php echo ($current_status === 'Late') ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-warning btn-sm" for="l_<?php echo $row['student_id']; ?>">
                                            <i class="fa-solid fa-clock me-1"></i>Late
                                        </label>

                                        <!-- Absent -->
                                        <input type="radio" class="btn-check status-absent" 
                                               name="attendance[<?php echo $row['student_id']; ?>]" 
                                               id="a_<?php echo $row['student_id']; ?>" 
                                               value="Absent" <?php echo ($current_status === 'Absent') ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-danger btn-sm" for="a_<?php echo $row['student_id']; ?>">
                                            <i class="fa-solid fa-xmark me-1"></i>Absent
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="remarks[<?php echo $row['student_id']; ?>]" 
                                           class="form-control form-control-sm bg-light" 
                                           placeholder="Notes (e.g. Sick, Medical leave)" 
                                           value="<?php echo htmlspecialchars($row['existing_remarks'] ?? ''); ?>">
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-slash display-5 mb-2 text-secondary d-block"></i>
                                No active students found to take attendance. <a href="add_student.php">Add students first</a>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($students_result && $students_result->num_rows > 0): ?>
            <div class="card-footer bg-white p-3 border-top d-flex justify-content-end">
                <button type="submit" name="save_attendance" class="btn btn-primary px-5 rounded-3 fw-semibold">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Save Attendance
                </button>
            </div>
        <?php endif; ?>
    </div>
</form>

<!-- JS Script for Quick Mark Buttons -->
<script>
function markAll(status) {
    if (status === 'Present') {
        document.querySelectorAll('.status-present').forEach(el => el.checked = true);
    } else if (status === 'Absent') {
        document.querySelectorAll('.status-absent').forEach(el => el.checked = true);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
