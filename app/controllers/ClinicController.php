<?php
// Clinic referrals, follow-ups, visits and the printed visit summary.
class ClinicController {
  public function index(){
    $st = Db::get()->prepare(
     "SELECT e.encounter_id, e.patient_id, e.created_at, e.diagnosis, p.name patient_name
      FROM encounter e JOIN patient p ON p.patient_id=e.patient_id
      WHERE e.type='OPD' AND e.pathway='Refer' AND e.referral_department_id=?
      ORDER BY e.created_at DESC LIMIT 30");
    $st->execute([Auth::dept()]);
    $fu = Db::get()->prepare(
     "SELECT e.patient_id, e.follow_up_date, e.diagnosis, p.name patient_name
      FROM encounter e JOIN patient p ON p.patient_id=e.patient_id
      JOIN user u ON u.user_id=e.doctor_id
      WHERE e.type='CLINIC' AND e.follow_up_date >= CURDATE() AND u.department_id=?
      ORDER BY e.follow_up_date LIMIT 20");
    $fu->execute([Auth::dept()]);
    view('clinic_queue', ['referrals'=>$st->fetchAll(), 'followups'=>$fu->fetchAll()]);
  }
  public function form(){
    $p = Patient::find($_GET['pid'] ?? '');
    if (!$p) { flash('Patient not found.', 'error'); redirect('clinic'); }
    view('clinic_form', ['p'=>$p, 'last'=>Encounter::lastClinicVisit($p['patient_id'])]);
  }
  public function save(){
    $d = $_POST; $d['doctor_id'] = Auth::id(); $d['type'] = 'CLINIC';
    $id = Encounter::create($d);
    flash('Clinic visit saved.');
    redirect('clinic_print', '&eid='.$id);
  }
  public function printSummary(){
    $enc = Encounter::find($_GET['eid'] ?? 0);
    if (!$enc || $enc['type'] !== 'CLINIC') { flash('Visit not found.', 'error'); redirect('clinic'); }
    $p = Patient::find($enc['patient_id']);
    require __DIR__.'/../views/clinic_print.php';   // standalone printable page
  }
}
