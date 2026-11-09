<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: account.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT member_id, first_name, password_hash, role, failed_logins,
                (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked
         FROM members WHERE email = ?"
    );
    $stmt->execute([$email]);
    $member = $stmt->fetch();

    if ($member && $member['is_locked']) {
        // account is locked from too many failed attempts
        $error = 'Too many failed attempts. Please try again in 15 minutes.';

    } elseif ($member && password_verify($password, $member['password_hash'])) {
        // password success - reset the counter and log in with a fresh session ID
        $pdo->prepare("UPDATE members SET failed_logins = 0, locked_until = NULL WHERE member_id = ?")
            ->execute([$member['member_id']]);
        session_regenerate_id(true);
        $_SESSION['member_id']  = (int)$member['member_id'];
        $_SESSION['first_name'] = $member['first_name'];
        $_SESSION['role']       = $member['role'];
        set_flash('Welcome back, ' . $member['first_name'] . '.');
        header('Location: account.php');
        exit;

    } else {
        // wrong password - count it and lock after 5 tries
        if ($member) {
            if ($member['failed_logins'] + 1 >= 5) {
                $pdo->prepare("UPDATE members SET failed_logins = 0, locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE member_id = ?")
                    ->execute([$member['member_id']]);
            } else {
                $pdo->prepare("UPDATE members SET failed_logins = failed_logins + 1 WHERE member_id = ?")
                    ->execute([$member['member_id']]);
            }
        }
        // same message either way
        $error = 'Incorrect email or password.';
    }
}

$pageTitle = 'Log In';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-wrap card">
        <div class="eyebrow">Members</div>
        <h1>Log In</h1>
        <p class="muted" style="margin-bottom:20px">Check your releases, cart, and orders.</p>

        <?php if ($error): ?>
            <ul class="errors"><li><?= e($error) ?></li></ul>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-dark" style="width:100%">Log In</button>
            <p class="form-note muted">New here? <a href="register.php">Join the Club</a></p>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>