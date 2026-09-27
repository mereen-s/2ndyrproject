<?php
// OPD consultation and the treat / refer / admit decision.
class OpdController {
  public function form(){
    $p = Patient::find($_GET['pid'] ?? '');
    if (!$p) { flash('Select a patient first (search on dashboard).', 'error'); redirect('dashboard'); }
    view('opd_form', ['p'=>$p, 'departments'=>UserModel::departments()]);
  }
  public function save(){
    $d = $_POST; $d['doctor_id'] = Auth::id(); $d['type'] = 'OPD';
    Encounter::create($d);
    OpdQueue::markSeen($d['patient_id']);
    if ($d['pathway'] === 'Admit') {
      Admission::createPending($d['patient_id'], $d['referral_department_id'], $d['diagnosis']);
      flash('Consultation saved. Admission order sent to the ward nurse.');
    } elseif ($d['pathway'] === 'Refer') {
      flash('Consultation saved. Patient referred to the clinic.');
    } else {
      flash('Consultation saved.');
    }
    redirect('history', '&pid='.$d['patient_id']);
  }
}
