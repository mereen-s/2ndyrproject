<?php
?>
<h2>Audit log <small style="font-size:0.6em;font-weight:400">(read-only, UC-43)</small></h2>

<!-- Filter form -->
<form method="get" action="<?= BASE_URL ?>/index.php" style="margin-bottom:1rem">
  <input type="hidden" name="page" value="audit">
  <div class="row" style="flex-wrap:wrap;gap:.5rem">
    <div>
      <label style="font-size:.85rem">Action keyword</label>
      <input name="f_action" value="<?= e($filter['action'] ?? '') ?>" placeholder="e.g. LAB_RESULT" style="width:180px">
    </div>
    <div>
      <label style="font-size:.85rem">Username</label>
      <input name="f_user" value="<?= e($filter['user'] ?? '') ?>" placeholder="username" style="width:140px">
    </div>
    <div>
      <label style="font-size:.85rem">From date</label>
      <input type="date" name="f_from" value="<?= e($filter['from'] ?? '') ?>" style="width:145px">
    </div>
    <div>
      <label style="font-size:.85rem">To date</label>
      <input type="date" name="f_to" value="<?= e($filter['to'] ?? '') ?>" style="width:145px">
    </div>
    <div style="align-self:flex-end">
      <button type="submit">Filter</button>
      <a href="<?= BASE_URL ?>/index.php?page=audit" class="btn secondary" style="margin-left:.4rem">Clear</a>
    </div>
  </div>
</form>

<p class="note" style="margin-bottom:.5rem">
  Showing <b><?= count($rows) ?></b> of <b><?= number_format($total) ?></b> entries.
  Entries cannot be edited or deleted.
</p>

<!-- Log table -->
<div style="overflow-x:auto">
<table>
  <tr>
    <th style="white-space:nowrap">Date / Time</th>
    <th>User</th>
    <th>Action</th>
    <th>Entity / Reference</th>
  </tr>
  <?php if (empty($rows)): ?>
  <tr><td colspan="4" style="text-align:center;color:#888;padding:1.5rem">No entries match the filter.</td></tr>
  <?php endif; ?>
  <?php foreach ($rows as $r): ?>
  <?php $isAlert = strpos($r['action'],'ALERT') === 0; ?>
  <tr>
    <td style="white-space:nowrap;font-size:.85rem;color:#555"><?= e($r['created_at']) ?></td>
    <td><?= e($r['username'] ?? '<em>system</em>') ?></td>
    <td>
      <?php if ($isAlert): ?>
        <span class="badge badge-red"><?= e($r['action']) ?></span>
      <?php elseif (strpos($r['action'],'DELETE')!==false||strpos($r['action'],'CANCEL')!==false): ?>
        <span class="badge badge-amber"><?= e($r['action']) ?></span>
      <?php elseif (strpos($r['action'],'ACCEPT')!==false||strpos($r['action'],'DISPENSE')!==false): ?>
        <span class="badge badge-green"><?= e($r['action']) ?></span>
      <?php else: ?>
        <code style="font-size:.82rem"><?= e($r['action']) ?></code>
      <?php endif; ?>
    </td>
    <td style="font-size:.88rem;color:#333"><?= e($r['entity_ref']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
</div>

<!-- Pagination -->
<?php
  $base = BASE_URL.'/index.php?page=audit'
        .'&f_action='.urlencode($filter['action'] ?? '').'&f_user='.urlencode($filter['user'] ?? '')
        .'&f_from='.urlencode($filter['from'] ?? '').'&f_to='.urlencode($filter['to'] ?? '');
  echo pagination_links($pages, $page, $base, 'pg');
?>

<div class="no-print" style="margin-top:1rem">
  <a href="javascript:ccwPrint()" class="btn secondary">Print this page</a>
</div>
