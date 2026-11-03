<?php
// Public header. Set $pageTitle before including this file.
$pageTitle = $pageTitle ?? 'Product Catalog';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | ShopCatalog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="index.php">Shop<span>Catalog</span></a>
        <input type="checkbox" id="menu-toggle" class="menu-toggle">
        <label for="menu-toggle" class="menu-icon" aria-label="Menu">&#9776;</label>
        <nav class="main-nav">
            <a href="index.php">Home</a>
            <a href="index.php#products">Products</a>
            <a href="admin/login.php">Admin</a>
        </nav>
    </div>
</header>
