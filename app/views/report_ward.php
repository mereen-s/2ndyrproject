<?php
?>
<h2>Ward &amp; Bed Utilisation Report</h2>
<p class="note">Snapshot as of <?= date('d M Y, H:i') ?>. Only currently admitted patients are counted.</p>

<!-- KPI strip -->
<div class="kpi-row">
  <div class="kpi-card">
    <span class="kpi-label">Total beds</span>
    <span class="kpi-val"><?= $stats['total_beds'] ?></span>
  </div>
  <div class="kpi-card">
    <span class="kpi-label">Occupied</span>
    <span class="kpi-val"><?= $stats['occupied'] ?></span>
  </div>
  <div class="kpi-card">
    <span class="kpi-label">Available</span>
    <span class="kpi-val"><?= $stats['available'] ?></span>
  </div>
  <div class="kpi-card">
    <span class="kpi-label">Pending admission</span>
    <span class="kpi-val"><?= $stats['pending_admission'] ?></span>
  </div>
  <div class="kpi-card">
    <span class="kpi-label">Overall occupancy</span>
    <span class="kpi-val">
      <?= fmt_num($stats['total_beds'] > 0 ? $stats['occupied'] / $stats['total_beds'] * 100 : 0, 0) ?>%
    </span>
  </div>
</div>

<!-- Per-department breakdown -->
<h3>Occupancy by department</h3>
<table>
  <tr><th>Department</th><th>Total beds</th><th>Occupied</th><th>Available</th><th>Occupancy</th></tr>
  <?php foreach ($byDept as $d): ?>
  <?php $pct = $d['total']>0 ? round($d['occupied']/$d['total']*100) : 0; ?>
  <tr>
    <td><?= e($d['dept_name']) ?></td>
    <td><?= $d['total'] ?></td>
    <td><?= $d['occupied'] ?></td>
    <td><?= $d['available'] ?></td>
    <td>
      <div class="bar-wrap">
        <div class="bar-fill <?= $pct>=90?'bar-red':($pct>=70?'bar-amber':'bar-green') ?>" style="width:<?= $pct ?>%"></div>
      </div>
      <small><?= $pct ?>%</small>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<!-- Longest stays -->
<h3>Current longest stays</h3>
<table>
  <tr><th>Patient</th><th>Bed</th><th>Department</th><th>Diagnosis</th><th>Admit date</th><th>Days</th></tr>
  <?php foreach ($longest as $a): ?>
  <tr>
    <td><b><?= e($a['patient_id']) ?></b> <?= e($a['patient_name']) ?></td>
    <td><?= e($a['bed_number'] ?? '—') ?></td>
    <td><?= e($a['dept_name']) ?></td>
    <td><?= e($a['diagnosis']) ?></td>
    <td><?= e($a['admit_date']) ?></td>
    <td><?= $a['days_admitted'] ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<p class="note">Stays longer than 7 days are highlighted.</p>
<div class="no-print"><a href="javascript:ccwPrint()" class="btn secondary">Print this report</a></div>
