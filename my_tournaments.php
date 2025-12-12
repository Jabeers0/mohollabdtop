<?php
require_once 'common/config.php';
if (!is_logged()) { header('Location: login.php'); exit; }
$user = current_user($mysqli);
$tab = isset($_GET['tab']) && $_GET['tab']=='completed' ? 'completed':'active';

if ($tab=='active') {
    $stmt = $mysqli->prepare("SELECT p.*, t.title, t.match_time, t.room_id, t.room_password, t.status FROM participants p JOIN tournaments t ON p.tournament_id=t.id WHERE p.user_id=? AND t.status!='Completed' ORDER BY t.match_time ASC");
    $stmt->bind_param('i',$_SESSION['user_id']);
} else {
    $stmt = $mysqli->prepare("SELECT p.*, t.title, t.match_time, t.status, p.result FROM participants p JOIN tournaments t ON p.tournament_id=t.id WHERE p.user_id=? AND t.status='Completed' ORDER BY t.match_time DESC");
    $stmt->bind_param('i',$_SESSION['user_id']);
}
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>My Tournaments</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
<?php include 'common/header.php'; ?>
<main class="p-4">
  <div class="flex gap-2 mb-4">
    <a href="?tab=active" class="flex-1 p-2 rounded-lg text-center <?php echo $tab=='active'? 'bg-indigo-600':'bg-gray-800'; ?>">Upcoming / Live</a>
    <a href="?tab=completed" class="flex-1 p-2 rounded-lg text-center <?php echo $tab=='completed'? 'bg-indigo-600':'bg-gray-800'; ?>">Completed</a>
  </div>

  <?php if($res->num_rows==0): ?>
    <div class="p-4 bg-gray-800 rounded">No tournaments found.</div>
  <?php else: ?>
    <div class="space-y-3">
      <?php while($r=$res->fetch_assoc()): ?>
        <div class="bg-gray-800 p-3 rounded-xl">
          <div class="flex justify-between">
            <div>
              <div class="font-bold"><?php echo htmlspecialchars($r['title']); ?></div>
              <div class="text-xs text-gray-400"><?php echo date('d M Y, h:i A', strtotime($r['match_time'])); ?></div>
            </div>
            <div class="text-right">
              <?php if($r['status']!='Completed'): ?>
                <div class="text-xs text-green-300"><?php echo htmlspecialchars($r['status']); ?></div>
                <?php if($r['room_id']): ?>
                  <div class="text-xs">Room: <span class="font-semibold"><?php echo htmlspecialchars($r['room_id']); ?></span></div>
                  <div class="text-xs">Pass: <span class="font-semibold"><?php echo htmlspecialchars($r['room_password']); ?></span></div>
                <?php endif; ?>
              <?php else: ?>
                <div class="text-xs text-gray-300">Result: <?php echo htmlspecialchars($r['result']); ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</main>
<?php include 'common/bottom.php'; ?>
</body>
</html>
