<?php
require_once 'common/config.php';
if (!is_logged()) { header('Location: login.php'); exit; }
$user = current_user($mysqli);
// fetch transactions
$stmt = $mysqli->prepare("SELECT * FROM transactions WHERE user_id=? ORDER BY created_at DESC LIMIT 100");
$stmt->bind_param('i',$_SESSION['user_id']);
$stmt->execute();
$transactions = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Wallet</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
<?php include 'common/header.php'; ?>
<main class="p-4">
  <div class="bg-gradient-to-r from-indigo-700 to-purple-700 p-4 rounded-xl mb-4">
    <div class="text-xs text-gray-200">Current Balance</div>
    <div class="text-2xl font-bold"><?php echo rupee($user['wallet_balance']); ?></div>
    <div class="mt-3 flex gap-2">
      <button class="flex-1 p-2 bg-green-600 rounded">Add Money</button>
      <button class="flex-1 p-2 bg-gray-800 rounded">Withdraw</button>
    </div>
  </div>

  <h3 class="text-sm mb-2">Transaction History</h3>
  <div class="space-y-2">
    <?php while($t=$transactions->fetch_assoc()): ?>
      <div class="bg-gray-800 p-3 rounded flex justify-between">
        <div>
          <div class="text-sm"><?php echo htmlspecialchars($t['description']); ?></div>
          <div class="text-xs text-gray-400"><?php echo date('d M Y, h:i A', strtotime($t['created_at'])); ?></div>
        </div>
        <div class="text-right">
          <div class="font-bold"><?php echo ($t['type']=='credit'? '+':'-') . rupee($t['amount']); ?></div>
          <div class="text-xs text-gray-400"><?php echo $t['type']; ?></div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</main>
<?php include 'common/bottom.php'; ?>
</body>
</html>
