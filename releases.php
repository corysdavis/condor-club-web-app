<?php
require_once 'includes/functions.php';
$pageTitle = 'Releases';

$releases = [
    [
        'name'    => 'Winter 26 Collection',
        'date'    => '2026-12-21T12:00:00',
        'pieces'  => '5 pieces',
        'tagline' => 'Cold-weather layers for late-season golf.',
        'items'   => ['Thermal quarter-zip', 'Quilted winter vest', 'Cuffed knit beanie', 'Fleece-lined trouser', 'Holiday driver headcover'],
    ],
    [
        'name'    => 'New Year 27 Collection',
        'date'    => '2027-01-15T12:00:00',
        'pieces'  => '4 pieces',
        'tagline' => 'New colorways to start the year.',
        'items'   => ['Signature pique polo', 'Lightweight wind shell', 'Structured rope hat', 'Leather scorecard holder'],
    ],
];

require_once 'includes/header.php';
?>

<section class="section releases-intro">
    <div class="container">
        <h1 class="section-title">Upcoming Releases</h1>
        <p class="muted">Limited collections. Club Members shop 48 hours early.</p>
    </div>
</section>

<section class="container release-grid">
    <?php foreach ($releases as $r): ?>
        <div class="release-card">
            <div class="release-top">
                <div class="eyebrow"><?= e($r['pieces']) ?> &middot; Limited Run</div>
                <h2><?= e($r['name']) ?></h2>
                <p class="release-date"><?= date('F jS, Y', strtotime($r['date'])) ?></p>
                <div class="release-countdown" data-countdown="<?= e($r['date']) ?>">
                    <?= date('M j', strtotime($r['date'])) ?>
                </div>
            </div>
            <div class="release-body">
                <p class="release-tagline"><?= e($r['tagline']) ?></p>
                <ul class="release-items">
                    <?php foreach ($r['items'] as $item): ?>
                        <li><?= e($item) ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="release-note">
                    Club Member early access opens <?= date('M j', strtotime($r['date'] . ' -2 days')) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="section">
    <div class="container">
        <div class="how-it-works">
            <div>
                <span class="step-num">01</span>
                <h3>Get notified</h3>
                <p class="muted">Release dates are posted here first.</p>
            </div>
            <div>
                <span class="step-num">02</span>
                <h3>Members shop early</h3>
                <p class="muted">Club Members get a 48-hour head start.</p>
            </div>
            <div>
                <span class="step-num">03</span>
                <h3>Public release</h3>
                <p class="muted">Remaining items open to everyone.</p>
            </div>
        </div>
        <div style="text-align:center; margin-top:36px">
            <a href="register.php" class="btn btn-dark">Join the Club for Early Access</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>