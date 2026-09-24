<h2>Role permissions</h2>
<p class="note">Tick which roles may open each page. Administrator access to admin pages is locked to prevent lock-out.</p>
<form method="post" action="<?= BASE_URL ?>/index.php?page=admin_perms_save">
  <?= csrf_field() ?>
<table>
<tr><th>Page</th><?php foreach ($roles as $r): ?><th style="font-size:10.5px"><?= e($r) ?></th><?php endforeach; ?></tr>
<?php foreach ($pages as $pg): ?>
<tr><td><b><?= e($pg) ?></b></td>
<?php foreach ($roles as $r): $locked = ($r==='Administrator' && strpos($pg,'admin')===0) || ($r==='Administrator' && $pg==='audit'); ?>
  <td style="text-align:center">
    <input type="checkbox" name="perm[<?= e($pg) ?>][<?= e($r) ?>]" style="width:auto"
      <?= isset($map[$pg][$r]) ? 'checked' : '' ?> <?= $locked ? 'checked disabled' : '' ?>>
  </td>
<?php endforeach; ?></tr>
<?php endforeach; ?>
</table>
<button>Save permissions</button>
</form>
