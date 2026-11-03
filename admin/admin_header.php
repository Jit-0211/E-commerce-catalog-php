<?php
// Set $adminPage (dashboard | products | categories) and $adminTitle before including.
$adminPage  = $adminPage ?? '';
$adminTitle = $adminTitle ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($adminTitle) ?> | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-wrap">
    <aside class="sidebar">
        <h2>Catalog Admin</h2>
        <a href="dashboard.php" class="<?= $adminPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="products.php" class="<?= $adminPage === 'products' ? 'active' : '' ?>">Products</a>
        <a href="categories.php" class="<?= $adminPage === 'categories' ? 'active' : '' ?>">Categories</a>
        <a href="../index.php" target="_blank">View site</a>
        <a href="logout.php">Log out</a>
    </aside>
    <div class="admin-main">
