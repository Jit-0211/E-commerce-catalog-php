<?php
// Include at the top of every protected admin page.
require __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
