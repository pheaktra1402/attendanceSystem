<?php
// Initialize error variables to avoid "Undefined variable" notices
$username_err = "";
$password_error = "";
$username = "";

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Add your login validation logic here...
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login - Attendance System</title>
    <!-- Include Bootstrap CSS for your classes (mb-3, form-control, btn, etc.) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<style>
</style>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <!-- Loading Screen Overlay (Separate from the card) -->
    <div id="loading-screen" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; justify-content: center; align-items: center;">
        <div class="loading-box text-center">
            <h4>Loading...</h4>
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    </div>

    <!-- Login Card Container -->
    <div class="card shadow p-4 w-100" style="max-width: 400px;">
        <h5 class="mb-3 text-center">Welcome to Attendance system.</h5>

        <form action="" method="POST">
            <!-- Username Field -->
            <div class="mb-3">
                <label class="form-label">User name</label>
                <div class="input-group">
                    <input type="text" name="username"
                        class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>"
                        placeholder="Enter your username" value="<?php echo htmlspecialchars($username); ?>">
                    <span class="input-group-text">
                        <i class="fa fa-user"></i>
                    </span>
                    <div class="invalid-feedback">
                        <?php echo $username_err; ?>
                    </div>
                </div>
            </div>

            <!-- Password Field -->
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" name="password"
                    class="form-control <?php echo (!empty($password_error)) ? 'is-invalid' : ''; ?>"
                    id="exampleInputPassword1" placeholder="Enter password">
                <div class="invalid-feedback">
                    <?php echo $password_error; ?>
                </div>
            </div>
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

</body>
<script>
    document.querySelector("form").addEventListener("submit", function (e) {
        e.preventDefault();
        // Show the loading screen
        document.getElementById("loading-screen").style.display = "flex";
        
        setTimeout(() => {
            // Fixed typo from submint() to submit() and use HTMLFormElement submit
            HTMLFormElement.prototype.submit.call(this);
        }, 2000);
    });
</script>

</html>

