<?php
require_once 'common/config.php';
if (!admin_logged()) { header('Location: login.php'); exit; }
$msg='';
if ($_SERVER['REQUEST_METHOD']=='POST'){
    if (isset($_POST['update_admin'])){
        $u = $_POST['username'];
        $p = $_POST['password'];
        if ($p) $p = password_hash($p,PASSWORD_DEFAULT);
        if ($p) $stmt = $mysqli->prepare("UPDATE admin SET username=?, password=? WHERE id=?");
        else $stmt = $mysqli->prepare("UPDATE admin SET username=? WHERE id=?");
        if ($p) $stmt->bind_param('ssi',$u,$p,$_SESSION['admin_id']); else $stmt->bind_param('si',$u,$_SESSION['admin_id']);
        if ($stmt->execute()) $msg='Updated.'; else $msg='Failed.';
    }
}
$admin = $mysqli->query("SELECT username FROM admin WHERE id=".(int)$_SESSION['admin_id'])->fetch_assoc();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Settings</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
<?php include 'common/header.php'; ?>
<main class="p-4">
  <?php if($msg): ?><div class="mb-3 p-2 bg-green-800 rounded"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
  <form method="post" class="bg-gray-800 p-3 rounded">
    <input name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" class="w-full p-2 mb-2 bg-gray-900 rounded" />
    <input name="password" placeholder="New Password (leave blank to keep)" class="w-full p-2 mb-2 bg-gray-900 rounded" />
    <button name="update_admin" class="w-full p-2 bg-indigo-600 rounded">Update</button>
  </form>
</main>
<?php include 'common/bottom.php'; ?>
</body>
</html>
