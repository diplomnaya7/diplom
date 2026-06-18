<?php
/**
 * Класс для защиты от XSS
 */

class Security
{
    /**
     * Экранирует HTML-символы для безопасного вывода
     */
    public static function escape($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'escape'], $data);
        }

        if ($data === null) {
            return '';
        }

        return htmlspecialchars((string) $data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Генерирует CSRF токен
     */
    public static function generateCSRFToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Проверяет CSRF токен
     */
    public static function verifyCSRFToken($token)
    {
        return !empty($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Хеширует пароль
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Проверяет пароль
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Валидирует MIME тип файла
     */
    public static function isValidMimeType($filePath, $allowedMimes)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return in_array($mimeType, $allowedMimes);
    }

    /**
     * Валидирует расширение файла
     */
    public static function isValidExtension($filename, $allowedExtensions)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowedExtensions);
    }

    /**
     * Защищенное имя файла
     */
    public static function sanitizeFilename($filename)
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    }
}
