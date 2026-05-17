<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT b.*, m.title, m.poster, s.show_date, s.show_time, t.name AS theater_name, u.name AS user_name FROM bookings b JOIN shows s ON b.show_id=s.id JOIN movies m ON s.movie_id=m.id JOIN theaters t ON s.theater_id=t.id JOIN users u ON b.user_id=u.id WHERE b.id=? AND (b.user_id=? OR ?='admin')");
$role = $_SESSION['role'];
$stmt->bind_param('iis', $id, $_SESSION['user_id'], $role);
$stmt->execute();
$bk = $stmt->get_result()->fetch_assoc();
if (!$bk) { http_response_code(404); die('Booking not found'); }
$pageTitle = 'Ticket';
require_once __DIR__ . '/includes/header.php';
?>
<div class="ticket">
  <h2>🎟 Your E-Ticket</h2>
  <div class="ticket-row"><span>Booking Code</span><strong><?= e($bk['booking_code']) ?></strong></div>
  <div class="ticket-row"><span>Movie</span><strong><?= e($bk['title']) ?></strong></div>
  <div class="ticket-row"><span>Theater</span><span><?= e($bk['theater_name']) ?></span></div>
  <div class="ticket-row"><span>Date</span><span><?= date('D, M j, Y', strtotime($bk['show_date'])) ?></span></div>
  <div class="ticket-row"><span>Time</span><span><?= date('g:i A', strtotime($bk['show_time'])) ?></span></div>
  <div class="ticket-row"><span>Seats</span><strong><?= e($bk['seats']) ?></strong></div>
  <div class="ticket-row"><span>Customer</span><span><?= e($bk['user_name']) ?></span></div>
  <div class="ticket-row"><span>Total Paid</span><strong>₹<?= number_format($bk['total_amount'],2) ?></strong></div>
  <div class="ticket-row"><span>Status</span><span style="color:var(--success)"><?= strtoupper($bk['status']) ?></span></div>
  <div style="text-align:center; margin-top:20px;">
    <a href="my_bookings.php" class="btn btn-secondary">My Bookings</a>
    <a href="index.php" class="btn">Book More</a>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
