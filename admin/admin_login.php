<?php
session_start(); // بدء الجلسة

// تضمين ملف الاتصال بقاعدة البيانات
require '../includes/db_connect.php'; // تضمين ملف الاتصال بقاعدة البيانات

// التحقق مما إذا كان المسؤول مسجل الدخول بالفعل
// سنستخدم مفتاح جلسة مختلف للمسؤولين ($_SESSION['admin_id']) لتمييزهم عن المستخدمين العاديين
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php"); // إعادة التوجيه إلى لوحة تحكم المسؤول
    exit();
}

$error = '';

// معالجة نموذج تسجيل الدخول للمسؤول عند الإرسال
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    // التحقق من المدخلات
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // البحث عن المسؤول في قاعدة البيانات باستخدام اسم المستخدم
        $stmt = $conn->prepare("SELECT id, password, role FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($admin_id, $hashed_password, $role);
            $stmt->fetch();

            // التحقق من كلمة المرور المُجزأة (hashed password)
            if (password_verify($password, $hashed_password)) {
                // تسجيل الدخول بنجاح كمسؤول
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_role'] = $role; // Store the admin's role
                // يمكنك إضافة المزيد من معلومات المسؤول إلى الجلسة إذا لزم الأمر

                // إعادة التوجيه إلى لوحة تحكم المسؤول
                header("Location: admin_dashboard.php");
                exit();
            } else {
                // كلمة المرور غير صحيحة
                $error = "Invalid username or password."; // رسالة عامة لأمان أفضل
            }
        } else {
            // المسؤول غير موجود
            $error = "Invalid username or password."; // رسالة عامة لأمان أفضل
        }
        $stmt->close();
    }
     $conn->close(); // Close connection after handling the request
}
// If GET request or POST failed, the HTML form will be displayed below.
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