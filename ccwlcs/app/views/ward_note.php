<h2>Daily Progress Note &mdash; <?= e($a['patient_id']) ?> &middot; <?= e($a['patient_name']) ?> &middot; Bed <?= e($a['bed_number']) ?></h2>
<?php if ($prev && !$f): ?>
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=ward_note&aid=<?= $a['admission_id'] ?>&copy=1">Load yesterday's note</a>
  <span class="note">One tap pre-fills all four sections &mdash; edit only what changed.</span>
<?php endif; ?>
<form method="post" action="<?= BASE_URL ?>/index.php?page=ward_note_save">
  <?= csrf_field() ?>
  <input type="hidden" name="admission_id" value="<?= $a['admission_id'] ?>">
  <input type="hidden" name="patient_id" value="<?= e($a['patient_id']) ?>">
  <?php include __DIR__.'/_soap_fields.php'; ?>
  <button>Save today's note</button>
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=history&pid=<?= e($a['patient_id']) ?>">Open record</a>
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=orders&pid=<?= e($a['patient_id']) ?>">New orders</a>
</form>
<p><a class="btn" href="<?= BASE_URL ?>/index.php?page=discharge_preview&aid=<?= $a['admission_id'] ?>">Discharge &mdash; review summary</a></p>
<p class="note">The full BHT (all daily notes, medications and results) stays in the system; the discharge summary is the patient's copy.</p>
