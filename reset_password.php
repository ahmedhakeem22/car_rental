<?php
session_start(); // بدء الجلسة
require 'includes/db_connect.php'; // تضمين ملف الاتصال بقاعدة البيانات

$error = '';
$success = '';

// التحقق مما إذا كانت معلومات إعادة التعيين موجودة في الجلسة
if (!isset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_security_question'])) {
    // إذا لم تكن المعلومات موجودة، إعادة التوجيه إلى صفحة نسيت كلمة المرور
    header("Location: forgot_password.php");
    exit();
}

$user_id = $_SESSION['reset_user_id'];
$email = $_SESSION['reset_user_email'];
$security_question = $_SESSION['reset_security_question'];


// معالجة نموذج إعادة تعيين كلمة المرور
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_answer = htmlspecialchars($_POST['security_answer']);
    $new_password = htmlspecialchars($_POST['new_password']);
    $confirm_new_password = htmlspecialchars($_POST['confirm_new_password']);

    // التحقق من المدخلات
    if (empty($submitted_answer) || empty($new_password) || empty($confirm_new_password)) {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_new_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) { // مثال بسيط لطول كلمة المرور
        $error = "New password must be at least 6 characters long.";
    } else {
         // Fetch the actual security answer from the database based on user_id
         $stmt = $conn->prepare("SELECT security_answer FROM users WHERE id = ?");
         $stmt->bind_param("i", $user_id);
         $stmt->execute();
         $stmt->bind_result($correct_answer);
         $stmt->fetch();
         $stmt->close();

        // التحقق من إجابة الأمان (غير حساسة لحالة الأحرف للحفاظ على البساطة)
        if (strtolower($submitted_answer) === strtolower($correct_answer)) {
            // تجزئة كلمة المرور الجديدة
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

            // تحديث كلمة المرور في قاعدة البيانات
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_new_password, $user_id);

            if ($update_stmt->execute()) {
                $success = "Your password has been successfully reset. You can now <a href='login.php'>login</a>.";
                // مسح معلومات إعادة التعيين من الجلسة
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_user_email']);
                unset($_SESSION['reset_security_question']);
                 // Prevent further form submissions
                 $_SESSION['reset_completed'] = true;

            } else {
                $error = "Error updating password: " . $conn->error;
            }
            $update_stmt->close();
        } else {
            $error = "Incorrect security answer.";
        }
    }
     $conn->close(); // Close connection after handling the request
}

// Check if reset was just completed successfully
if (isset($_SESSION['reset_completed'])) {
     $success = "Your password has been successfully reset. You can now <a href='login.php'>login</a>.";
     unset($_SESSION['reset_completed']); // Clear the flag
     // Clear session data as well
     unset($_SESSION['reset_user_id']);
     unset($_SESSION['reset_user_email']);
     unset($_SESSION['reset_security_question']);
}


// If reset was completed or session data is missing after POST,
// check if session data is still valid before displaying the form.
if (!isset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_security_question']) && !$success) {
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