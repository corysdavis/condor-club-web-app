<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: account.php');
    exit;
}

$errors = [];
$old = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $old['first_name'] = trim($_POST['first_name'] ?? '');
    $old['last_name']  = trim($_POST['last_name'] ?? '');
    $old['email']      = trim($_POST['email'] ?? '');
    $old['phone']      = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($old['first_name'] === '' || $old['last_name'] === '') $errors[] = 'First and last name are required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($old['phone'] !== '' && strlen($old['phone']) !== 10) $errors[] = 'Phone number must be 10 digits.';
    $errors = array_merge($errors, password_errors($password));
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    // make sure the email isnt registered
    if (!$errors) {
        $stmt = $pdo->prepare("SELECT member_id FROM members WHERE email = ?");
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) $errors[] = 'An account with that email already exists.';
    }

    if (!$errors) {
        
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            "INSERT INTO members (first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $old['first_name'], $old['last_name'], $old['email'],
            $old['phone'] !== '' ? $old['phone'] : null, $hash
        ]);

        // log the new member in
        session_regenerate_id(true);
        $_SESSION['member_id']  = (int)$pdo->lastInsertId();
        $_SESSION['first_name'] = $old['first_name'];
        $_SESSION['role']       = 'member';

        set_flash('Welcome to Condor Club, ' . $old['first_name'] . '!');
        header('Location: account.php');
        exit;
    }
}

$pageTitle = 'Join the Club';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-wrap card">
        <div class="eyebrow">Membership</div>
        <h1>Join the Club</h1>
        <p class="muted" style="margin-bottom:20px">Create a free account to shop, track orders, and upgrade to Club Member anytime.</p>

        <?php if ($errors): ?>
            <ul class="errors"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>

        <form method="post" novalidate>
            <?= csrf_field() ?>
            <div class="field-row">
                <div class="field">
                    <label for="first_name">First name</label>
                    <input type="text" id="first_name" name="first_name" value="<?= e($old['first_name']) ?>" required>
                </div>
                <div class="field">
                    <label for="last_name">Last name</label>
                    <input type="text" id="last_name" name="last_name" value="<?= e($old['last_name']) ?>" required>
                </div>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required>
            </div>
            <div class="field">
                <label for="phone">Mobile (optional)</label>
                <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" placeholder="(000) 000-0000">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="8" required>
                <p class="hint">At least 8 characters with an uppercase letter, a lowercase letter, a number, and a symbol.</p>
            </div>
            <div class="field">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
            </div>
            <button type="submit" class="btn btn-dark" style="width:100%">Create Account</button>
            <p class="form-note muted">Already a member? <a href="login.php">Log in</a></p>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
