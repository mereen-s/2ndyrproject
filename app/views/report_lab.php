<?php?>
<h2>Laboratory Report</h2>

<form method="get" class="report-meta">
  <input type="hidden" name="page" value="report_lab">
  <label>From <input id="report-from" type="date" name="from" value="<?= e($from) ?>"></label>
  <label>To   <input id="report-to"   type="date" name="to"   value="<?= e($to) ?>"></label>
  <button class="btn" style="margin-top:0">Filter</button>
</form>

<h3>Period totals</h3>
<div class="report-kpi">
  <div class="kpi-card"><div class="kpi-num"><?= (int)$totals['total'] ?></div><div class="kpi-lbl">Requests</div></div>
  <div class="kpi-card"><div class="kpi-num"><?= (int)$totals['completed'] ?></div><div class="kpi-lbl">Completed</div></div>
  <div class="kpi-card"><div class="kpi-num"><?= (int)$totals['rejected'] ?></div><div class="kpi-lbl">Rejected</div></div>
  <div class="kpi-card">
    <div class="kpi-num"><?= $avgTat > 0 ? $avgTat.' m' : '–' ?></div>
    <div class="kpi-lbl">Avg turnaround</div>
  </div>
  <?php if ($totals['total'] > 0): ?>
  <div class="kpi-card">
    <div class="kpi-num"><?= round($totals['completed'] / $totals['total'] * 100) ?>%</div>
    <div class="kpi-lbl">Completion rate</div>
  </div>
  <?php endif; ?>
</div>

<h3>Volume by test type</h3>
<?php if (empty($volume)): ?>
  <p class="note">No lab requests in this date range.</p>
<?php else: ?>
<table>
  <thead>
    <tr>
      <th>Test</th><th>Requested</th><th>Completed</th>
      <th>Cancelled</th><th>Rejected</th><th>Critical findings</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($volume as $v): ?>
    <tr>
      <td><?= e($v['test_name']) ?></td>
      <td><?= (int)$v['requested'] ?></td>
      <td><?= (int)$v['completed'] ?></td>
      <td><?= (int)$v['cancelled'] ?></td>
      <td><?= (int)$v['rejected'] ?></td>
      <td><?= (int)$v['critical'] > 0
            ? '<span class="badge badge-danger">'.(int)$v['critical'].' critical</span>'
            : '<span class="badge badge-success">None</span>'
          ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<p class="note" style="margin-top:14px">
  Turnaround time is measured from request submission to result acceptance.
</p>
<p class="no-print">
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=reports">← Back to summary</a>
  <button class="btn secondary" onclick="ccwPrint()">Print</button>
</p>
