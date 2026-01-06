<?php
/**
 * Supprimer son propre commentaire
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_log('delete_user_comment.php appelé');

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
    
    // Récupérer les données
    $json = file_get_contents('php://input');
    error_log('JSON reçu: ' . $json);
    
    $data = json_decode($json, true);
    
    if ($data === null) {
        throw new Exception('Erreur de décodage JSON');
    }

    $comment_id = intval($data['comment_id'] ?? 0);
    
    error_log('Tentative suppression: comment_id=' . $comment_id . ', user_id=' . $user_id);

    if (!$comment_id) {
        throw new Exception('ID commentaire invalide');
    }

    $conn = getDBConnection();

    // Vérifier que le commentaire appartient à l'utilisateur
    $verifyStmt = $conn->prepare("
        SELECT id, user_id FROM comments WHERE id = ?
    ");
    $verifyStmt->execute([$comment_id]);
    $comment = $verifyStmt->fetch();

    if (!$comment) {
        error_log('Commentaire non trouvé: ' . $comment_id);
        throw new Exception('Commentaire non trouvé');
    }

    if (intval($comment['user_id']) !== intval($user_id)) {
        error_log('Accès refusé: comment_user_id=' . $comment['user_id'] . ', session_user_id=' . $user_id);
        http_response_code(403);
        throw new Exception('Vous ne pouvez pas supprimer ce commentaire');
    }

    // Supprimer le commentaire
    $deleteStmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $result = $deleteStmt->execute([$comment_id, $user_id]);

    if (!$result) {
        error_log('Erreur SQL: ' . implode(' ', $deleteStmt->errorInfo()));
        throw new Exception('Erreur lors de la suppression');
    }

    error_log('Commentaire supprimé: ' . $comment_id);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Avis supprimé avec succès'
    ]);

} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>