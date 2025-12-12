<?php
require_once 'common/config.php';
if (!is_logged()) { header('Location: login.php'); exit; }
$user = current_user($mysqli);
$msg='';
if ($_SERVER['REQUEST_METHOD']=='POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        if ($username && $email) {
            $stmt = $mysqli->prepare("UPDATE users SET username=?, email=? WHERE id=?");
            $stmt->bind_param('ssi',$username,$email,$_SESSION['user_id']);
            if ($stmt->execute()) { $msg='Profile updated.'; }
            else { $msg='Update failed.'; }
        }
    } elseif (isset($_POST['change_password'])) {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        $stmt = $mysqli->prepare("SELECT password FROM users WHERE id=?");
        $stmt->bind_param('i',$_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res && password_verify($old,$res['password'])) {
            $h = password_hash($new,PASSWORD_DEFAULT);
            $up = $mysqli->prepare("UPDATE users SET password=? WHERE id=?");
            $up->bind_param('si',$h,$_SESSION['user_id']);
            if ($up->execute()) $msg='Password changed.'; else $msg='Failed to change.';
        } else $msg='Old password incorrect.';
    } elseif (isset($_POST['logout'])) {
        session_destroy(); header('Location: login.php'); exit;
    }
    $user = current_user($mysqli);
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Profile</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
<?php include 'common/header.php'; ?>
<main class="p-4">
  <?php if($msg): ?><div class="mb-3 p-3 rounded bg-green-800"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

  <form method="post" class="bg-gray-800 p-4 rounded-xl mb-4">
    <h3 class="font-bold mb-2">Edit Profile</h3>
    <input name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full p-2 bg-gray-900 mb-2 rounded" />
    <input name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-2 bg-gray-900 mb-2 rounded" />
    <button name="update_profile" class="w-full p-2 bg-indigo-600 rounded">Save</button>
  </form>

  <form method="post" class="bg-gray-800 p-4 rounded-xl mb-4">
    <h3 class="font-bold mb-2">Change Password</h3>
    <input name="old_password" type="password" placeholder="Old Password" class="w-full p-2 bg-gray-900 mb-2 rounded" />
    <input name="new_password" type="password" placeholder="New Password" class="w-full p-2 bg-gray-900 mb-2 rounded" />
    <button name="change_password" class="w-full p-2 bg-green-600 rounded">Change Password</button>
  </form>

  <form method="post">
    <button name="logout" class="w-full p-2 bg-red-600 rounded">Logout</button>
  </form>
</main>
<?php include 'common/bottom.php'; ?>
</body>
</html>
