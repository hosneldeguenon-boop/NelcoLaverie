<?php
/**
 * Vérifier l'authentification utilisateur via AJAX
 * Utilisé par comments.php et autres pages nécessitant l'auth
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    
    // Vérifier le timeout de session (30 minutes d'inactivité)
    if (isset($_SESSION['last_activity'])) {
        $inactive_time = time() - $_SESSION['last_activity'];
        
        if ($inactive_time > 1800) { // 30 minutes
            // Session expirée
            session_unset();
            session_destroy();
            
            echo json_encode([
                'authenticated' => false,
                'user_id' => null,
                'message' => 'Session expirée'
            ]);
            exit();
        }
    }
    
    // Mettre à jour le temps d'activité
    $_SESSION['last_activity'] = time();
    
    // Retourner les informations de l'utilisateur
    echo json_encode([
        'authenticated' => true,
        'user_id' => $_SESSION['user_id'],
        'firstname' => $_SESSION['user_firstname'] ?? '',
        'lastname' => $_SESSION['user_lastname'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'customer_code' => $_SESSION['customer_code'] ?? '',
        'points' => $_SESSION['points_counter'] ?? 0
    ]);
    
} else {
    // Non authentifié
    echo json_encode([
        'authenticated' => false,
        'user_id' => null,
        'message' => 'Non authentifié'
    ]);
}
?>