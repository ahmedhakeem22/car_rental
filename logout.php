<?php
session_start(); // بدء الجلسة

// تدمير جميع بيانات الجلسة
$_SESSION = array(); // مسح محتويات المصفوفة $_SESSION

// إذا كنت تستخدم الكوكيز الخاصة بالجلسة، فقم بحذفها أيضاً
// ملاحظة: سيؤدي هذا إلى حذف كوكيز الجلسة وليس فقط بيانات الجلسة
// التي تم ضبطها بواسطة session_set_cookie_params()
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// أخيراً، تدمير الجلسة
session_destroy();

// إعادة التوجيه إلى صفحة تسجيل الدخول
header("Location: login.php");
exit();
?>