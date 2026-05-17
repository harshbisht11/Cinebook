<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$active = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<aside class="admin-sidebar">
  <ul>
    <li><a href="index.php" class="<?= $active==='index'?'active':'' ?>">Dashboard</a></li>
    <li><a href="movies.php" class="<?= $active==='movies'?'active':'' ?>">Movies</a></li>
    <li><a href="theaters.php" class="<?= $active==='theaters'?'active':'' ?>">Theaters</a></li>
    <li><a href="shows.php" class="<?= $active==='shows'?'active':'' ?>">Shows</a></li>
    <li><a href="bookings.php" class="<?= $active==='bookings'?'active':'' ?>">Bookings</a></li>
    <li><a href="users.php" class="<?= $active==='users'?'active':'' ?>">Users</a></li>
    <li><a href="../index.php">← Back to site</a></li>
  </ul>
</aside>
