<?php
// Ward admissions: pending order -> admitted with a bed -> discharged.
class Admission {

  // create
  public static function createPending($pid, $deptId, $diagnosis = '') {
    $st = Db::get()->prepare(
      "INSERT INTO admission(patient_id,department_id,status,diagnosis) VALUES (?,?,'Pending',?)");
    $st->execute([$pid, $deptId, $diagnosis]);
    $id = Db::get()->lastInsertId();
    Audit::log('ADMISSION_ORDER', 'admission:'.$id.' patient:'.$pid);
    return $id;
  }

  // read
  public static function find($id) {
    $st = Db::get()->prepare(
     "SELECT a.*, p.name patient_name, p.dob, p.gender, p.nic,
             p.contact, d.name dept_name, b.bed_number
      FROM admission a
      JOIN patient p    ON p.patient_id    = a.patient_id
      JOIN department d ON d.department_id = a.department_id
      LEFT JOIN bed b   ON b.bed_id        = a.bed_id
      WHERE a.admission_id=?");
    $st->execute([$id]); return $st->fetch();
  }

  public static function pendingForDept($deptId) {
    $st = Db::get()->prepare(
     "SELECT a.*, p.name patient_name, p.patient_id
      FROM admission a JOIN patient p ON p.patient_id=a.patient_id
      WHERE a.department_id=? AND a.status='Pending'
      ORDER BY a.created_at");
    $st->execute([$deptId]); return $st->fetchAll();
  }

  public static function admittedForDept($deptId) {
    $st = Db::get()->prepare(
     "SELECT a.*, p.name patient_name, p.patient_id, p.dob, b.bed_number
      FROM admission a
      JOIN patient p  ON p.patient_id = a.patient_id
      LEFT JOIN bed b ON b.bed_id     = a.bed_id
      WHERE a.department_id=? AND a.status='Admitted'
      ORDER BY b.bed_number");
    $st->execute([$deptId]); return $st->fetchAll();
  }

  public static function activeForPatient($pid) {
    $st = Db::get()->prepare(
     "SELECT a.*, d.name dept_name, b.bed_number
      FROM admission a
      JOIN department d ON d.department_id=a.department_id
      LEFT JOIN bed b   ON b.bed_id=a.bed_id
      WHERE a.patient_id=? AND a.status IN ('Pending','Admitted')
      ORDER BY a.created_at DESC LIMIT 1");
    $st->execute([$pid]); return $st->fetch();
  }

  public static function historyForPatient($pid) {
    $st = Db::get()->prepare(
     "SELECT a.*, d.name dept_name, b.bed_number
      FROM admission a
      JOIN department d ON d.department_id=a.department_id
      LEFT JOIN bed b   ON b.bed_id=a.bed_id
      WHERE a.patient_id=?
      ORDER BY a.created_at DESC");
    $st->execute([$pid]); return $st->fetchAll();
  }

  // update
  public static function assignBed($admissionId, $bedId) {
    $db = Db::get();
    $db->prepare(
      "UPDATE admission SET bed_id=?, status='Admitted', admit_date=NOW() WHERE admission_id=?")
    ->execute([$bedId, $admissionId]);
    $db->prepare("UPDATE bed SET status='Occupied' WHERE bed_id=?")
    ->execute([$bedId]);
    Audit::log('BED_ASSIGN', 'admission:'.$admissionId.' bed:'.$bedId);
  }

  // cancel
  public static function cancelPending($id) {
    $st = Db::get()->prepare("SELECT status FROM admission WHERE admission_id=?");
    $st->execute([$id]); $row = $st->fetch();
    if (!$row || $row['status'] !== 'Pending') return false;
    Db::get()->prepare("DELETE FROM admission WHERE admission_id=?")->execute([$id]);
    Audit::log('ADMISSION_CANCEL', 'admission:'.$id);
    return true;
  }

  // discharge
  public static function discharge($admissionId) {
    $a  = self::find($admissionId);
    $db = Db::get();
    $db->prepare(
      "UPDATE admission SET status='Discharged', discharge_date=NOW() WHERE admission_id=?")
    ->execute([$admissionId]);
    if ($a && $a['bed_id']) {
      $db->prepare("UPDATE bed SET status='Free' WHERE bed_id=?")
         ->execute([$a['bed_id']]);
      Audit::log('BED_RELEASE', 'bed:'.$a['bed_id']);
    }
    Audit::log('DISCHARGE', 'admission:'.$admissionId.' patient:'.$a['patient_id']);
  }

  // reporting helpers
  public static function currentByDept() {
    return Db::get()->query(
      "SELECT d.name dept_name, COUNT(a.admission_id) admitted
       FROM department d
       LEFT JOIN admission a ON a.department_id=d.department_id AND a.status='Admitted'
       GROUP BY d.department_id, d.name
       ORDER BY d.name"
    )->fetchAll();
  }

  public static function avgLengthOfStay($days = 30): ?float {
    $st = Db::get()->prepare(
      "SELECT ROUND(AVG(DATEDIFF(discharge_date, admit_date)),1)
       FROM admission WHERE status='Discharged'
         AND discharge_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)");
    $st->execute([$days]);
    $val = $st->fetchColumn();
    return ($val !== null && $val !== false) ? (float)$val : null;
  }
}
