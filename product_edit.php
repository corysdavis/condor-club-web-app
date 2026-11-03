<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_admin();

$categories = product_categories();
$id = (int)($_GET['id'] ?? 0);

// load the product being edited
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    set_flash('That product no longer exists.', 'error');
    header('Location: shop.php');
    exit;
}

// save changes
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$data, $errors] = validate_product($_POST);

    if (!$errors) {
        $pdo->prepare("UPDATE products SET name = ?, category = ?, collection = ?, color = ?, price = ?, stock = ? WHERE product_id = ?")
            ->execute([...array_values($data), $id]);
        set_flash('Updated "' . $data['name'] . '".');
        header('Location: shop.php');
        exit;
    }
    $product = array_merge($product, $data);
}

$pageTitle = 'Edit Product';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-wrap card">
        <div class="eyebrow">Staff</div>
        <h1>Edit Product</h1>

        <?php if ($errors): ?>
            <ul class="errors"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            <div class="field"><label>Name</label><input type="text" name="name" value="<?= e($product['name']) ?>" required></div>
            <div class="field"><label>Category</label>
                <select name="category">
                    <?php foreach ($categories as $c): ?>
                        <option <?= $c === $product['category'] ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Collection</label><input type="text" name="collection" value="<?= e($product['collection']) ?>" required></div>
            <div class="field"><label>Color</label><input type="text" name="color" value="<?= e($product['color']) ?>" required></div>
            <div class="field-row">
                <div class="field"><label>Price ($)</label><input type="number" name="price" step="0.01" value="<?= e($product['price']) ?>" required></div>
                <div class="field"><label>Stock</label><input type="number" name="stock" value="<?= e($product['stock']) ?>" required></div>
            </div>
            <button class="btn btn-dark" style="width:100%">Save Changes</button>
            <p class="form-note muted"><a href="shop.php">Cancel</a></p>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>