<?php
require 'includes/config.php';

$q      = trim($_GET['q'] ?? '');
$catId  = (int)($_GET['cat'] ?? 0);
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;

// All categories for the filter bar
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

// Build the WHERE part safely with prepared-statement parameters
$where  = [];
$params = [];
if ($q !== '') {
    $where[] = '(p.name LIKE :q1 OR p.description LIKE :q2)';
    $params[':q1'] = '%' . $q . '%';
    $params[':q2'] = '%' . $q . '%';
}
if ($catId > 0) {
    $where[] = 'p.category_id = :cat';
    $params[':cat'] = $catId;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
$countStmt->execute($params);
$total      = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$sql = "SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        $whereSql
        ORDER BY p.created_at DESC, p.id DESC
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Helper to keep filters in pagination / category links
function build_url($changes = [])
{
    $query = array_merge($_GET, $changes);
    foreach ($query as $k => $v) {
        if ($v === '' || $v === 0 || $v === null) {
            unset($query[$k]);
        }
    }
    return 'index.php' . ($query ? '?' . http_build_query($query) : '') . '#products';
}

$pageTitle = 'Home';
include 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>Find what you need</h1>
        <p>Browse products by category or search by name.</p>
        <form class="search-form" method="get" action="index.php#products">
            <?php if ($catId > 0): ?>
                <input type="hidden" name="cat" value="<?= $catId ?>">
            <?php endif; ?>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search products..." aria-label="Search products">
            <button type="submit">Search</button>
        </form>
    </div>
</section>

<main class="container" id="products">
    <div class="category-bar">
        <a href="<?= e(build_url(['cat' => 0, 'page' => 1])) ?>" class="chip <?= $catId === 0 ? 'active' : '' ?>">All</a>
        <?php foreach ($categories as $c): ?>
            <a href="<?= e(build_url(['cat' => $c['id'], 'page' => 1])) ?>"
               class="chip <?= $catId === (int)$c['id'] ? 'active' : '' ?>"><?= e($c['name']) ?></a>
        <?php endforeach; ?>
    </div>

    <p class="result-info">
        <?= $total ?> product<?= $total === 1 ? '' : 's' ?> found
        <?= $q !== '' ? 'for "' . e($q) . '"' : '' ?>
    </p>

    <?php if (!$products): ?>
        <div class="empty">No products match your search. <a href="index.php">Show all products</a></div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($products as $p): ?>
                <article class="card">
                    <a href="product.php?id=<?= (int)$p['id'] ?>" class="card-img">
                        <?php if ($p['image']): ?>
                            <img src="uploads/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                        <?php else: ?>
                            <div class="no-image">No image</div>
                        <?php endif; ?>
                    </a>
                    <div class="card-body">
                        <span class="tag"><?= e($p['category'] ?? 'Uncategorised') ?></span>
                        <h3><?= e($p['name']) ?></h3>
                        <p class="short"><?= e(mb_strimwidth($p['description'], 0, 70, '...')) ?></p>
                        <div class="card-foot">
                            <span class="price"><?= money($p['price']) ?></span>
                            <a class="btn small" href="product.php?id=<?= (int)$p['id'] ?>">View details</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= e(build_url(['page' => $i])) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
