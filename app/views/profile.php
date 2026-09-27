<h2>Patient profile &mdash; <?= e($p['patient_id']) ?></h2>
<div class="grid2">
  <div class="card">
    <h3 style="margin-top:0">Demographics</h3>
    <table class="kv">
      <tr><th>Name</th><td><?= e($p['name']) ?></td></tr>
      <tr><th>Age</th><td><?= $age !== null ? $age.' years' : '&mdash;' ?></td></tr>
      <tr><th>Gender</th><td><?= $p['gender']==='M'?'Male':'Female' ?></td></tr>
      <tr><th>NIC</th><td><?= e($p['nic']) ?></td></tr>
      <tr><th>Date of birth</th><td><?= e($p['dob']) ?></td></tr>
      <tr><th>Contact</th><td><?= e($p['contact']) ?></td></tr>
      <tr><th>Address</th><td><?= e($p['address']) ?></td></tr>
      <tr><th>Registered</th><td><?= e($p['registered_date']) ?></td></tr>
    </table>
    <form method="post" action="<?= BASE_URL ?>/index.php?page=opd_assign">
  <?= csrf_field() ?>
      <input type="hidden" name="pid" value="<?= e($p['patient_id']) ?>">
      <button>Assign to OPD queue</button>
    </form>
    <form method="post" action="<?= BASE_URL ?>/index.php?page=patient_delete" data-confirm="Delete this patient? Only possible if no clinical records exist.">
  <?= csrf_field() ?>
      <input type="hidden" name="pid" value="<?= e($p['patient_id']) ?>">
      <button class="btn danger">Delete patient</button>
    </form>
    <p class="note">Delete is only permitted while the record is empty - registered in error. Once any consultation, test or admission exists the patient record is permanent.</p>
    <p class="note">Demographics only. Clinical records are visible to doctors.</p>
  </div>
  <div class="card">
    <h3 style="margin-top:0">Update details</h3>
    <form method="post" action="<?= BASE_URL ?>/index.php?page=patient_update">
  <?= csrf_field() ?>
      <input type="hidden" name="pid" value="<?= e($p['patient_id']) ?>">
      <label>Full name</label><input name="name" value="<?= e($p['name']) ?>" required>
      <div class="row">
        <div><label>NIC</label><input name="nic" value="<?= e($p['nic']) ?>"></div>
        <div><label>Date of birth</label><input name="dob" type="date" value="<?= e($p['dob']) ?>"></div>
        <div><label>Gender</label><select name="gender"><option <?= $p['gender']==='M'?'selected':'' ?>>M</option><option <?= $p['gender']==='F'?'selected':'' ?>>F</option></select></div>
      </div>
      <label>Contact</label><input name="contact" value="<?= e($p['contact']) ?>">
      <label>Address</label><input name="address" value="<?= e($p['address']) ?>">
      <button>Save changes</button>
    </form>
  </div>
</div>
