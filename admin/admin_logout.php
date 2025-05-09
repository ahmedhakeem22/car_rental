<?php
session_start(); 

unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

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


header("Location: admin_login.php");
exit();
?>