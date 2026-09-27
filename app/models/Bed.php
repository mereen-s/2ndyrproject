<?php
// Beds per department and occupancy figures.
class Bed {

  // read
  public static function forDept($deptId) {
    $st = Db::get()->prepare(
      "SELECT * FROM bed WHERE department_id=? ORDER BY bed_number");
    $st->execute([$deptId]); return $st->fetchAll();
  }

  public static function find($bedId) {
    $st = Db::get()->prepare("SELECT * FROM bed WHERE bed_id=?");
    $st->execute([$bedId]); return $st->fetch();
  }

  public static function availableForDept($deptId) {
    $st = Db::get()->prepare(
      "SELECT * FROM bed WHERE department_id=? AND status='Free' ORDER BY bed_number");
    $st->execute([$deptId]); return $st->fetchAll();
  }

  // write
  public static function occupy($bedId) {
    Db::get()->prepare("UPDATE bed SET status='Occupied' WHERE bed_id=?")
             ->execute([$bedId]);
    Audit::log('BED_OCCUPY', 'bed:'.$bedId);
  }

  public static function release($bedId) {
    Db::get()->prepare("UPDATE bed SET status='Free' WHERE bed_id=?")
             ->execute([$bedId]);
    Audit::log('BED_RELEASE', 'bed:'.$bedId);
  }

  // reporting helpers
  public static function occupancyStats() {
    $db = Db::get();
    $total     = (int)$db->query("SELECT COUNT(*) FROM bed")->fetchColumn();
    $occupied  = (int)$db->query("SELECT COUNT(*) FROM bed WHERE status='Occupied'")->fetchColumn();
    $available = $total - $occupied;
    $pending   = (int)$db->query("SELECT COUNT(*) FROM admission WHERE status='Pending'")->fetchColumn();
    return [
      'total_beds'       => $total,
      'occupied'         => $occupied,
      'available'        => $available,
      'pending_admission'=> $pending,
    ];
  }

  public static function occupancyByDept() {
    return Db::get()->query(
      "SELECT d.name dept_name,
              COUNT(b.bed_id)                                      AS total,
              SUM(b.status='Occupied')                             AS occupied,
              SUM(b.status='Free')                            AS available
       FROM department d
       LEFT JOIN bed b ON b.department_id=d.department_id
       GROUP BY d.department_id, d.name
       ORDER BY d.name"
    )->fetchAll();
  }

  public static function longestStays($limit = 10) {
    $st = Db::get()->prepare(
      "SELECT a.admission_id, a.patient_id, p.name patient_name,
              b.bed_number, d.name dept_name, a.diagnosis, a.admit_date,
              DATEDIFF(CURDATE(), a.admit_date) days_admitted
       FROM admission a
       JOIN patient p ON p.patient_id=a.patient_id
       JOIN department d ON d.department_id=a.department_id
       LEFT JOIN bed b ON b.bed_id=a.bed_id
       WHERE a.status='Admitted'
       ORDER BY days_admitted DESC
       LIMIT ".(int)$limit);   // LIMIT must be a number, not a quoted parameter
    $st->execute(); return $st->fetchAll();
  }
}
