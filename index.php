<?php
require_once 'includes/db.php';
$pageTitle = 'Home';

// membership tiers come from the database
$tiers = $pdo->query("SELECT tier_name, annual_price, early_access_hours, description FROM membership_tiers ORDER BY annual_price")->fetchAll();

require_once 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="eyebrow">Fall Collection &middot; Releasing Nov 7</div>
        <p>Club Members shop 48 hours early<br>&amp; get a shot at our raffle-only releases.</p>
        <a href="releases.php" class="btn">See Upcoming Releases</a>
        <a href="register.php" class="btn btn-outline">Join the Club</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="eyebrow">Membership</div>
        <h2 class="section-title">Pick your tier</h2>
        <div class="grid" style="margin-top:28px">
            <?php foreach ($tiers as $tier): ?>
                <div class="card tier <?= $tier['annual_price'] > 0 ? 'featured' : '' ?>">
                    <?php if ($tier['annual_price'] > 0): ?><span class="tag tag-brass">Most Popular</span><?php endif; ?>
                    <h3><?= e($tier['tier_name']) ?></h3>
                    <p class="price">
                        <?= $tier['annual_price'] > 0 ? '$' . e(number_format($tier['annual_price'], 2)) . ' / year' : 'Free' ?>
                    </p>
                    <p class="muted"><?= e($tier['description']) ?></p>
                    <?php if ($tier['early_access_hours'] > 0): ?>
                        <ul><li><?= e($tier['early_access_hours']) ?>-hour early access to every release</li></ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section signup-band">
    <div class="container">
        <div class="eyebrow">Stay in the loop</div>
        <h2 class="section-title">Never miss a release</h2>
        <p class="muted">Get alerts by email or text.</p>
        <form action="subscribe.php" method="post">
            <?= csrf_field() ?>
            <div class="signup-form">
                <div>
                    <label for="sub_email">Email</label>
                    <input type="email" id="sub_email" name="email" placeholder="you@example.com">
                </div>
                <div>
                    <label for="sub_phone">Text</label>
                    <input type="tel" id="sub_phone" name="phone" placeholder="(000) 000-0000">
                </div>
                <button type="submit" class="btn btn-dark">Sign Up</button>
            </div>
            <div class="signup-options">
                <label class="checkbox"><input type="checkbox" name="email_opt_in" checked> Email updates</label>
                <label class="checkbox"><input type="checkbox" name="text_opt_in"> Text alerts</label>
            </div>
        </form>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
