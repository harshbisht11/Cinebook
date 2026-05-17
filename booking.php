<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$show_id = (int)($_GET['show_id'] ?? 0);
$stmt = $conn->prepare("SELECT s.*, m.title, m.poster, t.name AS theater_name, t.total_seats FROM shows s JOIN movies m ON s.movie_id=m.id JOIN theaters t ON s.theater_id=t.id WHERE s.id=?");
$stmt->bind_param('i', $show_id);
$stmt->execute();
$show = $stmt->get_result()->fetch_assoc();
if (!$show) { http_response_code(404); die('Show not found'); }

$taken = bookedSeats($conn, $show_id);

// Handle booking POST
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $seats = $_POST['seats'] ?? '';
    $seatArr = array_filter(array_map('trim', explode(',', $seats)));
    if (empty($seatArr)) $error = 'Please select at least one seat';
    elseif (array_intersect($seatArr, $taken)) $error = 'Some seats are already booked';
    else {
        $total = count($seatArr) * $show['price'];
        $seatStr = implode(',', $seatArr);
        $code = generateBookingCode();
        $cnt = count($seatArr);
        $ins = $conn->prepare("INSERT INTO bookings (user_id, show_id, seats, total_seats, total_amount, booking_code) VALUES (?,?,?,?,?,?)");
        $ins->bind_param('iisids', $_SESSION['user_id'], $show_id, $seatStr, $cnt, $total, $code);
        $ins->execute();
        flash('Booking confirmed! Code: ' . $code);
        header('Location: ticket.php?id=' . $ins->insert_id); exit;
    }
}

$pageTitle = 'Book - ' . $show['title'];
require_once __DIR__ . '/includes/header.php';

// 6 rows A-F, 10 seats each = 60
$rows = ['A','B','C','D','E','F'];
$cols = 10;
?>
<h2 class="section-title">Select Your Seats</h2>
<div style="background: var(--surface); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
  <h3><?= e($show['title']) ?></h3>
  <p style="color:var(--muted)"><?= e($show['theater_name']) ?> • <?= date('D, M j', strtotime($show['show_date'])) ?> at <?= date('g:i A', strtotime($show['show_time'])) ?> • ₹<?= number_format($show['price']) ?>/seat</p>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="screen">SCREEN</div>

<div class="seat-legend">
  <span><span class="seat"></span> Available</span>
  <span><span class="seat selected"></span> Selected</span>
  <span><span class="seat taken"></span> Booked</span>
</div>

<div class="seats-container">
  <?php foreach ($rows as $r): ?>
    <div class="seat-row">
      <span class="row-label"><?= $r ?></span>
      <?php for ($c=1; $c<=$cols; $c++):
        $id = $r.$c;
        $isTaken = in_array($id, $taken);
      ?>
        <div class="seat<?= $isTaken ? ' taken' : '' ?>" data-seat="<?= $id ?>"><?= $c ?></div>
      <?php endfor; ?>
    </div>
  <?php endforeach; ?>
</div>

<form method="post" id="bookForm">
  <?= csrfField() ?>
  <input type="hidden" name="seats" id="seatsInput">
  <div class="booking-summary">
    <p>Selected Seats: <strong id="selectedList">None</strong></p>
    <p>Count: <span id="count">0</span> × ₹<?= number_format($show['price']) ?></p>
    <p class="total">Total: ₹<span id="total">0</span></p>
    <button class="btn" type="submit" id="payBtn" disabled style="margin-top:14px;">Confirm Booking</button>
  </div>
</form>

<script>
const price = <?= (float)$show['price'] ?>;
const selected = new Set();
document.querySelectorAll('.seat:not(.taken)').forEach(el => {
  el.addEventListener('click', () => {
    const s = el.dataset.seat;
    if (selected.has(s)) { selected.delete(s); el.classList.remove('selected'); }
    else { selected.add(s); el.classList.add('selected'); }
    update();
  });
});
function update() {
  const arr = [...selected];
  document.getElementById('selectedList').textContent = arr.length ? arr.join(', ') : 'None';
  document.getElementById('count').textContent = arr.length;
  document.getElementById('total').textContent = (arr.length * price).toFixed(2);
  document.getElementById('seatsInput').value = arr.join(',');
  document.getElementById('payBtn').disabled = arr.length === 0;
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
