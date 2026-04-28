<?php

class Auth
{
    public static function user()
    {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }

    public static function check()
    {
        return isset($_SESSION['user']);
    }

    public static function id()
    {
        return isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
    }

    public static function role()
    {
        return isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : null;
    }

    public static function login($user)
    {
        $_SESSION['user'] = array(
            'id'       => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role']
        );
    }

    public static function logout()
    {
        $_SESSION = array();
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    public static function requireLogin()
    {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function requireRole($role)
    {
        if (!self::check() || self::role() !== $role) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}
