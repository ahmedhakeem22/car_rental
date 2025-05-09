<?php
session_start(); // بدء الجلسة
require_once __DIR__ . '/includes/db_connect.php'; // يوفر المتغير $pdo

$message = '';
$error = '';

// معالجة نموذج طلب إعادة تعيين كلمة المرور
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // لا تحتاج إلى htmlspecialchars هنا، ولكن trim جيد
    $email = trim($_POST['email']);

    // التحقق من المدخلات
    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            // البحث عن المستخدم في قاعدة البيانات باستخدام PDO
            $stmt = $pdo->prepare("SELECT id, security_question FROM users WHERE email = :email");
            // $stmt->bindParam(':email', $email);
            // $stmt->execute();
            // أو الطريقة المختصرة:
            $stmt->execute(['email' => $email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC); // جلب المستخدم كـ مصفوفة ترابطية

            if ($user) {
                // المستخدم موجود، تخزين معلومات المستخدم وسؤال الأمان في الجلسة
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_user_email'] = $email; // يمكنك أيضًا استخدام $user['email'] إذا كان اسم العمود مختلفًا
                $_SESSION['reset_security_question'] = $user['security_question'];

                // إعادة التوجيه إلى صفحة إعادة تعيين كلمة المرور
                header("Location: reset_password.php");
                exit();
            } else {
                // المستخدم غير موجود - رسالة عامة لأمان أفضل
                // يمكنك اختيار عرض رسالة خطأ مباشرة أو رسالة عامة
                // $error = "No account found with that email address.";
                $message = "If an account with that email exists, instructions to reset your password have been sent (or you will be prompted for your security question on the next page).";
                // عمليًا، إذا كان المستخدم غير موجود، لا يجب أن تنتقل إلى الخطوة التالية.
                // الرسالة أعلاه هي أكثر غموضًا لأسباب أمنية، لكن في نظام سؤال الأمان، الخطأ المباشر قد يكون مقبولاً.
                // إذا كنت تريد أن تكون الرسالة دائمًا "If an account...", فضعها خارج الشرط.
                // في هذا السيناريو، إذا لم يتم العثور على المستخدم، يجب أن تظهر رسالة خطأ بدلًا من الرسالة العامة ثم لا شيء.
                $error = "No account found with that email address. Please check your input.";
            }
        } catch (PDOException $e) {
            error_log("Forgot Password PDOException: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
            // die("Database query failed: " . $e->getMessage()); // للتصحيح فقط
        }
    }
  
}
// If GET request or POST failed, the HTML form will be displayed below.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Reuse some login styles */
        .login-container {
            max-width: 400px;
            margin: 50px auto; /* Add some margin top/bottom */
        }
        .forgot-form input[type="email"] {
             width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 20px;
        }
         .submit-btn { /* Same as login-btn */
            width: 100%;
            padding: 12px;
            background: #333;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 20px;
        }
         .submit-btn:hover {
             background: #555;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
             color: green;
        }
         .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
         h1 {
             text-align: center;
             margin-bottom: 20px;
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
                 <li><a href="login.php">Login</a></li>
                 <li><a href="register.php">Register</a></li>
            </ul>
        </nav>

        <div class="login-container"> <!-- Reusing login-container class -->
            <h1>Forgot Password</h1>
            <p style="text-align: center; margin-bottom: 20px; color: #666;">Enter your email address to reset your password.</p>

            <?php
            if ($error) {
                echo '<p class="error-message">' . $error . '</p>';
            }
            if ($message) {
                 echo '<p class="message">' . $message . '</p>';
            }
            ?>

            <form action="forgot_password.php" method="POST" class="forgot-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <button type="submit" class="submit-btn">Find Account</button>
            </form>
            <p style="text-align: center; margin-top: 20px;"><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</body>
</html>