<?php
class SessionManager {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function setFlash($type, $message) {
        self::set('_flash', ['type' => $type, 'message' => $message]);
    }

    public static function getFlash() {
        $flash = self::get('_flash');
        self::remove('_flash');
        return $flash;
    }

    public static function isLoggedIn() {
        return !empty(self::get('login'));
    }

    public static function logout() {
        session_unset();
        session_destroy();
    }
}