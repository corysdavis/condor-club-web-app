<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();
$memberId = $_SESSION['member_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $pid = (int)($_POST['product_id'] ?? 0);

    // change the quantity - 0 removes it
    if ($action === 'update') {
        $qty = max(0, (int)$_POST['quantity']);
        if ($qty === 0) {
            $pdo->prepare("DELETE FROM cart_items WHERE member_id = ? AND product_id = ?")->execute([$memberId, $pid]);
        } else {
            $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE member_id = ? AND product_id = ?")->execute([$qty, $memberId, $pid]);
        }
        set_flash('Cart updated.');
    }

    // remove an item
    if ($action === 'remove') {
        $pdo->prepare("DELETE FROM cart_items WHERE member_id = ? AND product_id = ?")->execute([$memberId, $pid]);
        set_flash('Item removed from your cart.');
    }

    // create the order, reduce stock, empty the cart
    if ($action === 'checkout') {
        $stmt = $pdo->prepare("SELECT c.product_id, c.quantity, p.name, p.price FROM cart_items c JOIN products p ON c.product_id = p.product_id WHERE c.member_id = ?");
        $stmt->execute([$memberId]);
        $items = $stmt->fetchAll();

        if ($items) {
            $pdo->beginTransaction();
            $total = 0;
            foreach ($items as $item) {
                // only succeeds if there is enough stock left
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ? AND stock >= ?");
                $stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                if ($stmt->rowCount() === 0) {
                    $pdo->rollBack();
                    set_flash('Sorry, there isn\'t enough stock left for "' . $item['name'] . '". Please lower the quantity.', 'error');
                    header('Location: cart.php');
                    exit;
                }
                $total += $item['price'] * $item['quantity'];
            }

            $pdo->prepare("INSERT INTO orders (member_id, total) VALUES (?, ?)")->execute([$memberId, $total]);
            $orderId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmt->execute([$orderId, $item['product_id'], $item['name'], $item['price'], $item['quantity']]);
            }
            $pdo->prepare("DELETE FROM cart_items WHERE member_id = ?")->execute([$memberId]);
            $pdo->commit();

            set_flash("Order #$orderId placed! This is a simulated checkout, so no payment was taken.");
        }
    }
    header('Location: cart.php');
    exit;
}

// load the cart
$stmt = $pdo->prepare("SELECT c.product_id, c.quantity, p.name, p.color, p.price, p.stock FROM cart_items c JOIN products p ON c.product_id = p.product_id WHERE c.member_id = ?");
$stmt->execute([$memberId]);
$items = $stmt->fetchAll();
$total = 0;
foreach ($items as $item) $total += $item['price'] * $item['quantity'];

$pageTitle = 'Cart';
require_once 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="eyebrow">Your Cart</div>
        <h1 class="section-title">Cart</h1>

        <?php if (!$items): ?>
            <div class="card"><p>Your cart is empty. <a href="shop.php">Browse the shop</a>.</p></div>
        <?php else: ?>
            <div class="card">
                <?php foreach ($items as $item): ?>
                    <div class="cart-row">
                        <div>
                            <strong><?= e($item['name']) ?></strong>
                            <div class="muted"><?= e($item['color']) ?> &middot; $<?= number_format($item['price'], 2) ?> each</div>
                        </div>
                        <form method="post" class="cart-qty">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" max="<?= $item['stock'] ?>">
                            <button class="btn btn-small btn-dark">Update</button>
                        </form>
                        <strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            <button class="link-danger">Remove</button>
                        </form>
                    </div>
                <?php endforeach; ?>

                <div class="cart-total">
                    <span>Total: <strong>$<?= number_format($total, 2) ?></strong></span>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="checkout">
                        <button class="btn btn-dark">Place Order</button>
                    </form>
                </div>
                <p class="form-note muted">Checkout is simulated! No payment information is collected.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>