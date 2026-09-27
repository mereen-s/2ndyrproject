<?php
// Lab test orders and the patientID-testCode-requestNo barcode.
class LabRequest {

  // create
  public static function create($pid, $doctorId, $testCode) {
    $db = Db::get();
    $st = $db->prepare("SELECT COUNT(*) c FROM lab_request WHERE patient_id=? AND test_code=?");
    $st->execute([$pid, $testCode]); $no = $st->fetch()['c'] + 1;
    $barcode = sprintf('%s-%s-%03d', $pid, $testCode, $no);
    $st = $db->prepare(
      "INSERT INTO lab_request(patient_id,doctor_id,test_code,request_no,barcode) VALUES (?,?,?,?,?)");
    $st->execute([$pid, $doctorId, $testCode, $no, $barcode]);
    Audit::log('LAB_REQUEST', $barcode);
    return $barcode;
  }

  // read
  public static function pendingAll() {
    $st = Db::get()->query(
     "SELECT r.*, t.test_name, p.name patient_name
      FROM lab_request r
      JOIN test_type t ON t.test_code=r.test_code
      JOIN patient p ON p.patient_id=r.patient_id
      WHERE r.status <> 'Completed'
      ORDER BY r.request_datetime");
    return $st->fetchAll();
  }

  public static function find($id) {
    $st = Db::get()->prepare(
     "SELECT r.*, t.test_name, t.reference_range, p.name patient_name, u.full_name doctor_name
      FROM lab_request r
      JOIN test_type t ON t.test_code=r.test_code
      JOIN patient p ON p.patient_id=r.patient_id
      LEFT JOIN user u ON u.user_id=r.doctor_id
      WHERE r.request_id=?");
    $st->execute([$id]); return $st->fetch();
  }

  public static function resultsForPatient($pid) {
    $st = Db::get()->prepare(
     "SELECT r.barcode, r.request_datetime, t.test_name,
             res.finding, res.numeric_result, res.unit,
             res.accept_status, res.critical_flag
      FROM lab_request r
      JOIN test_type t ON t.test_code=r.test_code
      LEFT JOIN lab_result res ON res.request_id=r.request_id
      WHERE r.patient_id=? ORDER BY r.request_datetime DESC");
    $st->execute([$pid]); return $st->fetchAll();
  }

  // update
  public static function setStatus($id, $status) {
    Db::get()->prepare("UPDATE lab_request SET status=? WHERE request_id=?")
             ->execute([$status, $id]);
    Audit::log('LAB_STATUS_'.$status, 'labreq:'.$id);
  }

  // cancel
  public static function cancel($id) {
    // Safe-delete: a request can only be cancelled before the specimen is collected.
    $st = Db::get()->prepare("SELECT status, barcode FROM lab_request WHERE request_id=?");
    $st->execute([$id]); $row = $st->fetch();
    if (!$row || $row['status'] !== 'Requested') return false;
    Db::get()->prepare("DELETE FROM lab_result WHERE request_id=?")->execute([$id]);
    Db::get()->prepare("DELETE FROM lab_request WHERE request_id=?")->execute([$id]);
    Audit::log('LAB_REQUEST_CANCEL', $row['barcode']);
    return true;
  }

  // reporting helpers
  public static function dailySummary() {
    $db = Db::get();
    $completedToday = (int)$db->query(
      "SELECT COUNT(*) FROM lab_request WHERE status='Completed' AND DATE(request_datetime)=CURDATE()")
      ->fetchColumn();
    $pending    = (int)$db->query("SELECT COUNT(*) FROM lab_request WHERE status='Requested'")->fetchColumn();
    $processing = (int)$db->query("SELECT COUNT(*) FROM lab_request WHERE status='Processing'")->fetchColumn();
    return compact('completedToday','pending','processing');
  }

  public static function throughputByType($days = 30) {
    $st = Db::get()->prepare(
      "SELECT t.test_name,
              COUNT(r.request_id)                       AS total,
              SUM(r.status='Completed')                 AS completed,
              SUM(COALESCE(res.critical_flag,0))        AS critical
       FROM lab_request r
       JOIN test_type t ON t.test_code=r.test_code
       LEFT JOIN lab_result res ON res.request_id=r.request_id
       WHERE r.request_datetime >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
       GROUP BY t.test_code, t.test_name
       ORDER BY total DESC");
    $st->execute([$days]); return $st->fetchAll();
  }

  public static function criticalCount($days = 30) {
    $st = Db::get()->prepare(
      "SELECT COUNT(*) FROM lab_result
       WHERE critical_flag=1 AND accept_status='Accepted'
         AND entry_time >= DATE_SUB(NOW(), INTERVAL ? DAY)");
    $st->execute([$days]); return (int)$st->fetchColumn();
  }
}
