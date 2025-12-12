<?php
require_once __DIR__ . '/config.php';
$user = is_logged() ? current_user($mysqli) : null;
?>
<header class="bg-gray-900 text-gray-100 p-4 flex items-center justify-between select-none">
  <div class="flex items-center gap-3">
    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center">
      <i class="fas fa-gamepad"></i>
    </div>
    <div>
      <div class="text-sm font-semibold">Adept Play</div>
      <div class="text-xs text-gray-400">Mobile Tournaments</div>
    </div>
  </div>
  <div class="text-right">
    <div class="text-xs text-gray-400">Wallet</div>
    <div class="text-sm font-bold"><?php echo isset($user) ? rupee($user['wallet_balance']) : '—'; ?></div>
  </div>
</header>

<script>
// Disable text selection, right click and zoom
document.documentElement.style.zoom = '100%';
document.addEventListener('contextmenu', e => e.preventDefault());
document.addEventListener('selectstart', e => e.preventDefault());
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && (e.key === '+' || e.key === '-' || e.key === '=')) e.preventDefault();
    if (e.ctrlKey && e.key === '0') e.preventDefault();
});
</script>