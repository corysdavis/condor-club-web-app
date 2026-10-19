<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Condor Club';
$current = basename($_SERVER['PHP_SELF']);
function nav_class($file, $current) {
    return $file === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Condor Club</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="logo">CONDOR<span>CLUB</span></a>
        <nav class="main-nav">
            <a href="index.php" class="<?= nav_class('index.php', $current) ?>">Home</a>
            <a href="shop.php" class="<?= nav_class('shop.php', $current) ?>">Shop</a>
            <a href="releases.php" class="<?= nav_class('releases.php', $current) ?>">Releases</a>
            <a href="raffle.php" class="<?= nav_class('raffle.php', $current) ?>">Raffle</a>
        </nav>
        <div class="account-nav">
            <?php if (is_logged_in()): ?>
                <a href="account.php" class="<?= nav_class('account.php', $current) ?>">Hi, <?= e($_SESSION['first_name']) ?></a>
                <a href="logout.php" class="btn btn-small btn-outline">Log Out</a>
            <?php else: ?>
                <a href="login.php" class="<?= nav_class('login.php', $current) ?>">Log In</a>
                <a href="register.php" class="btn btn-small">Join the Club</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main>
<?php if ($flash = get_flash()): ?>
    <div class="container">
        <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    </div>
<?php endif; ?>
