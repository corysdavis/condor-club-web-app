<?php
require_once 'includes/functions.php';

$_SESSION = [];
session_regenerate_id(true);

set_flash("You've been logged out.");
header('Location: index.php');
exit;
