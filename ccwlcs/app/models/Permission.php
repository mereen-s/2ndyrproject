<?php
// Which roles may open which page. Read on every request, edited by the Administrator (UC-42).
// Pages are managed in bundles: a screen always travels with the actions it needs.
class Permission {

  const ROLES = ['Receptionist','OPDDoctor','ClinicDoctor','WardDoctor','WardNurse',
                 'LabPersonnel','RadiologyPersonnel','Pharmacist','Administrator'];

  // module => [ bundle key => [label, [pages]] ]
  const GROUPS = [
    'Registration' => [
      'register'       => ['Register and search patients',            ['registration','register_patient']],
      'profile'        => ['View and edit patient demographics',      ['profile','patient_update']],
      'patient_delete' => ['Delete patients registered in error',     ['patient_delete']],
      'opd_assign'     => ['Assign patients to the OPD queue',        ['opd_assign']],
      'search'         => ['Live patient search',                     ['api_patient_search']],
    ],
    'Clinical records' => [
      'history'        => ['View patient medical history',            ['history']],
      'orders'         => ['Prescribe and request lab tests / scans', ['orders','prescribe','request_lab','request_rad']],
    ],
    'OPD' => [
      'opd'            => ['Record OPD consultations',                ['opd_form','opd_save']],
    ],
    'Clinic' => [
      'clinic'         => ['Clinic queue and visits',                 ['clinic','clinic_form','clinic_save']],
      'clinic_print'   => ['Print clinic visit summaries',            ['clinic_print']],
    ],
    'Ward' => [
      'ward'           => ["View own ward's admitted patients",       ['ward']],
      'ward_notes'     => ['Write daily progress notes',              ['ward_note','ward_note_save']],
      'discharge'      => ['Discharge patients and print summaries',  ['discharge_preview','discharge','discharge_print']],
    ],
    'Nursing' => [
      'nurse'          => ['Admissions and bed allocation',           ['nurse','assign_bed']],
      'admission_cancel'=>['Cancel pending admission orders',         ['admission_cancel']],
      'med_admin'      => ['Record administered medication',          ['med_admin']],
      'ward_info'      => ['Update ward information',                 ['ward_info_save']],
    ],
    'Laboratory' => [
      'lab'            => ['Lab queue and result entry',              ['lab','lab_entry','lab_save']],
      'lab_verify'     => ['Accept or reject results',                ['lab_accept','lab_reject']],
      'lab_cancel'     => ['Cancel lab requests',                     ['lab_cancel']],
    ],
    'Radiology' => [
      'radiology'      => ['Radiology queue and scan upload',         ['radiology','rad_upload','rad_upload_save']],
    ],
    'Dispensary' => [
      'pharmacy'       => ['Dispensary queue and dispensing',         ['pharmacy','dispense']],
      'presc_cancel'   => ['Cancel prescriptions',                    ['presc_cancel']],
    ],
    'Administration' => [
      'admin_users'    => ['Manage user accounts',                    ['admin_users','admin_user_create','admin_user_update']],
      'admin_perms'    => ['Manage role permissions',                 ['admin_perms','admin_perm_role','admin_perms_save']],
      'audit'          => ['View the audit log',                      ['audit']],
    ],
    'Reports' => [
      'reports'        => ['Summary report',                          ['reports']],
      'report_lab'     => ['Laboratory report',                       ['report_lab']],
      'report_ward'    => ['Ward report',                             ['report_ward']],
      'report_patients'=> ['Patient registration report',             ['report_patients']],
    ],
  ];

  // the Administrator can never lock themselves out of these
  const LOCKED = ['Administrator' => ['admin_users','admin_perms','audit']];

  public static function rolesFor($page) {
    $st = Db::get()->prepare("SELECT role FROM permission WHERE page=?");
    $st->execute([$page]);
    return $st->fetchAll(PDO::FETCH_COLUMN);
  }

  public static function pagesForRole($role) {
    $st = Db::get()->prepare("SELECT page FROM permission WHERE role=?");
    $st->execute([$role]);
    return $st->fetchAll(PDO::FETCH_COLUMN);
  }

  // bundle keys this role currently holds (a bundle counts if any of its pages is granted)
  public static function grantedGroups($role) {
    $pages = array_flip(self::pagesForRole($role));
    $granted = [];
    foreach (self::GROUPS as $bundles) {
      foreach ($bundles as $key => [$label, $list]) {
        foreach ($list as $pg) { if (isset($pages[$pg])) { $granted[] = $key; break; } }
      }
    }
    return $granted;
  }

  public static function isLocked($role, $key) {
    return in_array($key, self::LOCKED[$role] ?? [], true);
  }

  // replace this role's permissions with the chosen bundles
  public static function saveForRole($role, array $keys) {
    $keys = array_unique(array_merge($keys, self::LOCKED[$role] ?? []));
    $pages = [];
    foreach (self::GROUPS as $bundles) {
      foreach ($bundles as $key => [$label, $list]) {
        if (in_array($key, $keys, true)) $pages = array_merge($pages, $list);
      }
    }
    $db = Db::get();
    $db->beginTransaction();
    $db->prepare("DELETE FROM permission WHERE role=?")->execute([$role]);
    $ins = $db->prepare("INSERT INTO permission(role,page) VALUES (?,?)");
    foreach (array_unique($pages) as $pg) $ins->execute([$role, $pg]);
    $db->commit();
    Audit::log('PERMISSIONS_UPDATE', 'role:'.$role);
  }
}
