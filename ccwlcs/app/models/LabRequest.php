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

  // a finding is only released once the lab has accepted it
  public static function resultsForPatient($pid) {
    $st = Db::get()->prepare(
     "SELECT r.barcode, r.request_datetime, t.test_name, t.reference_range,
             res.accept_status,
             CASE WHEN res.accept_status='Accepted' THEN res.finding        END AS finding,
             CASE WHEN res.accept_status='Accepted' THEN res.numeric_result END AS numeric_result,
             CASE WHEN res.accept_status='Accepted' THEN res.unit           END AS unit,
             CASE WHEN res.accept_status='Accepted' THEN res.critical_flag ELSE 0 END AS critical_flag
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

  // per test type, for requests made between $from and $to (inclusive).
  // Cancelled requests are deleted and rejected entries are sent back for re-entry,
  // so those two are counted from the audit log.
  public static function throughputByType($from, $to) {
    $st = Db::get()->prepare(
      "SELECT t.test_code, t.test_name,
         (SELECT COUNT(*) FROM lab_request r
            WHERE r.test_code=t.test_code AND DATE(r.request_datetime) BETWEEN ? AND ?) AS requested,
         (SELECT COUNT(*) FROM lab_request r
            WHERE r.test_code=t.test_code AND r.status='Completed'
              AND DATE(r.request_datetime) BETWEEN ? AND ?) AS completed,
         (SELECT COUNT(*) FROM lab_request r JOIN lab_result res ON res.request_id=r.request_id
            WHERE r.test_code=t.test_code AND res.critical_flag=1 AND res.accept_status='Accepted'
              AND DATE(r.request_datetime) BETWEEN ? AND ?) AS critical,
         (SELECT COUNT(*) FROM audit_log a JOIN lab_request r ON a.entity_ref=CONCAT('labreq:',r.request_id)
            WHERE a.action='LAB_RESULT_REJECT' AND r.test_code=t.test_code
              AND DATE(a.created_at) BETWEEN ? AND ?) AS rejected,
         (SELECT COUNT(*) FROM audit_log a
            WHERE a.action='LAB_REQUEST_CANCEL'
              AND SUBSTRING_INDEX(SUBSTRING_INDEX(a.entity_ref,'-',2),'-',-1)=t.test_code
              AND DATE(a.created_at) BETWEEN ? AND ?) AS cancelled
       FROM test_type t
       HAVING requested + rejected + cancelled > 0
       ORDER BY requested DESC, t.test_name");
    $st->execute([$from,$to, $from,$to, $from,$to, $from,$to, $from,$to]);
    return $st->fetchAll();
  }

  public static function avgTurnaround($from, $to) {
    $st = Db::get()->prepare(
      "SELECT AVG(TIMESTAMPDIFF(MINUTE, r.request_datetime, res.entry_time))
       FROM lab_request r JOIN lab_result res ON res.request_id = r.request_id
       WHERE res.accept_status = 'Accepted' AND res.entry_time IS NOT NULL
         AND DATE(r.request_datetime) BETWEEN ? AND ?");
    $st->execute([$from, $to]);
    return (int)round((float)$st->fetchColumn());
  }

}
