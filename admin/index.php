<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$stats = [
  'movies' => $conn->query("SELECT COUNT(*) c FROM movies")->fetch_assoc()['c'],
  'shows' => $conn->query("SELECT COUNT(*) c FROM shows")->fetch_assoc()['c'],
  'bookings' => $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='confirmed'")->fetch_assoc()['c'],
  'users' => $conn->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch_assoc()['c'],
  'revenue' => $conn->query("SELECT COALESCE(SUM(total_amount),0) s FROM bookings WHERE status='confirmed'")->fetch_assoc()['s'],
];
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div>
    <h2 class="section-title">Dashboard</h2>
    <div class="stats-grid">
      <div class="stat-card"><h3>Movies</h3><div class="value"><?= $stats['movies'] ?></div></div>
      <div class="stat-card"><h3>Shows</h3><div class="value"><?= $stats['shows'] ?></div></div>
      <div class="stat-card"><h3>Bookings</h3><div class="value"><?= $stats['bookings'] ?></div></div>
      <div class="stat-card"><h3>Users</h3><div class="value"><?= $stats['users'] ?></div></div>
      <div class="stat-card"><h3>Revenue</h3><div class="value">₹<?= number_format($stats['revenue']) ?></div></div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
