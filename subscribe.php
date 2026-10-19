<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
csrf_check();

$email = trim($_POST['email'] ?? '');
$phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? ''); // keep digits only
$emailOptIn = isset($_POST['email_opt_in']) ? 1 : 0;
$textOptIn = isset($_POST['text_opt_in']) ? 1 : 0;

// validate input
if ($email === '' && $phone === '') {
    set_flash('Enter an email or phone number to sign up.', 'error');
} elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('Please enter a valid email address.', 'error');
} elseif ($phone !== '' && strlen($phone) !== 10) {
    set_flash('Please enter a 10-digit phone number.', 'error');
} elseif ($textOptIn && $phone === '') {
    set_flash('Add a phone number to get text alerts.', 'error');
} else {
    $stmt = $pdo->prepare(
        "INSERT INTO subscribers (email, phone, email_opt_in, text_opt_in) VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE phone = VALUES(phone), email_opt_in = VALUES(email_opt_in), text_opt_in = VALUES(text_opt_in)"
    );
    $stmt->execute([$email !== '' ? $email : null, $phone !== '' ? $phone : null, $emailOptIn, $textOptIn]);
    set_flash("You're on the list. Watch for the next release.");
}

header('Location: index.php');
exit;
