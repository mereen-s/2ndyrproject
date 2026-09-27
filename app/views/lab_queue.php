<h2>Laboratory &mdash; pending requests</h2>
<table><tr><th>Barcode</th><th>Test</th><th>Patient</th><th>Requested</th><th>Status</th><th></th></tr>
<?php foreach ($requests as $r): ?>
<tr><td><b><?= e($r['barcode']) ?></b></td><td><?= e($r['test_name']) ?></td><td><?= e($r['patient_name']) ?></td>
    <td><?= e($r['request_datetime']) ?></td><td><span class="<?= badge_class($r['status']) ?>"><?= e($r['status']) ?></span></td>
    <td><a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=lab_entry&rid=<?= $r['request_id'] ?>">Enter result</a>
      <?php if ($r['status']==='Requested'): ?>
      <form method="post" action="<?= BASE_URL ?>/index.php?page=lab_cancel" style="display:inline" data-confirm="Cancel this request?">
  <?= csrf_field() ?>
        <input type="hidden" name="rid" value="<?= $r['request_id'] ?>">
        <button class="btn danger" style="margin-top:0">Cancel</button>
      </form><?php endif; ?></td></tr>
<?php endforeach; ?></table>
<p class="note">Microbiology and histopathology requests.</p>
