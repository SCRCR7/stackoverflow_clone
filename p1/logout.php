<?php
session_start();
session_unset(); // Optional: Unset all session variables
session_destroy();
header("Location: login.php");
exit();
?>