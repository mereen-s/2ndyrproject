<?php
// Uploaded scans - the file lives on disk, only its path is stored.
class RadImage {
  public static function add($radRequestId, $filePath, $userId, $critical) {
    $st = Db::get()->prepare("INSERT INTO rad_image(rad_request_id,file_path,uploaded_by,critical_flag) VALUES (?,?,?,?)");
    $st->execute([$radRequestId, $filePath, $userId, $critical ? 1 : 0]);
    RadRequest::complete($radRequestId);
    Audit::log('RAD_UPLOAD', 'radreq:'.$radRequestId);
    if ($critical) {
      $req = RadRequest::find($radRequestId);
      Notification::create($req['doctor_id'], $req['patient_id'], 'Radiology '.$req['barcode'],
        'CRITICAL radiology finding for '.$req['patient_name'].' ('.$req['scan_type'].' '.$req['body_part'].')');
    }
  }
}
