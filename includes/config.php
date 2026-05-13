<?php
// ============================================================
//  includes/config.php
//  Database connection – used by every page
// ============================================================

// Change these if your XAMPP settings are different
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // default XAMPP username
define('DB_PASS', '');           // default XAMPP password (empty)
define('DB_NAME', 'activate_academy');

// Connect to MySQL
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Stop the page and show error if connection fails
if (!$conn) {
    die("<h3 style='color:red;font-family:sans-serif'>
         ❌ Database connection failed: " . mysqli_connect_error() . "
         <br><small>Make sure XAMPP MySQL is running and you imported database.sql</small>
         </h3>");
}

// Set character encoding to UTF-8
mysqli_set_charset($conn, "utf8");
?>
