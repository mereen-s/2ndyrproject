<!DOCTYPE html><html><head><meta charset="utf-8"><title>Clinic Visit Summary</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__.'/../../assets/css/style.css') ?>"></head><body>
<main>
<h2 style="text-align:center;font-family:Georgia">CLINIC VISIT SUMMARY</h2>
<p style="text-align:center" class="note">Hospital Coordination System</p>
<table>
<tr><th>Patient</th><td><?= e($p['name']) ?> (<?= e($p['patient_id']) ?>)</td><th>Date</th><td><?= e($enc['created_at']) ?></td></tr>
<tr><th>Doctor</th><td><?= e($enc['doctor_name']) ?></td><th>Follow-up</th><td><?= e($enc['follow_up_date']) ?></td></tr>
<tr><th>Diagnosis</th><td colspan="3"><?= e($enc['diagnosis']) ?></td></tr>
<tr><th>Plan</th><td colspan="3"><?= e($enc['plan_note']) ?> (<?= e($enc['med_action']) ?>)</td></tr>
</table>
<p class="no-print actions-row">
  <button onclick="window.print()">Print</button>
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=history&pid=<?= e($p['patient_id']) ?>">Back to record</a>
</p>
<p class="note no-print">To save a PDF, choose &ldquo;Save as PDF&rdquo; as the printer in the print window.</p>
</main></body></html>
