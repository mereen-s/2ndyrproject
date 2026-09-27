<?php
// Front controller - every request comes through here.
session_start();
require __DIR__.'/config/config.php';
require __DIR__.'/config/database.php';
require __DIR__.'/app/core/Helpers.php';
require __DIR__.'/app/core/Audit.php';
require __DIR__.'/app/core/Auth.php';
require __DIR__.'/app/core/Validator.php';
spl_autoload_register(function($c){
  foreach (['models','controllers'] as $d) {
    $f = __DIR__."/app/$d/$c.php";
    if (file_exists($f)) { require $f; return; }
  }
});

$DOCTORS = ['OPDDoctor','ClinicDoctor','WardDoctor'];
$page = $_GET['page'] ?? 'login';

// route => [Controller, method, allowedRoles|null(any logged-in)|'public']
$routes = [
 'login'              => ['AuthController','loginForm','public'],
 'signup'             => ['AuthController','signupForm','public'],
 'do_signup'          => ['AuthController','signup','public'],
 'do_login'           => ['AuthController','login','public'],
 'logout'             => ['AuthController','logout',null],
 'dashboard'          => ['AuthController','dashboard',null],
 'registration'       => ['PatientController','registration',['Receptionist']],
 'register_patient'   => ['PatientController','register',['Receptionist']],
 'profile'            => ['PatientController','profile',['Receptionist']],
 'patient_update'     => ['PatientController','updatePatient',['Receptionist']],
 'patient_delete'     => ['PatientController','deletePatient',['Receptionist']],
 'opd_assign'         => ['PatientController','opdAssign',['Receptionist']],
 'api_patient_search' => ['PatientController','apiSearch',array_merge(['Receptionist'],$DOCTORS)],
 'history'            => ['PatientController','history',$DOCTORS],
 'orders'             => ['PatientController','orders',$DOCTORS],
 'prescribe'          => ['PatientController','prescribe',$DOCTORS],
 'request_lab'        => ['PatientController','requestLab',$DOCTORS],
 'request_rad'        => ['PatientController','requestRad',$DOCTORS],
 'opd_form'           => ['OpdController','form',['OPDDoctor']],
 'opd_save'           => ['OpdController','save',['OPDDoctor']],
 'clinic'             => ['ClinicController','index',['ClinicDoctor']],
 'clinic_form'        => ['ClinicController','form',['ClinicDoctor']],
 'clinic_save'        => ['ClinicController','save',['ClinicDoctor']],
 'clinic_print'       => ['ClinicController','printSummary',['ClinicDoctor']],
 'ward'               => ['WardController','index',['WardDoctor']],
 'ward_note'          => ['WardController','noteForm',['WardDoctor']],
 'ward_note_save'     => ['WardController','saveNote',['WardDoctor']],
 'discharge_preview'  => ['WardController','dischargePreview',['WardDoctor']],
 'discharge'          => ['WardController','discharge',['WardDoctor']],
 'discharge_print'    => ['WardController','dischargePrint',['WardDoctor']],
 'nurse'              => ['NurseController','index',['WardNurse']],
 'assign_bed'         => ['NurseController','assignBed',['WardNurse']],
 'med_admin'          => ['NurseController','medAdmin',['WardNurse']],
 'admission_cancel'   => ['NurseController','cancelAdmission',['WardNurse']],
 'ward_info_save'     => ['NurseController','wardInfoSave',['WardNurse']],
 'lab'                => ['LabController','queue',['LabPersonnel']],
 'lab_entry'          => ['LabController','entry',['LabPersonnel']],
 'lab_save'           => ['LabController','saveEntry',['LabPersonnel']],
 'lab_accept'         => ['LabController','accept',['LabPersonnel']],
 'lab_cancel'         => ['LabController','cancel',['LabPersonnel']],
 'lab_reject'         => ['LabController','reject',['LabPersonnel']],
 'radiology'          => ['RadiologyController','queue',['RadiologyPersonnel']],
 'rad_upload'         => ['RadiologyController','uploadForm',['RadiologyPersonnel']],
 'rad_upload_save'    => ['RadiologyController','upload',['RadiologyPersonnel']],
 'pharmacy'           => ['PharmacyController','queue',['Pharmacist']],
 'dispense'           => ['PharmacyController','dispense',['Pharmacist']],
 'presc_cancel'       => ['PharmacyController','cancel',['Pharmacist']],
 'admin_users'        => ['AdminController','users',['Administrator']],
 'admin_user_create'  => ['AdminController','createUser',['Administrator']],
 'admin_user_update'  => ['AdminController','updateUser',['Administrator']],
 'audit'              => ['AdminController','audit',['Administrator']],
 'admin_perms'        => ['AdminController','permissions',['Administrator']],
 'admin_perm_role'    => ['AdminController','permissionsRole',['Administrator']],
 'admin_perms_save'   => ['AdminController','permissionsSave',['Administrator']],
 'notifications'      => ['NotificationController','index',null],
 'notif_open'         => ['NotificationController','open',null],
 // reports
 'reports'            => ['ReportController','index',   ['Administrator']],
 'report_lab'         => ['ReportController','lab',     ['Administrator','LabPersonnel']],
 'report_ward'        => ['ReportController','ward',    ['Administrator','WardDoctor','WardNurse']],
 'report_patients'    => ['ReportController','patients',['Administrator','Receptionist']],
];

if (!isset($routes[$page])) { http_response_code(404); die('Page not found'); }
[$ctrl, $method, $access] = $routes[$page];
if ($access !== 'public') {
  if ($access === null) { Auth::check(); }
  else {
    Auth::check();
    // permissions are data, managed by the Administrator; the array is the fallback seed
    $allowed = Permission::rolesFor($page) ?: $access;
    if (!in_array(Auth::role(), $allowed)) { http_response_code(403); die('Access denied for your role.'); }
  }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_verify();   // every form post must carry the session token
(new $ctrl)->$method();
