<?php
require __DIR__ . '/auth.php';

$totalProducts   = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCategories = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$uncategorised   = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE category_id IS NULL')->fetchColumn();

$perCategory = $pdo->query(
    'SELECT c.name, COUNT(p.id) AS total
     FROM categories c LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id, c.name ORDER BY c.name'
)->fetchAll();

$adminPage  = 'dashboard';
$adminTitle = 'Dashboard';
include 'admin_header.php';
?>
<div class="admin-top">
    <h1>Dashboard</h1>
    <span>Logged in as <strong><?= e($_SESSION['admin_name'] ?? 'admin') ?></strong></span>
</div>

<div class="stats">
    <div class="stat"><div class="num"><?= $totalProducts ?></div>Total products</div>
    <div class="stat"><div class="num"><?= $totalCategories ?></div>Categories</div>
    <div class="stat"><div class="num"><?= $uncategorised ?></div>Uncategorised products</div>
</div>

<div class="panel" style="margin-top:20px">
    <h3 style="margin-bottom:10px">Products per category</h3>
    <div class="table-wrap">
        <table>
            <tr><th>Category</th><th>Products</th></tr>
            <?php foreach ($perCategory as $row): ?>
                <tr><td><?= e($row['name']) ?></td><td><?= (int)$row['total'] ?></td></tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php include 'admin_footer.php'; ?>
