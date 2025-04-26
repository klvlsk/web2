<?php
class AuthMiddleware {
    public static function requireAdmin() {
        $db = new DatabaseRepository();
        if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
            self::unauthorized();
        }

        if (!$db->validateAdminCredentials($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
            self::unauthorized();
        }
    }

    private static function unauthorized() {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        echo '<h1>401 Требуется авторизация</h1>';
        exit();
    }

    public static function redirectIfNotLoggedIn() {
        if (!SessionManager::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
}