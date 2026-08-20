<?php
require_once __DIR__ . '/init/init.php';

// Ensure user is logged in
check_login();

$page_title = 'Dashboard';
$today = date('Y-m-d');

// 1. Get Total Students Count
$res_students = $db->query("SELECT COUNT(*) as total FROM students");
$total_students = ($res_students) ? $res_students->fetch_assoc()['total'] : 0;

// 2. Get Today's Present Count
$res_present = $db->query("SELECT COUNT(*) as total FROM attendance WHERE attendance_date = '$today' AND status = 'Present'");
$total_present = ($res_present) ? $res_present->fetch_assoc()['total'] : 0;

// 3. Get Today's Absent Count
$res_absent = $db->query("SELECT COUNT(*) as total FROM attendance WHERE attendance_date = '$today' AND status = 'Absent'");
$total_absent = ($res_absent) ? $res_absent->fetch_assoc()['total'] : 0;

// 4. Get Today's Late Count
$res_late = $db->query("SELECT COUNT(*) as total FROM attendance WHERE attendance_date = '$today' AND status = 'Late'");
$total_late = ($res_late) ? $res_late->fetch_assoc()['total'] : 0;

// 5. Fetch Recent Attendance Records (Join students table to display student name & code)
$recent_query = "SELECT a.*, s.student_code, s.full_name, s.class_name 
                FROM attendance a 
                JOIN students s ON a.student_id = s.id 
                ORDER BY a.attendance_date DESC, a.id DESC 
                LIMIT 5";
$recent_result = $db->query($recent_query);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Dashboard</h3>
        <p class="text-muted mb-0">Overview of student attendance statistics for today (<?php echo date('F j, Y'); ?>)</p>
    </div>
    <div>
        <a href="attendance.php" class="btn btn-primary shadow-sm rounded-pill px-4">
            <i class="fa-solid fa-calendar-plus me-1"></i> Take Attendance
        </a>
    </div>
</div>

<!-- Stat Cards Row -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Students -->
    <div class="col-6 col-md-3">
        <div class="card card-stat shadow-sm p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Students</span>
                    <h2 class="fw-bold mb-0 mt-1"><?php echo $total_students; ?></h2>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Present Today -->
    <div class="col-6 col-md-3">
        <div class="card card-stat shadow-sm p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Present Today</span>
                    <h2 class="fw-bold text-success mb-0 mt-1"><?php echo $total_present; ?></h2>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Absent Today -->
    <div class="col-6 col-md-3">
        <div class="card card-stat shadow-sm p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Absent Today</span>
                    <h2 class="fw-bold text-danger mb-0 mt-1"><?php echo $total_absent; ?></h2>
                </div>
                <div class="stat-icon bg-danger-subtle text-danger">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Late Today -->
    <div class="col-6 col-md-3">
        <div class="card card-stat shadow-sm p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Late Today</span>
                    <h2 class="fw-bold text-warning mb-0 mt-1"><?php echo $total_late; ?></h2>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action & Recent Attendance Section -->
<div class="row g-4">
    <!-- Recent Attendance Activity Table -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                <h5 class="fw-bold mb-0 text-secondary">
                    <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Recent Attendance Activity
                </h5>
                <a href="attendance_report.php" class="btn btn-sm btn-link text-decoration-none fw-semibold">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_result && $recent_result->num_rows > 0): ?>
                            <?php while ($row = $recent_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                        <span class="text-muted small"><?php echo htmlspecialchars($row['student_code']); ?></span>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['class_name']); ?></span></td>
                                    <td class="small text-muted"><?php echo date('M d, Y', strtotime($row['attendance_date'])); ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'Present'): ?>
                                            <span class="badge badge-present px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Present</span>
                                        <?php elseif ($row['status'] === 'Absent'): ?>
                                            <span class="badge badge-absent px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-xmark me-1"></i>Absent</span>
                                        <?php else: ?>
                                            <span class="badge badge-late px-3 py-2 rounded-pill"><i class="fa-solid fa-clock me-1"></i>Late</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-folder-open display-6 mb-2 d-block text-secondary"></i>
                                    No attendance records submitted yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-secondary">
                    <i class="fa-solid fa-bolt me-2 text-warning"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body d-flex flex-column gap-2 pt-1">
                <a href="attendance.php" class="btn btn-outline-primary p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="bg-primary text-white p-2 rounded-3">
                        <i class="fa-solid fa-calendar-check fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Take Today's Attendance</div>
                        <div class="small text-muted">Record present/absent for students</div>
                    </div>
                </a>

                <a href="add_student.php" class="btn btn-outline-success p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="bg-success text-white p-2 rounded-3">
                        <i class="fa-solid fa-user-plus fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Add New Student</div>
                        <div class="small text-muted">Enroll a new student into the system</div>
                    </div>
                </a>

                <a href="attendance_report.php" class="btn btn-outline-info p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="bg-info text-white p-2 rounded-3">
                        <i class="fa-solid fa-file-lines fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Attendance Reports</div>
                        <div class="small text-muted">Filter logs by date or student</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>