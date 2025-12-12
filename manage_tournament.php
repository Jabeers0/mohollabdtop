<?php
require_once 'common/config.php';
if (!admin_logged()) { header('Location: login.php'); exit; }
if (!isset($_GET['id'])) { header('Location: tournament.php'); exit; }
$id = (int)$_GET['id'];
$msg='';
$t = $mysqli->query("SELECT * FROM tournaments WHERE id=$id")->fetch_assoc();
// participants
$parts = $mysqli->query("SELECT p.id,p.user_id,u.username FROM participants p JOIN users u ON p.user_id=u.id WHERE p.tournament_id=$id");
if ($_SERVER['REQUEST_METHOD']=='POST') {
    if (isset($_POST['update_room'])) {
        $room = $_POST['room_id']; $pass = $_POST['room_password'];
        $u = $mysqli->prepare("UPDATE tournaments SET room_id=?, room_password=?, status='Live' WHERE id=?");
        $u->bind_param('ssi',$room,$pass,$id);
        if ($u->execute()) { $msg='Room updated.'; }
    } elseif (isset($_POST['declare_winner']) && isset($_POST['winner_id'])) {
        $winner_part = (int)$_POST['winner_id'];
        // get participant and user
        $p = $mysqli->query("SELECT * FROM participants WHERE id=$winner_part")->fetch_assoc();
        if ($p) {
            $winner_user = $p['user_id'];
            // give prize to winner - consider commission
            $prize = $t['prize_pool'];
            $commission = (int)$t['commission_percent'];
            $give = $prize * (1 - $commission/100);
            $mysqli->begin_transaction();
            try {
                // credit winner
                $stmt = $mysqli->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?");
                $stmt->bind_param('di',$give,$winner_user);
                $stmt->execute();
                $stmt = $mysqli->prepare("INSERT INTO transactions (user_id,amount,type,description) VALUES (?,?,?,?)");
                $desc = 'Tournament Prize for tournament #'.$id;
                $stmt->bind_param('idss',$winner_user,$give,'credit',$desc);
                $stmt->execute();
                // update participant results
                $mysqli->query("UPDATE participants SET result='Participated' WHERE tournament_id=$id");
                $mysqli->query("UPDATE participants SET result='Winner' WHERE id=$winner_part");
                // mark tournament completed
                $u = $mysqli->prepare("UPDATE tournaments SET status='Completed' WHERE id=?");
                $u->bind_param('i',$id); $u->execute();
                $mysqli->commit();
                $msg='Winner declared and prize distributed.';
            } catch (Exception $e) {
                $mysqli->rollback();
                $msg='Error: '.$e->getMessage();
            }
        }
    }
    // reload data
    $t = $mysqli->query("SELECT * FROM tournaments WHERE id=$id")->fetch_assoc();
    $parts = $mysqli->query("SELECT p.id,p.user_id,u.username FROM participants p JOIN users u ON p.user_id=u.id WHERE p.tournament_id=$id");
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Tournament</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
<?php include 'common/header.php'; ?>
<main class="p-4">
  <?php if($msg): ?><div class="mb-3 p-2 bg-green-800 rounded"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
  <h3 class="font-bold mb-2"><?php echo htmlspecialchars($t['title']); ?></h3>
  <div class="bg-gray-800 p-3 rounded mb-3">
    <form method="post" class="space-y-2">
      <input name="room_id" placeholder="Room ID" value="<?php echo htmlspecialchars($t['room_id']); ?>" class="w-full p-2 bg-gray-900 rounded" />
      <input name="room_password" placeholder="Room Password" value="<?php echo htmlspecialchars($t['room_password']); ?>" class="w-full p-2 bg-gray-900 rounded" />
      <button name="update_room" class="p-2 bg-indigo-600 rounded">Update Room & Set Live</button>
    </form>
  </div>

  <div class="bg-gray-800 p-3 rounded mb-3">
    <h4 class="font-bold mb-2">Participants</h4>
    <?php if($parts->num_rows==0): ?><div>No participants yet.</div><?php else: ?>
      <form method="post">
        <select name="winner_id" class="w-full p-2 bg-gray-900 rounded mb-2">
          <?php while($p=$parts->fetch_assoc()): ?>
            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['username']); ?></option>
          <?php endwhile; ?>
        </select>
        <button name="declare_winner" class="w-full p-2 bg-green-600 rounded">Declare Winner & Distribute Prize</button>
      </form>
    <?php endif; ?>
  </div>

</main>
<?php include 'common/bottom.php'; ?>
</body>
</html>
