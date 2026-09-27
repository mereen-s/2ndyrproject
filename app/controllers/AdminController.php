<?php
// Accounts, role permissions and the audit log.
class AdminController {

  private const AUDIT_PAGE_SIZE = 50;

  // user management
  public function users() {
    view('admin_users', [
      'users'       => UserModel::all(),
      'departments' => UserModel::departments(),
    ]);
  }

  public function createUser() {
    $v = (new Validator($_POST))
      ->required('username',   'Username is required')
      ->maxLen('username', 40, 'Username must be under 40 characters')
      ->required('password',   'Password is required')
      ->minLen('password', 6,  'Password must be at least 6 characters')
      ->required('full_name',  'Full name is required')
      ->required('role',       'Role is required');

    if ($v->fails()) {
      flash(implode(' | ', $v->errors()), 'error');
      redirect('admin_users');
      return;
    }

    UserModel::create(
      $_POST['username'],  $_POST['password'],
      $_POST['full_name'], $_POST['role'],
      $_POST['department_id'] ?: null
    );
    flash('Account created for '.$_POST['username'].'.');
    redirect('admin_users');
  }

  public function updateUser() {
    UserModel::update(
      $_POST['user_id'],
      $_POST['role'],
      $_POST['department_id'] ?: null,
      $_POST['status']
    );
    flash('Account updated. The user must log in again for changes to take effect.');
    redirect('admin_users');
  }

  // permissions
  // step 1 - the list of roles
  public function permissions() {
    $all = 0; foreach (Permission::GROUPS as $b) $all += count($b);
    $reports = count(Permission::GROUPS[Permission::ADMIN_ONLY_MODULE]);
    $roles = [];
    foreach (Permission::ROLES as $r) {
      $granted = count(Permission::grantedGroups($r));
      $roles[$r] = [$granted, $r === 'Administrator' ? $all : $all - $reports];
    }
    view('admin_perms', ['roles' => $roles]);
  }

  // step 2 - one role's permissions, grouped by module
  public function permissionsRole() {
    $role = $_GET['role'] ?? '';
    if (!in_array($role, Permission::ROLES, true)) { flash('Unknown role.', 'error'); redirect('admin_perms'); }
    view('admin_perm_role', [
      'role'    => $role,
      'groups'  => Permission::GROUPS,
      'granted' => Permission::grantedGroups($role),
    ]);
  }

  public function permissionsSave() {
    $role = $_POST['role'] ?? '';
    if (!in_array($role, Permission::ROLES, true)) { flash('Unknown role.', 'error'); redirect('admin_perms'); }
    Permission::saveForRole($role, array_keys($_POST['groups'] ?? []));
    flash('Permissions saved for '.role_label($role).'. They apply from the next page load.');
    redirect('admin_perm_role', '&role='.urlencode($role));
  }


  // audit log — paginated with filters
  public function audit() {
    $db      = Db::get();
    $pg      = max(1, (int)($_GET['pg'] ?? 1));
    $offset  = ($pg - 1) * self::AUDIT_PAGE_SIZE;

    // Build dynamic WHERE clause from filter inputs
    $where   = ['1=1']; $params = [];
    if (!empty($_GET['f_action'])) {
      $where[] = 'al.action LIKE ?';
      $params[] = '%'.$_GET['f_action'].'%';
    }
    if (!empty($_GET['f_user'])) {
      $where[] = 'u.username LIKE ?';
      $params[] = '%'.$_GET['f_user'].'%';
    }
    if (!empty($_GET['f_from'])) {
      $where[] = 'DATE(al.created_at) >= ?';
      $params[] = $_GET['f_from'];
    }
    if (!empty($_GET['f_to'])) {
      $where[] = 'DATE(al.created_at) <= ?';
      $params[] = $_GET['f_to'];
    }

    $whereStr = implode(' AND ', $where);

    // Total count (for pagination)
    $cntSt = $db->prepare(
      "SELECT COUNT(*) FROM audit_log al LEFT JOIN user u ON u.user_id=al.user_id WHERE $whereStr");
    $cntSt->execute($params);
    $total = (int)$cntSt->fetchColumn();
    $pages = (int)ceil($total / self::AUDIT_PAGE_SIZE);

    // Rows for current page
    $st = $db->prepare(
      "SELECT al.*, u.username FROM audit_log al
       LEFT JOIN user u ON u.user_id=al.user_id
       WHERE $whereStr
       ORDER BY al.created_at DESC
       LIMIT ".self::AUDIT_PAGE_SIZE." OFFSET $offset");
    $st->execute($params);

    view('audit', [
      'rows'   => $st->fetchAll(),
      'total'  => $total,
      'page'   => $pg,
      'pages'  => $pages,
      'filter' => [
        'action' => $_GET['f_action'] ?? '',
        'user'   => $_GET['f_user']   ?? '',
        'from'   => $_GET['f_from']   ?? '',
        'to'     => $_GET['f_to']     ?? '',
      ],
    ]);
  }
}
