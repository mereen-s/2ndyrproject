<?php
// Reception screens plus the shared patient record used by all doctors.
class PatientController {

  // registration
  public function registration() {
    view('registration');
  }

  public function register() {
    // validate before registering
    $v = (new Validator($_POST))
      ->required('name',    'Full name is required')
      ->maxLen('name', 120, 'Name must be under 120 characters')
      ->nic('nic',          'NIC must be a valid Sri Lankan NIC (9 digits + V/X, or 12 digits)')
      ->required('dob',     'Date of birth is required')
      ->date('dob',         'Date of birth must be a valid date')
      ->required('gender',  'Gender is required')
      ->in('gender', ['M','F'], 'Invalid gender value')
      ->required('contact', 'Contact number is required')
      ->phone('contact',    'Contact must be a valid 10-digit Sri Lankan phone number')
      ->maxLen('address', 255, 'Address must be under 255 characters');

    if ($v->fails()) {
      flash(implode(' | ', $v->errors()));
      redirect('registration');
      return;
    }

    // Check for duplicate NIC before inserting
    // only check duplicates when an NIC was given - children often don't have one
    if (trim($_POST['nic'] ?? '') !== '' && Patient::findByNic($_POST['nic'])) {
      flash('A patient with NIC '.$_POST['nic'].' is already registered.');
      redirect('registration');
      return;
    }

    $id = Patient::create($_POST);
    flash('Patient registered. ID: '.$id);
    redirect('registration');
  }

  // profile
  public function profile() {
    $p = Patient::find($_GET['pid'] ?? '');
    if (!$p) { flash('Patient not found.'); redirect('registration'); }
    view('profile', ['p'=>$p, 'age'=>age_from_dob($p['dob'])]);
  }

  public function updatePatient() {
    $v = (new Validator($_POST))
      ->required('name',    'Full name is required')
      ->maxLen('name', 120, 'Name must be under 120 characters')
      ->required('contact', 'Contact number is required')
      ->phone('contact',    'Contact must be a valid 10-digit Sri Lankan phone number')
      ->maxLen('address', 255, 'Address must be under 255 characters');

    if ($v->fails()) {
      flash(implode(' | ', $v->errors()));
      redirect('profile', '&pid='.$_POST['pid']);
      return;
    }
    Patient::update($_POST['pid'], $_POST);
    flash('Patient details updated.');
    redirect('profile', '&pid='.$_POST['pid']);
  }

  // OPD queue
  public function opdAssign() {
    OpdQueue::add($_POST['pid']);
    flash('Patient placed in the OPD queue.');
    redirect('profile', '&pid='.$_POST['pid']);
  }

  // delete
  public function deletePatient() {
    $id = $_POST['pid'] ?? '';
    if (Patient::delete($id)) {
      flash('Patient '.$id.' deleted (no clinical records existed).');
      redirect('registration');
      return;
    }
    flash('Cannot delete '.$id.' — clinical records exist. Patient records are permanent once care has begun.');
    redirect('profile', '&pid='.$id);
  }

  // search API
  public function apiSearch() {
    header('Content-Type: application/json');
    echo json_encode(Patient::search($_GET['q'] ?? ''));
    exit;
  }

  // clinical history
  public function history() {
    $p = Patient::find($_GET['pid'] ?? '');
    if (!$p) { flash('Patient not found.'); redirect('dashboard'); }
    view('history', [
      'p'           => $p,
      'encounters'  => Encounter::forPatient($p['patient_id']),
      'labs'        => LabRequest::resultsForPatient($p['patient_id']),
      'rads'        => RadRequest::imagesForPatient($p['patient_id']),
      'prescriptions'=> Prescription::forPatient($p['patient_id']),
      'tests'       => Db::get()->query("SELECT * FROM test_type")->fetchAll(),
    ]);
  }

  // prescribe
  public function prescribe() {
    $v = (new Validator($_POST))
      ->required('drug',      'Drug name is required')
      ->maxLen('drug', 120,   'Drug name too long')
      ->required('dose',      'Dose is required')
      ->required('frequency', 'Frequency is required')
      ->required('duration',  'Duration is required');

    if ($v->fails()) {
      flash(implode(' | ', $v->errors()));
      redirect('history', '&pid='.$_POST['pid']);
      return;
    }
    Prescription::create(
      $_POST['pid'], Auth::id(),
      $_POST['drug'], $_POST['dose'], $_POST['frequency'], $_POST['duration']);
    flash('Prescription sent to dispensary.');
    redirect('history', '&pid='.$_POST['pid']);
  }

  // request lab test
  public function requestLab() {
    $v = (new Validator($_POST))
      ->required('test_code', 'Please select a test type');
    if ($v->fails()) {
      flash(implode(' | ', $v->errors()));
      redirect('history', '&pid='.$_POST['pid']);
      return;
    }
    $b = LabRequest::create($_POST['pid'], Auth::id(), $_POST['test_code']);
    flash('Lab test requested. Barcode: '.$b);
    redirect('history', '&pid='.$_POST['pid']);
  }

  // request radiology
  public function requestRad() {
    $v = (new Validator($_POST))
      ->required('scan_type', 'Scan type is required')
      ->required('body_part', 'Body part is required')
      ->maxLen('body_part', 80, 'Body part description too long');
    if ($v->fails()) {
      flash(implode(' | ', $v->errors()));
      redirect('history', '&pid='.$_POST['pid']);
      return;
    }
    $b = RadRequest::create($_POST['pid'], Auth::id(), $_POST['scan_type'], $_POST['body_part']);
    flash('Scan requested. Barcode: '.$b);
    redirect('history', '&pid='.$_POST['pid']);
  }
}
