<?php
// Small helpers used across views and controllers.
function e($s): string { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function redirect(string $page, string $extra = ''): void {
    header('Location: '.BASE_URL.'/index.php?page='.$page.$extra); exit;
}

// flash messages
function flash(string $msg = null): ?string {
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

// view renderer
function view(string $name, array $data = []): void {
    extract($data, EXTR_SKIP);
    require __DIR__.'/../views/_header.php';
    require __DIR__.'/../views/'.$name.'.php';
    require __DIR__.'/../views/_footer.php';
}

// date/time
function fmt_dt(?string $dt, bool $dateOnly = false): string {
    if (!$dt || $dt === '0000-00-00 00:00:00') return '–';
    try {
        $d = new DateTime($dt);
        return $dateOnly ? $d->format('d M Y') : $d->format('d M Y, H:i');
    } catch (Exception $e) { return e($dt); }
}

function time_ago(?string $dt): string {
    if (!$dt) return '–';
    $diff = time() - strtotime($dt);
    if ($diff < 60)     return $diff . 's ago';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return fmt_dt($dt, true);
}

function age_from_dob(?string $dob): ?int {
    if (!$dob) return null;
    try {
        $born = new DateTime($dob);
        return (int)(new DateTime())->diff($born)->y;
    } catch (Exception $e) { return null; }
}

// numbers
function int_val(mixed $v): int { return (int)filter_var($v, FILTER_SANITIZE_NUMBER_INT); }

function fmt_num(mixed $v, int $decimals = 2): string {
    return is_numeric($v) ? number_format((float)$v, $decimals) : '–';
}

// status badges
function badge_class(string $status): string {
    return match (strtolower(trim($status))) {
        'critical', 'rejected'                         => 'chip red',
        'completed', 'dispensed', 'accepted', 'active' => 'chip green',
        'pending', 'requested', 'waiting'              => 'chip amber',
        default                                        => 'chip blue',
    };
}

// CSRF protection
function csrf_field(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return '<input type="hidden" name="csrf_token" value="'.e($_SESSION['csrf_token']).'">';
}

function csrf_verify(): void {
    $tok = $_SESSION['csrf_token'] ?? '';
    if (!hash_equals($tok, $_POST['csrf_token'] ?? '')) {
        flash('Security token mismatch – please try again.');
        redirect('dashboard');
    }
}

// pagination
function paginate(array $items, int $perPage = 25): array {
    $total = count($items);
    $page  = max(1, int_val($_GET['p'] ?? 1));
    $pages = max(1, (int)ceil($total / $perPage));
    $page  = min($page, $pages);
    return [
        'items' => array_slice($items, ($page - 1) * $perPage, $perPage),
        'total' => $total,
        'page'  => $page,
        'pages' => $pages,
    ];
}

function pagination_links(int $pages, int $current, string $base, string $param = 'p'): string {
    if ($pages <= 1) return '';
    $html = '<div class="pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        $cls   = $i === $current ? 'btn' : 'btn secondary';
        $html .= '<a class="'.$cls.'" href="'.$base.'&'.$param.'='.$i.'">'.$i.'</a> ';
    }
    return $html.'</div>';
}

// string helpers
function truncate(string $s, int $max = 60): string {
    return mb_strlen($s) > $max ? mb_substr($s, 0, $max).'…' : $s;
}

function initials(string $name): string {
    $words = preg_split('/\s+/', trim($name));
    $init  = strtoupper(substr($words[0], 0, 1));
    if (isset($words[1])) $init .= strtoupper(substr($words[1], 0, 1));
    return $init;
}

// role helpers
function is_doctor(): bool {
    return in_array(Auth::role(), ['OPDDoctor', 'ClinicDoctor', 'WardDoctor'], true);
}

function role_label(string $role): string {
    $labels = [
        'OPDDoctor' => 'OPD Doctor', 'ClinicDoctor' => 'Clinic Doctor', 'WardDoctor' => 'Ward Doctor',
        'WardNurse' => 'Ward Nurse', 'LabPersonnel' => 'Lab Personnel',
        'RadiologyPersonnel' => 'Radiology Personnel',
    ];
    return $labels[$role] ?? $role;
}
