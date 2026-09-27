<?php $u = Auth::user(); $unread = $u ? Notification::unreadCount($u['id']) : 0; ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>CCWLCS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__.'/../../assets/css/style.css') ?>">
</head>
<body>
<header class="app">
  <h1>Hospital Coordination System</h1>
  <span class="spacer"></span>
  <?php if ($u): ?>
    <div class="who">
      <span class="avatar"><?= e(initials($u['name'])) ?></span>
      <span class="who-text"><b><?= e($u['name']) ?></b><small><?= e(role_label($u['role'])) ?></small></span>
    </div>
    <a class="icon-btn" title="Notifications" href="<?= BASE_URL ?>/index.php?page=notifications">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
      <?php if($unread): ?><span class="badge"><?= $unread ?></span><?php endif; ?>
    </a>
    <a class="logout-btn" href="<?= BASE_URL ?>/index.php?page=logout">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  <?php endif; ?>
</header>
<?php if ($u): ?>
<nav class="menu">
  <a href="<?= BASE_URL ?>/index.php?page=dashboard">Dashboard</a>
  <?php if ($u['role']==='Receptionist'): ?><a href="<?= BASE_URL ?>/index.php?page=registration">Registration &amp; Search</a><?php endif; ?>
  <?php if ($u['role']==='ClinicDoctor'): ?><a href="<?= BASE_URL ?>/index.php?page=clinic">Clinic referrals</a><?php endif; ?>
  <?php if ($u['role']==='WardDoctor'): ?><a href="<?= BASE_URL ?>/index.php?page=ward">My ward</a><?php endif; ?>
  <?php if ($u['role']==='WardNurse'): ?><a href="<?= BASE_URL ?>/index.php?page=nurse">Admissions &amp; beds</a><?php endif; ?>
  <?php if ($u['role']==='LabPersonnel'): ?><a href="<?= BASE_URL ?>/index.php?page=lab">Lab queue</a><?php endif; ?>
  <?php if ($u['role']==='RadiologyPersonnel'): ?><a href="<?= BASE_URL ?>/index.php?page=radiology">Radiology queue</a><?php endif; ?>
  <?php if ($u['role']==='Pharmacist'): ?><a href="<?= BASE_URL ?>/index.php?page=pharmacy">Dispensary</a><?php endif; ?>
  <?php if ($u['role']==='Administrator'): ?>
    <a href="<?= BASE_URL ?>/index.php?page=admin_users">Users</a>
    <a href="<?= BASE_URL ?>/index.php?page=audit">Audit log</a>
    <a href="<?= BASE_URL ?>/index.php?page=admin_perms">Permissions</a>
    <a href="<?= BASE_URL ?>/index.php?page=reports">Reports</a>
  <?php endif; ?>
  <?php if (in_array($u['role'],['Administrator','LabPersonnel'])): ?>
    <a href="<?= BASE_URL ?>/index.php?page=report_lab">Lab report</a>
  <?php endif; ?>
  <?php if (in_array($u['role'],['Administrator','WardDoctor','WardNurse'])): ?>
    <a href="<?= BASE_URL ?>/index.php?page=report_ward">Ward report</a>
  <?php endif; ?>
  <?php if (in_array($u['role'],['Administrator','Receptionist'])): ?>
    <a href="<?= BASE_URL ?>/index.php?page=report_patients">Patient report</a>
  <?php endif; ?>
  <span class="nav-clock" id="navClock" aria-label="Current date and time">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
    <span class="nav-clock-text">&nbsp;</span>
  </span>
</nav>
<script>
(function(){
  var el = document.querySelector('#navClock .nav-clock-text');
  if(!el) return;
  var days=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  var months=['January','February','March','April','May','June','July','August','September','October','November','December'];
  function tick(){
    var d=new Date();
    var h=d.getHours(), m=d.getMinutes(), s=d.getSeconds();
    var ampm=h>=12?'PM':'AM';
    h=h%12; if(h===0)h=12;
    var mm=(m<10?'0':'')+m, ss=(s<10?'0':'')+s;
    var date=days[d.getDay()]+', '+d.getDate()+' '+months[d.getMonth()]+' '+d.getFullYear();
    el.textContent=date+'  \u00B7  '+h+':'+mm+':'+ss+' '+ampm;
  }
  tick(); setInterval(tick,1000);
})();
</script>
<?php endif; ?>
<main>
<?php if ($m = flash()): ?><div class="flash <?= flash_type() ?>"><?= e($m) ?></div><?php endif; ?>
