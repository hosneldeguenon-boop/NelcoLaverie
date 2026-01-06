<?php
/**
 * Script de connexion administrateur - VERSION ROBUSTE
 * Fichier: admin_login_process.php
 * 
 * Ce fichier DOIT être à la RACINE du projet
 */

session_start();

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_log('=== admin_login_process.php APPELÉ ===');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    // ===== ÉTAPE 1: Récupérer les données =====
    
    $json = file_get_contents('php://input');
    error_log('JSON reçu');
    
    if (empty($json)) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $data = json_decode($json, true);
    
    if ($data === null) {
        throw new Exception('Erreur de décodage JSON: ' . json_last_error_msg());
    }
    
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    
    error_log('Tentative connexion: ' . $username);
    
    // ===== ÉTAPE 2: Validation =====
    
    if (empty($username) || empty($password)) {
        throw new Exception('Pseudonyme et mot de passe sont obligatoires');
    }
    
    // ===== ÉTAPE 3: Connexion BD =====
    
    if (!file_exists('config.php')) {
        throw new Exception('config.php non trouvé');
    }
    
    require_once 'config.php';
    
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // ===== ÉTAPE 4: Récupérer l'admin =====
    
    $stmt = $conn->prepare("
        SELECT id, lastname, firstname, username, email, password, status 
        FROM admins 
        WHERE username = :username
        LIMIT 1
    ");
    
    if (!$stmt) {
        throw new Exception('Erreur de requête');
    }
    
    $stmt->execute([':username' => $username]);
    
    if ($stmt->rowCount() === 0) {
        error_log('Admin non trouvé: ' . $username);
        throw new Exception('Pseudonyme ou mot de passe incorrect');
    }
    
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // ===== ÉTAPE 5: Vérifier le statut =====
    
    if ($admin['status'] === 'suspendu') {
        throw new Exception('Votre compte a été suspendu. Contactez l\'administrateur.');
    }
    
    if ($admin['status'] === 'inactif') {
        throw new Exception('Votre compte est inactif. Contactez l\'administrateur.');
    }
    
    // ===== ÉTAPE 6: Vérifier le mot de passe =====
    
    if (!password_verify($password, $admin['password'])) {
        error_log('Mot de passe incorrect pour: ' . $username);
        throw new Exception('Pseudonyme ou mot de passe incorrect');
    }
    
    error_log('Mot de passe vérifié OK');
    
    // ===== ÉTAPE 7: Mettre à jour last_login =====
    
    $updateStmt = $conn->prepare("UPDATE admins SET updated_at = NOW() WHERE id = :id");
    $updateStmt->execute([':id' => $admin['id']]);
    
    // ===== ÉTAPE 8: Créer la session =====
    
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_firstname'] = $admin['firstname'];
    $_SESSION['admin_lastname'] = $admin['lastname'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_logged_in'] = true;
    
    error_log('Session créée pour admin: ' . $username);
    
    // ===== ÉTAPE 9: Retourner le succès =====
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'admin' => [
            'id' => $admin['id'],
            'firstname' => $admin['firstname'],
            'lastname' => $admin['lastname'],
            'username' => $admin['username'],
            'email' => $admin['email']
        ]
    ]);
    
    error_log('Connexion réussie: ' . $username);

} catch (Exception $e) {
    error_log('EXCEPTION: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} catch (PDOException $e) {
    error_log('PDO EXCEPTION: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données'
    ]);
    
} catch (Throwable $e) {
    error_log('FATAL: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
}
?>