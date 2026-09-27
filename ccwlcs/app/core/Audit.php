<?php
// Writes the audit trail. Called from the models so nothing important goes unlogged.
class Audit
{
    public static function log(string $action, string $ref = ''): void
    {
        try {
            $uid = Auth::isLoggedIn() ? Auth::id() : null;
            $ip  = self::clientIp();
            Db::get()
              ->prepare("INSERT INTO audit_log(user_id, action, entity_ref, ip_address, created_at)
                         VALUES (?, ?, ?, ?, NOW())")
              ->execute([$uid, $action, $ref, $ip]);
        } catch (Throwable $ex) {
            error_log('Audit::log error – '.$ex->getMessage());
        }
    }

    public static function actionSummary(): array
    {
        return Db::get()
            ->query("SELECT action, COUNT(*) cnt FROM audit_log GROUP BY action ORDER BY cnt DESC")
            ->fetchAll();
    }

    public static function dailyLogins(int $days = 30): array
    {
        $st = Db::get()->prepare(
            "SELECT DATE(created_at) day, COUNT(*) cnt
             FROM audit_log
             WHERE action='LOGIN' AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY day"
        );
        $st->execute([$days]);
        return $st->fetchAll();
    }

    // private helpers

    private static function clientIp(): string
    {
        // Only trust X-Forwarded-For when running behind a known local proxy.
        // In the XAMPP/development environment we simply use REMOTE_ADDR.
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
