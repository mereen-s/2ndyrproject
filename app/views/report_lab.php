<?php?>
<h2>Laboratory Report</h2>

<form method="get" class="report-meta">
  <input type="hidden" name="page" value="report_lab">
  <label>From <input id="report-from" type="date" name="from" value="<?= e($from) ?>"></label>
  <label>To   <input id="report-to"   type="date" name="to"   value="<?= e($to) ?>"></label>
  <button class="btn">Filter</button>
</form>

<h3>Period totals</h3>
<div class="report-kpi">
  <div class="kpi-card"><div class="kpi-num"><?= (int)$totals['total'] ?></div><div class="kpi-lbl">Requests</div></div>
  <div class="kpi-card"><div class="kpi-num"><?= (int)$totals['completed'] ?></div><div class="kpi-lbl">Completed</div></div>
  <div class="kpi-card"><div class="kpi-num"><?= (int)$totals['pending'] ?></div><div class="kpi-lbl">Pending</div></div>
  <div class="kpi-card"><div class="kpi-num"><?= (int)$totals['rejected'] ?></div><div class="kpi-lbl">Rejected entries</div></div>
  <div class="kpi-card">
    <div class="kpi-num"><?php if ($avgTat <= 0) echo '–'; elseif ($avgTat < 60) echo $avgTat.' min'; elseif ($avgTat < 2880) echo round($avgTat/60, 1).' h'; else echo round($avgTat/1440, 1).' d'; ?></div>
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
  Rejected entries are findings sent back for re-entry; cancelled requests were withdrawn before collection.
</p>
<p class="no-print">
  <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=reports">← Back to summary</a>
  <button class="btn secondary" onclick="ccwPrint()">Print</button>
</p>
