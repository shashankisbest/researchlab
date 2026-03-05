<?php
session_start();
session_unset();
session_destroy();

// Redirect back to the login page after clearing the session
header("Location: login.php");
exit;
?>