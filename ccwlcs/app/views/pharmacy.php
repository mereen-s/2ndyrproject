<h2>Drug Dispensary &mdash; pending prescriptions</h2>
<table><tr><th>Date</th><th>Patient</th><th>Drug</th><th>Prescriber</th><th>Dispense</th></tr>
<?php foreach ($prescriptions as $pr): ?>
<tr><td><?= e($pr['prescribed_at']) ?></td><td><b><?= e($pr['patient_id']) ?></b> <?= e($pr['patient_name']) ?></td>
    <td><?= e($pr['drug']) ?> <?= e($pr['dose']) ?> &middot; <?= e($pr['frequency']) ?> &middot; <?= e($pr['duration']) ?></td>
    <td><?= e($pr['doctor_name']) ?></td>
<td class="actions"><form method="post" action="<?= BASE_URL ?>/index.php?page=dispense">
  <?= csrf_field() ?>
  <input type="hidden" name="prescription_id" value="<?= $pr['prescription_id'] ?>">
  <input data-type="numeric" name="quantity" placeholder="qty" style="width:70px" required>
  <button>Dispense</button>
</form>
<form method="post" action="<?= BASE_URL ?>/index.php?page=presc_cancel" data-confirm="Cancel this prescription?">
  <?= csrf_field() ?>
  <input type="hidden" name="prescription_id" value="<?= $pr['prescription_id'] ?>">
  <button class="btn danger">Cancel</button>
</form></td></tr>
<?php endforeach; ?></table>
<p class="note">Dispensing is recorded with your ID and time; the status becomes Dispensed.</p>
