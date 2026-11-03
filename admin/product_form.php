<?php
// Add a new product, or edit an existing one when ?id= is given.
require __DIR__ . '/auth.php';

$id = (int)($_GET['id'] ?? 0);
$editing = $id > 0;

$product = ['name' => '', 'description' => '', 'price' => '', 'category_id' => '', 'image' => ''];

if ($editing) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        header('Location: products.php');
        exit;
    }
    $product = $found;
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price'] ?? '';
    $categoryId  = (int)($_POST['category_id'] ?? 0);

    if ($name === '' || mb_strlen($name) > 150) { $errors[] = 'Name is required (max 150 characters).'; }
    if ($description === '')                    { $errors[] = 'Description is required.'; }
    if (!is_numeric($price) || $price < 0)      { $errors[] = 'Enter a valid price.'; }

    // Category must exist (or be empty)
    $categoryValue = null;
    if ($categoryId > 0) {
        $chk = $pdo->prepare('SELECT id FROM categories WHERE id = :id');
        $chk->execute([':id' => $categoryId]);
        if ($chk->fetch()) { $categoryValue = $categoryId; } else { $errors[] = 'Invalid category.'; }
    }

    // Image upload (optional)
    $newImage = null;
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image must be smaller than 2 MB.';
        } else {
            $info = @getimagesize($file['tmp_name']);
            $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'];
            if (!$info || !isset($allowed[$info[2]])) {
                $errors[] = 'Only JPG, PNG or GIF images are allowed.';
            } else {
                $newImage = bin2hex(random_bytes(10)) . '.' . $allowed[$info[2]];
            }
        }
    }

    if (!$errors) {
        $imageName = $product['image'];
        if ($newImage) {
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $newImage);
            if ($editing && $product['image']) {
                @unlink(__DIR__ . '/../uploads/' . basename($product['image']));
            }
            $imageName = $newImage;
        }

        if ($editing) {
            $upd = $pdo->prepare(
                'UPDATE products SET category_id = :cat, name = :name, description = :d, price = :price, image = :img
                 WHERE id = :id'
            );
            $upd->execute([
                ':cat' => $categoryValue, ':name' => $name, ':d' => $description,
                ':price' => $price, ':img' => $imageName, ':id' => $id,
            ]);
            header('Location: products.php?msg=updated');
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO products (category_id, name, description, price, image)
                 VALUES (:cat, :name, :d, :price, :img)'
            );
            $ins->execute([
                ':cat' => $categoryValue, ':name' => $name, ':d' => $description,
                ':price' => $price, ':img' => $imageName ?: null,
            ]);
            header('Location: products.php?msg=added');
        }
        exit;
    }

    // Keep what the user typed if there were errors
    $product['name'] = $name;
    $product['description'] = $description;
    $product['price'] = $price;
    $product['category_id'] = $categoryId;
}

$adminPage  = 'products';
$adminTitle = $editing ? 'Edit product' : 'Add product';
include 'admin_header.php';
?>
<div class="admin-top">
    <h1><?= $editing ? 'Edit product' : 'Add product' ?></h1>
    <a class="btn grey" href="products.php">&larr; Back</a>
</div>

<div class="panel" style="max-width:640px">
    <?php foreach ($errors as $err): ?><div class="alert error"><?= e($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-row">
            <label for="name">Product name</label>
            <input type="text" id="name" name="name" value="<?= e($product['name']) ?>" required>
        </div>
        <div class="form-row">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="0">-- None --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (int)$product['category_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <label for="price">Price (₹)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= e($product['price']) ?>" required>
        </div>
        <div class="form-row">
            <label for="description">Description</label>
            <textarea id="description" name="description" required><?= e($product['description']) ?></textarea>
        </div>
        <div class="form-row">
            <label for="image">Image (JPG/PNG/GIF, max 2 MB)</label>
            <?php if ($editing && $product['image']): ?>
                <img class="preview" src="../uploads/<?= e($product['image']) ?>" alt="Current image">
                <div style="font-size:.85rem;color:#6b7280;margin-bottom:6px">Choose a new file only if you want to replace it.</div>
            <?php endif; ?>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <button class="btn" type="submit"><?= $editing ? 'Save changes' : 'Add product' ?></button>
    </form>
</div>
<?php include 'admin_footer.php'; ?>
