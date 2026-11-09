<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = 'Shop';
$categories = product_categories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // members - add a product to their cart
    if ($action === 'add_to_cart') {
        require_login();
        $pid = (int)$_POST['product_id'];
        $stmt = $pdo->prepare("SELECT name, stock FROM products WHERE product_id = ?");
        $stmt->execute([$pid]);
        $product = $stmt->fetch();

        if ($product && $product['stock'] > 0) {
            $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE member_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['member_id'], $pid]);
            $inCart = $stmt->fetchColumn();

            if ($inCart === false) {
                $pdo->prepare("INSERT INTO cart_items (member_id, product_id, quantity) VALUES (?, ?, 1)")
                    ->execute([$_SESSION['member_id'], $pid]);
            } elseif ($inCart < $product['stock']) {
                $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE member_id = ? AND product_id = ?")
                    ->execute([$_SESSION['member_id'], $pid]);
            }
            set_flash('Added "' . $product['name'] . '" to your cart.');
        } else {
            set_flash('Sorry, that item is sold out.', 'error');
        }
        header('Location: shop.php');
        exit;
    }

    // admins - add or delete a product
    require_admin();

    if ($action === 'add') {
        [$data, $errors] = validate_product($_POST);
        if ($errors) {
            set_flash(implode(' ', $errors), 'error');
        } else {
            $pdo->prepare("INSERT INTO products (name, category, collection, color, price, stock) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute(array_values($data));
            set_flash('Added "' . $data['name'] . '" to the shop.');
        }
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM products WHERE product_id = ?")->execute([(int)$_POST['product_id']]);
        set_flash('Product deleted.');
    }
    header('Location: shop.php');
    exit;
}

// search
$q = trim($_GET['q'] ?? '');
$category = in_array($_GET['category'] ?? '', $categories, true) ? $_GET['category'] : '';
$like = "%$q%";

$stmt = $pdo->prepare(
    "SELECT * FROM products
     WHERE (name LIKE ? OR collection LIKE ? OR color LIKE ?)
       AND (? = '' OR category = ?)
     ORDER BY collection, name"
);
$stmt->execute([$like, $like, $like, $category, $category]);
$products = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="eyebrow">Shop</div>
        <h1 class="section-title">The Collection</h1>
        <p class="muted">Search by name, color, or collection, or pick a category. Leave both blank to see everything.</p>

        <form method="get" class="search-bar">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search products..." maxlength="50">
            <select name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $c): ?>
                    <option <?= $c === $category ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-dark">Search</button>
        </form>

        <?php if (is_admin()): ?>
            <details class="card admin-panel">
                <summary>+ Add a product</summary>
                <form method="post" class="admin-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <div class="field"><label>Name</label><input type="text" name="name" required></div>
                    <div class="field">
                        <label>Category</label>
                        <select name="category">
                            <?php foreach ($categories as $c): ?>
                                <option><?= e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field"><label>Collection</label><input type="text" name="collection" required></div>
                    <div class="field"><label>Color</label><input type="text" name="color" required></div>
                    <div class="field"><label>Price ($)</label><input type="number" name="price" step="0.01" required></div>
                    <div class="field"><label>Stock</label><input type="number" name="stock" required></div>
                    <button class="btn btn-dark">Add Product</button>
                </form>
            </details>
        <?php endif; ?>

        <p class="results-count"><?= count($products) ?> product(s) found</p>

        <div class="grid">
            <?php foreach ($products as $p): ?>
                <div class="card">
                    <div class="product-img">
                        <?php if (!empty($p['image']) && file_exists('img/' . $p['image'])): ?>
                            <img src="img/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                        <?php else: ?>
                            <span><?= e($p['category']) ?></span>
                        <?php endif; ?>
                    </div>

                    <span class="tag"><?= $p['stock'] > 0 ? 'In Stock' : 'Sold Out' ?></span>
                    <h3><?= e($p['name']) ?></h3>
                    <p class="muted"><?= e($p['color']) ?> &middot; <?= e($p['collection']) ?></p>
                    <p class="price">$<?= number_format($p['price'], 2) ?></p>

                    <?php if (is_admin()): ?>
                        <div class="admin-actions">
                            <a href="product_edit.php?id=<?= $p['product_id'] ?>">Edit</a>
                            <form method="post" onsubmit="return confirm('Delete this product?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                <button class="link-danger">Delete</button>
                            </form>
                        </div>
                    <?php elseif ($p['stock'] > 0): ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                            <button class="btn btn-small btn-dark">Add to Cart</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>