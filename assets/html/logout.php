<?php
session_start();
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $parms = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000, $parms['path'] . $parms['domain'], $params["secure"], $params["httponly"]);
};
session_destroy();
header("Location: login.php");
exit();
