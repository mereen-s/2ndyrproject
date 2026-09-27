<?php
// Doses actually given by the nurse (separate from what the doctor prescribed).
class MedicationAdmin {
  public static function add($admissionId, $nurseId, $drugDose, $route) {
    $st = Db::get()->prepare("INSERT INTO medication_admin(admission_id,nurse_id,drug_dose,route) VALUES (?,?,?,?)");
    $st->execute([$admissionId, $nurseId, $drugDose, $route]);
    Audit::log('MED_ADMIN', 'admission:'.$admissionId);
  }
  public static function forAdmission($admissionId) {
    $st = Db::get()->prepare(
     "SELECT m.*, u.full_name nurse_name FROM medication_admin m JOIN user u ON u.user_id=m.nurse_id
      WHERE m.admission_id=? ORDER BY m.admin_time");
    $st->execute([$admissionId]); return $st->fetchAll();
  }
}
