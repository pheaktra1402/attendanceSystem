<?php
require_once __DIR__ . '/init/init.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$page_title = 'Register';
$error = '';

// Handle Registration Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = $_POST['full_name'] ?? '';
    $username         = $_POST['username'] ?? '';
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($full_name) || empty($username) || empty($password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Username is already taken. Please choose another.';
        } else {
            // Hash password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into database
            $insert_stmt = $db->prepare("INSERT INTO users (full_name, username, password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $full_name, $username, $hashed_password);

            if ($insert_stmt->execute()) {
                set_flash_message('success', 'Registration successful! You can now log in.');
                redirect('login.php');
            } else {
                $error = 'Database error: Could not register user.';
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="bg-success-subtle text-success d-inline-flex p-3 rounded-circle mb-2">
                        <i class="fa-solid fa-user-plus fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Create Account</h4>
                    <p class="text-muted small">Register to start managing attendance</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Full Name</label>
                        <input type="text" name="full_name" class="form-control bg-light" placeholder="e.g. Alex Johnson" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control bg-light" placeholder="e.g. alexj" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control bg-light" placeholder="At least 6 characters" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-muted fw-semibold">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control bg-light" placeholder="Repeat password" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 rounded-3 fw-semibold">
                        Register Account <i class="fa-solid fa-user-check ms-1"></i>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="text-muted small mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-semibold">Log in here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
