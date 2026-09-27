<h2>Radiology &mdash; pending scan orders</h2>
<table><tr><th>Barcode</th><th>Scan</th><th>Patient</th><th>Requested</th><th></th></tr>
<?php foreach ($requests as $r): ?>
<tr><td><b><?= e($r['barcode']) ?></b></td><td><?= e($r['scan_type']) ?> <?= e($r['body_part']) ?></td>
    <td><?= e($r['patient_name']) ?></td><td><?= e($r['request_datetime']) ?></td>
    <td><a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=rad_upload&rid=<?= $r['rad_request_id'] ?>">Upload scan</a></td></tr>
<?php endforeach; ?></table>
