<?php
session_start(); // بدء الجلسة

// تضمين ملف الاتصال بقاعدة البيانات
// المسار '../includes/db_connect.php' يعني أن هذا الملف موجود داخل مجلد (مثل admin)
// وأن مجلد includes موجود في المستوى الأعلى (بجوار مجلد admin)
require_once __DIR__ . '/../includes/db_connect.php'; // استخدام __DIR__ أكثر موثوقية للمسارات النسبية

// التحقق مما إذا كان المسؤول مسجل الدخول بالفعل
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

// معالجة نموذج تسجيل الدخول للمسؤول عند الإرسال
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // لا تحتاج لـ htmlspecialchars هنا، خاصة لكلمة المرور.
    $username = trim($_POST['username']); // trim جيد
    $password = $_POST['password']; // لا تستخدم htmlspecialchars لكلمة المرور

    // التحقق من المدخلات
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            // البحث عن المسؤول في قاعدة البيانات باستخدام اسم المستخدم (PDO)
            $stmt = $pdo->prepare("SELECT id, password, role FROM admins WHERE username = :username");
            // $stmt->bindParam(':username', $username);
            // $stmt->execute();
            // أو الطريقة المختصرة:
            $stmt->execute(['username' => $username]);

            $admin = $stmt->fetch(PDO::FETCH_ASSOC); // جلب المسؤول كـ مصفوفة ترابطية

            if ($admin) {
                // التحقق من كلمة المرور المُجزأة
                // $password هي المدخلة من المسؤول
                // $admin['password'] هي المجزأة من قاعدة البيانات
                if (password_verify($password, $admin['password'])) {
                    // تسجيل الدخول بنجاح كمسؤول
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $username; // أو $admin['username']
                    $_SESSION['admin_role'] = $admin['role'];
                    // يمكنك إضافة المزيد من معلومات المسؤول إلى الجلسة إذا لزم الأمر

                    // إعادة التوجيه إلى لوحة تحكم المسؤول
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    // كلمة المرور غير صحيحة
                    $error = "Invalid username or password.";
                }
            } else {
                // المسؤول غير موجود
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            error_log("Admin Login PDOException: " . $e->getMessage());
            $error = "An error occurred during login. Please try again later.";
            // die("Database query failed: " . $e->getMessage()); // للتصحيح فقط
        }
    }
  
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>

    <link rel="stylesheet" href="styles.css">
    <!-- يمكن إضافة ستايلات إضافية خاصة بالمسؤول هنا -->
     <style>
        /* Reuse and adapt login styles */
        .login-container {
            max-width: 400px;
            margin: 50px auto; /* Add some margin top/bottom */
            padding: 40px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
         .login-container h1 {
             text-align: center;
             margin-bottom: 30px;
             color: #333;
         }
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .login-btn { /* Reuse login-btn style */
            width: 100%;
            padding: 12px;
            background: #1B2559; /* Use a distinct color for admin button */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 10px;
            margin-bottom: 0; /* No margin bottom needed usually */
             transition: background 0.3s ease;
        }
        .login-btn:hover {
             background: #0A122B;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
         /* Remove elements not needed for admin login */
        .navbar, .register-text, .google-btn, .or-divider, .checkbox-group, .forgot-password, .sign-in-text {
            display: none;
        }
     </style>
</head>
<body>
    <div class="container">
        <!-- No main user navbar for admin login -->

        <div class="login-container">
            <h1>Admin Login</h1>

            <?php
            if ($error) {
                echo '<p class="error-message">' . $error . '</p>';
            }
            ?>

            <form action="admin_login.php" method="POST" class="admin-login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <!-- Preserve username value if submission fails -->
                    <input type="text" id="username" name="username" placeholder="Enter username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>
             <p style="text-align: center; margin-top: 20px; font-size: 14px;"><a href="login.php">Back to User Login</a></p>
        </div>
    </div>
</body>
</html>