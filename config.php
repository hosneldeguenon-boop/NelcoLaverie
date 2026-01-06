<?php
/**
 * CONFIGURATION POUR INFINITYFREE
 */

// ========================================
// CONFIGURATION BASE DE DONNÉES
// ========================================
define('DB_HOST', 'sql111.infinityfree.com');
define('DB_USER', 'if0_40818441');
define('DB_PASS', 'Lenhros23112006');
define('DB_NAME', 'if0_40818441_laverie');
define('DB_CHARSET', 'utf8mb4');

// ========================================
// URL DU SITE
// ========================================
define('SITE_URL', 'https://nelcolaverie.infinityfreeapp.com');

// ========================================
// CONFIGURATION EMAIL (SMTP Gmail)
// ========================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'hosneldeguenon@gmail.com');
define('SMTP_PASSWORD', 'ggkk nwrt hdjn jxzp');
define('FROM_EMAIL', 'hosneldeguenon@gmail.com');
define('FROM_NAME', 'Nelco Laverie');

// ========================================
// SÉCURITÉ
// ========================================
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/errors.log');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', 1); // HTTPS

date_default_timezone_set('Africa/Porto-Novo');

// ========================================
// FONCTION CONNEXION BASE DE DONNÉES
// ========================================
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            
            $conn = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false,
                ]
            );
            
            return $conn;
            
        } catch (PDOException $e) {
            error_log('Erreur connexion BDD: ' . $e->getMessage());
            die('Erreur de connexion à la base de données.');
        }
    }
    
    return $conn;
}

// ========================================
// HELPER FUNCTIONS
// ========================================
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
           && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function generateSecureCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
?>