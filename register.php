<?php
// File: C:\Users\Zainon\Herd\car_rental\register.php

// 1. إعدادات الصفحة والمتطلبات الأساسية
$page_title = "Register New Account";

// config.php (يبدأ الجلسة ويحدد الثوابت) يتم تضمينه عبر header.php
// لكننا نحتاج إلى db_connect.php هنا لمنطق التسجيل
require_once __DIR__ . '/includes/config.php'; // لضمان APP_URL والجلسة
require_once __DIR__ . '/includes/db_connect.php'; // يوفر المتغير $pdo

// إذا كان المستخدم مسجلاً دخوله بالفعل، وجهه إلى لوحة التحكم
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// متغيرات للاحتفاظ بقيم النموذج في حالة الخطأ (باستثناء كلمات المرور)
$form_name = '';
$form_email = '';
$form_phone = ''; // حقل الهاتف أضفته كاختياري في النموذج
$form_security_question = '';
$form_security_answer = '';
$form_terms_checked = false;


// 2. معالجة نموذج التسجيل عند الإرسال
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استرجاع القيم مع الاحتفاظ بها لإعادة ملء النموذج عند الخطأ
    $form_name = trim($_POST['name']);
    $form_email = trim($_POST['email']);
    $password = $_POST['password']; // لا تقم بـ htmlspecialchars لكلمة المرور هنا
    $confirm_password = $_POST['confirm_password'];
    $form_phone = isset($_POST['phone']) ? trim($_POST['phone']) : ''; // حقل الهاتف
    $form_security_question = trim($_POST['security_question']);
    $form_security_answer = trim($_POST['security_answer']); // سيتم تجزئة هذا لاحقًا
    $form_terms_checked = isset($_POST['terms']);

    // التحقق من المدخلات
    if (empty($form_name) || empty($form_email) || empty($password) || empty($confirm_password) || empty($form_security_question) || empty($form_security_answer)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($form_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) { // زيادة طول كلمة المرور الموصى به
        $error = "Password must be at least 8 characters long.";
    } elseif (!$form_terms_checked) {
         $error = "You must agree to the Terms and Conditions.";
    } else {
        try {
            // التحقق مما إذا كان البريد الإلكتروني موجودًا بالفعل
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $form_email);
            $stmt->execute();

            if ($stmt->fetchColumn()) {
                $error = "Email address is already registered.";
            } else {
                // تجزئة كلمة المرور
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // *** هام: تجزئة إجابة الأمان ***
                $hashed_security_answer = password_hash(strtolower(trim($form_security_answer)), PASSWORD_DEFAULT); // تحويل إلى أحرف صغيرة وإزالة المسافات قبل التجزئة للمقارنة

                // إدراج المستخدم الجديد
                // تم إضافة حقل الهاتف `phone` (اختياري، لذا يمكن أن يكون NULL)
                $insert_stmt = $pdo->prepare(
                    "INSERT INTO users (name, email, password, phone, security_question, security_answer, registration_ip, user_agent) 
                     VALUES (:name, :email, :password, :phone, :security_question, :security_answer, :registration_ip, :user_agent)"
                );
                
                $registration_ip = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];

                $insert_stmt->bindParam(':name', $form_name);
                $insert_stmt->bindParam(':email', $form_email);
                $insert_stmt->bindParam(':password', $hashed_password);
                $insert_stmt->bindParam(':phone', $form_phone, !empty($form_phone) ? PDO::PARAM_STR : PDO::PARAM_NULL); // التعامل مع قيمة الهاتف الاختيارية
                $insert_stmt->bindParam(':security_question', $form_security_question);
                $insert_stmt->bindParam(':security_answer', $hashed_security_answer); // استخدام الإجابة المجزأة
                $insert_stmt->bindParam(':registration_ip', $registration_ip);
                $insert_stmt->bindParam(':user_agent', $user_agent);


                if ($insert_stmt->execute()) {
                    $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                    // مسح قيم النموذج بعد النجاح
                    $form_name = $form_email = $form_phone = $form_security_question = $form_security_answer = '';
                    $form_terms_checked = false;
                    // لا تمسح $_POST بالكامل إذا كنت تريد عرض رسالة النجاح مع بقاء الصفحة كما هي
                    // $_POST = array(); // يمكن استخدامها إذا كنت ستعيد التوجيه فورًا
                } else {
                    $errorInfo = $insert_stmt->errorInfo();
                    error_log("Registration Error: " . $errorInfo[2]); // سجل الخطأ الكامل
                    $error = "Error registering user. Please try again. If the problem persists, contact support.";
                }
            }
        } catch (PDOException $e) {
            error_log("Registration PDOException: " . $e->getMessage());
            $error = "An error occurred during registration. Please contact support. (Code: DB_REG_FAIL)";
        }
    }
}

