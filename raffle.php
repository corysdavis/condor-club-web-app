<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = 'Live Raffle';

// member names from the database for the shuffle
$names = $pdo->query("SELECT CONCAT(first_name, ' ', last_name) FROM members WHERE role = 'member'")->fetchAll(PDO::FETCH_COLUMN);
$winner = 'Bill Johnson';

require_once 'includes/header.php';
?>

<section class="raffle-page">
    <div class="raffle-stage">
        <div class="eyebrow">Live Raffle &middot; Members Tour Stand Bag</div>
        <div id="raffle-name" class="raffle-name">Ready?</div>
        <button id="start-draw" class="btn">Start the Draw</button>
        <p id="raffle-result" class="raffle-result" hidden>
            Congratulations, <?= e($winner) ?>! <br>
            You will receive an email with details about your raffle winnings.
        </p>
    </div>
</section>

<script>
    const names = <?= json_encode($names) ?>;
    const nameEl = document.getElementById('raffle-name');

    document.getElementById('start-draw').onclick = function () {
        this.disabled = true;
        const spin = setInterval(() => {
            nameEl.textContent = names[Math.floor(Math.random() * names.length)];
        }, 80);

        setTimeout(() => {
            clearInterval(spin);
            nameEl.textContent = '<?= e($winner) ?>';
            nameEl.classList.add('winner');
            document.getElementById('raffle-result').hidden = false;
        }, 3000);
    };
</script>

<?php require_once 'includes/footer.php'; ?>