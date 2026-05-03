<?php 
// 1. Initialize the session logic
session_start(); 

// 2. Clear all session variables
$_SESSION = array();

// 3. Destroy the session entirely
session_destroy(); 

// 4. Redirect the user back to the login page
header("Location: index.php"); 

// 5. Stop script execution
exit; 
?>