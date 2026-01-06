<?php
/**
 * Récupérer tous les commentaires (publique)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

error_log('get_comments.php appelé');

require_once 'config.php';

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Récupérer tous les commentaires avec infos utilisateur
    $stmt = $conn->prepare("
        SELECT 
            c.id, 
            c.user_id, 
            c.rating, 
            c.comment_text, 
            c.created_at,
            u.firstname, 
            u.lastname
        FROM comments c
        INNER JOIN users u ON c.user_id = u.id
        ORDER BY c.created_at DESC
        LIMIT 1000
    ");
    
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log('Commentaires chargés: ' . count($comments));

    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'count' => count($comments)
    ]);

} catch (PDOException $e) {
    error_log('Erreur PDO dans get_comments.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Exception dans get_comments.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
?>