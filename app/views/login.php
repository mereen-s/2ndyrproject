<div style="max-width:340px;margin:30px auto;text-align:center">
  <h2>Log in</h2>
  <form method="post" action="<?= BASE_URL ?>/index.php?page=do_login">
  <?= csrf_field() ?>
    <label style="text-align:left">Username</label><input name="username" required>
    <label style="text-align:left">Password</label><input name="password" type="password" required>
    <button>Log in</button>
  </form>
  <p style="margin-top:14px">New staff member? <a href="<?= BASE_URL ?>/index.php?page=signup">Create an account</a></p>
  <p class="note">Role-based access: you are routed to your role's dashboard after login.</p>
</div>
