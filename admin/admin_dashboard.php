<?php
session_start(); // بدء الجلسة

// التحقق مما إذا كان المسؤول مسجل الدخول
if (!isset($_SESSION['admin_id'])) {
    // إذا لم يكن مسجل الدخول كمسؤول، إعادة التوجيه إلى صفحة تسجيل الدخول للمسؤول
    header("Location: admin_login.php");
    exit();
}

// معلومات المسؤول من الجلسة
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$admin_role = $_SESSION['admin_role'];

// يمكنك استخدام $admin_role لتحديد ما يمكن للمسؤول الوصول إليه أو رؤيته

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
     <link rel="stylesheet" href="styles.css">
     <style>
        /* Adapt styles for admin dashboard */
        body {
             background-color: #e9ecef; /* Slightly different background */
        }
        .admin-navbar {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            background: #1B2559; /* Darker background */
            padding: 15px 20px;
            border-radius: 0; /* No border-radius for full width */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
             justify-content: space-between; /* Space out logo and links */
        }
        .admin-navbar .logo img {
             height: 30px;
             filter: brightness(0) invert(1); /* Make logo white if it's dark */
        }
         .admin-navbar ul {
            display: flex;
            list-style: none;
            gap: 20px;
            padding: 0;
            margin: 0;
         }
         .admin-navbar a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 400;
            font-size: 15px;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s ease;
         }
         .admin-navbar a:hover {
             background-color: rgba(255, 255, 255, 0.1);
             color: white;
         }

        .content {
            text-align: center;
            margin-top: 30px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
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
        <nav class="admin-navbar">
             <div class="logo">
                <img src="../assets/images/logo.png" alt="Admin Logo">
            </div>
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li><a href="#">Manage Users</a></li>
                <li><a href="#">Manage Cars</a></li>
                <li><a href="#">View Rentals</a></li>
                 <li><a href="admin_logout.php">Logout</a></li> <!-- رابط تسجيل الخروج للمسؤول -->
            </ul>
        </nav>

        <div class="content">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($admin_username); ?>!</p>
            <p>Your role: <?php echo htmlspecialchars($admin_role); ?></p>

            <h2>Quick Actions</h2>
            <ul>
                <li><a href="#">View all registered users</a></li>
                <li><a href="#">Add a new car</a></li>
                <li><a href="#">View pending rentals</a></li>
                <!-- Add more admin-specific links here -->
            </ul>

            <p style="margin-top: 30px;">This is a basic admin dashboard. More features can be added based on your role.</p>

        </div>
    </div>
</body>
</html>