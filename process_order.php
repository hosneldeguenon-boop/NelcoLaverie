<?php
/**
 * Script de traitement de la commande
 * Version mise à jour avec tous les nouveaux champs
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour passer une commande'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $conn = getDBConnection();
    
    // Récupérer les informations de l'utilisateur
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT customer_code, points_counter FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    // Générer un numéro de commande unique
    $orderNumber = 'CMD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Vérifier que le numéro n'existe pas
    $stmt = $conn->prepare("SELECT id FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    if ($stmt->fetch()) {
        $orderNumber = 'CMD-' . date('YmdHis') . '-' . rand(100, 999);
    }
    
    // Récupérer tous les prix
    $prixLavage = floatval($data['prixLavage'] ?? 0);
    $prixSechage = floatval($data['prixSechage'] ?? 0);
    $prixPliage = floatval($data['prixPliage'] ?? 0);
    $prixRepassage = floatval($data['prixRepassage'] ?? 0);
    $prixCollecte = floatval($data['prixCollecte'] ?? 0);
    $totalAmount = floatval($data['total'] ?? 0);
    
    // Parser les détails de poids
    $detailsPoidsArray = json_decode($data['detailsPoids'], true);
    
    // Préparer les détails COMPLETS de la commande (JSON)
    $orderDetails = [
        // Informations client SAISIES dans le formulaire
        'nomClientSaisi' => $data['nomClient'] ?? '',
        'telephoneSaisi' => $data['telephone'] ?? '',
        
        // Détails de lavage
        'poids' => $data['poids'] ?? [],
        'detailsPoidsComplets' => $detailsPoidsArray,
        'poidsTotal' => floatval($data['poidsTotal'] ?? 0),
        
        // Adresses et communes
        'communeCollecte' => $data['communeCollecte'] ?? '',
        'communeLivraison' => $data['communeLivraison'] ?? '',
        
        // Finances détaillées
        'prixLavage' => $prixLavage,
        'prixSechage' => $prixSechage,
        'prixPliage' => $prixPliage,
        'prixRepassage' => $prixRepassage,
        'prixCollecte' => $prixCollecte,
        'reductionFidelite' => floatval($data['reductionFidelite'] ?? 0),
        'moyenPaiement' => $data['paiement'] ?? ''
    ];
    
    // Insérer la commande
    $sql = "INSERT INTO orders (
        user_id, 
        order_number, 
        service_type,
        pickup_address, 
        pickup_date,
        delivery_address,
        delivery_date,
        total_amount,
        washing_price,
        drying_price,
        folding_price,
        ironing_price,
        delivery_price,
        loyalty_discount,
        total_weight,
        order_details,
        payment_method,
        status,
        points_at_order
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $userId,
        $orderNumber,
        'Lavage',
        $data['adresseCollecte'] ?? '',
        $data['dateCollecte'] ?? '',
        $data['adresseLivraison'] ?? '',
        $data['dateLivraison'] ?? '',
        $totalAmount,
        $prixLavage,
        $prixSechage,
        $prixPliage,
        $prixRepassage,
        $prixCollecte,
        floatval($data['reductionFidelite'] ?? 0),
        floatval($data['poidsTotal'] ?? 0),
        json_encode($orderDetails, JSON_UNESCAPED_UNICODE),
        $data['paiement'] ?? '',
        'en_attente_paiement',
        $user['points_counter']
    ]);
    
    $orderId = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Commande enregistrée avec succès',
        'orderId' => $orderId,
        'orderNumber' => $orderNumber,
        'totalAmount' => $totalAmount,
        'customerCode' => $user['customer_code']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>