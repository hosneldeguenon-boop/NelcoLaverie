<?php
/**
 * Script d'ajout de commentaire
 * Assurez-vous que ce fichier est à la racine du projet
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Log pour déboguer
error_log('add_comment.php appelé');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // Vérifier l'authentification
    if (!isset($_SESSION['user_id'])) {
        error_log('Erreur: user_id non défini dans la session');
        http_response_code(401);
        throw new Exception('Vous devez être connecté');
    }

    $user_id = $_SESSION['user_id'];
    error_log('User ID: ' . $user_id);

    // Récupérer les données
    $json = file_get_contents('php://input');
    error_log('JSON reçu: ' . $json);
    
    $data = json_decode($json, true);
    
    if ($data === null) {
        error_log('Erreur JSON: ' . json_last_error_msg());
        throw new Exception('Erreur de décodage JSON');
    }

    $comment_text = trim($data['comment_text'] ?? '');
    $rating = isset($data['rating']) && $data['rating'] ? intval($data['rating']) : null;

    error_log('Commentaire: ' . substr($comment_text, 0, 50) . '...');
    error_log('Note: ' . ($rating ?? 'nulle'));

    // Validation
    if (empty($comment_text)) {
        throw new Exception('Le commentaire ne peut pas être vide');
    }

    if (strlen($comment_text) < 10) {
        throw new Exception('Le commentaire doit contenir au moins 10 caractères');
    }

    if (strlen($comment_text) > 500) {
        throw new Exception('Le commentaire ne peut pas dépasser 500 caractères');
    }

    if ($rating !== null && ($rating < 1 || $rating > 5)) {
        throw new Exception('La note doit être entre 1 et 5');
    }

    // Vérifier les points de fidélité
    $conn = getDBConnection();
    
    $userStmt = $conn->prepare("SELECT points_counter FROM users WHERE id = ?");
    $userStmt->execute([$user_id]);
    $user = $userStmt->fetch();

    if (!$user) {
        error_log('Erreur: utilisateur ' . $user_id . ' non trouvé');
        throw new Exception('Utilisateur non trouvé');
    }

    if ($user['points_counter'] <= 0) {
        error_log('Erreur: points insuffisants pour user ' . $user_id);
        throw new Exception('Vous devez passer au moins une commande avant de commenter');
    }

    // Insérer le commentaire
    $stmt = $conn->prepare("
        INSERT INTO comments (user_id, comment_text, rating, created_at, updated_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ");

    $result = $stmt->execute([$user_id, $comment_text, $rating]);

    if (!$result) {
        error_log('Erreur SQL: ' . implode(' ', $stmt->errorInfo()));
        throw new Exception('Erreur lors de l\'insertion en base de données');
    }

    error_log('Commentaire inséré avec succès pour user ' . $user_id);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Commentaire ajouté avec succès'
    ]);

} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    error_log('Erreur fatale: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
}
?>