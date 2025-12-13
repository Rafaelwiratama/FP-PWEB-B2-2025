<?php
// config/config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '127.0.0.1';
$port = 3307;          
$db   = 'slasher_db';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Koneksi gagal: ' . $e->getMessage());
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/Slashers.com/');
}

if (!function_exists('rupiah')) {
    function rupiah(int $angka): string {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

function get_setting(string $key, string $default = ''): string
{
    global $pdo;
    static $cache = [];

    if (isset($cache[$key])) {
        return $cache[$key];
    }

    try {
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $value = false;
    }

    if ($value === false) {
        return $default;
    }

    $cache[$key] = $value;
    return $value;
}

if (!function_exists('generate_redeem_code')) {
    function generate_redeem_code(string $platformSlug = ''): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $raw = '';
        for ($i = 0; $i < 16; $i++) {
            $raw .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $chunks = str_split($raw, 4);
        $code = implode('-', $chunks);

        if ($platformSlug) {
            $prefix = strtoupper(substr(preg_replace('/[^A-Z0-9]/i','',$platformSlug), 0, 4));
            return ($prefix ? $prefix.'-' : '') . $code;
        }
        return $code;
    }
}
