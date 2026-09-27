<?php
// Lab queue, result entry and the accept / reject step.
class LabController {

  // queue
  // two tabs: work still to do, and results already released
  public function queue() {
    $daily = LabRequest::dailySummary();
    $tab   = ($_GET['tab'] ?? '') === 'completed' ? 'completed' : 'pending';
    $requests = LabRequest::pendingAll();
    $done  = $tab === 'completed' ? paginate(LabRequest::completedAll(), 15)
                                  : ['items' => [], 'pages' => 1, 'page' => 1];
    view('lab_queue', [
      'tab'         => $tab,
      'requests'    => $requests,
      'completed'   => $done['items'],
      'pages'       => $done['pages'],
      'current'     => $done['page'],
      'completedToday'  => $daily['completedToday'],
      'pending'     => $daily['pending'],
      'processing'  => $daily['processing'],
    ]);
  }

  // entry form
  public function entry() {
    $r = LabRequest::find($_GET['rid'] ?? 0);
    if (!$r) { flash('Request not found.', 'error'); redirect('lab'); }
    if ($r['status'] === 'Requested') {
      LabRequest::setStatus($r['request_id'], 'Processing');
      $r['status'] = 'Processing'; // reflect in view without re-query
    }
    view('lab_entry', [
      'r'   => $r,
      'res' => LabResult::ensure($r['request_id']),
      'ref' => $r['reference_range'] ?? null,
    ]);
  }

  // save finding
  public function saveEntry() {
    $v = (new Validator($_POST))
      ->required('specimen', 'Specimen type is required')
      ->required('finding',  'Finding text is required — enter the result before accepting');

    if ($v->fails()) {
      flash(implode(' | ', $v->errors()), 'error');
      redirect('lab_entry', '&rid='.$_POST['rid']);
      return;
    }

    $numericResult = $_POST['numeric_result'] !== '' ? (float)$_POST['numeric_result'] : null;
    $unit          = trim($_POST['unit'] ?? '');

    LabResult::save(
      $_POST['rid'],
      $_POST['specimen'],
      $_POST['finding'],
      isset($_POST['critical']),
      Auth::id(),
      $numericResult,
      $unit
    );

    flash('Finding entered. Review it, then Accept to commit or Reject to re-enter.');
    redirect('lab_entry', '&rid='.$_POST['rid']);
  }

  // cancel (before specimen collected)
  public function cancel() {
    if (LabRequest::cancel($_POST['rid'])) {
      flash('Request cancelled and removed (specimen not yet collected).');
    } else {
      flash('Cannot cancel — the specimen has already been collected or processed.', 'error');
    }
    redirect('lab');
  }

  // accept
  public function accept() {
    $rid = (int)$_POST['rid'];
    $res = LabResult::find($rid);
    if (!$res || empty($res['finding'])) {
      flash('Cannot accept — no finding has been entered yet.', 'error');
      redirect('lab_entry', '&rid='.$rid);
      return;
    }
    LabResult::accept($rid);
    flash('Result accepted and committed. Critical results alert the requesting doctor.');
    redirect('lab');
  }

  // reject
  public function reject() {
    LabResult::reject($_POST['rid']);
    flash('Result rejected — please re-enter the finding.');
    redirect('lab_entry', '&rid='.$_POST['rid']);
  }
}
