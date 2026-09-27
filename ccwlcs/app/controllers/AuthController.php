<?php
// Login, logout, sign-up and the role-specific dashboard.
class AuthController {
  public function loginForm(){ if (Auth::user()) redirect('dashboard'); view('login'); }
  public function login(){
    if (Auth::attempt($_POST['username'] ?? '', $_POST['password'] ?? '')) redirect('dashboard');
    flash('Invalid username or password.', 'error'); redirect('login');
  }
  public function signupForm(){ view('signup', ['departments'=>UserModel::departments()]); }
  public function signup(){
    $un = trim($_POST['username'] ?? '');
    if ($un === '' || strlen($_POST['password'] ?? '') < 5) { flash('Username required and password must be at least 5 characters.', 'error'); redirect('signup'); }
    if (UserModel::usernameExists($un)) { flash('That username is already taken.', 'error'); redirect('signup'); }
    UserModel::signup($un, $_POST['password'], $_POST['full_name'], $_POST['role'], $_POST['department_id']);
    flash('Account request submitted. An Administrator must activate your account before you can log in.');
    redirect('login');
  }
  public function logout(){ Auth::logout(); redirect('login'); }

  public function dashboard(){
    $db = Db::get(); $stats = [];
    $one = function($sql, $args=[]) use ($db) { $st=$db->prepare($sql); $st->execute($args); return $st->fetch()['c']; };
    switch (Auth::role()) {
      case 'Receptionist':
        $stats = ['Registered patients'=>$one("SELECT COUNT(*) c FROM patient"),
                  'Registered today'=>$one("SELECT COUNT(*) c FROM patient WHERE DATE(registered_date)=CURDATE()")];
        break;
      case 'OPDDoctor':
        $stats = ['Waiting in OPD queue'=>$one("SELECT COUNT(*) c FROM opd_queue WHERE status='Waiting'"),
                  'My consultations today'=>$one("SELECT COUNT(*) c FROM encounter WHERE doctor_id=? AND DATE(created_at)=CURDATE()", [Auth::id()]),
                  'Unread alerts'=>$one("SELECT COUNT(*) c FROM notification WHERE recipient_user_id=? AND status='Unread'", [Auth::id()]),
                  'Results awaiting me'=>$one("SELECT COUNT(*) c FROM lab_request r JOIN lab_result s ON s.request_id=r.request_id WHERE r.doctor_id=? AND s.accept_status='Accepted'", [Auth::id()])];
        break;
      case 'ClinicDoctor':
        $stats = ['Referrals to my clinic'=>$one("SELECT COUNT(*) c FROM encounter WHERE type='OPD' AND pathway='Refer' AND referral_department_id=?", [Auth::dept()]),
                  'My visits today'=>$one("SELECT COUNT(*) c FROM encounter WHERE doctor_id=? AND DATE(created_at)=CURDATE()", [Auth::id()]),
                  'Unread alerts'=>$one("SELECT COUNT(*) c FROM notification WHERE recipient_user_id=? AND status='Unread'", [Auth::id()])];
        break;
      case 'WardDoctor':
        $stats = ['Patients in my ward'=>$one("SELECT COUNT(*) c FROM admission WHERE department_id=? AND status='Admitted'", [Auth::dept()]),
                  "Today's notes written"=>$one("SELECT COUNT(*) c FROM encounter WHERE doctor_id=? AND type='WARD' AND note_date=CURDATE()", [Auth::id()]),
                  'Unread alerts'=>$one("SELECT COUNT(*) c FROM notification WHERE recipient_user_id=? AND status='Unread'", [Auth::id()])];
        break;
      case 'WardNurse':
        $stats = ['Pending admission orders'=>$one("SELECT COUNT(*) c FROM admission WHERE department_id=? AND status='Pending'", [Auth::dept()]),
                  'Free beds'=>$one("SELECT COUNT(*) c FROM bed WHERE department_id=? AND status='Free'", [Auth::dept()]),
                  'Admitted patients'=>$one("SELECT COUNT(*) c FROM admission WHERE department_id=? AND status='Admitted'", [Auth::dept()])];
        break;
      case 'LabPersonnel':
        $stats = ['Pending requests'=>$one("SELECT COUNT(*) c FROM lab_request WHERE status<>'Completed'"),
                  'Completed today'=>$one("SELECT COUNT(*) c FROM lab_request WHERE status='Completed' AND DATE(request_datetime)>=CURDATE()-INTERVAL 7 DAY")];
        break;
      case 'RadiologyPersonnel':
        $stats = ['Pending scan orders'=>$one("SELECT COUNT(*) c FROM rad_request WHERE status='Requested'"),
                  'Uploaded this week'=>$one("SELECT COUNT(*) c FROM rad_image WHERE upload_time>=CURDATE()-INTERVAL 7 DAY")];
        break;
      case 'Pharmacist':
        $stats = ['Pending prescriptions'=>$one("SELECT COUNT(*) c FROM prescription WHERE status='Pending'"),
                  'Dispensed today'=>$one("SELECT COUNT(*) c FROM prescription WHERE status='Dispensed' AND DATE(dispensed_at)=CURDATE()")];
        break;
      case 'Administrator':
        $stats = ['Active accounts'=>$one("SELECT COUNT(*) c FROM user WHERE status='Active'"),
                  'Audit entries today'=>$one("SELECT COUNT(*) c FROM audit_log WHERE DATE(created_at)=CURDATE()"),
                  'Patients in system'=>$one("SELECT COUNT(*) c FROM patient")];
        break;
    }
    $queue = [];
    if (Auth::role() === 'OPDDoctor') {
      $queue = OpdQueue::waiting();
    }
    $notifs = array_values(array_filter(Notification::forUser(Auth::id()), fn($n) => $n['status'] === 'Unread'));
    view('dashboard', ['stats'=>$stats, 'queue'=>$queue, 'notifs'=>$notifs]);
  }
}
