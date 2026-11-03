<?php
require 'includes/config.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = :id'
);
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    include 'includes/header.php';
    echo '<main class="container"><div class="empty">Product not found. <a href="index.php">Back to catalog</a></div></main>';
    include 'includes/footer.php';
    exit;
}

// Related products from the same category
$related = [];
if ($product['category_id']) {
    $r = $pdo->prepare(
        'SELECT id, name, price, image FROM products
         WHERE category_id = :cat AND id <> :id
         ORDER BY created_at DESC LIMIT 4'
    );
    $r->execute([':cat' => $product['category_id'], ':id' => $product['id']]);
    $related = $r->fetchAll();
}

$pageTitle = $product['name'];
include 'includes/header.php';
?>

<main class="container">
    <p class="breadcrumb">
        <a href="index.php">Home</a> &rsaquo;
        <?php if ($product['category_id']): ?>
            <a href="index.php?cat=<?= (int)$product['category_id'] ?>#products"><?= e($product['category']) ?></a> &rsaquo;
        <?php endif; ?>
        <?= e($product['name']) ?>
    </p>

    <div class="detail">
        <div class="detail-img">
            <?php if ($product['image']): ?>
                <img src="uploads/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
            <?php else: ?>
                <div class="no-image big">No image</div>
            <?php endif; ?>
        </div>
        <div class="detail-info">
            <span class="tag"><?= e($product['category'] ?? 'Uncategorised') ?></span>
            <h1><?= e($product['name']) ?></h1>
            <p class="price big"><?= money($product['price']) ?></p>
            <h3>Description</h3>
            <p class="desc"><?= nl2br(e($product['description'])) ?></p>
            <a class="btn" href="index.php">&larr; Back to catalog</a>
        </div>
    </div>

    <?php if ($related): ?>
        <h2 class="section-title">More in <?= e($product['category']) ?></h2>
        <div class="grid">
            <?php foreach ($related as $p): ?>
                <article class="card">
                    <a href="product.php?id=<?= (int)$p['id'] ?>" class="card-img">
                        <?php if ($p['image']): ?>
                            <img src="uploads/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                        <?php else: ?>
                            <div class="no-image">No image</div>
                        <?php endif; ?>
                    </a>
                    <div class="card-body">
                        <h3><?= e($p['name']) ?></h3>
                        <div class="card-foot">
                            <span class="price"><?= money($p['price']) ?></span>
                            <a class="btn small" href="product.php?id=<?= (int)$p['id'] ?>">View</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
