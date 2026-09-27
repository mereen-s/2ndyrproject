<?php
// Patient demographics - registration, search, update and safe delete.
class Patient {

  // create
  public static function create($d) {
    $db = Db::get();
    $row = $db->query("SELECT MAX(CAST(SUBSTRING(patient_id,2) AS UNSIGNED)) m FROM patient")->fetch();
    $id = 'P'.str_pad(($row['m'] ?? 0) + 1, 5, '0', STR_PAD_LEFT);
    $st = $db->prepare(
      "INSERT INTO patient(patient_id,name,nic,dob,gender,contact,address) VALUES (?,?,?,?,?,?,?)");
    $st->execute([$id, $d['name'], $d['nic'], $d['dob'] ?: null,
                  $d['gender'], $d['contact'], $d['address'] ?? '']);
    Audit::log('PATIENT_REGISTER', $id);
    return $id;
  }

  // read
  public static function find($id) {
    $st = Db::get()->prepare("SELECT * FROM patient WHERE patient_id=?");
    $st->execute([$id]); return $st->fetch();
  }

  public static function findByNic($nic) {
    $st = Db::get()->prepare("SELECT * FROM patient WHERE nic=? LIMIT 1");
    $st->execute([$nic]); return $st->fetch();
  }

  public static function search($q, $limit = 15) {
    $st = Db::get()->prepare(
      "SELECT * FROM patient WHERE patient_id LIKE ? OR name LIKE ? OR nic LIKE ?
       ORDER BY name LIMIT $limit");
    $like = '%'.$q.'%'; $st->execute([$like,$like,$like]); return $st->fetchAll();
  }

  // update
  public static function update($id, $d) {
    $st = Db::get()->prepare(
      "UPDATE patient SET name=?, nic=?, dob=?, gender=?, contact=?, address=? WHERE patient_id=?");
    $st->execute([$d['name'], $d['nic'], $d['dob'] ?: null,
                  $d['gender'], $d['contact'], $d['address'] ?? '', $id]);
    Audit::log('PATIENT_UPDATE', $id);
  }

  // delete
  public static function hasClinicalRecords($id) {
    $db = Db::get();
    foreach ([['encounter','patient_id'],['admission','patient_id'],['lab_request','patient_id'],
              ['rad_request','patient_id'],['prescription','patient_id']] as [$t,$col]) {
      $st = $db->prepare("SELECT COUNT(*) c FROM $t WHERE $col=?");
      $st->execute([$id]);
      if ($st->fetch()['c'] > 0) return true;
    }
    return false;
  }

  public static function delete($id) {
    // Safe-delete: a patient may only be removed if no clinical record exists (registered in error).
    if (self::hasClinicalRecords($id)) return false;
    OpdQueue::removeForPatient($id);
    Db::get()->prepare("DELETE FROM patient WHERE patient_id=?")->execute([$id]);
    Audit::log('PATIENT_DELETE', $id);
    return true;
  }

  // reporting helpers
  public static function registrationTotals() {
    $db = Db::get();
    $total      = (int)$db->query("SELECT COUNT(*) FROM patient")->fetchColumn();
    $thisMonth  = (int)$db->query("SELECT COUNT(*) FROM patient WHERE MONTH(registered_date)=MONTH(CURDATE()) AND YEAR(registered_date)=YEAR(CURDATE())")->fetchColumn();
    $thisWeek   = (int)$db->query("SELECT COUNT(*) FROM patient WHERE registered_date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)")->fetchColumn();
    $today      = (int)$db->query("SELECT COUNT(*) FROM patient WHERE DATE(registered_date)=CURDATE()")->fetchColumn();
    return compact('total','thisMonth','thisWeek','today');
  }

  public static function countByMonth($months = 12) {
    $st = Db::get()->prepare(
      "SELECT DATE_FORMAT(registered_date,'%Y-%m') AS month, COUNT(*) cnt
       FROM patient WHERE registered_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
       GROUP BY month ORDER BY month");
    $st->execute([$months]); return $st->fetchAll();
  }

  public static function countByGender() {
    return Db::get()->query(
      "SELECT gender, COUNT(*) cnt FROM patient GROUP BY gender ORDER BY gender")->fetchAll();
  }

  public static function recentRegistrations($limit = 20) {
    $st = Db::get()->prepare(
      "SELECT * FROM patient ORDER BY registered_date DESC LIMIT ".(int)$limit);
    $st->execute(); return $st->fetchAll();
  }

  public static function countToday() {
    return (int)Db::get()
      ->query("SELECT COUNT(*) FROM patient WHERE DATE(registered_date)=CURDATE()")
      ->fetchColumn();
  }
}
