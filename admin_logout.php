<?php
/**
 * Script de déconnexion administrateur
 * Ce fichier détruit la session et redirige vers la page de connexion
 */

// Démarrer la session
session_start();

// Détruire toutes les variables de session
$_SESSION = array();

// Supprimer le cookie de session si existant
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Détruire la session
session_destroy();

// Headers pour empêcher le cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Rediriger vers la page de connexion
header("Location: admin_login.php?logout=success");
exit();
?>