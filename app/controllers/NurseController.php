<?php
// Nurse screens: admission orders, beds, medication given, ward info.
class NurseController {
  public function wardInfoSave(){
    Db::get()->prepare("UPDATE department SET info=? WHERE department_id=?")->execute([$_POST['info'], Auth::dept()]);
    Audit::log('WARD_INFO_UPDATE', 'dept:'.Auth::dept());
    flash('Ward information updated.');
    redirect('nurse');
  }
  public function index(){
    $dept = Db::get()->prepare('SELECT * FROM department WHERE department_id=?');
    $dept->execute([Auth::dept()]);
    view('nurse', [
      'dept'=>$dept->fetch(),
      'pending'=>Admission::pendingForDept(Auth::dept()),
      'beds'=>Bed::forDept(Auth::dept()),
      'admitted'=>Admission::admittedForDept(Auth::dept()),
    ]);
  }
  public function assignBed(){
    Admission::assignBed($_POST['aid'], $_POST['bed_id']);
    flash('Bed assigned. Patient admitted.');
    redirect('nurse');
  }
  public function cancelAdmission(){
    if (Admission::cancelPending($_POST['aid'])) flash('Admission order cancelled and removed.');
    else flash('Only pending orders can be cancelled - this patient already has a bed.', 'error');
    redirect('nurse');
  }
  public function medAdmin(){
    MedicationAdmin::add($_POST['aid'], Auth::id(), $_POST['drug_dose'], $_POST['route']);
    flash('Medication administration recorded.');
    redirect('nurse');
  }
}
