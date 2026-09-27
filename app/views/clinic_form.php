<h2>Clinic Visit &mdash; <?= e($p['patient_id']) ?> &middot; <?= e($p['name']) ?></h2>
<?php if ($last && !isset($_GET['copy'])): ?>
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=clinic_form&pid=<?= e($p['patient_id']) ?>&copy=1">Load last clinic visit</a>
  <span class="note">Loads the previous visit so you can edit only what changed.</span>
<?php endif; ?>
<form method="post" action="<?= BASE_URL ?>/index.php?page=clinic_save">
  <?= csrf_field() ?>
  <input type="hidden" name="patient_id" value="<?= e($p['patient_id']) ?>">
  <?php $f = isset($_GET['copy']) ? $last : null; include __DIR__.'/_soap_fields.php'; ?>
  <h3>Follow-up</h3>
  <label>Follow-up date</label><input type="date" name="follow_up_date" value="<?= e($f['follow_up_date'] ?? '') ?>">
  <button>Save visit &mdash; opens printable summary</button>
</form>
<p class="note">The printed visit summary is for the patient to take home.</p>
