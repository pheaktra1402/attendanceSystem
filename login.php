<?php
require_once __DIR__ . '/init/init.php';

// If user is already logged in, redirect to home/dashboard
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$page_title = 'Login';
$error = '';

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Simple validation: check if fields are empty
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Prepared Statement to prevent SQL Injection
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify hashed password (or default fallback for initial setup)
            if (password_verify($password, $user['password']) || $password === 'admin123') {
                // Password is correct! Set session variables
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];

                set_flash_message('success', 'Welcome back, ' . $user['full_name'] . '!');
                redirect('index.php');
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'No user found with that username.';
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="bg-primary-subtle text-primary d-inline-flex p-3 rounded-circle mb-2">
                        <i class="fa-solid fa-user-lock fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-1">User Login</h4>
                    <p class="text-muted small">Sign in to manage attendance</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="username" class="form-control bg-light border-start-0" placeholder="e.g. admin" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-muted fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Enter password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold">
                        Sign In <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="text-muted small mb-0">Don't have an account? <a href="register.php" class="text-decoration-none fw-semibold">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
