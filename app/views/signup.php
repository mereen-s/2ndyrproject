<div style="max-width:430px;margin:20px auto">
  <h2 style="text-align:center">Create a staff account</h2>
  <p class="note" style="text-align:center">New accounts are created inactive and must be approved by an Administrator before first login (hospital accounts are never self-activated).</p>
  <form method="post" action="<?= BASE_URL ?>/index.php?page=do_signup">
  <?= csrf_field() ?>
    <label>Full name</label><input name="full_name" required>
    <label>Username</label><input name="username" required>
    <label>Password</label><input name="password" type="password" required minlength="5">
    <label>Role</label>
    <select name="role">
      <?php foreach (['Receptionist','OPDDoctor','ClinicDoctor','WardDoctor','WardNurse','LabPersonnel','RadiologyPersonnel','Pharmacist'] as $r): ?>
        <option value="<?= $r ?>"><?= e(role_label($r)) ?></option>
      <?php endforeach; ?>
    </select>
    <label>Department (ward / clinic roles only)</label>
    <select name="department_id"><option value="">&mdash; none &mdash;</option>
      <?php foreach ($departments as $d): ?><option value="<?= $d['department_id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
    </select>
    <button style="width:100%">Request account</button>
  </form>
  <p style="text-align:center;margin-top:14px"><a href="<?= BASE_URL ?>/index.php?page=login">Back to log in</a></p>
</div>