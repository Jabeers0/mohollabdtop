<?php
require_once __DIR__.'/common/config.php';
if (!is_logged()) { header('Location: login.php'); exit; }
$user = current_user($mysqli);
$msg='';

// Handle Join Now from the form (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_tournament'])) {
    $t_id = (int)$_POST['tournament_id'];
    // check tournament
    $stmt = $mysqli->prepare("SELECT id,entry_fee,status FROM tournaments WHERE id=?");
    $stmt->bind_param('i',$t_id);
    $stmt->execute();
    $t = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$t) { $msg = 'Tournament not found.'; }
    elseif ($t['status'] === 'Completed') { $msg = 'Tournament already completed.'; }
    else {
        // check already joined
        $stmt = $mysqli->prepare("SELECT id FROM participants WHERE user_id=? AND tournament_id=?");
        $stmt->bind_param('ii',$_SESSION['user_id'],$t_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows>0) { $msg = 'You have already joined this tournament.'; }
        else {
            // check balance
            $fee = (float)$t['entry_fee'];
            $stmt->close();
            $stmt = $mysqli->prepare("SELECT wallet_balance FROM users WHERE id=?");
            $stmt->bind_param('i',$_SESSION['user_id']);
            $stmt->execute();
            $bal = $stmt->get_result()->fetch_assoc()['wallet_balance'];
            $stmt->close();
            if ($bal < $fee) { $msg = 'Insufficient balance.'; }
            else {
                // deduct & insert participant & transaction - in transaction
                $mysqli->begin_transaction();
                try {
                    $newbal = $bal - $fee;
                    $u = $mysqli->prepare("UPDATE users SET wallet_balance=? WHERE id=?");
                    $u->bind_param('di',$newbal,$_SESSION['user_id']);
                    $u->execute();
                    $p = $mysqli->prepare("INSERT INTO participants (user_id,tournament_id) VALUES (?,?)");
                    $p->bind_param('ii',$_SESSION['user_id'],$t_id);
                    $p->execute();
                    $tr = $mysqli->prepare("INSERT INTO transactions (user_id,amount,type,description) VALUES (?,?,?,?)");
                    $desc = 'Entry fee for tournament #'.$t_id;
                    $tr->bind_param('idss',$_SESSION['user_id'],$fee,'debit',$desc);
                    $tr->execute();
                    $mysqli->commit();
                    $msg = 'Joined successfully!';
                } catch (Exception $e) {
                    $mysqli->rollback();
                    $msg = 'Error while joining: '.$e->getMessage();
                }
            }
        }
    }
}

// Fetch upcoming tournaments
$now = date('Y-m-d H:i:s');
$ts = $mysqli->query("SELECT * FROM tournaments WHERE status IN ('Upcoming','Live') ORDER BY match_time ASC");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Adept Play - Home</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
<?php include __DIR__.'/common/header.php'; ?>
<main class="p-4">
  <?php if($msg): ?>
    <div class="mb-3 p-3 rounded bg-green-800 text-green-200"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <h2 class="text-lg font-semibold mb-3">Upcoming Tournaments</h2>
  <div class="grid grid-cols-1 gap-3">
    <?php while($row=$ts->fetch_assoc()): ?>
      <div class="bg-gray-800 rounded-xl p-3">
        <div class="flex justify-between items-start">
          <div>
            <div class="font-bold text-sm"><?php echo htmlspecialchars($row['title']); ?></div>
            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($row['game_name']); ?> • <?php echo date('d M Y, h:i A', strtotime($row['match_time'])); ?></div>
          </div>
          <div class="text-right">
            <div class="text-sm font-semibold"><?php echo rupee($row['prize_pool']); ?></div>
            <div class="text-xs text-gray-400">Entry: <?php echo rupee($row['entry_fee']); ?></div>
          </div>
        </div>
        <div class="mt-3 flex gap-2">
          <form method="post" class="w-full">
            <input type="hidden" name="tournament_id" value="<?php echo $row['id']; ?>">
            <button name="join_tournament" class="w-full p-2 rounded bg-indigo-600">Join Now</button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</main>

<?php include __DIR__.'/common/bottom.php'; ?>

</body>
</html>
