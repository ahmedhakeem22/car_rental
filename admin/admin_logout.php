<?php
session_start(); // بدء الجلسة

// تدمير معلومات جلسة المسؤول فقط
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

// إذا لم يعد هناك أي معرفات في الجلسة (أي لا يوجد مستخدم عادي مسجل دخوله أيضاً)
// يمكنك تدمير الجلسة بالكامل
if (empty($_SESSION)) {
     $_SESSION = array();
     if (ini_get("session.use_cookies")) {
         $params = session_get_cookie_params();
         setcookie(session_name(), '', time() - 42000,
             $params["path"], $params["domain"],
             $params["secure"], $params["httponly"]
         );
     }
     session_destroy();
}


// إعادة التوجيه إلى صفحة تسجيل الدخول للمسؤول
header("Location: admin_login.php");
exit();
?>