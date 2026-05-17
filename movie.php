<?php
require_once __DIR__ . '/includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM movies WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
if (!$movie) { http_response_code(404); die('Movie not found'); }
$pageTitle = $movie['title'];

// Get shows grouped by date
$stmt = $conn->prepare("SELECT s.*, t.name AS theater_name, t.location FROM shows s JOIN theaters t ON s.theater_id=t.id WHERE s.movie_id=? AND s.show_date >= CURDATE() ORDER BY s.show_date, s.show_time");
$stmt->bind_param('i', $id);
$stmt->execute();
$shows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$grouped = [];
foreach ($shows as $s) { $grouped[$s['show_date']][] = $s; }

require_once __DIR__ . '/includes/header.php';
?>
<div class="movie-detail">
  <img src="<?= e($movie['poster']) ?>" alt="<?= e($movie['title']) ?>" onerror="this.src='https://via.placeholder.com/300x450/1a1a2e/e50914?text=<?= urlencode($movie['title']) ?>'">
  <div>
    <h1><?= e($movie['title']) ?></h1>
    <div class="detail-meta">
      <span class="rating">★ <?= number_format($movie['rating'],1) ?></span>
      <span><?= e($movie['genre']) ?></span>
      <span><?= e($movie['language']) ?></span>
      <span><?= (int)$movie['duration'] ?> min</span>
      <span>Released: <?= e($movie['release_date']) ?></span>
    </div>
    <p style="line-height:1.7; color: #cfcfd9;"><?= nl2br(e($movie['description'])) ?></p>
    <?php if ($movie['trailer_url']): ?>
      <a class="btn btn-secondary" target="_blank" href="<?= e($movie['trailer_url']) ?>" style="margin-top:16px;">▶ Watch Trailer</a>
    <?php endif; ?>
  </div>
</div>

<div class="shows-section">
  <h2 class="section-title">Showtimes</h2>
  <?php if (empty($grouped)): ?>
    <p style="color: var(--muted);">No shows available right now.</p>
  <?php else: ?>
    <?php foreach ($grouped as $date => $list): ?>
      <div class="show-date-group">
        <div class="show-date"><?= date('l, F j, Y', strtotime($date)) ?></div>
        <div class="shows-grid">
          <?php foreach ($list as $s): ?>
            <a class="show-btn" href="booking.php?show_id=<?= $s['id'] ?>">
              <strong><?= date('g:i A', strtotime($s['show_time'])) ?></strong>
              <small><?= e($s['theater_name']) ?> • ₹<?= number_format($s['price']) ?></small>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
