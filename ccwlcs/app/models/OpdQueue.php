<?php
// Patients waiting to be seen at OPD: Waiting -> Seen.
class OpdQueue {
  public static function add($patientId) {
    Db::get()->prepare("INSERT INTO opd_queue(patient_id) VALUES (?)")->execute([$patientId]);
    Audit::log('OPD_ASSIGN', $patientId);
  }

  public static function waiting() {
    return Db::get()->query(
      "SELECT q.queue_id, q.assigned_at, p.patient_id, p.name
       FROM opd_queue q JOIN patient p ON p.patient_id = q.patient_id
       WHERE q.status = 'Waiting' ORDER BY q.assigned_at")->fetchAll();
  }

  public static function markSeen($patientId) {
    Db::get()->prepare("UPDATE opd_queue SET status='Seen' WHERE patient_id=? AND status='Waiting'")
             ->execute([$patientId]);
  }

  // used by the patient safe-delete, before the patient row itself goes
  public static function removeForPatient($patientId) {
    Db::get()->prepare("DELETE FROM opd_queue WHERE patient_id=?")->execute([$patientId]);
  }
}
