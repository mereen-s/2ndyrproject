<h2>Laboratory</h2>
<div class="tab-bar">
  <a class="tab-btn <?= $tab === 'pending' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=lab">Pending (<?= count($requests) ?>)</a>
  <a class="tab-btn <?= $tab === 'completed' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=lab&tab=completed">Completed</a>
</div>

<?php if ($tab === 'pending'): ?>
<?php if (!$requests): ?><p class="note">No pending requests.</p><?php else: ?>
<table><tr><th>Barcode</th><th>Test</th><th>Patient</th><th>Requested</th><th>Status</th><th></th></tr>
<?php foreach ($requests as $r): ?>
<tr><td class="nowrap"><b><?= e($r['barcode']) ?></b></td><td><?= e($r['test_name']) ?></td><td><?= e($r['patient_name']) ?></td>
    <td><?= e(fmt_dt($r['request_datetime'])) ?></td><td><span class="<?= badge_class($r['status']) ?>"><?= e($r['status']) ?></span></td>
    <td class="actions"><a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=lab_entry&rid=<?= $r['request_id'] ?>">Enter result</a>
      <?php if ($r['status']==='Requested'): ?>
      <form method="post" action="<?= BASE_URL ?>/index.php?page=lab_cancel" data-confirm="Cancel this request?">
  <?= csrf_field() ?>
        <input type="hidden" name="rid" value="<?= $r['request_id'] ?>">
        <button class="btn danger">Cancel</button>
      </form><?php endif; ?></td></tr>
<?php endforeach; ?></table>
<?php endif; ?>
<p class="note">Microbiology and histopathology requests.</p>

<?php else: ?>
<?php if (!$completed): ?><p class="note">No completed results yet.</p><?php else: ?>
<table><tr><th>Barcode</th><th>Test</th><th>Patient</th><th>Result</th><th>Released</th></tr>
<?php foreach ($completed as $c): ?>
<tr><td class="nowrap"><b><?= e($c['barcode']) ?></b></td><td><?= e($c['test_name']) ?></td>
    <td><?= e($c['patient_name']) ?><br><small class="note"><?= e($c['patient_id']) ?></small></td>
    <td><?php if ($c['critical_flag']): ?><span class="critical">CRITICAL</span><br><?php endif; ?>
      <?= nl2br(e($c['finding'] ?? '')) ?>
      <?php if ($c['numeric_result'] !== null && $c['numeric_result'] !== ''): ?>
        <div class="result-value">Value: <b><?= e($c['numeric_result']) ?> <?= e($c['unit'] ?? '') ?></b>
        <?php if (!empty($c['reference_range'])): ?><span class="note">(reference: <?= e($c['reference_range']) ?>)</span><?php endif; ?></div>
      <?php endif; ?></td>
    <td class="nowrap"><?= e(fmt_dt($c['entry_time'])) ?><br><small class="note">by <?= e($c['entered_by_name'] ?? '—') ?></small></td></tr>
<?php endforeach; ?></table>
<?= pagination_links($pages, $current, BASE_URL.'/index.php?page=lab&tab=completed') ?>
<?php endif; ?>
<p class="note">Released results are read-only. A result cannot be changed once accepted.</p>
<?php endif; ?>
