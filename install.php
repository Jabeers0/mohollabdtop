<?php
// install.php - creates database and tables
ini_set('display_errors',1);
error_reporting(E_ALL);
$host = '127.0.0.1';
$user = 'root';
$pass = 'root';
$dbname = 'adept_play';

$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_errno) {
    die("DB connection failed: " . $mysqli->connect_error);
}

// Create DB
$mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") or die($mysqli->error);
$mysqli->select_db($dbname);

// users
$mysqli->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    wallet_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
") or die($mysqli->error);

// admin
$mysqli->query("CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
") or die($mysqli->error);

// tournaments
$mysqli->query("CREATE TABLE IF NOT EXISTS tournaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    game_name VARCHAR(150) NOT NULL,
    entry_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    prize_pool DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    match_time DATETIME NOT NULL,
    commission_percent INT NOT NULL DEFAULT 0,
    room_id VARCHAR(255) DEFAULT NULL,
    room_password VARCHAR(255) DEFAULT NULL,
    status ENUM('Upcoming','Live','Completed') DEFAULT 'Upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
") or die($mysqli->error);

// participants
$mysqli->query("CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tournament_id INT NOT NULL,
    result VARCHAR(100) DEFAULT 'Participated',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
") or die($mysqli->error);

// transactions
$mysqli->query("CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    type ENUM('credit','debit') NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
") or die($mysqli->error);

// Insert default admin if not exists
$admin_user = 'admin';
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("SELECT id FROM admin WHERE username = ?");
$stmt->bind_param('s',$admin_user);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $ins = $mysqli->prepare("INSERT INTO admin (username,password) VALUES (?,?)");
    $ins->bind_param('ss',$admin_user,$admin_pass);
    $ins->execute();
}

$stmt->close();
$mysqli->close();

header('Location: login.php');
exit;
?>