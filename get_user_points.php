<?php
/**
 * Script pour récupérer le nombre de lavages de l'utilisateur connecté
 * 
 * MODIFICATION SYSTÈME FIDÉLITÉ:
 * - Ancien: "points_counter" = points de fidélité
 * - Nouveau: "points_counter" = nombre de lavages effectués
 * 
 * Le champ BDD reste "points_counter" pour compatibilité,
 * mais représente maintenant le nombre total de lavages du client.
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
            'nombre_lavage' => 0,
            'points' => 0 // Rétrocompatibilité
        ]);
        exit;
    }

    $userId = $_SESSION['user_id'];
    
    $conn = getDBConnection();
    
    // Récupérer le nombre de lavages de l'utilisateur
    $stmt = $conn->prepare("SELECT points_counter FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }
    
    $nombreLavage = intval($user['points_counter']);
    
    echo json_encode([
        'success' => true,
        'nombre_lavage' => $nombreLavage,  // Nouveau nom
        'points' => $nombreLavage          // Rétrocompatibilité
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'nombre_lavage' => 0,
        'points' => 0
    ]);
}
?>