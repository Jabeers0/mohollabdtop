<?php
require_once 'common/config.php';
if (!admin_logged()) { header('Location: login.php'); exit; }
// stats
$total_users = $mysqli->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$total_tournaments = $mysqli->query("SELECT COUNT(*) AS c FROM tournaments")->fetch_assoc()['c'];
$total_prize = $mysqli->query("SELECT IFNULL(SUM(prize_pool),0) AS s FROM tournaments WHERE status='Completed'")->fetch_assoc()['s'];
$total_revenue = $mysqli->query("SELECT IFNULL(SUM((t.entry_fee * (t.commission_percent/100)) * (SELECT COUNT(*) FROM participants p WHERE p.tournament_id = t.id)),0) revenue FROM tournaments t WHERE t.status='Completed'")->fetch_assoc()['revenue'];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
<?php include 'common/header.php'; ?>
<main class="p-4">
  <div class="grid grid-cols-2 gap-3">
    <div class="bg-gray-800 p-3 rounded"> <div class="text-xs">Total Users</div><div class="text-xl font-bold"><?php echo $total_users; ?></div></div>
    <div class="bg-gray-800 p-3 rounded"> <div class="text-xs">Total Tournaments</div><div class="text-xl font-bold"><?php echo $total_tournaments; ?></div></div>
    <div class="bg-gray-800 p-3 rounded"> <div class="text-xs">Prize Distributed</div><div class="text-xl font-bold"><?php echo rupee($total_prize); ?></div></div>
    <div class="bg-gray-800 p-3 rounded"> <div class="text-xs">Total Revenue</div><div class="text-xl font-bold"><?php echo rupee($total_revenue); ?></div></div>
  </div>
  <div class="mt-4">
    <a href="tournament.php" class="p-3 bg-indigo-600 rounded">Create New Tournament</a>
    <a href="user.php" class="p-3 bg-gray-800 rounded ml-2">Manage Users</a>
    <a href="setting.php" class="p-3 bg-gray-800 rounded ml-2">Settings</a>
  </div>
</main>
<?php include 'common/bottom.php'; ?>
</body>
</html>
