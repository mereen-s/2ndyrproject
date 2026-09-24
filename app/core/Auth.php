<?php
// Login, sessions and the role/department checks every page relies on.
class Auth
{
    // login / logout

    public static function attempt(string $username, string $password): bool
    {
        $st = Db::get()->prepare(
            "SELECT u.*, d.name dept_name
             FROM user u
             LEFT JOIN department d ON d.department_id = u.department_id
             WHERE u.username = ? AND u.status = 'Active'
             LIMIT 1"
        );
        $st->execute([$username]);
        $u = $st->fetch();

        if (!$u || !password_verify($password, $u['password_hash'])) {
            // Record failed attempt without revealing which field was wrong
            Audit::log('LOGIN_FAIL', 'username:'.substr($username, 0, 30));
            return false;
        }

        session_regenerate_id(true);                 // prevent session fixation
        $_SESSION['user'] = [
            'id'       => (int)$u['user_id'],
            'name'     => $u['full_name'],
            'role'     => $u['role'],
            'dept'     => $u['department_id'] !== null ? (int)$u['department_id'] : null,
            'username' => $u['username'],
        ];
        $_SESSION['_rotated_at'] = time();
        Audit::log('LOGIN', 'user:'.$u['user_id']);
        return true;
    }

    public static function logout(): void
    {
        Audit::log('LOGOUT', 'user:'.self::id());
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: '.BASE_URL.'/index.php?page=login');
        exit;
    }

    // session guards

    public static function check(): void
    {
        if (empty($_SESSION['user'])) {
            flash('Please log in to continue.');
            redirect('login');
        }
        // Periodic session ID rotation (anti-fixation)
        if (!isset($_SESSION['_rotated_at'])) {
            $_SESSION['_rotated_at'] = time();
        } elseif (time() - $_SESSION['_rotated_at'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_rotated_at'] = time();
        }
    }

    public static function requireRole(array|string $roles): void
    {
        self::check();
        if (!in_array(self::role(), (array)$roles, true)) {
            http_response_code(403);
            die('Access denied for your role.');
        }
    }

    // accessors

    public static function isLoggedIn(): bool { return !empty($_SESSION['user']); }

    public static function user(): ?array { return $_SESSION['user'] ?? null; }

    public static function id(): int { return (int)($_SESSION['user']['id'] ?? 0); }

    public static function role(): string { return $_SESSION['user']['role'] ?? ''; }

    public static function dept(): ?int
    {
        $d = $_SESSION['user']['dept'] ?? null;
        return $d !== null ? (int)$d : null;
    }

    public static function name(): string { return $_SESSION['user']['name'] ?? ''; }
}
