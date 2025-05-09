<?php
session_start(); // بدء الجلسة

// التحقق مما إذا كان المستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    // إذا لم يكن مسجل الدخول، إعادة التوجيه إلى صفحة تسجيل الدخول
    header("Location: login.php");
    exit();
}

// معلومات المستخدم من الجلسة
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name']; // Assume name is stored in session during login

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
     <style>
        .content {
            text-align: center;
            margin-top: 50px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .content h1 {
             color: #333;
             margin-bottom: 20px;
        }
        .content p {
             color: #666;
             margin-bottom: 20px;
        }
         .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #f44336; /* Red color */
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
             transition: background 0.3s ease;
        }
        .logout-btn:hover {
            background: #d32f2f;
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
                <li><a href="logout.php">Logout</a></li> <!-- إضافة رابط تسجيل الخروج -->
            </ul>
        </nav>

        <div class="content">
            <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p>You have successfully logged in.</p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</body>
</html>