<?php
session_start(); // بدء الجلسة
require_once __DIR__ . '/includes/db_connect.php'; // يوفر المتغير $pdo

$error = '';
$success = '';

// التحقق مما إذا كانت معلومات إعادة التعيين موجودة في الجلسة
if (!isset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_security_question'])) {
    // إذا لم تكن المعلومات موجودة، إعادة التوجيه إلى صفحة نسيت كلمة المرور
    header("Location: forgot_password.php");
    exit();
}

$user_id = $_SESSION['reset_user_id'];
$email = $_SESSION['reset_user_email']; // $email قد لا يكون ضروريًا هنا إذا كان $user_id كافيًا
$security_question = $_SESSION['reset_security_question'];


// معالجة نموذج إعادة تعيين كلمة المرور
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_answer = trim($_POST['security_answer']); // لا تستخدم htmlspecialchars للإجابة التي ستُقارن
    $new_password = $_POST['new_password']; // لا تستخدم htmlspecialchars لكلمة المرور الجديدة
    $confirm_new_password = $_POST['confirm_new_password'];

    // التحقق من المدخلات
    if (empty($submitted_answer) || empty($new_password) || empty($confirm_new_password)) {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_new_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        try {
            // 1. جلب إجابة الأمان الفعلية من قاعدة البيانات بناءً على user_id باستخدام PDO
            $stmt = $pdo->prepare("SELECT security_answer FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $db_security_info = $stmt->fetch(PDO::FETCH_ASSOC); // جلب الصف كـ مصفوفة ترابطية

            if ($db_security_info) {
                $correct_answer_from_db = $db_security_info['security_answer'];

                // 2. التحقق من إجابة الأمان
                // إذا كنت تخزن إجابة الأمان كنص عادي (غير مجزأ):
                if (strtolower($submitted_answer) === strtolower($correct_answer_from_db)) {
                // إذا كنت قد جزأت إجابة الأمان عند التسجيل (وهو الأفضل):
                // if (password_verify($submitted_answer, $correct_answer_from_db)) {

                    // 3. تجزئة كلمة المرور الجديدة
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // 4. تحديث كلمة المرور في قاعدة البيانات باستخدام PDO
                    $update_stmt = $pdo->prepare("UPDATE users SET password = :new_password WHERE id = :user_id");
                    $update_stmt->bindParam(':new_password', $hashed_new_password);
                    $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

                    if ($update_stmt->execute()) {
                        $success = "Your password has been successfully reset. You can now <a href='login.php'>login</a>.";
                        // مسح معلومات إعادة التعيين من الجلسة
                        unset($_SESSION['reset_user_id']);
                        unset($_SESSION['reset_user_email']);
                        unset($_SESSION['reset_security_question']);
                        $_SESSION['reset_completed'] = true; // لمنع إعادة الإرسال وعرض الرسالة بشكل صحيح
                        // لا حاجة لإعادة التوجيه هنا إذا كنت تريد عرض رسالة النجاح على نفس الصفحة
                    } else {
                        $errorInfo = $update_stmt->errorInfo();
                        $error = "Error updating password. Please try again. Error: " . $errorInfo[2];
                    }
                } else {
                    $error = "Incorrect security answer.";
                }
            } else {
                // هذا لا ينبغي أن يحدث إذا كانت الجلسة صحيحة
                $error = "Could not retrieve user information. Please try the forgot password process again.";
                // ربما مسح الجلسة وإعادة التوجيه
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_user_email']);
                unset($_SESSION['reset_security_question']);
                header("Location: forgot_password.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Reset Password PDOException: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
            // die("Database error: " . $e->getMessage()); // للتصحيح فقط
        }
    }
    // لا تقم بإغلاق الاتصال $pdo هنا إذا كنت ستستخدمه لاحقًا في الصفحة.
    // إذا كانت هذه آخر عملية لقاعدة البيانات في هذا الطلب، يمكنك وضع:
    // $pdo = null;
}

// تحقق مما إذا اكتملت إعادة التعيين بنجاح (تم تعيينه في كتلة POST)
if (isset($_SESSION['reset_completed']) && $_SESSION['reset_completed'] === true) {
    $success = "Your password has been successfully reset. You can now <a href='login.php'>login</a>.";
    // مسح العلامة والمتغيرات الأخرى ذات الصلة إذا لم يتم مسحها بالفعل
    unset($_SESSION['reset_completed']);
    unset($_SESSION['reset_user_id']); // تأكد من مسحها
    unset($_SESSION['reset_user_email']);
    unset($_SESSION['reset_security_question']);
} elseif (!isset($_SESSION['reset_user_id']) && $_SERVER["REQUEST_METHOD"] !== "POST" && empty($success)) {
    // إذا لم تكن هناك جلسة نشطة ولم يكن هذا طلب POST (ولم تكن هناك رسالة نجاح بالفعل)
    // فهذا يعني أن المستخدم وصل إلى الصفحة مباشرة بدون المرور بالخطوات السابقة
    header("Location: forgot_password.php");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
     <link rel="stylesheet" href="assets/css/styles.css">
    <style>
         /* Reuse some login styles */
        .login-container {
            max-width: 450px; /* Might need wider for questions/answers */
            margin: 50px auto; /* Add some margin top/bottom */
        }
        .reset-form input[type="text"],
        .reset-form input[type="password"] {
             width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
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
            margin-top: 10px; /* Add margin above button */
            margin-bottom: 20px;
        }
         .submit-btn:hover {
             background: #555;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
        }
         .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }
         h1 {
             text-align: center;
             margin-bottom: 20px;
         }
         .security-question-text {
             font-weight: bold;
             margin-bottom: 15px;
             text-align: center;
             font-size: 16px;
             color: #333;
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
            <h1>Reset Password</h1>

            <?php
            if ($error) {
                echo '<p class="error-message">' . $error . '</p>';
            }
            if ($success) {
                echo '<p class="success-message">' . $success . '</p>';
            }
            // Only show the form if reset hasn't been completed successfully
            if (!$success) {
            ?>

            <p class="security-question-text">Your security question:</p>
            <p style="text-align: center; margin-bottom: 20px; font-style: italic;"><?php echo htmlspecialchars($security_question); ?></p>

            <form action="reset_password.php" method="POST" class="reset-form">
                <div class="form-group">
                    <label for="security_answer">Your Answer</label>
                    <input type="text" id="security_answer" name="security_answer" placeholder="Enter your answer" required>
                </div>

                 <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                </div>

                 <div class="form-group">
                    <label for="confirm_new_password">Confirm New Password</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm new password" required>
                </div>

                <button type="submit" class="submit-btn">Reset Password</button>
            </form>

             <?php } // End of if(!$success) ?>

            <p style="text-align: center; margin-top: 20px;"><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</body>
</html>