<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php'; // تضمين ملف الاتصال بقاعدة البيانات

$error = '';
$success = '';

// معالجة نموذج التسجيل عند الإرسال
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $confirm_password = htmlspecialchars($_POST['confirm_password']);
    $security_question = htmlspecialchars($_POST['security_question']); // assuming added to DB schema
    $security_answer = htmlspecialchars($_POST['security_answer']);   // assuming added to DB schema
    $terms = isset($_POST['terms']); // Check if checkbox is checked

    // التحقق من المدخلات
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($security_question) || empty($security_answer)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) { // مثال بسيط لطول كلمة المرور
        $error = "Password must be at least 6 characters long.";
    } elseif (!$terms) {
         $error = "You must agree to the Terms and Conditions.";
    } else {
        // التحقق مما إذا كان البريد الإلكتروني موجودًا بالفعل
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email address is already registered.";
        } else {
            // تجزئة كلمة المرور قبل الحفظ
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // ملاحظة: يفضل تجزئة إجابة الأمان أيضاً في تطبيق حقيقي
            // لكن لأغراض التوضيح هنا، سنحفظها كنص (بعد htmlspecialchars)

            // إدراج المستخدم الجديد في قاعدة البيانات
            // NOTE: The provided schema needs security_question and security_answer columns
            $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, security_question, security_answer) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $name, $email, $hashed_password, $security_question, $security_answer);

            if ($insert_stmt->execute()) {
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                 // Optional: Clear form fields on success
                 $name = $email = $password = $confirm_password = $security_question = $security_answer = ''; // Clear variables
            } else {
                $error = "Error registering user: " . $conn->error;
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
     <link rel="stylesheet" href="assets/css/styles.css">
    <!-- يمكن إضافة CSS إضافي هنا أو داخل styles.css -->
    <style>
        /* بعض الستايلات المخصصة لصفحة التسجيل */
        .login-container { /* إعادة استخدام ستايل login-container */
             max-width: 450px; /* قد تحتاج لتوسيعها قليلا لسؤال الأمان */
        }
        .security-group {
            margin-bottom: 20px;
        }
         .security-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #555;
        }
        .security-group input, .security-group select {
             width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .register-btn { /* نفس ستايل login-btn */
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
        .register-btn:hover {
             background: #555;
        }

         .google-btn { /* إبقاء ستايل زر جوجل */
            width: 100%;
            padding: 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 25px;
        }

         .or-divider { 
            position: relative;
            margin: 25px 0;
            text-align: center;
            color: #999;
        }

        .or-divider::before,
        .or-divider::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #ddd;
        }

        .or-divider::before {
            left: 0;
        }

        .or-divider::after {
            right: 0;
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
            </ul>
        </nav>

        <div class="login-container">
            <h1>Sign Up</h1>
            <p class="register-text">Already have an account? <a href="login.php">Sign in here</a></p>

            <button class="google-btn">
                Continue with Google
            </button>

            <div class="or-divider">
                <span>or</span>
            </div>

            <?php
            if ($error) {
                echo '<p class="error-message">' . $error . '</p>';
            }
             if ($success) {
                echo '<p class="success-message">' . $success . '</p>';
            }
            ?>

            <form action="register.php" method="POST" class="register-form">
                 <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" placeholder="Enter your name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                
                <div class="form-group">
                    <label>Phone (Optional)</label>
                    <input type="text" name="phone" placeholder="Enter your phone number" value="<?php // echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
               

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                 <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                </div>

                 <div class="security-group">
                    <label>Security Question</label>
                     <!-- يمكن أن تكون قائمة من أسئلة محددة أو حقل نصي -->
                    <select name="security_question" required>
                        <option value="">-- Select a question --</option>
                        <option value="What is your mother's maiden name?" <?php echo (isset($_POST['security_question']) && $_POST['security_question'] == "What is your mother's maiden name?") ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                        <option value="What is the name of your first pet?" <?php echo (isset($_POST['security_question']) && $_POST['security_question'] == "What is the name of your first pet?") ? 'selected' : ''; ?>>What is the name of your first pet?</option>
                        <option value="What is your favorite color?" <?php echo (isset($_POST['security_question']) && $_POST['security_question'] == "What is your favorite color?") ? 'selected' : ''; ?>>What is your favorite color?</option>
                         <!-- أضف المزيد من الأسئلة حسب الحاجة -->
                    </select>
                </div>

                 <div class="security-group">
                    <label>Security Answer</label>
                    <input type="text" name="security_answer" placeholder="Your answer" value="<?php echo isset($_POST['security_answer']) ? htmlspecialchars($_POST['security_answer']) : ''; ?>" required>
                </div>


                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> required>
                    <label for="terms">I agree with Term and Condition</label>
                </div>

                <button type="submit" class="register-btn">Sign Up</button>
            </form>

        </div>
    </div>
</body>
</html>