// 3. تضمين الهيدر
require_once __DIR__ . '/includes/header.php';
?>

        <?php // الهيدر والـ Navbar موجودان الآن في header.php ?>
        <div class="register-container"> <?php // استخدام اسم الكلاس الجديد أو .login-container ?>
            <h1>Create Account</h1> <?php // تغيير العنوان ليعكس التسجيل ?>
            <p class="register-text">Already have an account? <a href="<?php echo APP_URL; ?>login.php">Sign in here</a></p>

            <?php /*
            // يمكنك إزالة زر جوجل من صفحة التسجيل إذا لم يكن لديك تكامل فعلي معه
            // أو تركه إذا كنت تخطط لإضافته لاحقًا.
            <button class="google-btn">
                Continue with Google
            </button>

            <div class="or-divider">
                <span>or</span>
            </div>
            */ ?>

            <?php
            if (!empty($error)) { // تحقق من أن الخطأ ليس فارغًا قبل عرضه
                echo '<p class="error-message">' . htmlspecialchars($error) . '</p>';
            }
            if (!empty($success)) { // تحقق من أن النجاح ليس فارغًا قبل عرضه
                echo '<p class="success-message">' . $success . '</p>'; // success message already contains HTML link
            }
            ?>

            <?php if (empty($success)): // لا تعرض النموذج إذا كان التسجيل ناجحًا ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="register-form">
                 <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($form_name); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($form_email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone (Optional)</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" value="<?php echo htmlspecialchars($form_phone); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password (min 8 chars)" required>
                </div>

                 <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                </div>

                 <div class="security-group">
                    <label for="security_question">Security Question</label>
                    <select id="security_question" name="security_question" required>
                        <option value="">-- Select a question --</option>
                        <option value="What is your mother's maiden name?" <?php echo ($form_security_question == "What is your mother's maiden name?") ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                        <option value="What is the name of your first pet?" <?php echo ($form_security_question == "What is the name of your first pet?") ? 'selected' : ''; ?>>What is the name of your first pet?</option>
                        <option value="What city were you born in?" <?php echo ($form_security_question == "What city were you born in?") ? 'selected' : ''; ?>>What city were you born in?</option>
                        <option value="What is your favorite book?" <?php echo ($form_security_question == "What is your favorite book?") ? 'selected' : ''; ?>>What is your favorite book?</option>
                    </select>
                </div>

                 <div class="security-group">
                    <label for="security_answer">Security Answer</label>
                    <input type="text" id="security_answer" name="security_answer" placeholder="Your answer (case-insensitive)" value="<?php echo htmlspecialchars($form_security_answer); ?>" required>
                    <small>This helps recover your account. Answer will be stored securely.</small>
                </div>


                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" <?php echo $form_terms_checked ? 'checked' : ''; ?> required>
                    <label for="terms">I agree to the <a href="<?php echo APP_URL; ?>terms.php" target="_blank">Terms and Conditions</a></label>
                </div>

                <button type="submit" class="register-btn">Create Account</button>
            </form>
            <?php endif; // نهاية التحقق من $success لعرض النموذج ?>

        </div>

<?php
// 4. تضمين الفوتر
require_once __DIR__ . '/includes/footer.php';
?>