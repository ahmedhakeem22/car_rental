<?php
$page_title = "Admin Login";
$show_navbar = false;

require_once __DIR__ . '/includes/admin_header.php';


if (isset($_SESSION['admin_id'])) {
    header("Location: " . APP_URL . "admin/admin_dashboard.php");
    exit();
}

$username = '';
$error_message = '';

if (isset($_SESSION['admin_error'])) {
    $error_message = $_SESSION['admin_error'];
    unset($_SESSION['admin_error']);
}
if (isset($_SESSION['admin_success'])) {
    $success_message = $_SESSION['admin_success'];
    unset($_SESSION['admin_success']);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM admins WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                
                header("Location: " . APP_URL . "admin/admin_dashboard.php");
                exit();
            } else {
                $error_message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error_message = "An unexpected error occurred. Please try again later.";
            error_log("Admin Login PDOException: " . $e->getMessage());
        }
    }
}
?>
<div class="login-page-wrapper">
    <div class="login-container-admin">
        <div class="text-center mb-4">
            <img src="<?php echo APP_URL; ?>assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" style="height: 60px;">
            <h2 class="mt-3"><?php echo SITE_NAME; ?> - Admin Panel</h2>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mt-2">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control form-control-lg" id="username" name="username" placeholder="Enter your username" value="<?php echo htmlspecialchars($username); ?>" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                 <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </div>
        </form>
        <div class="text-center">
            <a href="<?php echo APP_URL; ?>index.php" class="text-muted small"><i class="bi bi-arrow-left-circle me-1"></i>Back to Main Site</a>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>