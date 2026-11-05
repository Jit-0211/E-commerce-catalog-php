<?php
require __DIR__ . '/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '' || mb_strlen($name) > 100) {
            $error = 'Category name is required (max 100 characters).';
        } else {
            try {
                $pdo->prepare('INSERT INTO categories (name) VALUES (:n)')->execute([':n' => $name]);
                $success = 'Category added.';
            } catch (PDOException $ex) {
                $error = 'That category already exists.';
            }
        }
    } elseif ($action === 'rename') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id > 0 && $name !== '' && mb_strlen($name) <= 100) {
            try {
                $pdo->prepare('UPDATE categories SET name = :n WHERE id = :id')->execute([':n' => $name, ':id' => $id]);
                $success = 'Category renamed.';
            } catch (PDOException $ex) {
                $error = 'That category name already exists.';
            }
        } else {
            $error = 'Enter a valid name.';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM categories WHERE id = :id')->execute([':id' => $id]);
        $success = 'Category deleted. Its products are now uncategorised.';
    }
}

$categories = $pdo->query(
    'SELECT c.id, c.name, COUNT(p.id) AS total
     FROM categories c LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id, c.name ORDER BY c.name'
)->fetchAll();

$adminPage  = 'categories';
$adminTitle = 'Categories';
include 'admin_header.php';
?>
<div class="admin-top"><h1>Categories</h1></div>

<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>

<div class="panel" style="max-width:500px">
    <form method="post" style="display:flex;gap:8px">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="New category name" required
               style="flex:1;padding:10px;border:1px solid #d1d5db;border-radius:6px">
        <button class="btn" type="submit">Add</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <tr><th>Name</th><th>Products</th><th>Actions</th></tr>
        <?php if (!$categories): ?><tr><td colspan="3">No categories yet.</td></tr><?php endif; ?>
        <?php foreach ($categories as $c): ?>
            <tr>
                <td>
                    <form method="post" class="inline-form" style="display:flex;gap:6px">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="rename">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <input type="text" name="name" value="<?= e($c['name']) ?>" required
                               style="padding:6px;border:1px solid #d1d5db;border-radius:6px">
                        <button class="btn small grey" type="submit">Rename</button>
                    </form>
                </td>
                <td><?= (int)$c['total'] ?></td>
                <td>
                    <form class="inline-form" method="post"
                          onsubmit="return confirm('Delete this category? Its products will become uncategorised.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <button class="btn small danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include 'admin_footer.php'; ?>
