<?php
// Alert list; opening an alert jumps to the patient.
class NotificationController {
  public function index(){
    $pg = paginate(Notification::forUser(Auth::id()), 15);
    view('notifications', ['items'=>$pg['items'], 'pages'=>$pg['pages'], 'current'=>$pg['page']]);
  }
  public function open(){
    $n = Notification::open($_GET['nid'] ?? 0, Auth::id());
    if ($n && $n['patient_id'] && is_doctor()) {
      redirect('history', '&pid='.$n['patient_id']);
    }
    redirect('notifications');
  }
}
