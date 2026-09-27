<?php
// Ward doctor screens: daily notes and discharge.
class WardController {

  // ward overview
  public function index() {
    $deptId  = Auth::dept();
    $admitted = Admission::admittedForDept($deptId);

    // Bed layout for the visual grid
    $allBeds = Bed::forDept($deptId);

    // Department bulletin notice
    $di = Db::get()->prepare("SELECT info FROM department WHERE department_id=?");
    $di->execute([$deptId]);
    $deptInfo = $di->fetch()['info'] ?? '';

    // Count pending (no bed yet) admissions for this dept
    $pSt = Db::get()->prepare(
      "SELECT COUNT(*) FROM admission WHERE department_id=? AND status='Pending'");
    $pSt->execute([$deptId]);
    $pendingAdm = (int)$pSt->fetchColumn();

    view('ward_index', compact('admitted','allBeds','deptInfo','pendingAdm'));
  }

  // daily note form
  public function noteForm() {
    $a = Admission::find($_GET['aid'] ?? 0);
    if (!$a || $a['department_id'] != Auth::dept()) {
      flash('Admission not found in your ward.', 'error');
      redirect('ward');
      return;
    }
    $prev    = Encounter::lastWardNote($a['admission_id']);
    $prefill = (isset($_GET['copy']) && $prev) ? $prev : null;
    view('ward_note', ['a'=>$a, 'prev'=>$prev, 'f'=>$prefill]);
  }

  // save daily note
  public function saveNote() {
    $v = (new Validator($_POST))
      ->required('diagnosis', 'Diagnosis / assessment is required')
      ->required('plan_note', 'Plan note is required');

    if ($v->fails()) {
      flash(implode(' | ', $v->errors()), 'error');
      redirect('ward_note', '&aid='.$_POST['admission_id']);
      return;
    }

    $d = $_POST;
    $d['doctor_id'] = Auth::id();
    $d['type']      = 'WARD';
    $d['note_date'] = date('Y-m-d');
    Encounter::create($d);
    flash('Daily note saved.');
    redirect('ward_note', '&aid='.$d['admission_id']);
  }

  // loads an admission only if it belongs to this doctor's ward
  private function ownAdmission($aid) {
    $a = Admission::find($aid);
    if (!$a || $a['department_id'] != Auth::dept()) {
      flash('That admission is not in your ward.', 'error');
      redirect('ward');
    }
    return $a;
  }

  // step 1 - show the summary as a draft; the patient is NOT discharged yet
  public function dischargePreview() {
    $a = $this->ownAdmission($_GET['aid'] ?? 0);
    if ($a['status'] === 'Discharged') { redirect('discharge_print', '&aid='.$a['admission_id']); }
    if ($a['status'] !== 'Admitted') {
      flash('Only admitted patients can be discharged.', 'error');
      redirect('ward');
    }
    $this->renderSummary($a, true);
  }

  // step 2 - the doctor confirmed: discharge, free the bed, show the final copy
  public function discharge() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('ward'); }
    $a = $this->ownAdmission($_POST['aid'] ?? 0);
    if ($a['status'] === 'Admitted') {
      Admission::discharge($a['admission_id']);
    }
    redirect('discharge_print', '&aid='.$a['admission_id']);
  }

  // final discharge summary (printable)
  public function dischargePrint() {
    $a = $this->ownAdmission($_GET['aid'] ?? 0);
    if ($a['status'] !== 'Discharged') { redirect('discharge_preview', '&aid='.$a['admission_id']); }
    $this->renderSummary($a, false);
  }

  private function renderSummary($a, $preview) {
    $notes = Encounter::wardNotes($a['admission_id']);
    $meds  = MedicationAdmin::forAdmission($a['admission_id']);
    // the patient's copy lists verified results only
    $labs  = array_values(array_filter(LabRequest::resultsForPatient($a['patient_id']),
               fn($l) => ($l['accept_status'] ?? '') === 'Accepted'));
    require __DIR__.'/../views/discharge_print.php';   // standalone printable page
  }
}
