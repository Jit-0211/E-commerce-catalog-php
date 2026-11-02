<?php
// Database connection + small helper functions used on every page.
session_start();

$db_host = 'localhost';
$db_name = 'catalog_db';
$db_user = 'root';
$db_pass = '';   // XAMPP default is an empty password

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $ex) {
    die('Could not connect to the database. Check includes/config.php and make sure MySQL is running.');
}

// Escape output to prevent XSS.
function e($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

// Format price in rupees.
function money($amount)
{
    return '₹' . number_format((float)$amount, 2);
}

// CSRF protection for forms.
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check()
{
    $sent = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(400);
        die('Invalid request (CSRF check failed). Go back and try again.');
    }
}
