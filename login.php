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

    $stmt = $pdo->prepare("SELECT member_id, first_name, password_hash, role FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $member = $stmt->fetch();

    if ($member && password_verify($password, $member['password_hash'])) {

        session_regenerate_id(true);
        $_SESSION['member_id']  = (int)$member['member_id'];
        $_SESSION['first_name'] = $member['first_name'];
        $_SESSION['role']       = $member['role'];

        set_flash('Welcome back, ' . $member['first_name'] . '.');
        header('Location: account.php');
        exit;
    }

    $error = 'Incorrect email or password.';
}

$pageTitle = 'Log In';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-wrap card">
        <div class="eyebrow">Members</div>
        <h1>Log In</h1>

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
