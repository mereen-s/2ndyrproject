<h2>OPD Consultation &mdash; <?= e($p['patient_id']) ?> &middot; <?= e($p['name']) ?></h2>
<form method="post" action="<?= BASE_URL ?>/index.php?page=opd_save">
  <?= csrf_field() ?>
  <input type="hidden" name="patient_id" value="<?= e($p['patient_id']) ?>">
  <?php $f=null; include __DIR__.'/_soap_fields.php'; ?>
  <h3>Pathway</h3>
  <div class="row">
    <div><label>Decision</label>
      <select name="pathway"><option>Treat</option><option>Refer</option><option>Admit</option></select></div>
    <div><label>Clinic / ward department (for Refer or Admit)</label>
      <select name="referral_department_id">
        <?php foreach ($departments as $d): ?><option value="<?= $d['department_id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
      </select></div>
  </div>
  <button>Save consultation</button>
</form>
<p class="note">Admit sends an admission order to the ward; Refer adds the patient to the clinic queue.</p>
