<?php
?>
<h2>Patient Registration Report</h2>
<p class="note">Data as of <?= date('d M Y, H:i') ?>.</p>

<!-- KPI strip -->
<div class="kpi-row">
  <div class="kpi-card">
    <span class="kpi-label">Total registered</span>
    <span class="kpi-val"><?= number_format($totals['total']) ?></span>
  </div>
  <div class="kpi-card">
    <span class="kpi-label">This month</span>
    <span class="kpi-val"><?= $totals['this_month'] ?></span>
  </div>
  <div class="kpi-card">
    <span class="kpi-label">This week</span>
    <span class="kpi-val"><?= $totals['this_week'] ?></span>
  </div>
  <div class="kpi-card">
    <span class="kpi-label">Today</span>
    <span class="kpi-val"><?= $totals['today'] ?></span>
  </div>
</div>

<!-- Monthly registration trend -->
<h3>Monthly registration trend (last 12 months)</h3>
<?php if (empty($byMonth)): ?>
  <p class="note">No data available for the selected period.</p>
<?php else: ?>
<?php
  // all of the last 12 months, including months with no registrations
  $counts = array_column($byMonth, 'cnt', 'month');
  $months = [];
  for ($i = 11; $i >= 0; $i--) {
    $key = date('Y-m', mktime(0, 0, 0, (int)date('n') - $i, 1, (int)date('Y')));
    $months[$key] = (int)($counts[$key] ?? 0);
  }
  $max = max($months) ?: 1;
?>
<div class="trend-chart" role="img" aria-label="Registrations per month for the last 12 months">
  <?php foreach ($months as $key => $cnt): ?>
  <div class="trend-col">
    <span class="trend-num"><?= $cnt ?></span>
    <div class="trend-bar" style="height:<?= $cnt ? max(4, round($cnt / $max * 100)) : 0 ?>%"></div>
    <span class="trend-lbl"><?= date('M', strtotime($key.'-01')) ?><br><small><?= date('Y', strtotime($key.'-01')) ?></small></span>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Gender breakdown -->
<h3>By gender</h3>
<table>
  <tr><th>Gender</th><th>Count</th><th>Share</th></tr>
  <?php $total_g = array_sum(array_column($byGender,'cnt')) ?: 1; ?>
  <?php foreach ($byGender as $g): ?>
  <tr>
    <td><?= $g['gender']==='M' ? 'Male' : ($g['gender']==='F' ? 'Female' : 'Other/Unknown') ?></td>
    <td><?= $g['cnt'] ?></td>
    <td><?= round($g['cnt']/$total_g*100) ?>%</td>
  </tr>
  <?php endforeach; ?>
</table>

<!-- Recent registrations -->
<h3>Recently registered patients</h3>
<table>
  <tr><th>ID</th><th>Name</th><th>NIC</th><th>Gender</th><th>Registered</th></tr>
  <?php foreach ($recent as $p): ?>
  <tr>
    <td><?= e($p['patient_id']) ?></td>
    <td><a href="<?= BASE_URL ?>/index.php?page=profile&pid=<?= e($p['patient_id']) ?>"><?= e($p['name']) ?></a></td>
    <td><?= e($p['nic']) ?></td>
    <td><?= $p['gender']==='M' ? 'M' : 'F' ?></td>
    <td><?= e($p['registered_date']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<p class="note">Showing most recent <?= count($recent) ?> registrations. Full patient list is available via Registration &amp; Search.</p>
<div class="no-print"><a href="javascript:ccwPrint()" class="btn secondary">Print this report</a></div>
