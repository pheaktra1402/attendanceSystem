<?php
// Get current script name to mark active menu item
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-3">
    <div class="container">
        <!-- Brand Logo & Title -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
            <i class="fa-solid fa-clipboard-user text-info fs-4"></i>
            <span>AttendanceSystem</span>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="fa-solid fa-house me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'students.php' || $current_page == 'add_student.php' || $current_page == 'edit_student.php') ? 'active' : ''; ?>" href="students.php">
                        <i class="fa-solid fa-user-graduate me-1"></i> Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>" href="attendance.php">
                        <i class="fa-solid fa-calendar-check me-1"></i> Take Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'attendance_report.php') ? 'active' : ''; ?>" href="attendance_report.php">
                        <i class="fa-solid fa-chart-column me-1"></i> Reports
                    </a>
                </li>
            </ul>

            <!-- Right User Menu -->
            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle btn-sm px-3 rounded-pill" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user-circle me-1"></i> 
                            <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenu">
                            <li><span class="dropdown-item-text text-muted small">Logged in as <b><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></b></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm px-3">Login</a>
                    <a href="register.php" class="btn btn-primary btn-sm px-3">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
