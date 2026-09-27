<?php?>
<h2>System Summary Report</h2>

<form method="get" class="report-meta">
  <input type="hidden" name="page" value="reports">
  <label>From <input id="report-from" type="date" name="from" value="<?= e($from) ?>"></label>
  <label>To   <input id="report-to"   type="date" name="to"   value="<?= e($to) ?>"></label>
  <button class="btn" style="margin-top:0">Filter</button>
  <span class="note" style="margin:0">Showing <?= e($from) ?> → <?= e($to) ?></span>
</form>

<h3>Patient statistics</h3>
<div class="report-kpi">
  <div class="kpi-card"><div class="kpi-num"><?= $totalPatients ?></div><div class="kpi-lbl">Total registered</div></div>
  <div class="kpi-card"><div class="kpi-num"><?= $newPatients ?></div><div class="kpi-lbl">New in period</div></div>
  <div class="kpi-card"><div class="kpi-num"><?= $encByType['OPD'] ?></div><div class="kpi-lbl">OPD consultations</div></div>
  <div class="kpi-card"><div class="kpi-num"><?= $encByType['CLINIC'] ?></div><div class="kpi-lbl">Clinic visits</div></div>
  <div class="kpi-card"><div class="kpi-num"><?= $encByType['WARD'] ?></div><div class="kpi-lbl">Ward admissions</div></div>
  <div class="kpi-card"><div class="kpi-num"><?= $activeToday ?></div><div class="kpi-lbl">Active users today</div></div>
</div>

<h3>Current queue status</h3>
<div class="stat-grid">
  <div class="stat <?= $pendingLab  > 0 ? 'alert':'' ?>"><div class="num"><?= $pendingLab  ?></div><div class="lbl">Lab pending</div></div>
  <div class="stat <?= $pendingRad  > 0 ? 'alert':'' ?>"><div class="num"><?= $pendingRad  ?></div><div class="lbl">Radiology pending</div></div>
  <div class="stat <?= $pendingPharm> 0 ? 'alert':'' ?>"><div class="num"><?= $pendingPharm ?></div><div class="lbl">Pharmacy pending</div></div>
</div>

<h3>Bed occupancy by department</h3>
<table>
  <thead><tr><th>Department</th><th>Total beds</th><th>Occupied</th><th>Available</th><th>Occupancy %</th></tr></thead>
  <tbody>
  <?php foreach ($occupancy as $o):
    $pct = $o['total'] > 0 ? round($o['occupied'] / $o['total'] * 100) : 0;
  ?>
    <tr>
      <td><?= e($o['dept_name']) ?></td>
      <td><?= (int)$o['total'] ?></td>
      <td><?= (int)$o['occupied'] ?></td>
      <td><?= (int)$o['total'] - (int)$o['occupied'] ?></td>
      <td>
        <div style="display:flex;align-items:center;gap:8px">
          <div style="flex:1;max-width:120px;height:8px;background:var(--line);border-radius:4px;overflow:hidden">
            <div style="width:<?= $pct ?>%;height:100%;background:<?= $pct>80?'var(--red)':($pct>50?'var(--amber)':'var(--green)') ?>;border-radius:4px"></div>
          </div>
          <span><?= $pct ?>%</span>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<h3>Logins per day (last 14 days)</h3>
<table>
  <thead><tr><th>Date</th><th>Logins</th></tr></thead>
  <tbody>
  <?php foreach ($logins as $l): ?>
    <tr><td><?= e(fmt_dt($l['day'], true)) ?></td><td><?= (int)$l['cnt'] ?></td></tr>
  <?php endforeach; ?>
  <?php if (!$logins): ?><tr><td colspan="2">No logins recorded in this period.</td></tr><?php endif; ?>
  </tbody>
</table>

<h3>Audit event summary</h3>
<table>
  <thead><tr><th>Action</th><th>Total events</th></tr></thead>
  <tbody>
  <?php foreach (array_slice($auditSummary, 0, 15) as $a): ?>
    <tr><td><code><?= e($a['action']) ?></code></td><td><?= (int)$a['cnt'] ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
<p class="note">Full audit detail available on the <a href="<?= BASE_URL ?>/index.php?page=audit">Audit log</a> page.</p>

<p class="no-print" style="margin-top:22px">
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=report_lab">Lab report</a>
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=report_ward">Ward report</a>
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=report_patients">Patient report</a>
  <button class="btn secondary" onclick="ccwPrint()">Print this report</button>
</p>
