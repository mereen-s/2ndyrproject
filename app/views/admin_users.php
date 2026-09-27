<h2>User accounts</h2>
<h3>Create account</h3>
<form method="post" action="<?= BASE_URL ?>/index.php?page=admin_user_create">
  <?= csrf_field() ?>
  <div class="row">
    <div><label>Username</label><input name="username" required></div>
    <div><label>Password</label><input name="password" required></div>
    <div style="flex:2"><label>Full name</label><input name="full_name" required></div>
  </div>
  <div class="row">
    <div><label>Role</label><select name="role">
      <?php foreach (['Receptionist','OPDDoctor','ClinicDoctor','WardDoctor','WardNurse','LabPersonnel','RadiologyPersonnel','Pharmacist','Administrator'] as $r): ?><option value="<?= $r ?>"><?= e(role_label($r)) ?></option><?php endforeach; ?>
    </select></div>
    <div><label>Department</label><select name="department_id"><option value="">&mdash;</option>
      <?php foreach ($departments as $d): ?><option value="<?= $d['department_id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
    </select></div>
  </div>
  <button>Create</button>
</form>
<h3>All accounts</h3>
<table><tr><th>User</th><th>Name</th><th>Role / Department / Status</th></tr>
<?php foreach ($users as $usr): ?>
<tr><td><b><?= e($usr['username']) ?></b></td><td><?= e($usr['full_name']) ?></td>
<td><form method="post" action="<?= BASE_URL ?>/index.php?page=admin_user_update" style="display:flex;gap:6px;flex-wrap:wrap">
  <?= csrf_field() ?>
  <input type="hidden" name="user_id" value="<?= $usr['user_id'] ?>">
  <select name="role"><?php foreach (['Receptionist','OPDDoctor','ClinicDoctor','WardDoctor','WardNurse','LabPersonnel','RadiologyPersonnel','Pharmacist','Administrator'] as $r): ?><option value="<?= $r ?>" <?= $usr['role']===$r?'selected':'' ?>><?= e(role_label($r)) ?></option><?php endforeach; ?></select>
  <select name="department_id"><option value="">&mdash;</option><?php foreach ($departments as $d): ?><option value="<?= $d['department_id'] ?>" <?= $usr['department_id']==$d['department_id']?'selected':'' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select>
  <select name="status"><option <?= $usr['status']==='Active'?'selected':'' ?>>Active</option><option <?= $usr['status']==='Inactive'?'selected':'' ?>>Inactive</option></select>
  <button>Save</button>
</form></td></tr>
<?php endforeach; ?></table>
<p class="note">Accounts are deactivated rather than deleted.</p>
