<?php
// Read-only statistics pages.
class ReportController {

  // date range helpers
  private function dateRange(): array {
    $from = $_GET['from'] ?? date('Y-m-01');
    $to   = $_GET['to']   ?? date('Y-m-d');
    $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from : date('Y-m-01');
    $to   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)   ? $to   : date('Y-m-d');
    if ($to < $from) $to = $from;
    return [$from, $to];
  }

  // system summary
  public function index(): void {
    [$from, $to] = $this->dateRange();
    $db = Db::get();

    $totalPatients = (int)$db->query("SELECT COUNT(*) FROM patient")->fetchColumn();
    $stNew = $db->prepare(
      "SELECT COUNT(*) FROM patient WHERE DATE(registered_date) BETWEEN ? AND ?");
    $stNew->execute([$from, $to]);
    $newPatients = (int)$stNew->fetchColumn();

    $stEnc = $db->prepare(
      "SELECT type, COUNT(*) cnt FROM encounter
       WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY type");
    $stEnc->execute([$from, $to]);
    $encByType = ['OPD' => 0, 'CLINIC' => 0, 'WARD' => 0];
    foreach ($stEnc->fetchAll() as $r) $encByType[$r['type']] = (int)$r['cnt'];

    $occupancy    = Bed::occupancyByDept();
    $pendingLab   = (int)$db->query("SELECT COUNT(*) FROM lab_request  WHERE status NOT IN ('Completed','Cancelled')")->fetchColumn();
    $pendingRad   = (int)$db->query("SELECT COUNT(*) FROM rad_request  WHERE status NOT IN ('Completed','Cancelled')")->fetchColumn();
    $pendingPharm = (int)$db->query("SELECT COUNT(*) FROM prescription WHERE status='Pending'")->fetchColumn();
    $auditSummary = Audit::actionSummary();
    $logins       = Audit::dailyLogins(14);
    $activeToday  = (int)$db->query(
      "SELECT COUNT(DISTINCT user_id) FROM audit_log WHERE DATE(created_at)=CURDATE()"
    )->fetchColumn();

    view('report_summary', compact(
      'from','to','totalPatients','newPatients','encByType',
      'occupancy','pendingLab','pendingRad','pendingPharm',
      'auditSummary','activeToday','logins'
    ));
  }

  // laboratory throughput report
  public function lab(): void {
    [$from, $to] = $this->dateRange();
    $volume = LabRequest::throughputByType($from, $to);
    $total     = array_sum(array_column($volume, 'requested'));
    $completed = array_sum(array_column($volume, 'completed'));
    $totals = [
      'total'     => $total,
      'completed' => $completed,
      'pending'   => $total - $completed,
      'rejected'  => array_sum(array_column($volume, 'rejected')),
    ];
    $avgTat = LabRequest::avgTurnaround($from, $to);
    view('report_lab', compact('from','to','volume','totals','avgTat'));
  }


  // ward / bed utilisation report
  public function ward(): void {
    $stats   = Bed::occupancyStats();
    $byDept  = Bed::occupancyByDept();
    $longest = Bed::longestStays(10);
    view('report_ward', compact('stats','byDept','longest'));
  }

  // patient registration report
  public function patients(): void {
    $rawTotals = Patient::registrationTotals();
    $totals = [
      'total'      => $rawTotals['total'],
      'this_month' => $rawTotals['thisMonth'],
      'this_week'  => $rawTotals['thisWeek'],
      'today'      => $rawTotals['today'],
    ];
    $byMonth  = Patient::countByMonth(12);
    $byGender = Patient::countByGender();
    $recent   = Patient::recentRegistrations(20);
    view('report_patients', compact('totals','byMonth','byGender','recent'));
  }
}
