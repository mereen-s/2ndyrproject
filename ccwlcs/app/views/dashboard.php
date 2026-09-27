<?php
// Dashboard — role-personalised landing page
$u = Auth::user();
?>
<h2>Welcome, <?= e($u['name']) ?>
  <small style="font-weight:400;font-size:.55em;color:#666">
    &mdash; <?= e(role_label($u['role'])) ?>
  </small>
</h2>
<p class="note"><?= date('l, d F Y, H:i') ?></p>

<!-- ── KPI strip ─────────────────────────────────────────────────────────── -->
<?php if (!empty($stats)): ?>
<div class="kpi-row" style="margin-bottom:1.2rem">
  <?php foreach ($stats as $lbl => $num): ?>
  <?php $isAlert = (stripos($lbl,'alert')!==false || stripos($lbl,'critical')!==false) && $num > 0; ?>
  <div class="kpi-card <?= $isAlert ? 'kpi-alert' : '' ?>">
    <span class="kpi-label"><?= e($lbl) ?></span>
    <span class="kpi-val"><?= (int)$num ?></span>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Patient search (roles that use it) ───────────────────────────────── -->
<?php if (in_array($u['role'], ['OPDDoctor','ClinicDoctor','WardDoctor','Receptionist'])): ?>
<h3>Find a patient</h3>
<input id="dsearch" placeholder="Type name / NIC / patient ID...">
<div id="dresults" style="margin-bottom:1rem"></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  attachPatientSearch(
    'dsearch', 'dresults',
    '<?= $u['role']==='Receptionist' ? 'profile' : 'history' ?>'
  );
});
</script>
<?php endif; ?>

<!-- ── OPD waiting queue (OPD Doctor) ───────────────────────────────────── -->
<?php if (!empty($queue)): ?>
<h3>OPD queue &mdash; waiting patients</h3>
<table>
  <tr><th>Waiting since</th><th>Patient ID</th><th>Name</th><th></th></tr>
  <?php foreach ($queue as $q): ?>
  <tr>
    <td style="color:#777;font-size:.88rem"><?= e($q['assigned_at']) ?></td>
    <td><b><?= e($q['patient_id']) ?></b></td>
    <td><?= e($q['name']) ?></td>
    <td>
      <a class="btn" href="<?= BASE_URL ?>/index.php?page=opd_form&pid=<?= e($q['patient_id']) ?>">
        Start consultation
      </a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php elseif ($u['role']==='OPDDoctor'): ?>
<p class="note">No patients in the OPD queue right now. Search for a patient above for a return visit.</p>
<?php endif; ?>

<!-- ── Unread notifications panel ───────────────────────────────────────── -->
<?php if (!empty($notifs)): ?>
<h3>
  Recent notifications
  <span class="badge badge-red" style="font-size:.7rem;vertical-align:middle"><?= count($notifs) ?> unread</span>
</h3>
<div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.2rem">
  <?php foreach (array_slice($notifs, 0, 5) as $n): ?>
  <div class="notif-card <?= strpos($n['message'],'CRITICAL')!==false ? 'notif-critical' : '' ?>">
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
      <div>
        <?php if (strpos($n['message'],'CRITICAL')!==false): ?>
          <span class="badge badge-red">CRITICAL</span>
        <?php endif; ?>
        <b><?= e($n['source']) ?></b>
        <?php if (!empty($n['patient_name'])): ?>
          &mdash; <?= e($n['patient_name']) ?>
        <?php endif; ?>
        <br>
        <span style="font-size:.9rem"><?= e($n['message']) ?></span>
      </div>
      <a href="<?= BASE_URL ?>/index.php?page=notif_open&nid=<?= $n['notification_id'] ?>"
         style="white-space:nowrap;margin-left:1rem" class="btn secondary">View</a>
    </div>
    <small style="color:#888"><?= e(time_ago($n['created_at'])) ?></small>
  </div>
  <?php endforeach; ?>
  <?php if (count($notifs) > 5): ?>
  <a href="<?= BASE_URL ?>/index.php?page=notifications" class="btn secondary" style="align-self:flex-start">
    View all <?= count($notifs) ?> notifications
  </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Module quick links ────────────────────────────────────────────────── -->
<h3>Your module</h3>
<div class="module-links">
<?php
$links = [
  'Receptionist'      => ['registration'  => 'Patient registration &amp; search',
                           'report_patients'=> 'Patient report'],
  'OPDDoctor'         => ['notifications' => 'Notifications'],
  'ClinicDoctor'      => ['clinic'        => 'Clinic referral queue',
                           'notifications' => 'Notifications'],
  'WardDoctor'        => ['ward'          => 'My ward &mdash; daily notes &amp; discharge',
                           'report_ward'   => 'Ward report',
                           'notifications' => 'Notifications'],
  'WardNurse'         => ['nurse'         => 'Admissions, beds &amp; medication',
                           'report_ward'   => 'Ward report'],
  'LabPersonnel'      => ['lab'           => 'Laboratory request queue',
                           'report_lab'    => 'Lab throughput report'],
  'RadiologyPersonnel'=> ['radiology'     => 'Radiology request queue'],
  'Pharmacist'        => ['pharmacy'      => 'Pending prescriptions'],
  'Administrator'     => ['admin_users'   => 'User accounts',
                           'audit'         => 'Audit log',
                           'admin_perms'   => 'Permissions',
                           'reports'       => 'System reports'],
];
foreach (($links[$u['role']] ?? []) as $pg => $label) {
  echo '<a class="btn" href="'.BASE_URL.'/index.php?page='.$pg.'">'.$label.'</a> ';
}
if ($u['role']==='OPDDoctor') {
  echo '<p class="note" style="width:100%;margin-top:.5rem">Search a patient above and open their record to start a new OPD consultation.</p>';
}
?>
</div>
