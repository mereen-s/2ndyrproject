<?php
?>
<h2>Result entry &mdash; <?= e($r['barcode']) ?> &middot; <?= e($r['test_name']) ?></h2>

<!-- Request summary bar -->
<div class="notif-card" style="margin-bottom:1rem">
  <div class="row" style="gap:1.5rem;flex-wrap:wrap">
    <div><span style="color:#888;font-size:.82rem">Patient</span><br><b><?= e($r['patient_id']) ?></b> <?= e($r['patient_name']) ?></div>
    <div><span style="color:#888;font-size:.82rem">Test</span><br><?= e($r['test_name']) ?></div>
    <div><span style="color:#888;font-size:.82rem">Requested</span><br><?= e($r['request_datetime']) ?></div>
    <div><span style="color:#888;font-size:.82rem">Status</span><br>
      <span class="badge <?= $r['status']==='Completed'?'badge-green':($r['status']==='Processing'?'badge-amber':'badge-blue') ?>">
        <?= e($r['status']) ?>
      </span>
    </div>
    <?php if (!empty($r['doctor_name'])): ?>
    <div><span style="color:#888;font-size:.82rem">Requesting doctor</span><br><?= e($r['doctor_name']) ?></div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($ref)): ?>
<!-- Reference range -->
<div style="background:#f0f7fb;border-left:3px solid #1E5F75;padding:.6rem 1rem;margin-bottom:1rem;border-radius:0 4px 4px 0;font-size:.9rem">
  <b>Reference range:</b> <?= e($ref) ?>
  <span style="color:#666;margin-left:.6rem">&mdash; flag critical if the result falls significantly outside this range</span>
</div>
<?php endif; ?>

<!-- Entry form -->
<form method="post" action="<?= BASE_URL ?>/index.php?page=lab_save" id="lab-entry-form">
  <?= csrf_field() ?>
  <input type="hidden" name="rid" value="<?= $r['request_id'] ?>">

  <div class="row" style="gap:1rem;flex-wrap:wrap;align-items:flex-start">
    <div style="flex:1;min-width:180px">
      <label>Specimen type</label>
      <select name="specimen">
        <?php foreach (['Blood','Urine','Sputum','Stool','Swab','CSF','Other'] as $sp): ?>
        <option value="<?= $sp ?>" <?= ($res['specimen']??'')===$sp?'selected':'' ?>><?= $sp ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="flex:1;min-width:180px">
      <label>Specimen collected at</label>
      <input type="datetime-local" name="collected_at"
             value="<?= e($res['collected_at'] ?? date('Y-m-d\TH:i')) ?>">
    </div>
  </div>

  <label>Numeric result (if applicable)</label>
  <div class="row" style="gap:.6rem;align-items:center">
    <input type="number" step="0.01" name="numeric_result"
           value="<?= e($res['numeric_result'] ?? '') ?>"
           placeholder="e.g. 5.4" style="width:130px" id="num-result">
    <input name="unit" value="<?= e($res['unit'] ?? '') ?>" placeholder="unit (e.g. mmol/L)" style="width:160px">
  </div>

  <label>Finding (descriptive / interpretive text) <span class="text-muted">— required before Accept</span></label>
  <textarea name="finding" rows="5" id="finding-txt"><?= e($res['finding'] ?? '') ?></textarea>

  <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
    <input type="checkbox" name="critical" id="crit-check" style="width:auto;margin:0"
           <?= !empty($res['critical_flag'])?'checked':'' ?>>
    <span>Mark as <b>critical finding</b> &mdash; the requesting doctor will be alerted immediately on Accept</span>
  </label>
  <p id="crit-note" class="note" style="color:#c0392b;display:<?= !empty($res['critical_flag'])?'block':'none' ?>">
    &#9888; Critical flag set. An alert notification will be created when this result is accepted.
  </p>

  <button type="submit" form="lab-entry-form">Save entry</button>
  <a href="<?= BASE_URL ?>/index.php?page=lab" class="btn secondary" style="margin-left:.5rem">Back to queue</a>
</form>

<!-- Accept / Reject panel -->
<?php if (!empty($res['finding']) && ($res['accept_status']??'')==='Pending'): ?>
<hr style="margin:1.5rem 0">
<h3 style="margin-bottom:.5rem">Review &amp; Accept</h3>
<p class="note">The result is saved but <b>not yet committed</b> to the patient record. Review the finding above, then Accept or Reject.</p>
<div class="row" style="gap:.8rem">
  <form method="post" action="<?= BASE_URL ?>/index.php?page=lab_accept">
  <?= csrf_field() ?>
    <input type="hidden" name="rid" value="<?= $r['request_id'] ?>">
    <button type="submit" data-confirm="Accept this result and commit it to the patient record?">Accept</button>
  </form>
  <form method="post" action="<?= BASE_URL ?>/index.php?page=lab_reject">
  <?= csrf_field() ?>
    <input type="hidden" name="rid" value="<?= $r['request_id'] ?>">
    <button type="submit" class="btn secondary" style="background:#fff;color:#c0392b;border-color:#c0392b">
      Reject &amp; re-enter
    </button>
  </form>
</div>
<?php elseif (($res['accept_status']??'')==='Accepted'): ?>
<div class="flash" style="margin-top:1rem">&#10003; Result accepted and committed. To correct an accepted result contact the Administrator (generates a new audit entry).</div>
<?php endif; ?>

<p class="note" style="margin-top:1.5rem">
  Nothing is added to the patient record until you press Accept.
  All actions are recorded in the audit log.
</p>

<script>
document.getElementById('crit-check').addEventListener('change', function(){
  document.getElementById('crit-note').style.display = this.checked ? 'block' : 'none';
});
</script>
