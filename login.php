<?php
require_once 'common/config.php';
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $u = trim($_POST['username']); $p = $_POST['password'];
    if ($u && $p) {
        $stmt = $mysqli->prepare("SELECT id,password FROM admin WHERE username=?");
        $stmt->bind_param('s',$u);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res && password_verify($p,$res['password'])) { $_SESSION['admin_id']=$res['id']; header('Location: index.php'); exit;} else $msg='Invalid';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center">
  <div class="max-w-md w-full p-4 bg-gray-800 rounded-xl">
    <h2 class="font-bold mb-2">Admin Login</h2>
    <?php if($msg): ?><div class="mb-2 text-red-400"><?php echo $msg; ?></div><?php endif; ?>
    <form method="post">
      <input name="username" placeholder="Username" class="w-full p-2 mb-2 bg-gray-900 rounded" />
      <input name="password" type="password" placeholder="Password" class="w-full p-2 mb-2 bg-gray-900 rounded" />
      <button class="w-full p-2 bg-indigo-600 rounded">Login</button>
    </form>
  </div>
</body>
</html>
