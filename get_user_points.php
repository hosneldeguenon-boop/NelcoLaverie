<?php
/**
 * Script pour récupérer les points de fidélité de l'utilisateur connecté
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

try {
    // Vérifier que l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Utilisateur non connecté',
            'points' => 0
        ]);
        exit;
    }

    $userId = $_SESSION['user_id'];
    
    $conn = getDBConnection();
    
    // Récupérer les points de l'utilisateur
    $stmt = $conn->prepare("SELECT points_counter FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }
    
    echo json_encode([
        'success' => true,
        'points' => intval($user['points_counter'])
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'points' => 0
    ]);
}
?>