<?php
// config.php - central DB connection & helpers
session_start();
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = 'root';
$DB_NAME = 'adept_play';

$mysqli = new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if ($mysqli->connect_errno) {
    die('DB connect error: '. $mysqli->connect_error);
}

function rupee($num) {
    return '₹' . number_format((float)$num,2);
}

function is_logged() {
    return isset($_SESSION['user_id']);
}

function current_user($mysqli) {
    if (!is_logged()) return null;
    $id = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT id,username,email,wallet_balance FROM users WHERE id=?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res;
}

?>