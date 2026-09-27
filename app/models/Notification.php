<?php
// Critical-result alerts for the doctor who ordered the test.
class Notification {

  // create
  public static function create($recipientUserId, $patientId, $source, $message) {
    $st = Db::get()->prepare(
      "INSERT INTO notification(recipient_user_id,patient_id,source,message)
       VALUES (?,?,?,?)");
    $st->execute([$recipientUserId, $patientId, $source, $message]);
    // Note: the audit log entry for ALERT_SENT is written by the calling code
    // (LabResult::accept / RadiologyController::upload) to keep full context.
  }

  // read
  public static function forUser($userId) {
    $st = Db::get()->prepare(
      "SELECT n.*, p.name patient_name
       FROM notification n
       LEFT JOIN patient p ON p.patient_id=n.patient_id
       WHERE n.recipient_user_id=?
       ORDER BY (n.status='Unread') DESC, n.created_at DESC");
    $st->execute([$userId]); return $st->fetchAll();
  }

  public static function unreadCount($userId) {
    $st = Db::get()->prepare(
      "SELECT COUNT(*) c FROM notification
       WHERE recipient_user_id=? AND status='Unread'");
    $st->execute([$userId]); return (int)$st->fetch()['c'];
  }

  public static function criticalUnreadCount($userId) {
    $st = Db::get()->prepare(
      "SELECT COUNT(*) c FROM notification
       WHERE recipient_user_id=? AND status='Unread' AND message LIKE 'CRITICAL%'");
    $st->execute([$userId]); return (int)$st->fetch()['c'];
  }

  // open / Mark viewed
  public static function open($id, $userId) {
    $st = Db::get()->prepare(
      "SELECT n.*, p.name patient_name
       FROM notification n LEFT JOIN patient p ON p.patient_id=n.patient_id
       WHERE n.notification_id=? AND n.recipient_user_id=?");
    $st->execute([$id, $userId]); $n = $st->fetch();
    if ($n && $n['status'] === 'Unread') {
      Db::get()->prepare(
        "UPDATE notification SET status='Viewed' WHERE notification_id=?")
      ->execute([$id]);
      Audit::log('ALERT_VIEWED', 'notification:'.$id);
    }
    return $n;
  }

  // housekeeping
  public static function purgeOld($days = 30) {
    $st = Db::get()->prepare(
      "DELETE FROM notification
       WHERE status='Viewed'
         AND message NOT LIKE 'CRITICAL%'
         AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
    $st->execute([$days]);
    return Db::get()->query("SELECT ROW_COUNT() c")->fetchColumn();
  }
}
