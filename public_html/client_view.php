<?php
require_once __DIR__ . '/../config.php';

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch clients
$clients_result = $conn->query("SELECT * FROM clients ORDER BY event_date DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Client View - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-4">
    <h1>Daftar Klien dan Rundown Acara</h1>
    <?php if ($clients_result && $clients_result->num_rows > 0): ?>
        <?php foreach ($clients_result as $client): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h5><?= htmlspecialchars($client['groom_name']) ?> &amp; <?= htmlspecialchars($client['bride_name']) ?></h5>
                    <small>Tanggal: <?= htmlspecialchars($client['event_date']) ?> | Waktu: <?= htmlspecialchars($client['event_time']) ?> | Lokasi: <?= htmlspecialchars($client['event_location']) ?></small>
                </div>
                <div class="card-body">
                    <p><strong>Tema Acara:</strong> <?= htmlspecialchars($client['event_theme']) ?></p>
                    <div class="accordion" id="rundownAccordion<?= $client['id'] ?>">
                        <?php
                        // Fetch rundown segments for this client
                        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                        $stmt = $conn->prepare("SELECT * FROM rundown_segments WHERE client_id = ? ORDER BY start_time ASC");
                        $stmt->bind_param("i", $client['id']);
                        $stmt->execute();
                        $segments_result = $stmt->get_result();
                        ?>
                        <?php if ($segments_result && $segments_result->num_rows > 0): ?>
                            <?php foreach ($segments_result as $segment): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?= $segment['id'] ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $segment['id'] ?>" aria-expanded="false" aria-controls="collapse<?= $segment['id'] ?>">
                                            <?= htmlspecialchars($segment['segment_name']) ?> (<?= htmlspecialchars($segment['start_time']) ?> - <?= htmlspecialchars($segment['end_time']) ?>)
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $segment['id'] ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $segment['id'] ?>" data-bs-parent="#rundownAccordion<?= $client['id'] ?>">
                                        <div class="accordion-body">
                                            <p><strong>Catatan:</strong> <?= nl2br(htmlspecialchars($segment['notes'])) ?></p>
                                            <?php
                                            // Fetch jobdesk for this segment
                                            $conn2 = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                            $stmt2 = $conn2->prepare("SELECT job_description, assigned_to FROM rundown_jobs WHERE segment_id = ?");
                                            $stmt2->bind_param("i", $segment['id']);
                                            $stmt2->execute();
                                            $jobs_result = $stmt2->get_result();
                                            ?>
                                            <?php if ($jobs_result && $jobs_result->num_rows > 0): ?>
                                                <ul>
                                                    <?php foreach ($jobs_result as $job): ?>
                                                        <li><?= htmlspecialchars($job['job_description']) ?> - <em><?= htmlspecialchars($job['assigned_to']) ?></em></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p>Tidak ada jobdesk untuk segmen ini.</p>
                                            <?php endif; ?>
                                            <?php $stmt2->close(); $conn2->close(); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Tidak ada rundown untuk klien ini.</p>
                        <?php endif; ?>
                        <?php $stmt->close(); $conn->close(); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Tidak ada data klien.</p>
    <?php endif; ?>
</div>
</body>
</html>
