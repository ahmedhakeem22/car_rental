<?php
// File: C:\Users\Zainon\Herd\car_rental\reset_password.php

$page_title = "Reset Your Password";

$error = '';
$success = '';

if (!isset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_security_question'])) {
    $_SESSION['forgot_password_error'] = "Session expired or invalid. Please start the password reset process again.";
    header("Location: " . APP_URL . "forgot_password.php");
    exit();
}

$user_id = $_SESSION['reset_user_id'];
$email_from_session = $_SESSION['reset_user_email']; // قد نحتاجه للعرض أو التأكيد
$security_question_from_session = $_SESSION['reset_security_question'];


// 2. معالجة نموذج إعادة تعيين كلمة المرور
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // التأكد مرة أخرى من وجود متغيرات الجلسة قبل المعالجة (قد تكون انتهت صلاحيتها بين التحميل والإرسال)
    if (!isset($_SESSION['reset_user_id'], $_SESSION['reset_security_question'])) {
        $error = "Your session has expired. Please start the password reset process again.";
        // مسح أي بقايا محتملة
        unset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_security_question'], $_SESSION['reset_completed']);
    } else {
        $submitted_answer = trim($_POST['security_answer']);
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        if (empty($submitted_answer) || empty($new_password) || empty($confirm_new_password)) {
            $error = "Please fill in all fields.";
        } elseif ($new_password !== $confirm_new_password) {
            $error = "New passwords do not match.";
        } elseif (strlen($new_password) < 8) { // تطابق مع متطلبات التسجيل
            $error = "New password must be at least 8 characters long.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT security_answer FROM users WHERE id = :user_id");
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user_data) {
                    $hashed_security_answer_from_db = $user_data['security_answer'];

                    // *** هام: التحقق من إجابة الأمان المجزأة ***
                    // تذكر أننا قمنا بـ strtolower(trim()) قبل التجزئة في register.php
                    if (password_verify(strtolower(trim($submitted_answer)), $hashed_security_answer_from_db)) {
                        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

                        $update_stmt = $pdo->prepare("UPDATE users SET password = :new_password WHERE id = :user_id");
                        $update_stmt->bindParam(':new_password', $hashed_new_password);
                        $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

                        if ($update_stmt->execute()) {
                            $success = "Your password has been successfully reset. You can now <a href='" . APP_URL . "login.php'>login</a>.";
                            // مسح معلومات إعادة التعيين من الجلسة
                            unset($_SESSION['reset_user_id']);
                            unset($_SESSION['reset_user_email']);
                            unset($_SESSION['reset_security_question']);
                            $_SESSION['reset_completed_message'] = $success; // لتمرير الرسالة بعد إعادة التوجيه أو التحديث
                            
                            // من الأفضل إعادة التوجيه لتجنب إعادة إرسال النموذج عند تحديث الصفحة
                            // وإنشاء "صفحة نجاح" أو عرض الرسالة على صفحة تسجيل الدخول
                            // لكن لعرضها على نفس الصفحة مباشرة بعد المسح:
                            // لا يوجد header() هنا لأننا سنعرض الصفحة مع رسالة النجاح
                        } else {
                            $errorInfo = $update_stmt->errorInfo();
                            error_log("Reset Password Update Error: " . $errorInfo[2]);
                            $error = "Error updating password. Please try again.";
                        }
                    } else {
                        $error = "Incorrect security answer. Please try again.";
                    }
                } else {
                    $error = "Could not retrieve user information. Please start the forgot password process again.";
                    unset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_security_question']);
                }
            } catch (PDOException $e) {
                error_log("Reset Password PDOException: " . $e->getMessage());
                $error = "An error occurred. Please try again later. (Code: DB_RESET_FAIL)";
            }
        }
    }
}


// 3. التحقق النهائي لعرض الرسائل أو النموذج (خارج كتلة POST)
// إذا تم تمرير رسالة نجاح عبر الجلسة (بعد إعادة توجيه مثلاً، أو إذا تم ضبطها في كتلة POST ولم يتم إعادة التوجيه)
if (isset($_SESSION['reset_completed_message']) && !empty($_SESSION['reset_completed_message'])) {
    $success = $_SESSION['reset_completed_message'];
    unset($_SESSION['reset_completed_message']); // امسحها بعد العرض
    // تأكد من مسح متغيرات الجلسة الأخرى إذا لم يتم مسحها بالفعل
    unset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_security_question']);
} elseif (empty($success) && empty($error) && !isset($_SESSION['reset_user_id'])) {
    // إذا لم يكن هناك نجاح، ولا خطأ، ولا جلسة نشطة لإعادة التعيين
    // (وصل المستخدم إلى الصفحة بطريقة غير صحيحة ولم تتم معالجة أي شيء بعد)
    $_SESSION['forgot_password_error'] = "Invalid access to password reset page. Please start again.";
    header("Location: " . APP_URL . "forgot_password.php");
    exit();
}


// 4. تضمين الهيدر
require_once __DIR__ . '/includes/header.php';
?>

        <?php // الهيدر والـ Navbar موجودان الآن في header.php ?>
        <div class="login-container"> <?php // استخدام نفس الكلاس العام للحاوية ?>
            <h1>Reset Your Password</h1>

            <?php
            if (!empty($error)) {
                echo '<p class="error-message">' . htmlspecialchars($error) . '</p>';
            }
            // عرض رسالة النجاح. إذا كانت موجودة، لن يتم عرض النموذج.
            if (!empty($success)) {
                echo '<p class="success-message">' . $success . '</p>'; // رسالة النجاح تحتوي بالفعل على HTML (الرابط)
            }
            ?>

            <?php
            if (empty($success) && isset($_SESSION['reset_user_id'], $security_question_from_session)):
            ?>
                <div class="security-question-display"> <?php // كلاس جديد لتمييز عرض السؤال ?>
                    <p>Your security question for <strong><?php echo htmlspecialchars($email_from_session); ?></strong>:</p>
                    <p class="question-text"><?php echo htmlspecialchars($security_question_from_session); ?></p>
                </div>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="reset-form">
                    <div class="form-group">
                        <label for="security_answer">Your Answer</label>
                        <input type="text" id="security_answer" name="security_answer" placeholder="Enter your answer (case-insensitive)" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password (min 8 chars)" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_new_password">Confirm New Password</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm new password" required>
                    </div>

                    <button type="submit" class="btn-primary submit-btn">Reset Password</button> <?php // استخدام كلاس عام للزر الأساسي submit-btn ?>
                </form>
            <?php endif; // نهاية if لـ empty($success) و isset($_SESSION['reset_user_id']) ?>

            <p style="text-align: center; margin-top: 20px;">
                <a href="<?php echo APP_URL; ?>login.php" class="link-secondary">Back to Login</a>
            </p>
        </div>

<?php
// 5. تضمين الفوتر
require_once __DIR__ . '/includes/footer.php';
?>