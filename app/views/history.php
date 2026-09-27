<?php $role = Auth::role(); ?>
<h2><?= e($p['patient_id']) ?> &middot; <?= e($p['name']) ?> &middot; <?= e($p['gender']) ?><?= $p['dob'] ? ' &middot; DOB '.e($p['dob']) : '' ?></h2>
<p class="no-print">
  <?php if ($role==='OPDDoctor'): ?><a class="btn" href="<?= BASE_URL ?>/index.php?page=opd_form&pid=<?= e($p['patient_id']) ?>">New OPD consultation</a><?php endif; ?>
  <?php if ($role==='ClinicDoctor'): ?><a class="btn" href="<?= BASE_URL ?>/index.php?page=clinic_form&pid=<?= e($p['patient_id']) ?>">New clinic visit</a><?php endif; ?>
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=orders&pid=<?= e($p['patient_id']) ?>">New orders (prescribe / request tests)</a>
</p>
<h3>Encounters (newest first)</h3>
<table><tr><th>Date</th><th>Type</th><th>Doctor</th><th>Diagnosis</th><th>Plan</th></tr>
<?php foreach ($encounters as $enc): ?>
<tr><td><?= e($enc['created_at']) ?></td><td><b><?= e($enc['type']) ?></b></td><td><?= e($enc['doctor_name']) ?></td>
    <td><?= e($enc['diagnosis']) ?></td><td><?= e(truncate($enc['plan_note'] ?? '', 70)) ?></td></tr>
<?php endforeach; ?></table>
<h3>Lab results</h3>
<table><tr><th>Barcode</th><th>Test</th><th>Finding</th><th>Status</th></tr>
<?php foreach ($labs as $l): ?>
<tr><td><b><?= e($l['barcode']) ?></b></td><td><?= e($l['test_name']) ?></td><td><?= e(truncate($l['finding'] ?? '', 90)) ?></td>
    <td><?= $l['critical_flag'] ? '<span class="critical">CRITICAL</span> ' : '' ?><?= e($l['accept_status'] ?? 'Requested') ?></td></tr>
<?php endforeach; ?></table>
<h3>Radiology images</h3>
<table><tr><th>Barcode</th><th>Scan</th><th>Image</th><th></th></tr>
<?php foreach ($rads as $r): ?>
<tr><td><b><?= e($r['barcode']) ?></b></td><td><?= e($r['scan_type']) ?> <?= e($r['body_part']) ?></td>
    <td><a href="<?= BASE_URL.'/'.e($r['file_path']) ?>" target="_blank">view image</a></td>
    <td><?= $r['critical_flag'] ? '<span class="critical">CRITICAL</span>' : '' ?></td></tr>
<?php endforeach; ?></table>
<h3>Prescriptions</h3>
<table><tr><th>Date</th><th>Drug</th><th>Status</th></tr>
<?php foreach ($prescriptions as $pr): ?>
<tr><td><?= e($pr['prescribed_at']) ?></td><td><?= e($pr['drug']) ?> <?= e($pr['dose']) ?> <?= e($pr['frequency']) ?> <?= e($pr['duration']) ?></td><td><?= e($pr['status']) ?></td></tr>
<?php endforeach; ?></table>
