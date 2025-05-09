<?php
session_start(); // بدء الجلسة
require_once __DIR__ . '/includes/db_connect.php'; // يوفر المتغير $pdo

// التحقق مما إذا كان المستخدم مسجل الدخول بالفعل
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); // إعادة التوجيه إلى لوحة القيادة إذا كان مسجل الدخول
    exit();
}

$error = '';

// معالجة نموذج تسجيل الدخول عند الإرسال
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // لا تستخدم htmlspecialchars هنا، خاصة لكلمة المرور.
    // htmlspecialchars للإيميل يمكن أن يكون مقبولاً إذا كنت ستعرضه مرة أخرى في النموذج عند الخطأ
    // ولكن لا تحتاجه للمقارنة مع قاعدة البيانات.
    $email = trim($_POST['email']);
    $password = $_POST['password']; // لا تستخدم htmlspecialchars لكلمة المرور

    // التحقق من المدخلات
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            // البحث عن المستخدم في قاعدة البيانات باستخدام PDO
            $stmt = $pdo->prepare("SELECT id, name, password FROM users WHERE email = :email");
            // يمكنك استخدام bindParam أو تمرير مصفوفة إلى execute
            // $stmt->bindParam(':email', $email);
            // $stmt->execute();
            // أو الطريقة المختصرة:
            $stmt->execute(['email' => $email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC); // جلب المستخدم كـ مصفوفة ترابطية

            if ($user) {
                // التحقق من كلمة المرور
                // $password هي كلمة المرور التي أدخلها المستخدم
                // $user['password'] هي كلمة المرور المجزأة من قاعدة البيانات
                if (password_verify($password, $user['password'])) {
                    // تسجيل الدخول بنجاح
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    // يمكنك إضافة المزيد من معلومات المستخدم إلى الجلسة إذا لزم الأمر

                    // إعادة التوجيه إلى صفحة لوحة القيادة أو أي صفحة بعد تسجيل الدخول
                    header("Location: dashboard.php");
                    exit();
                } else {
                    // كلمة المرور غير صحيحة
                    $error = "Invalid email or password."; // رسالة عامة لأمان أفضل
                }
            } else {
                // المستخدم غير موجود
                $error = "Invalid email or password."; // رسالة عامة لأمان أفضل
            }
        } catch (PDOException $e) {
            error_log("Login PDOException: " . $e->getMessage()); // سجل الخطأ الفعلي للمطور
            $error = "An error occurred during login. Please try again later.";
            // die("Database query failed: " . $e->getMessage()); // للتصحيح فقط، لا تستخدم die في الإنتاج
        }
    }
  
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>

    <link rel="stylesheet" href="assets/css/styles.css">
     <style>
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <img src="assets/images/logo.png" alt="Your Logo">
            </div>
            <ul>
                <li><a href="#">Cars</a></li>
                <li><a href="#">Discover</a></li>
                <li><a href="#">Gallery</a></li>
                <li><a href="#">Templates</a></li>
                <li><a href="#">Updates</a></li>
                <li><a href="register.php">Register</a></li> 
            </ul>
        </nav>

        <div class="login-container">
            <h1>Sign in</h1>
            <p class="register-text">Don't have an account yet? <a href="register.php">Register here</a></p>

            <?php
            if ($error) {
                echo '<p class="error-message">' . $error . '</p>';
            }
            ?>

            <button class="google-btn">
                Continue with Google
            </button>

            <div class="or-divider">
                <span>or</span>
            </div>

            <form action="login.php" method="POST" class="login-form">
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
                    <label for="terms">Remember Me (Optional, requires more logic)</label>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <p class="forgot-password">Forgot password? <a href="forgot_password.php" class="reset-link">Reset</a></p>
          
        </div>
    </div>
</body>
</html>