<?php
require_once 'common/config.php';
if (!admin_logged()) { header('Location: login.php'); exit; }
$msg='';
if ($_SERVER['REQUEST_METHOD']=='POST') {
    if (isset($_POST['create_tournament'])) {
        $title = $_POST['title']; $game = $_POST['game_name']; $entry = (float)$_POST['entry_fee']; $prize = (float)$_POST['prize_pool']; $match_time = $_POST['match_time']; $commission = (int)$_POST['commission_percent'];
        $stmt = $mysqli->prepare("INSERT INTO tournaments (title,game_name,entry_fee,prize_pool,match_time,commission_percent) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('ssddsi',$title,$game,$entry,$prize,$match_time,$commission);
        if ($stmt->execute()) $msg='Tournament created.'; else $msg='Error creating.';
    } elseif (isset($_POST['delete']) && isset($_POST['tournament_id'])) {
        $id = (int)$_POST['tournament_id'];
        $d = $mysqli->prepare("DELETE FROM tournaments WHERE id=?"); $d->bind_param('i',$id); $d->execute(); $msg='Deleted.';
    }
}
$list = $mysqli->query("SELECT * FROM tournaments ORDER BY created_at DESC");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Tournaments</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
<?php include 'common/header.php'; ?>
<main class="p-4">
  <?php if($msg): ?><div class="mb-3 p-2 bg-green-800 rounded"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
  <form method="post" class="bg-gray-800 p-3 rounded mb-4">
    <h3 class="font-bold mb-2">Create Tournament</h3>
    <input name="title" placeholder="Title" class="w-full p-2 mb-2 bg-gray-900 rounded" />
    <input name="game_name" placeholder="Game Name" class="w-full p-2 mb-2 bg-gray-900 rounded" />
    <input name="entry_fee" placeholder="Entry Fee" class="w-full p-2 mb-2 bg-gray-900 rounded" />
    <input name="prize_pool" placeholder="Prize Pool" class="w-full p-2 mb-2 bg-gray-900 rounded" />
    <input name="match_time" type="datetime-local" class="w-full p-2 mb-2 bg-gray-900 rounded" />
    <input name="commission_percent" placeholder="Commission %" class="w-full p-2 mb-2 bg-gray-900 rounded" />
    <button name="create_tournament" class="w-full p-2 bg-indigo-600 rounded">Create</button>
  </form>
  <div class="space-y-2">
    <?php while($r=$list->fetch_assoc()): ?>
      <div class="bg-gray-800 p-3 rounded flex justify-between">
        <div>
          <div class="font-bold"><?php echo htmlspecialchars($r['title']); ?></div>
          <div class="text-xs text-gray-400"><?php echo htmlspecialchars($r['game_name']); ?> • <?php echo date('d M Y, h:i A',strtotime($r['match_time'])); ?></div>
        </div>
        <div class="flex items-center gap-2">
          <a href="manage_tournament.php?id=<?php echo $r['id']; ?>" class="p-2 bg-blue-600 rounded">Manage</a>
          <form method="post" onsubmit="return confirm('Delete?')">
            <input type="hidden" name="tournament_id" value="<?php echo $r['id']; ?>">
            <button name="delete" class="p-2 bg-red-600 rounded">Delete</button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</main>
<?php include 'common/bottom.php'; ?>
</body>
</html>
