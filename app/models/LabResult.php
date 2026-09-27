<?php
// Lab findings. Nothing reaches the patient record until it is accepted.
class LabResult {

  // ensure a draft row exists
  public static function ensure($requestId) {
    $db = Db::get();
    $st = $db->prepare("SELECT * FROM lab_result WHERE request_id=?");
    $st->execute([$requestId]);
    if ($r = $st->fetch()) return $r;
    $db->prepare("INSERT INTO lab_result(request_id) VALUES (?)")->execute([$requestId]);
    $st->execute([$requestId]);
    return $st->fetch();
  }

  // find by request id
  public static function find($requestId) {
    $st = Db::get()->prepare("SELECT * FROM lab_result WHERE request_id=?");
    $st->execute([$requestId]); return $st->fetch();
  }

  // save finding
  public static function save(
    int    $requestId,
    string $specimen,
    string $finding,
    bool   $critical,
    int    $userId,
    ?float $numericResult = null,
    string $unit = ''
  ): void {
    self::ensure($requestId);
    $st = Db::get()->prepare(
      "UPDATE lab_result
       SET specimen=?, finding=?, critical_flag=?, entered_by=?,
           entry_time=NOW(), accept_status='Pending',
           numeric_result=?, unit=?
       WHERE request_id=?");
    $st->execute([
      $specimen, $finding, $critical ? 1 : 0,
      $userId, $numericResult, $unit ?: null, $requestId,
    ]);
    Audit::log('LAB_RESULT_ENTER', 'labreq:'.$requestId);
  }

  // accept
  public static function accept(int $requestId): void {
    $db = Db::get();
    $db->prepare("UPDATE lab_result SET accept_status='Accepted' WHERE request_id=?")
       ->execute([$requestId]);
    LabRequest::setStatus($requestId, 'Completed');
    Audit::log('LAB_RESULT_ACCEPT', 'labreq:'.$requestId);

    // Send notification if critical
    $req = LabRequest::find($requestId);
    $st  = $db->prepare("SELECT critical_flag FROM lab_result WHERE request_id=?");
    $st->execute([$requestId]);
    if ($st->fetch()['critical_flag']) {
      Notification::create(
        $req['doctor_id'],
        $req['patient_id'],
        'Lab '.$req['barcode'],
        'CRITICAL lab result for '.$req['patient_name'].' ('.$req['test_name'].')'
      );
      Audit::log('ALERT_SENT', 'to doctor:'.$req['doctor_id'].' - '.$req['barcode']);
    }
  }

  // reject
  public static function reject(int $requestId): void {
    Db::get()->prepare(
      "UPDATE lab_result SET accept_status='Pending', finding=NULL, entered_by=NULL WHERE request_id=?")
    ->execute([$requestId]);
    Audit::log('LAB_RESULT_REJECT', 'labreq:'.$requestId);
  }

  // reporting
  public static function criticalToday(): int {
    return (int)Db::get()
      ->query("SELECT COUNT(*) FROM lab_result WHERE critical_flag=1 AND accept_status='Accepted' AND DATE(entry_time)=CURDATE()")
      ->fetchColumn();
  }

  public static function avgTurnaround(): ?float {
    $val = Db::get()->query(
      "SELECT ROUND(AVG(TIMESTAMPDIFF(MINUTE,r.request_datetime,res.entry_time)),1)
       FROM lab_request r JOIN lab_result res ON res.request_id=r.request_id
       WHERE res.accept_status='Accepted'"
    )->fetchColumn();
    return ($val !== null && $val !== false) ? (float)$val : null;
  }
}
