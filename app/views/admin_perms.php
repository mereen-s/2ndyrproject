<h2>Role permissions</h2>
<p class="note">Choose a role to see and change what it can access.</p>
<table>
  <tr><th>Role</th><th>Access</th><th></th></tr>
  <?php foreach ($roles as $r => $c): ?>
  <tr>
    <td><b><?= e(role_label($r)) ?></b></td>
    <td><?= $c[0] ?> of <?= $c[1] ?> permissions</td>
    <td class="actions"><a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=admin_perm_role&role=<?= urlencode($r) ?>">Manage</a></td>
  </tr>
  <?php endforeach; ?>
</table>
