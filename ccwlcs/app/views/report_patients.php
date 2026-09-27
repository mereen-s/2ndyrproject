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
<table>
  <tr><th>Month</th><th>Registrations</th><th>Bar</th></tr>
  <?php $max = max(array_column($byMonth,'cnt')) ?: 1; ?>
  <?php foreach ($byMonth as $row): ?>
  <tr>
    <td><?= date('M Y', strtotime($row['month'].'-01')) ?></td>
    <td><?= $row['cnt'] ?></td>
    <td>
      <div class="bar-wrap">
        <div class="bar-fill bar-green" style="width:<?= round($row['cnt']/$max*100) ?>%"></div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
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
