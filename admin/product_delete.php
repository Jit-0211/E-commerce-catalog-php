<?php
require __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}
csrf_check();

$id = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT image FROM products WHERE id = :id');
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if ($row) {
    if ($row['image']) {
        @unlink(__DIR__ . '/../uploads/' . basename($row['image']));
    }
    $del = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $del->execute([':id' => $id]);
}

header('Location: products.php?msg=deleted');
exit; 
