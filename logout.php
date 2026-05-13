<?php
// logout.php — destroys session and sends user back to login
session_start();
session_destroy();
header("Location: /activate_academy/login.php");
exit();
?>
