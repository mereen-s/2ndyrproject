<h2>New orders &mdash; <?= e($p['patient_id']) ?> &middot; <?= e($p['name']) ?></h2>
<p><a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=history&pid=<?= e($p['patient_id']) ?>">Back to record</a></p>
<div class="row no-print">
  <div class="card" style="flex:1">
    <b>Prescribe</b>
    <form method="post" action="<?= BASE_URL ?>/index.php?page=prescribe">
  <?= csrf_field() ?>
      <input type="hidden" name="pid" value="<?= e($p['patient_id']) ?>">
      <label>Drug</label><input name="drug" required>
      <div class="row">
        <div><label>Dose</label><input name="dose"></div>
        <div><label>Frequency</label><input name="frequency"></div>
        <div><label>Duration</label><input name="duration"></div>
      </div>
      <button>Send to dispensary</button>
    </form>
  </div>
  <div class="card" style="flex:1">
    <b>Request investigation</b>
    <form method="post" action="<?= BASE_URL ?>/index.php?page=request_lab">
  <?= csrf_field() ?>
      <input type="hidden" name="pid" value="<?= e($p['patient_id']) ?>">
      <label>Lab test</label>
      <select name="test_code"><?php foreach ($tests as $t): ?><option value="<?= e($t['test_code']) ?>"><?= e($t['test_name']) ?></option><?php endforeach; ?></select>
      <button>Request lab test</button>
    </form>
    <form method="post" action="<?= BASE_URL ?>/index.php?page=request_rad">
  <?= csrf_field() ?>
      <input type="hidden" name="pid" value="<?= e($p['patient_id']) ?>">
      <div class="row">
        <div><label>Scan</label><select name="scan_type"><option>X-ray</option><option>CT</option></select></div>
        <div><label>Body part</label><input name="body_part"></div>
      </div>
      <button>Request scan</button>
    </form>
  </div>
</div>
<p class="note">New orders appear in the patient's record once they are placed.</p>
