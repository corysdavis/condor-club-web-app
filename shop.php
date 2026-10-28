<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = 'Shop';

$q = trim(substr($_GET['q'] ?? '', 0, 50));
$category = $_GET['category'] ?? '';
$categories = ['Polos', 'Outerwear', 'Pants', 'Headwear', 'Accessories', 'Bags'];
if (!in_array($category, $categories, true)) {
    $category = ''; // only allow known categories
}

// prepared statement keeps the search safe from SQL injection
$like = '%' . $q . '%';
$stmt = $pdo->prepare(
    "SELECT name, category, collection, color, price, stock
     FROM products
     WHERE (name LIKE ? OR collection LIKE ? OR color LIKE ? OR category LIKE ?)
       AND (? = '' OR category = ?)
     ORDER BY collection, name"
);
$stmt->execute([$like, $like, $like, $like, $category, $category]);
$products = $stmt->fetchAll();
$searching = ($q !== '' || $category !== '');

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="eyebrow">Shop</div>
        <h1 class="section-title">The Collection</h1>
        <p class="muted">Search by product name, color, or pick a category. Leave blank to see everything.</p>

        <form method="get" action="shop.php" class="search-bar">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search products..." maxlength="50" aria-label="Search products">
            <select name="category" aria-label="Category">
                <option value="">All categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c) ?>" <?= $c === $category ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-dark">Search</button>
            <?php if ($searching): ?><a href="shop.php" class="clear-link">Clear</a><?php endif; ?>
        </form>

        <p class="results-count">
            <?php if ($searching): ?>
                <?= count($products) ?> result<?= count($products) === 1 ? '' : 's' ?>
                <?= $q !== '' ? 'for "' . e($q) . '"' : '' ?>
                <?= $category !== '' ? 'in ' . e($category) : '' ?>
            <?php else: ?>
                Showing all <?= count($products) ?> products
            <?php endif; ?>
        </p>

        <?php if (!$products): ?>
            <div class="card"><p>No products matched your search. Try a different word or <a href="shop.php">view all products</a>.</p></div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($products as $p): ?>
                    <?php
                    if ($p['stock'] == 0)      { $label = 'Sold Out';                      $tagClass = 'tag tag-soldout'; }
                    elseif ($p['stock'] <= 10) { $label = 'Only ' . $p['stock'] . ' left'; $tagClass = 'tag tag-brass'; }
                    else                       { $label = 'In Stock';                      $tagClass = 'tag'; }
                    ?>
                    <div class="card">
                        <div class="product-img">CC</div>
                        <span class="<?= $tagClass ?>"><?= e($label) ?></span>
                        <h3><?= e($p['name']) ?></h3>
                        <p class="muted"><?= e($p['color']) ?> &middot; <?= e($p['collection']) ?></p>
                        <p class="price">$<?= e(number_format($p['price'], 2)) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>