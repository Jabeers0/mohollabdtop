<?php
require_once __DIR__.'/common/config.php';
$msg = '';
$tab = 'login';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $tab = 'login';
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        if (!$username || !$password) {
            $msg = 'Fill all fields';
        } else {
            $stmt = $mysqli->prepare("SELECT id,password FROM users WHERE username=?");
            $stmt->bind_param('s',$username);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($res && password_verify($password,$res['password'])) {
                $_SESSION['user_id'] = $res['id'];
                header('Location: index.php'); exit;
            } else {
                $msg = 'Invalid credentials';
            }
        }
    } elseif (isset($_POST['signup'])) {
        $tab = 'signup';
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        if (!$username || !$email || !$password) {
            $msg = 'Fill all fields';
        } else {
            // insert
            $hash = password_hash($password,PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO users (username,email,password,wallet_balance) VALUES (?,?,?,?)");
            $zero = 0.00;
            $stmt->bind_param('sssd',$username,$email,$hash,$zero);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $stmt->close();
                header('Location: index.php'); exit;
            } else {
                $msg = 'Signup failed: '. $mysqli->error;
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Adept Play - Login</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>body{background:#0f172a;color:#e6eef8} .no-select{user-select:none}</style>
</head>
<body class="min-h-screen flex flex-col">
<div class="p-4">
  <div class="max-w-md mx-auto bg-gray-900 rounded-2xl p-4 shadow-lg">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-bold">Adept Play</h1>
      <div class="text-xs text-gray-400">Play • Compete • Win</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-3">
      <div class="flex">
        <button onclick="show('login')" id="b-login" class="w-1/2 py-2 rounded-lg">Login</button>
        <button onclick="show('signup')" id="b-signup" class="w-1/2 py-2 rounded-lg">Sign Up</button>
      </div>
      <?php if($msg): ?>
        <div class="mt-3 text-sm text-red-400"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>
      <div id="login" class="mt-3">
        <form method="post">
          <input name="username" placeholder="Username" class="w-full p-3 rounded bg-gray-900 mb-2" />
          <input name="password" type="password" placeholder="Password" class="w-full p-3 rounded bg-gray-900 mb-2" />
          <button name="login" class="w-full p-3 bg-indigo-600 rounded text-white">Login</button>
        </form>
      </div>
      <div id="signup" class="mt-3 hidden">
        <form method="post">
          <input name="username" placeholder="Username" class="w-full p-3 rounded bg-gray-900 mb-2" />
          <input name="email" placeholder="Email" class="w-full p-3 rounded bg-gray-900 mb-2" />
          <input name="password" type="password" placeholder="Password" class="w-full p-3 rounded bg-gray-900 mb-2" />
          <button name="signup" class="w-full p-3 bg-green-600 rounded text-white">Sign Up</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function show(t){
  document.getElementById('login').classList.toggle('hidden', t!=='login');
  document.getElementById('signup').classList.toggle('hidden', t!=='signup');
}
// default show
show('<?php echo $tab;?>');
// disable right click and selection
document.addEventListener('contextmenu', e=>e.preventDefault());
document.addEventListener('selectstart', e=>e.preventDefault());
</script>
</body>
</html>
