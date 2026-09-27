<!DOCTYPE html><html><head><meta charset="utf-8"><title>Discharge Summary</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__.'/../../assets/css/style.css') ?>"></head><body>
<main>
<h2 style="text-align:center;font-family:Georgia">DISCHARGE SUMMARY</h2>
<p style="text-align:center" class="note">Hospital Coordination System &middot; <?= e($a['dept_name']) ?> Ward</p>
<?php if ($preview): ?>
<div class="flash error no-print">Draft &mdash; this patient has <b>not</b> been discharged yet. Review the summary, then confirm below.</div>
<?php endif; ?>
<table>
<tr><th>Patient</th><td><?= e($a['patient_name']) ?> (<?= e($a['patient_id']) ?>)</td><th>Bed</th><td><?= e($a['bed_number']) ?></td></tr>
<tr><th>Admitted</th><td><?= e($a['admit_date']) ?></td><th>Discharged</th><td><?= $preview ? '<i>on confirmation</i>' : e($a['discharge_date']) ?></td></tr>
<tr><th>Final diagnosis</th><td colspan="3"><?= e($a['diagnosis']) ?></td></tr>
</table>
<h3>Course in ward (daily notes)</h3>
<table><tr><th>Date</th><th>Progress</th><th>Impression</th><th>Plan</th></tr>
<?php foreach ($notes as $n): ?>
<tr><td><?= e($n['note_date']) ?></td><td><?= e($n['progress']) ?></td><td><?= e($n['diagnosis']) ?></td><td><?= e($n['plan_note']) ?></td></tr>
<?php endforeach; ?></table>
<h3>Medications given</h3>
<table><tr><th>Time</th><th>Drug &middot; dose</th><th>Route</th></tr>
<?php foreach ($meds as $m): ?>
<tr><td><?= e($m['admin_time']) ?></td><td><?= e($m['drug_dose']) ?></td><td><?= e($m['route']) ?></td></tr>
<?php endforeach; ?></table>
<h3>Key investigation results</h3>
<table><tr><th>Barcode</th><th>Test</th><th>Finding</th></tr>
<?php foreach ($labs as $l): if (($l['accept_status'] ?? '') !== 'Accepted') continue; ?>
<tr><td><?= e($l['barcode']) ?></td><td><?= e($l['test_name']) ?></td><td><?= $l['critical_flag']?'<span class="critical">CRITICAL</span> ':'' ?><?= e($l['finding']) ?></td></tr>
<?php endforeach; ?></table>
<?php if ($preview): ?>
<form class="no-print actions-row" method="post" action="<?= BASE_URL ?>/index.php?page=discharge">
  <?= csrf_field() ?>
  <input type="hidden" name="aid" value="<?= $a['admission_id'] ?>">
  <button>Confirm discharge</button>
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=ward_note&aid=<?= $a['admission_id'] ?>">Cancel</a>
</form>
<?php else: ?>
<p class="no-print actions-row">
  <button onclick="window.print()">Print</button>
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=ward">Back to ward</a>
</p>
<p class="note no-print">To save a PDF, choose &ldquo;Save as PDF&rdquo; as the printer in the print window.</p>
<?php endif; ?>
</main></body></html>
