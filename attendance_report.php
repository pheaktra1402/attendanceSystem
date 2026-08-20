<?php
require_once __DIR__ . '/init/init.php';

// Ensure user is logged in
check_login();

$page_title = 'Attendance Reports';

// Filters
$filter_date  = sanitize($_GET['date'] ?? '');
$filter_name  = sanitize($_GET['search'] ?? '');
$filter_class = sanitize($_GET['class'] ?? '');

// Handle Inline Record Status Update
if (isset($_GET['action']) && $_GET['action'] === 'change_status' && isset($_GET['id']) && isset($_GET['new_status'])) {
    $att_id     = (int)$_GET['id'];
    $new_status = sanitize($_GET['new_status']);
    
    if (in_array($new_status, ['Present', 'Absent', 'Late'])) {
        $u_stmt = $db->prepare("UPDATE attendance SET status = ? WHERE id = ?");
        $u_stmt->bind_param("si", $new_status, $att_id);
        $u_stmt->execute();
        $u_stmt->close();
        
        set_flash_message('success', "Attendance record updated to $new_status.");
        // Maintain search parameters on redirect
        redirect("attendance_report.php?date=$filter_date&search=$filter_name&class=$filter_class");
    }
}

// Handle Inline Record Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $att_id = (int)$_GET['id'];
    
    $d_stmt = $db->prepare("DELETE FROM attendance WHERE id = ?");
    $d_stmt->bind_param("i", $att_id);
    $d_stmt->execute();
    $d_stmt->close();
    
    set_flash_message('success', "Attendance record deleted successfully.");
    redirect("attendance_report.php?date=$filter_date&search=$filter_name&class=$filter_class");
}

// Fetch class list for dropdown
$classes_result = $db->query("SELECT DISTINCT class_name FROM students ORDER BY class_name ASC");

// Build Query with JOINs
$where_clauses = [];

if (!empty($filter_date)) {
    $where_clauses[] = "a.attendance_date = '$filter_date'";
}
if (!empty($filter_name)) {
    $where_clauses[] = "(s.full_name LIKE '%$filter_name%' OR s.student_code LIKE '%$filter_name%')";
}
if (!empty($filter_class)) {
    $where_clauses[] = "s.class_name = '$filter_class'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

$report_query = "SELECT a.*, s.student_code, s.full_name, s.class_name 
                FROM attendance a 
                JOIN students s ON a.student_id = s.id 
                $where_sql 
                ORDER BY a.attendance_date DESC, s.class_name ASC, s.student_code ASC";

$report_result = $db->query($report_query);

// Summary statistics for current view
$count_present = 0;
$count_absent  = 0;
$count_late    = 0;
$rows_data     = [];

if ($report_result) {
    while ($r = $report_result->fetch_assoc()) {
        $rows_data[] = $r;
        if ($r['status'] === 'Present') $count_present++;
        elseif ($r['status'] === 'Absent') $count_absent++;
        elseif ($r['status'] === 'Late') $count_late++;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header & Title -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Attendance Reports</h3>
        <p class="text-muted mb-0">View, search, and manage attendance history</p>
    </div>
    <div>
        <a href="attendance.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Take Attendance
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="attendance_report.php" class="row g-2 align-items-center">
            <!-- Search Name or Code -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" 
                           placeholder="Search student code or name..." 
                           value="<?php echo htmlspecialchars($filter_name); ?>">
                </div>
            </div>

            <!-- Date Filter -->
            <div class="col-md-3">
                <input type="date" name="date" class="form-control bg-light" 
                       value="<?php echo htmlspecialchars($filter_date); ?>">
            </div>

            <!-- Class Filter -->
            <div class="col-md-3">
                <select name="class" class="form-select bg-light">
                    <option value="">All Classes</option>
                    <?php if ($classes_result): ?>
                        <?php while ($c = $classes_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($c['class_name']); ?>" <?php echo ($filter_class === $c['class_name']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['class_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">Filter</button>
                <?php if (!empty($filter_date) || !empty($filter_name) || !empty($filter_class)): ?>
                    <a href="attendance_report.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Summary Pills -->
<div class="d-flex gap-3 mb-3">
    <div class="badge badge-present p-2 px-3 fs-6 rounded-pill">
        <i class="fa-solid fa-circle-check me-1"></i> Present: <?php echo $count_present; ?>
    </div>
    <div class="badge badge-absent p-2 px-3 fs-6 rounded-pill">
        <i class="fa-solid fa-circle-xmark me-1"></i> Absent: <?php echo $count_absent; ?>
    </div>
    <div class="badge badge-late p-2 px-3 fs-6 rounded-pill">
        <i class="fa-solid fa-clock me-1"></i> Late: <?php echo $count_late; ?>
    </div>
</div>

<!-- Report Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Date</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th class="text-end">Change / Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rows_data) > 0): ?>
                    <?php $idx = 1; ?>
                    <?php foreach ($rows_data as $row): ?>
                        <tr>
                            <td class="fw-bold text-muted"><?php echo $idx++; ?></td>
                            <td class="small fw-semibold"><?php echo date('M d, Y', strtotime($row['attendance_date'])); ?></td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                <span class="text-muted small font-monospace"><?php echo htmlspecialchars($row['student_code']); ?></span>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['class_name']); ?></span></td>
                            <td>
                                <?php if ($row['status'] === 'Present'): ?>
                                    <span class="badge badge-present px-3 py-2 rounded-pill"><i class="fa-solid fa-check me-1"></i>Present</span>
                                <?php elseif ($row['status'] === 'Absent'): ?>
                                    <span class="badge badge-absent px-3 py-2 rounded-pill"><i class="fa-solid fa-xmark me-1"></i>Absent</span>
                                <?php else: ?>
                                    <span class="badge badge-late px-3 py-2 rounded-pill"><i class="fa-solid fa-clock me-1"></i>Late</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?php echo htmlspecialchars($row['remarks'] ?: '-'); ?></td>
                            <td class="text-end">
                                <!-- Quick Status Change Dropdown -->
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item text-success" href="attendance_report.php?action=change_status&id=<?php echo $row['id']; ?>&new_status=Present&date=<?php echo $filter_date; ?>&search=<?php echo $filter_name; ?>&class=<?php echo $filter_class; ?>"><i class="fa-solid fa-check me-2"></i>Set Present</a></li>
                                        <li><a class="dropdown-item text-warning" href="attendance_report.php?action=change_status&id=<?php echo $row['id']; ?>&new_status=Late&date=<?php echo $filter_date; ?>&search=<?php echo $filter_name; ?>&class=<?php echo $filter_class; ?>"><i class="fa-solid fa-clock me-2"></i>Set Late</a></li>
                                        <li><a class="dropdown-item text-danger" href="attendance_report.php?action=change_status&id=<?php echo $row['id']; ?>&new_status=Absent&date=<?php echo $filter_date; ?>&search=<?php echo $filter_name; ?>&class=<?php echo $filter_class; ?>"><i class="fa-solid fa-xmark me-2"></i>Set Absent</a></li>
                                    </ul>

                                    <a href="attendance_report.php?action=delete&id=<?php echo $row['id']; ?>&date=<?php echo $filter_date; ?>&search=<?php echo $filter_name; ?>&class=<?php echo $filter_class; ?>" 
                                       class="btn btn-outline-danger" 
                                       title="Delete Record" 
                                       onclick="return confirm('Delete this attendance record?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open display-5 mb-2 text-secondary d-block"></i>
                            No attendance history found matching your filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
