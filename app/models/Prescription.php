<?php
// Prescriptions from any doctor, through to dispensing.
class Prescription {

  // create
  public static function create($pid, $doctorId, $drug, $dose, $freq, $dur) {
    $st = Db::get()->prepare(
      "INSERT INTO prescription(patient_id,doctor_id,drug,dose,frequency,duration)
       VALUES (?,?,?,?,?,?)");
    $st->execute([$pid, $doctorId, $drug, $dose, $freq, $dur]);
    Audit::log('PRESCRIBE', 'patient:'.$pid.' '.$drug.' '.$dose);
  }

  // read
  public static function pending() {
    return Db::get()->query(
     "SELECT pr.*, p.name patient_name, u.full_name doctor_name
      FROM prescription pr
      JOIN patient p ON p.patient_id=pr.patient_id
      JOIN user u ON u.user_id=pr.doctor_id
      WHERE pr.status='Pending'
      ORDER BY pr.prescribed_at"
    )->fetchAll();
  }

  public static function forPatient($pid) {
    $st = Db::get()->prepare(
     "SELECT pr.*, u.full_name doctor_name
      FROM prescription pr
      JOIN user u ON u.user_id=pr.doctor_id
      WHERE pr.patient_id=?
      ORDER BY pr.prescribed_at DESC");
    $st->execute([$pid]); return $st->fetchAll();
  }

  public static function find($id) {
    $st = Db::get()->prepare(
     "SELECT pr.*, p.name patient_name, u.full_name doctor_name
      FROM prescription pr
      JOIN patient p ON p.patient_id=pr.patient_id
      JOIN user u ON u.user_id=pr.doctor_id
      WHERE pr.prescription_id=?");
    $st->execute([$id]); return $st->fetch();
  }

  // dispense
  public static function dispense($id, $pharmacistId, $qty) {
    $st = Db::get()->prepare(
      "UPDATE prescription
       SET status='Dispensed', dispensed_by=?, dispensed_at=NOW(), quantity=?
       WHERE prescription_id=?");
    $st->execute([$pharmacistId, $qty, $id]);
    Audit::log('DISPENSE', 'prescription:'.$id);
  }

  // cancel
  public static function cancel($id) {
    $st = Db::get()->prepare(
      "SELECT status, drug, patient_id FROM prescription WHERE prescription_id=?");
    $st->execute([$id]); $row = $st->fetch();
    if (!$row || $row['status'] !== 'Pending') return false;
    Db::get()->prepare("DELETE FROM prescription WHERE prescription_id=?")->execute([$id]);
    Audit::log('PRESCRIPTION_CANCEL', 'prescription:'.$id.' '.$row['drug'].' patient:'.$row['patient_id']);
    return true;
  }

  // reporting helpers
  public static function countPending() {
    return (int)Db::get()
      ->query("SELECT COUNT(*) FROM prescription WHERE status='Pending'")
      ->fetchColumn();
  }

  public static function topDrugsByVolume($days = 30, $limit = 10) {
    $st = Db::get()->prepare(
      "SELECT drug, COUNT(*) cnt
       FROM prescription
       WHERE status='Dispensed' AND dispensed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
       GROUP BY drug ORDER BY cnt DESC LIMIT ".(int)$limit);
    $st->execute([$days]); return $st->fetchAll();
  }

  public static function dailyDispensing($days = 14) {
    $st = Db::get()->prepare(
      "SELECT DATE(dispensed_at) day, COUNT(*) cnt
       FROM prescription WHERE status='Dispensed'
         AND dispensed_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
       GROUP BY day ORDER BY day");
    $st->execute([$days]); return $st->fetchAll();
  }
}
