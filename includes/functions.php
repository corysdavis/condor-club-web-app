<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return isset($_SESSION['member_id']);
}

// send visitors to the login page if not signed in
function require_login() {
    if (!is_logged_in()) {
        set_flash('Please log in to view that page.', 'error');
        header('Location: login.php');
        exit;
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check() {
    $sent = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        die('Invalid form submission. Please go back and try again.');
    }
}

function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// password complexity
// used on registration and change password
function password_errors($pw) {
    $errors = [];
    if (strlen($pw) < 8)                       $errors[] = 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Z]/', $pw))           $errors[] = 'Password needs at least one uppercase letter.';
    if (!preg_match('/[a-z]/', $pw))           $errors[] = 'Password needs at least one lowercase letter.';
    if (!preg_match('/[0-9]/', $pw))           $errors[] = 'Password needs at least one number.';
    if (!preg_match('/[^A-Za-z0-9]/', $pw))    $errors[] = 'Password needs at least one symbol (like ! or #).';
    return $errors;
}

//  product management for admin users
function is_admin() {
    return ($_SESSION['role'] ?? '') === 'admin';
}

function require_admin() {
    if (!is_admin()) {
        set_flash('Only staff accounts can manage products.', 'error');
        header('Location: shop.php');
        exit;
    }
}

function product_categories() {
    return ['Polos', 'Outerwear', 'Pants', 'Headwear', 'Accessories', 'Bags'];
}

// clean/validate product form input
function validate_product($in) {
    $data = [
        'name'       => trim($in['name'] ?? ''),
        'category'   => $in['category'] ?? '',
        'collection' => trim($in['collection'] ?? ''),
        'color'      => trim($in['color'] ?? ''),
        'price'      => trim($in['price'] ?? ''),
        'stock'      => trim($in['stock'] ?? ''),
    ];
    $errors = [];
    if ($data['name'] === '' || strlen($data['name']) > 100)         $errors[] = 'Product name is required (100 characters max).';
    if (!in_array($data['category'], product_categories(), true))   $errors[] = 'Please choose a valid category.';
    if ($data['collection'] === '' || strlen($data['collection']) > 50) $errors[] = 'Collection is required (50 characters max).';
    if ($data['color'] === '' || strlen($data['color']) > 30)        $errors[] = 'Color is required (30 characters max).';
    if (!is_numeric($data['price']) || $data['price'] <= 0 || $data['price'] > 9999.99) $errors[] = 'Price must be a number between 0.01 and 9999.99.';
    if (filter_var($data['stock'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]) === false) $errors[] = 'Stock must be a whole number from 0 to 9999.';
    return [$data, $errors];
}