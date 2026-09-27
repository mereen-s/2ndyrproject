<?php $u = Auth::user(); $unread = $u ? Notification::unreadCount($u['id']) : 0; ?>
<?php
// menu icons (also reused by the dashboard) and the page each menu item stands for
$svg = fn($d) => '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$d.'</svg>';
$navIcons = [
  'dashboard' => $svg('<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>'),
  'register'  => $svg('<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M19 8v6M16 11h6"/>'),
  'clinic'    => $svg('<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1"/><path d="M9 11h6M9 15h4"/>'),
  'bed'       => $svg('<path d="M3 18V6"/><path d="M3 13h18v5"/><circle cx="7.5" cy="10" r="2"/><path d="M11 13V9h7a3 3 0 0 1 3 3v1"/>'),
  'lab'       => $svg('<path d="M9 3h6"/><path d="M10 3v6L4.5 19a1.5 1.5 0 0 0 1.3 2h12.4a1.5 1.5 0 0 0 1.3-2L14 9V3"/><path d="M7 15h10"/>'),
  'scan'      => $svg('<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-8 8"/>'),
  'pill'      => $svg('<path d="M10.5 20.5a4.95 4.95 0 0 1-7-7l6-6a4.95 4.95 0 0 1 7 7z"/><path d="m8.5 8.5 7 7"/>'),
  'users'     => $svg('<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M16 4a4 4 0 0 1 0 8M22 21a7 7 0 0 0-4-6.3"/>'),
  'list'      => $svg('<path d="M8 6h13M8 12h13M8 18h13"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/>'),
  'shield'    => $svg('<path d="M12 3 4 6v6c0 5 3.4 8.3 8 9 4.6-.7 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>'),
  'report'    => $svg('<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 17v-3M12 17v-6M16 17v-8"/>'),
  'bell'      => $svg('<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>'),
  'note'      => $svg('<path d="M4 4h12l4 4v12H4z"/><path d="M8 12h8M8 16h5"/>'),
  'check'     => $svg('<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>'),
  'activity'  => $svg('<path d="M3 12h4l2-5 4 10 2-5h6"/>'),
];
$curPage = $_GET['page'] ?? 'dashboard';
$isActive = fn(array $pages) => in_array($curPage, $pages, true) ? ' class="active"' : '';
?>
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
  <a href="<?= BASE_URL ?>/index.php?page=dashboard"<?= $isActive(['dashboard']) ?>><?= $navIcons['dashboard'] ?>Dashboard</a>
  <?php if ($u['role']==='Receptionist'): ?><a href="<?= BASE_URL ?>/index.php?page=registration"<?= $isActive(['registration','profile']) ?>><?= $navIcons['register'] ?>Registration &amp; Search</a><?php endif; ?>
  <?php if ($u['role']==='ClinicDoctor'): ?><a href="<?= BASE_URL ?>/index.php?page=clinic"<?= $isActive(['clinic','clinic_form','clinic_print']) ?>><?= $navIcons['clinic'] ?>Clinic referrals</a><?php endif; ?>
  <?php if ($u['role']==='WardDoctor'): ?><a href="<?= BASE_URL ?>/index.php?page=ward"<?= $isActive(['ward','ward_note','discharge_preview']) ?>><?= $navIcons['bed'] ?>My ward</a><?php endif; ?>
  <?php if ($u['role']==='WardNurse'): ?><a href="<?= BASE_URL ?>/index.php?page=nurse"<?= $isActive(['nurse']) ?>><?= $navIcons['bed'] ?>Admissions &amp; beds</a><?php endif; ?>
  <?php if ($u['role']==='LabPersonnel'): ?><a href="<?= BASE_URL ?>/index.php?page=lab"<?= $isActive(['lab','lab_entry']) ?>><?= $navIcons['lab'] ?>Lab queue</a><?php endif; ?>
  <?php if ($u['role']==='RadiologyPersonnel'): ?><a href="<?= BASE_URL ?>/index.php?page=radiology"<?= $isActive(['radiology','rad_upload']) ?>><?= $navIcons['scan'] ?>Radiology queue</a><?php endif; ?>
  <?php if ($u['role']==='Pharmacist'): ?><a href="<?= BASE_URL ?>/index.php?page=pharmacy"<?= $isActive(['pharmacy']) ?>><?= $navIcons['pill'] ?>Dispensary</a><?php endif; ?>
  <?php if ($u['role']==='Administrator'): ?>
    <a href="<?= BASE_URL ?>/index.php?page=admin_users"<?= $isActive(['admin_users']) ?>><?= $navIcons['users'] ?>Users</a>
    <a href="<?= BASE_URL ?>/index.php?page=audit"<?= $isActive(['audit']) ?>><?= $navIcons['list'] ?>Audit log</a>
    <a href="<?= BASE_URL ?>/index.php?page=admin_perms"<?= $isActive(['admin_perms','admin_perm_role']) ?>><?= $navIcons['shield'] ?>Permissions</a>
    <a href="<?= BASE_URL ?>/index.php?page=reports"<?= $isActive(['reports']) ?>><?= $navIcons['report'] ?>Reports</a>
    <a href="<?= BASE_URL ?>/index.php?page=report_lab"<?= $isActive(['report_lab']) ?>><?= $navIcons['report'] ?>Lab report</a>
    <a href="<?= BASE_URL ?>/index.php?page=report_ward"<?= $isActive(['report_ward']) ?>><?= $navIcons['report'] ?>Ward report</a>
    <a href="<?= BASE_URL ?>/index.php?page=report_patients"<?= $isActive(['report_patients']) ?>><?= $navIcons['report'] ?>Patient report</a>
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
