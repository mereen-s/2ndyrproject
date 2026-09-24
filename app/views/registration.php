<h2>Patient Registration &amp; Search</h2>
<form method="post" action="<?= BASE_URL ?>/index.php?page=register_patient">
  <?= csrf_field() ?>
  <div class="row">
    <div style="flex:2"><label>Full name</label><input name="name" required></div>
    <div><label>NIC</label><input name="nic"></div>
    <div><label>Date of birth</label><input name="dob" type="date"></div>
    <div><label>Gender</label><select name="gender"><option>M</option><option>F</option></select></div>
  </div>
  <div class="row">
    <div><label>Contact</label><input name="contact"></div>
    <div style="flex:2"><label>Address</label><input name="address"></div>
  </div>
  <button>Register patient</button>
  <span class="note">The patient ID is generated automatically.</span>
</form>
<hr>
<h3>Search</h3>
<input id="psearch" placeholder="Type name / NIC / patient ID...">
<div id="presults"></div>
<p class="note">This view shows patient demographics only.</p>
<script>document.addEventListener('DOMContentLoaded', function(){ attachPatientSearch('psearch','presults','profile'); });</script>
