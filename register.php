<?php
/**
 * Script d'inscription - VERSION CORRIGÉE
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/errors.log');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Fonction pour générer un code client unique
function generateCustomerCode($conn) {
    do {
        $letters = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
        $numbers = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $customerCode = "LAV-{$letters}-{$numbers}";
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE customer_code = ?");
        $stmt->execute([$customerCode]);
        $exists = $stmt->fetch();
    } while ($exists);
    
    return $customerCode;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
    return preg_match('/^[\+]?[0-9]{8,15}$/', $cleaned);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    error_log("========================================");
    error_log(">>> DÉBUT register.php");
    
    $json = file_get_contents('php://input');
    error_log("JSON reçu: " . $json);
    
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }

    // Récupérer et nettoyer les données
    $lastname  = cleanInput($data['lastname'] ?? '');
    $firstname = cleanInput($data['firstname'] ?? '');
    $email     = cleanInput($data['email'] ?? '');
    $phone     = cleanInput($data['phone'] ?? '');
    $whatsapp  = cleanInput($data['whatsapp'] ?? '');
    $address   = cleanInput($data['address'] ?? '');
    $gender    = cleanInput($data['gender'] ?? '');
    $password  = $data['password'] ?? '';

    error_log("Données extraites:");
    error_log("  - Nom: $lastname");
    error_log("  - Prénom: $firstname");
    error_log("  - Email: $email");
    error_log("  - Phone: $phone");

    // Validations
    if (
        empty($lastname) || empty($firstname) || empty($email) ||
        empty($phone) || empty($whatsapp) || empty($address) ||
        empty($gender) || empty($password)
    ) {
        throw new Exception('Tous les champs sont obligatoires');
    }

    if (strlen($lastname) < 2) {
        throw new Exception('Le nom doit contenir au moins 2 caractères');
    }

    if (strlen($firstname) < 2) {
        throw new Exception('Le prénom doit contenir au moins 2 caractères');
    }

    if (!validateEmail($email)) {
        throw new Exception('Format d\'email invalide');
    }

    if (!validatePhone($phone)) {
        throw new Exception('Format de numéro de téléphone invalide');
    }

    if (!validatePhone($whatsapp)) {
        throw new Exception('Format de numéro WhatsApp invalide');
    }

    if (strlen($password) < 8) {
        throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
    }

    if (!in_array($gender, ['homme', 'femme'])) {
        throw new Exception('Genre invalide');
    }

    error_log("Validations OK");

    // Connexion BDD
    $conn = getDBConnection();
    error_log("Connexion BDD OK");

    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Vérifier si le téléphone existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        throw new Exception('Ce numéro de téléphone est déjà utilisé');
    }

    error_log("Email et téléphone disponibles");

    // Générer le code client
    $customerCode = generateCustomerCode($conn);
    error_log("Code client généré: $customerCode");

    // Hasher le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    error_log("Mot de passe hashé");

    // Insérer l'utilisateur
    $sql = "INSERT INTO users 
        (lastname, firstname, email, phone, whatsapp, address, gender, password, customer_code, points_counter, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'actif', NOW())";

    $stmt = $conn->prepare($sql);
    $inserted = $stmt->execute([
        $lastname,
        $firstname,
        $email,
        $phone,
        $whatsapp,
        $address,
        $gender,
        $hashedPassword,
        $customerCode
    ]);

    if (!$inserted) {
        throw new Exception('Erreur lors de l\'insertion en base de données');
    }

    $userId = $conn->lastInsertId();
    error_log("✅ Utilisateur créé - ID: $userId");
    error_log(">>> FIN register.php (SUCCÈS)");
    error_log("========================================");

    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie',
        'userId' => $userId,
        'customerCode' => $customerCode
    ]);

} catch (Exception $e) {
    error_log("❌ ERREUR: " . $e->getMessage());
    error_log(">>> FIN register.php (ÉCHEC)");
    error_log("========================================");
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>