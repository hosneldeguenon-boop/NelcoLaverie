<?php
/**
 * Script d'inscription administrateur - VERSION ROBUSTE
 * Fichier: admin_signup_process.php
 * 
 * Ce fichier DOIT être à la RACINE du projet au même niveau que admin_signup.php
 */

// Démarrer les logs
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Ne pas afficher les erreurs (retourner du JSON)
ini_set('log_errors', '1');

// Headers JSON (TRÈS IMPORTANT)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Traiter les requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_log('=== admin_signup_process.php APPELÉ ===');
error_log('Méthode: ' . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilisez POST.'
    ]);
    exit;
}

try {
    // ===== ÉTAPE 1: Récupérer et valider les données =====
    
    $json = file_get_contents('php://input');
    error_log('JSON brut reçu: ' . substr($json, 0, 100) . '...');
    
    if (empty($json)) {
        throw new Exception('Aucune donnée reçue (JSON vide)');
    }
    
    $data = json_decode($json, true);
    
    if ($data === null) {
        error_log('Erreur décodage JSON: ' . json_last_error_msg());
        throw new Exception('Erreur de décodage JSON: ' . json_last_error_msg());
    }
    
    error_log('Données décodées: ' . print_r($data, true));
    
    // Récupération sécurisée des données
    $lastname = trim($data['lastname'] ?? '');
    $firstname = trim($data['firstname'] ?? '');
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $gender = trim($data['gender'] ?? '');
    $password = $data['password'] ?? '';
    
    // ===== ÉTAPE 2: Validation des données =====
    
    if (empty($lastname)) {
        throw new Exception('Le nom est obligatoire');
    }
    
    if (empty($firstname)) {
        throw new Exception('Le prénom est obligatoire');
    }
    
    if (empty($username)) {
        throw new Exception('Le pseudonyme est obligatoire');
    }
    
    if (empty($email)) {
        throw new Exception('L\'email est obligatoire');
    }
    
    if (empty($phone)) {
        throw new Exception('Le téléphone est obligatoire');
    }
    
    if (empty($gender)) {
        throw new Exception('Le sexe est obligatoire');
    }
    
    if (empty($password)) {
        throw new Exception('Le mot de passe est obligatoire');
    }
    
    if (strlen($password) < 8) {
        throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email invalide');
    }
    
    $phone_clean = preg_replace('/[\s\-\.]/', '', $phone);
    if (!preg_match('/^[0-9]{10,}$/', $phone_clean)) {
        throw new Exception('Numéro de téléphone invalide (minimum 10 chiffres)');
    }
    
    if (!in_array($gender, ['M', 'F', 'Autre'])) {
        throw new Exception('Valeur de sexe invalide');
    }
    
    error_log('Validation réussie');
    
    // ===== ÉTAPE 3: Connexion à la BD =====
    
    if (!file_exists('configs.php')) {
        throw new Exception('Erreur: config.php non trouvé');
    }
    
    require_once 'configs.php';
    
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    error_log('Connexion BD réussie');
    
    // ===== ÉTAPE 4: Vérifier l'unicité du pseudo et email =====
    
    $checkStmt = $conn->prepare("
        SELECT id FROM admins 
        WHERE username = :username OR email = :email
        LIMIT 1
    ");
    
    $checkStmt->execute([
        ':username' => $username,
        ':email' => $email
    ]);
    
    if ($checkStmt->rowCount() > 0) {
        throw new Exception('Le pseudonyme ou l\'email existe déjà');
    }
    
    error_log('Pseudo/email uniques OK');
    
    // ===== ÉTAPE 5: Hasher le mot de passe =====
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    if (!$hashedPassword) {
        throw new Exception('Erreur lors du hashage du mot de passe');
    }
    
    error_log('Hash généré OK');
    
    // ===== ÉTAPE 6: Insérer en BD =====
    
    $insertStmt = $conn->prepare("
        INSERT INTO admins 
        (lastname, firstname, username, email, phone, gender, password, status, created_at, updated_at)
        VALUES 
        (:lastname, :firstname, :username, :email, :phone, :gender, :password, :status, NOW(), NOW())
    ");
    
    $result = $insertStmt->execute([
        ':lastname' => $lastname,
        ':firstname' => $firstname,
        ':username' => $username,
        ':email' => $email,
        ':phone' => $phone,
        ':gender' => $gender,
        ':password' => $hashedPassword,
        ':status' => 'actif'
    ]);
    
    if (!$result) {
        $errorInfo = $insertStmt->errorInfo();
        error_log('Erreur SQL: ' . $errorInfo[2]);
        throw new Exception('Erreur lors de l\'enregistrement: ' . $errorInfo[2]);
    }
    
    error_log('Admin inséré en BD avec succès: ' . $username);
    
    // ===== ÉTAPE 7: Retourner le succès =====
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie ! Redirection vers la connexion...',
        'admin_id' => $conn->lastInsertId()
    ]);
    
    error_log('=== Inscription admin réussie ===');

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
        'message' => 'Erreur base de données: ' . $e->getMessage()
    ]);
    
} catch (Throwable $e) {
    error_log('FATAL ERROR: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>