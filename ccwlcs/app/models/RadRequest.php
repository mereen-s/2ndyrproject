<?php
// X-ray / CT orders with their barcodes.
class RadRequest {
  public static function create($pid, $doctorId, $scanType, $bodyPart) {
    $db = Db::get();
    $code = $scanType === 'X-ray' ? '51' : '52';
    $st = $db->prepare("SELECT COUNT(*) c FROM rad_request WHERE patient_id=? AND scan_type=?");
    $st->execute([$pid, $scanType]); $no = $st->fetch()['c'] + 1;
    $barcode = sprintf('%s-%s-%03d', $pid, $code, $no);
    $st = $db->prepare("INSERT INTO rad_request(patient_id,doctor_id,scan_type,body_part,request_no,barcode) VALUES (?,?,?,?,?,?)");
    $st->execute([$pid, $doctorId, $scanType, $bodyPart, $no, $barcode]);
    Audit::log('RAD_REQUEST', $barcode);
    return $barcode;
  }
  public static function pending() {
    return Db::get()->query(
     "SELECT r.*, p.name patient_name FROM rad_request r JOIN patient p ON p.patient_id=r.patient_id
      WHERE r.status='Requested' ORDER BY r.request_datetime")->fetchAll();
  }
  public static function find($id) {
    $st = Db::get()->prepare(
     "SELECT r.*, p.name patient_name FROM rad_request r JOIN patient p ON p.patient_id=r.patient_id WHERE r.rad_request_id=?");
    $st->execute([$id]); return $st->fetch();
  }
  public static function complete($id) {
    Db::get()->prepare("UPDATE rad_request SET status='Completed' WHERE rad_request_id=?")->execute([$id]);
  }
  public static function imagesForPatient($pid) {
    $st = Db::get()->prepare(
     "SELECT r.barcode, r.scan_type, r.body_part, i.file_path, i.upload_time, i.critical_flag
      FROM rad_request r JOIN rad_image i ON i.rad_request_id=r.rad_request_id
      WHERE r.patient_id=? ORDER BY i.upload_time DESC");
    $st->execute([$pid]); return $st->fetchAll();
  }
}
