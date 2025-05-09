<?php
$page_title = "Forgot Your Password";
if (isset($_SESSION['user_id'])) {
    header("Location: " . APP_URL . "dashboard.php");
    exit();
}

$message = ''; // لرسائل إعلامية عامة (ليست أخطاء)
$error = '';   // لرسائل الخطأ

// عرض رسالة الخطأ إذا تم تمريرها من صفحة reset_password.php (مثلاً، عند انتهاء الجلسة هناك)
if (isset($_SESSION['forgot_password_error'])) {
    $error = $_SESSION['forgot_password_error'];
    unset($_SESSION['forgot_password_error']); // امسحها بعد العرض
}


// 2. معالجة نموذج طلب إعادة تعيين كلمة المرور
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_input = trim($_POST['email']);

    if (empty($email_input)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            // تأكد من أن جدول users يحتوي على الأعمدة id, email, security_question
            // وأن security_question يتم ملؤه عند تسجيل المستخدم
            $stmt = $pdo->prepare("SELECT id, email, security_question FROM users WHERE email = :email");
            $stmt->execute(['email' => $email_input]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // المستخدم موجود
                if (empty($user['security_question'])) {
                    // حالة نادرة: المستخدم موجود ولكن ليس لديه سؤال أمان (خطأ في البيانات أو عملية تسجيل قديمة)
                    $error = "Password reset via security question is not available for this account. Please contact support.";
                } else {
                    // تخزين معلومات المستخدم وسؤال الأمان في الجلسة
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['reset_user_email'] = $user['email']; // استخدام البريد الإلكتروني من قاعدة البيانات لضمان الدقة
                    $_SESSION['reset_security_question'] = $user['security_question'];

                    // إعادة التوجيه إلى صفحة إعادة تعيين كلمة المرور
                    header("Location: " . APP_URL . "reset_password.php");
                    exit();
                }
            } else {
                // المستخدم غير موجود
                $error = "No account found with that email address. Please check your input or <a href='" . APP_URL . "register.php'>register</a> for a new account.";
            }
        } catch (PDOException $e) {
            error_log("Forgot Password PDOException: " . $e->getMessage());
            $error = "An error occurred while trying to find your account. Please try again later. (Code: DB_FORGOT_FAIL)";
        }
    }
}

// 3. تضمين الهيدر
require_once __DIR__ . '/includes/header.php';
?>

        <?php // الهيدر والـ Navbar موجودان الآن في header.php ?>
        <div class="login-container"> <?php // استخدام نفس الكلاس العام للحاوية ?>
            <h1>Forgot Your Password?</h1>
            <p class="form-description"> <?php // إضافة كلاس لسهولة التنسيق إذا لزم الأمر ?>
                No problem. Enter your email address below and we'll help you reset it using your security question.
            </p>

            <?php
            if (!empty($error)) {
                echo '<p class="error-message">' . $error . '</p>'; // رسالة الخطأ قد تحتوي على HTML
            }
            // $message لم يعد مستخدمًا بنفس الطريقة السابقة، لكن يمكن إبقاؤه لرسائل أخرى
            // if (!empty($message)) {
            //     echo '<p class="success-message">' . htmlspecialchars($message) . '</p>'; // إذا كانت $message نصًا عاديًا
            // }
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="forgot-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required autofocus>
                </div>

                <button type="submit" class="btn-primary submit-btn">Find Account</button> <?php // استخدام كلاس عام للزر الأساسي ?>
            </form>

            <p style="text-align: center; margin-top: 20px;">
                Remembered your password? <a href="<?php echo APP_URL; ?>login.php" class="link-secondary">Login here</a>
            </p>
            <p style="text-align: center; margin-top: 10px;">
                Don't have an account? <a href="<?php echo APP_URL; ?>register.php" class="link-secondary">Create one</a>
            </p>
        </div>

<?php
// 4. تضمين الفوتر
require_once __DIR__ . '/includes/footer.php';
?>