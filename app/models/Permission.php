<?php
// Which roles may open which page. Read on every request, edited by the Administrator.
class Permission {
  public static function rolesFor($page) {
    $st = Db::get()->prepare("SELECT role FROM permission WHERE page=?");
    $st->execute([$page]);
    return $st->fetchAll(PDO::FETCH_COLUMN);
  }

  public static function all() {
    return Db::get()->query("SELECT role, page FROM permission ORDER BY page, role")->fetchAll();
  }

  // $perm is the submitted grid: $perm[page][role] = on
  public static function saveAll(array $perm) {
    $db = Db::get();
    $pages = $db->query("SELECT DISTINCT page FROM permission")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($pages as $pg) {
      // the Administrator can never lock themselves out of the admin pages
      $db->prepare("DELETE FROM permission WHERE page=?
                    AND NOT (role='Administrator' AND (page LIKE 'admin%' OR page='audit'))")
         ->execute([$pg]);
      foreach (array_keys($perm[$pg] ?? []) as $role) {
        $db->prepare("INSERT IGNORE INTO permission(role,page) VALUES (?,?)")->execute([$role, $pg]);
      }
    }
    Audit::log('PERMISSIONS_UPDATE', 'all pages');
  }
}
