<?php
// Ward Doctor — admitted patient list with daily note links and bed grid
?>
<h2>My ward &mdash; admitted patients</h2>

<?php if ($deptInfo): ?>
<div class="flash" style="margin-bottom:1rem"><?= e($deptInfo) ?></div>
<?php endif; ?>

<?php if ($pendingAdm > 0): ?>
<div class="notif-card" style="margin-bottom:1rem">
  <b>&#9888; <?= $pendingAdm ?> patient<?= $pendingAdm>1?'s':'' ?> awaiting bed assignment.</b>
  The WardNurse must assign beds — patients will appear in this list once a bed is allocated.
</div>
<?php endif; ?>

<!-- Quick stats bar -->
<div class="kpi-row" style="margin-bottom:1rem">
  <div class="kpi-card"><span class="kpi-label">Admitted</span><span class="kpi-val"><?= count($admitted) ?></span></div>
  <div class="kpi-card"><span class="kpi-label">Beds total</span><span class="kpi-val"><?= count($allBeds) ?></span></div>
  <div class="kpi-card"><span class="kpi-label">Occupied</span>
    <span class="kpi-val"><?= count(array_filter($allBeds, fn($b)=>$b['status']==='Occupied')) ?></span></div>
  <div class="kpi-card"><span class="kpi-label">Free</span>
    <span class="kpi-val"><?= count(array_filter($allBeds, fn($b)=>$b['status']==='Free')) ?></span></div>
</div>

<!-- Bed grid -->
<?php if (!empty($allBeds)): ?>
<h3>Bed layout</h3>
<div class="bed-grid" style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.2rem">
  <?php foreach ($allBeds as $b): ?>
  <div class="bed-chip bed-<?= strtolower($b['status']) ?>"
       title="Bed <?= e($b['bed_number']) ?> — <?= e($b['status']) ?>"
       style="width:56px;height:42px;display:flex;align-items:center;justify-content:center;
              border-radius:6px;font-weight:600;font-size:.85rem;
              background:<?= $b['status']==='Occupied'?'#e8f0f2':'#d4f1d4' ?>;
              border:1px solid <?= $b['status']==='Occupied'?'#1E5F75':'#27ae60' ?>;
              color:<?= $b['status']==='Occupied'?'#1E5F75':'#1a6b28' ?>">
    <?= e($b['bed_number']) ?>
  </div>
  <?php endforeach; ?>
</div>
<p class="note" style="margin-bottom:1rem">
  <span style="background:#d4f1d4;border:1px solid #27ae60;padding:1px 8px;border-radius:4px;font-size:.8rem">Free</span>
  &nbsp;
  <span style="background:#e8f0f2;border:1px solid #1E5F75;padding:1px 8px;border-radius:4px;font-size:.8rem">Occupied</span>
</p>
<?php endif; ?>

<!-- Admitted patient table -->
<h3>Admitted patients</h3>
<?php if (empty($admitted)): ?>
<p class="note">No patients currently admitted to this ward.</p>
<?php else: ?>
<table>
  <tr><th>Bed</th><th>Patient</th><th>Diagnosis</th><th>Admitted</th><th>Days</th><th>Actions</th></tr>
  <?php foreach ($admitted as $a): ?>
  <?php $days = (int)((time() - strtotime($a['admit_date'])) / 86400); ?>
  <tr>
    <td><b><?= e($a['bed_number'] ?? '—') ?></b></td>
    <td>
      <b><?= e($a['patient_id']) ?></b><br>
      <span style="font-size:.88rem"><?= e($a['patient_name']) ?></span>
    </td>
    <td style="font-size:.9rem"><?= e($a['diagnosis']) ?></td>
    <td style="font-size:.85rem;color:#555;white-space:nowrap"><?= e($a['admit_date']) ?></td>
    <td>
      <span class="badge <?= $days>7?'badge-red':($days>3?'badge-amber':'badge-green') ?>">
        <?= $days ?> d
      </span>
    </td>
    <td style="white-space:nowrap">
      <a class="btn" href="<?= BASE_URL ?>/index.php?page=ward_note&aid=<?= $a['admission_id'] ?>">Daily note</a>
      <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=history&pid=<?= $a['patient_id'] ?>">History</a>
      <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=discharge_preview&aid=<?= $a['admission_id'] ?>">Discharge</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<p class="note" style="margin-top:1rem">
  Ward notes are saved under your account and cannot be changed once saved.
</p>
