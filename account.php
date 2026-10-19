<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

$memberId = $_SESSION['member_id'];
$pwErrors = [];

// change password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM members WHERE member_id = ?");
    $stmt->execute([$memberId]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) $pwErrors[] = 'Current password is incorrect.';
    if (strlen($new) < 8) $pwErrors[] = 'New password must be at least 8 characters.';
    if ($new !== $confirm) $pwErrors[] = 'New passwords do not match.';
    if ($new !== '' && $new === $current) $pwErrors[] = 'New password must be different from your current one.';

    if (!$pwErrors) {
        $stmt = $pdo->prepare("UPDATE members SET password_hash = ? WHERE member_id = ?");
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $memberId]);
        set_flash('Your password has been updated.');
        header('Location: account.php');
        exit;
    }
}

// member details with tier
$stmt = $pdo->prepare(
    "SELECT m.first_name, m.last_name, m.email, m.phone, m.created_at, t.tier_name, t.early_access_hours
     FROM members m JOIN membership_tiers t ON m.tier_id = t.tier_id
     WHERE m.member_id = ?"
);
$stmt->execute([$memberId]);
$member = $stmt->fetch();

$pageTitle = 'My Account';
require_once 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="eyebrow">My Account</div>
        <h1 class="section-title">Welcome, <?= e($member['first_name']) ?></h1>
        <p class="muted">Your membership, upcoming releases, and account settings.</p>

        <div class="account-grid">
            <div class="card">
                <h3>Membership</h3>
                <div class="detail"><span class="muted">Name</span><span><?= e($member['first_name'] . ' ' . $member['last_name']) ?></span></div>
                <div class="detail"><span class="muted">Email</span><span><?= e($member['email']) ?></span></div>
                <div class="detail"><span class="muted">Mobile</span><span><?= e($member['phone'] ?? 'Not added') ?></span></div>
                <div class="detail"><span class="muted">Tier</span><span class="tag tag-brass" style="margin:0"><?= e($member['tier_name']) ?></span></div>
                <div class="detail"><span class="muted">Member since</span><span><?= e(date('F Y', strtotime($member['created_at']))) ?></span></div>
                <?php if ($member['early_access_hours'] == 0): ?>
                    <p class="form-note muted">Upgrade to Club Member for 48-hour early access and raffle entry.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Change Password</h3>
                <?php if ($pwErrors): ?>
                    <ul class="errors" style="margin-top:12px"><?php foreach ($pwErrors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                <?php endif; ?>
                <form method="post" style="margin-top:12px">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="current_password">Current password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="field">
                        <label for="new_password">New password</label>
                        <input type="password" id="new_password" name="new_password" minlength="8" required>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm new password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-dark">Update Password</button>
                </form>
            </div>

            <div class="card">
                <h3>Raffle Entries</h3>
                <p class="muted">Your entries and results will show up here once raffles open.</p>
            </div>

            <div class="card">
                <h3>Order History</h3>
                <p class="muted">Your past orders will show up here after your first purchase.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
