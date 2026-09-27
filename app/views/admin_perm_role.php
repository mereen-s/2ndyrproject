<h2>Permissions &mdash; <?= e(role_label($role)) ?></h2>
<p><a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=admin_perms">&larr; All roles</a></p>
<form method="post" action="<?= BASE_URL ?>/index.php?page=admin_perms_save">
  <?= csrf_field() ?>
  <input type="hidden" name="role" value="<?= e($role) ?>">
  <?php foreach ($groups as $module => $bundles): ?>
    <?php if ($module === Permission::ADMIN_ONLY_MODULE && $role !== 'Administrator'): ?>
      <h3><?= e($module) ?></h3>
      <p class="note">Reports cover every department, so they are available to the Administrator only.</p>
      <?php continue; ?>
    <?php endif; ?>
    <h3><?= e($module) ?></h3>
    <div class="perm-list">
      <?php foreach ($bundles as $key => $bundle): $label = $bundle[0]; $locked = Permission::isLocked($role, $key); ?>
        <label class="perm-item">
          <input type="checkbox" name="groups[<?= e($key) ?>]" value="1"
            <?= in_array($key, $granted, true) || $locked ? 'checked' : '' ?> <?= $locked ? 'disabled' : '' ?>>
          <span><?= e($label) ?><?= $locked ? ' <small class="note">(always on)</small>' : '' ?></span>
        </label>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  <div class="actions-row">
    <button>Save permissions</button>
    <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=admin_perms">Cancel</a>
  </div>
</form>
