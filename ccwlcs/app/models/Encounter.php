<?php
// OPD consultations, clinic visits and ward notes all live in one encounter table.
class Encounter {

  // create
  public static function create($d) {
    $st = Db::get()->prepare(
     "INSERT INTO encounter(patient_id,doctor_id,type,history_note,pain,temp,bp,pulse,exam_note,
       investigations_note,progress,diagnosis,med_action,plan_note,pathway,referral_department_id,
       follow_up_date,admission_id,note_date)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $st->execute([
      $d['patient_id'], $d['doctor_id'], $d['type'],
      $d['history_note'] ?? null,
      $d['pain']  !== '' ? $d['pain']  : null,
      $d['temp']  !== '' ? $d['temp']  : null,
      $d['bp']    ?? null,
      $d['pulse'] !== '' ? $d['pulse'] : null,
      $d['exam_note']            ?? null,
      $d['investigations_note']  ?? null,
      $d['progress']    ?: null,
      $d['diagnosis']   ?? null,
      $d['med_action']  ?: null,
      $d['plan_note']   ?? null,
      $d['pathway']                  ?? null,
      $d['referral_department_id']   ?? null,
      $d['follow_up_date']           ?? null,
      $d['admission_id']             ?? null,
      $d['note_date']                ?? null,
    ]);
    $id = Db::get()->lastInsertId();
    Audit::log('ENCOUNTER_'.$d['type'], 'encounter:'.$id.' patient:'.$d['patient_id']);
    return $id;
  }

  // read
  public static function find($id) {
    $st = Db::get()->prepare(
     "SELECT e.*, u.full_name doctor_name
      FROM encounter e JOIN user u ON u.user_id=e.doctor_id
      WHERE encounter_id=?");
    $st->execute([$id]); return $st->fetch();
  }

  public static function forPatient($pid) {
    $st = Db::get()->prepare(
     "SELECT e.*, u.full_name doctor_name
      FROM encounter e JOIN user u ON u.user_id=e.doctor_id
      WHERE e.patient_id=? ORDER BY e.created_at DESC");
    $st->execute([$pid]); return $st->fetchAll();
  }

  public static function lastWardNote($admissionId) {
    $st = Db::get()->prepare(
     "SELECT * FROM encounter WHERE admission_id=? AND type='WARD'
      ORDER BY note_date DESC, encounter_id DESC LIMIT 1");
    $st->execute([$admissionId]); return $st->fetch();
  }

  public static function lastClinicVisit($pid) {
    $st = Db::get()->prepare(
     "SELECT * FROM encounter WHERE patient_id=? AND type='CLINIC'
      ORDER BY created_at DESC LIMIT 1");
    $st->execute([$pid]); return $st->fetch();
  }

  public static function wardNotes($admissionId) {
    $st = Db::get()->prepare(
     "SELECT e.*, u.full_name doctor_name
      FROM encounter e JOIN user u ON u.user_id=e.doctor_id
      WHERE e.admission_id=? AND e.type='WARD' ORDER BY e.note_date");
    $st->execute([$admissionId]); return $st->fetchAll();
  }

  // reporting helpers
  public static function countByTypeLast($days = 30) {
    $st = Db::get()->prepare(
      "SELECT type, COUNT(*) cnt FROM encounter
       WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
       GROUP BY type");
    $st->execute([$days]);
    $rows = $st->fetchAll(); $out = [];
    foreach ($rows as $r) { $out[$r['type']] = (int)$r['cnt']; }
    return $out;
  }

  public static function dailyVolume($days = 14) {
    $st = Db::get()->prepare(
      "SELECT DATE(created_at) day, COUNT(*) cnt
       FROM encounter WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
       GROUP BY day ORDER BY day");
    $st->execute([$days]); return $st->fetchAll();
  }

  public static function countToday() {
    return (int)Db::get()
      ->query("SELECT COUNT(*) FROM encounter WHERE DATE(created_at)=CURDATE()")
      ->fetchColumn();
  }

  public static function upcomingFollowUps($days = 7) {
    $st = Db::get()->prepare(
      "SELECT e.follow_up_date, e.plan_note, p.name patient_name, p.patient_id,
              u.full_name doctor_name
       FROM encounter e
       JOIN patient p ON p.patient_id=e.patient_id
       JOIN user u ON u.user_id=e.doctor_id
       WHERE e.follow_up_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
       ORDER BY e.follow_up_date");
    $st->execute([$days]); return $st->fetchAll();
  }
}
