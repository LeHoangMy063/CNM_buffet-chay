<?php

// Kiem tra quyen admin - PHP 5.3 compatible

class AdminAuthMiddleware
{
    // Kiem tra admin da dang nhap chua
    public static function isAuthenticated()
    {
        return isset($_SESSION[ADMIN_SESSION_KEY])
            && !empty($_SESSION[ADMIN_SESSION_KEY])
            && isset($_SESSION[ADMIN_SESSION_KEY]['role'])
            && $_SESSION[ADMIN_SESSION_KEY]['role'] === ROLE_ADMIN;
    }

    // Lay thong tin admin hien tai
    public static function getCurrentAdmin()
    {
        if (self::isAuthenticated()) {
            return $_SESSION[ADMIN_SESSION_KEY];
        }
        return null;
    }

    // Kiem tra session timeout
    public static function isSessionValid()
    {
        if (!self::isAuthenticated()) {
            return false;
        }

        if (isset($_SESSION['admin_last_activity'])) {
            $timeout = ADMIN_SESSION_TIMEOUT * 60;

            if ((time() - $_SESSION['admin_last_activity']) > $timeout) {
                self::destroySession();
                return false;
            }
        }

        $_SESSION['admin_last_activity'] = time();
        return true;
    }

    // Xoa session admin
    public static function destroySession()
    {
        if (isset($_SESSION[ADMIN_SESSION_KEY])) {
            unset($_SESSION[ADMIN_SESSION_KEY]);
        }
        if (isset($_SESSION['admin_last_activity'])) {
            unset($_SESSION['admin_last_activity']);
        }
    }

    // Redirect ve login neu chua xac thuc
    public static function requireLogin($redirectAfterLogin = '')
    {
        if (!self::isSessionValid()) {
            self::destroySession();

            $loginUrl = ADMIN_LOGIN_URL;
            if ($redirectAfterLogin) {
                $loginUrl .= '?redirect=' . urlencode($redirectAfterLogin);
            }

            header('Location: ' . $loginUrl);
            exit;
        }
    }

    // Log hanh dong admin
    public static function logAction($action, $description = '')
    {
        $admin = self::getCurrentAdmin();
        if (!$admin) {
            return;
        }

        // PHP 5.3: dung isset thay ?? operator
        $adminName = isset($admin['full_name']) && $admin['full_name'] !== ''
            ? $admin['full_name']
            : (isset($admin['username']) ? $admin['username'] : '');

        $ipAddress = isset($_SERVER['REMOTE_ADDR'])    ? $_SERVER['REMOTE_ADDR']    : '';
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        $logData = array(
            'admin_id'    => $admin['id'],
            'admin_name'  => $adminName,
            'action'      => $action,
            'description' => $description,
            'timestamp'   => date('Y-m-d H:i:s'),
            'ip_address'  => $ipAddress,
            'user_agent'  => $userAgent
        );

        // TODO: Ghi log vao database hoac file
        // error_log(json_encode($logData));
    }
}
