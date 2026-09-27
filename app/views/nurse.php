<h2>Ward &mdash; admissions, beds &amp; medication</h2>
<h3>Pending admission orders</h3>
<table><tr><th>Ordered</th><th>Patient</th><th>Diagnosis</th><th>Assign bed</th></tr>
<?php $freeBeds = array_filter($beds, fn($b)=>$b['status']==='Free'); foreach ($pending as $ad): ?>
<tr><td><?= e($ad['created_at']) ?></td><td><b><?= e($ad['patient_id']) ?></b> <?= e($ad['patient_name']) ?></td><td><?= e($ad['diagnosis']) ?></td>
<td><form method="post" action="<?= BASE_URL ?>/index.php?page=assign_bed" style="display:flex;gap:6px">
  <?= csrf_field() ?>
  <input type="hidden" name="aid" value="<?= $ad['admission_id'] ?>">
  <select name="bed_id"><?php foreach ($freeBeds as $b): ?><option value="<?= $b['bed_id'] ?>">Bed <?= $b['bed_number'] ?></option><?php endforeach; ?></select>
  <button style="margin-top:0">Admit</button>
</form>
<form method="post" action="<?= BASE_URL ?>/index.php?page=admission_cancel" data-confirm="Cancel this admission order?">
  <?= csrf_field() ?>
  <input type="hidden" name="aid" value="<?= $ad['admission_id'] ?>">
  <button class="btn danger" style="margin-top:6px">Cancel order</button>
</form></td></tr>
<?php endforeach; ?></table>
<h3>Bed map</h3>
<table><tr><?php foreach ($beds as $b): ?><td style="text-align:center"><b>Bed <?= $b['bed_number'] ?></b><br><?= $b['status']==='Free' ? 'free' : '<span class="critical">occupied</span>' ?></td><?php endforeach; ?></tr></table>
<h3>Ward information</h3>
<form method="post" action="<?= BASE_URL ?>/index.php?page=ward_info_save">
  <?= csrf_field() ?>
  <input name="info" value="<?= e($dept['info'] ?? '') ?>" style="max-width:640px" placeholder="Visiting hours, ward notices...">
  <button>Update ward info</button>
</form>
<h3>Record administered medication</h3>
<form method="post" action="<?= BASE_URL ?>/index.php?page=med_admin">
  <?= csrf_field() ?>
  <div class="row">
    <div><label>Patient (admitted)</label>
      <select name="aid"><?php foreach ($admitted as $ad): ?><option value="<?= $ad['admission_id'] ?>"><?= e($ad['patient_id']) ?> &middot; <?= e($ad['patient_name']) ?> (bed <?= e($ad['bed_number']) ?>)</option><?php endforeach; ?></select></div>
    <div><label>Drug &middot; dose</label><input name="drug_dose" required></div>
    <div><label>Route</label><input name="route" placeholder="oral / IV"></div>
  </div>
  <button>Record</button>
</form>
