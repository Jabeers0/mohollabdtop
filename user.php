<?php
require_once 'common/config.php';
if (!admin_logged()) { header('Location: login.php'); exit; }
if (isset($_GET['block']) && is_numeric($_GET['block'])) {
    // simple block by deleting or set a flag - here we just delete for demo
    $id = (int)$_GET['block']; $mysqli->query("DELETE FROM users WHERE id=$id");
    header('Location: user.php'); exit;
}
$users = $mysqli->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Users</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
<?php include 'common/header.php'; ?>
<main class="p-4">
  <div class="space-y-2">
    <?php while($u=$users->fetch_assoc()): ?>
      <div class="bg-gray-800 p-3 rounded flex justify-between">
        <div>
          <div class="font-bold"><?php echo htmlspecialchars($u['username']); ?></div>
          <div class="text-xs text-gray-400"><?php echo htmlspecialchars($u['email']); ?></div>
        </div>
        <div class="text-right">
          <div class="font-bold"><?php echo rupee($u['wallet_balance']); ?></div>
          <div class="flex gap-2 mt-2">
            <a href="?block=<?php echo $u['id']; ?>" class="p-2 bg-red-600 rounded text-xs">Block</a>
            <a href="view_user.php?id=<?php echo $u['id']; ?>" class="p-2 bg-gray-700 rounded text-xs">View</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</main>
<?php include 'common/bottom.php'; ?>
</body>
</html>
