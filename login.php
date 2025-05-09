<?php

// Page specific configurations
$page_title = "Login";
require_once __DIR__ . '/includes/db_connect.php'; // Adjust path if needed

// التحقق مما إذا كان المستخدم مسجل الدخول بالفعل
// This check must happen BEFORE any HTML output (i.e., before including header.php)
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); // إعادة التوجيه إلى لوحة القيادة إذا كان مسجل الدخول
    exit();
}

$error = '';

// معالجة نموذج تسجيل الدخول عند الإرسال
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, password, email FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    // Store email in session if you need it globally, e.g., for a profile page
                    // $_SESSION['user_email'] = $user['email'];

                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            error_log("Login PDOException: " . $e->getMessage());
            $error = "An error occurred during login. Please try again later.";
        }
    }
}

// Now include the header
require_once __DIR__ . '/includes/header.php';
?>

        <?php // The navbar is now in header.php. We start with the login-container. ?>
        <div class="login-container">
            <h1>Sign in</h1>
            <p class="register-text">Don't have an account yet? <a href="register.php">Register here</a></p>

            <?php
            if ($error) {
                // The .error-message class is now styled in header.php or your main styles.css
                echo '<p class="error-message">' . htmlspecialchars($error) . '</p>';
            }
            ?>

            <button class="google-btn">
                Continue with Google
            </button>

            <div class="or-divider">
                <span>or</span>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login-form">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms">
                    <label for="terms">Remember Me</label> <?php // Simplified message, actual logic for "Remember Me" is more complex ?>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <p class="forgot-password">Forgot password? <a href="forgot_password.php" class="reset-link">Reset</a></p>

        </div>

<?php
// Include the footer
require_once __DIR__ . '/includes/footer.php';
?>