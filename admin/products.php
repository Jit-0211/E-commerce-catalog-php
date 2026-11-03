<?php
require __DIR__ . '/auth.php';

$msg = $_GET['msg'] ?? '';

$products = $pdo->query(
    'SELECT p.id, p.name, p.price, p.image, c.name AS category
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.id DESC'
)->fetchAll();

$adminPage  = 'products';
$adminTitle = 'Products';
include 'admin_header.php';
?>
<div class="admin-top">
    <h1>Products</h1>
    <a class="btn" href="product_form.php">+ Add product</a>
</div>

<?php if ($msg === 'added'):   ?><div class="alert success">Product added.</div><?php endif; ?>
<?php if ($msg === 'updated'): ?><div class="alert success">Product updated.</div><?php endif; ?>
<?php if ($msg === 'deleted'): ?><div class="alert success">Product deleted.</div><?php endif; ?>

<div class="table-wrap">
    <table>
        <tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Actions</th></tr>
        <?php if (!$products): ?>
            <tr><td colspan="6">No products yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td>
                    <?php if ($p['image']): ?>
                        <img src="../uploads/<?= e($p['image']) ?>" alt="">
                    <?php else: ?>-<?php endif; ?>
                </td>
                <td><?= e($p['name']) ?></td>
                <td><?= e($p['category'] ?? 'Uncategorised') ?></td>
                <td><?= money($p['price']) ?></td>
                <td>
                    <a class="btn small" href="product_form.php?id=<?= (int)$p['id'] ?>">Edit</a>
                    <form class="inline-form" method="post" action="product_delete.php"
                          onsubmit="return confirm('Delete this product?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button class="btn small danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include 'admin_footer.php'; ?>
