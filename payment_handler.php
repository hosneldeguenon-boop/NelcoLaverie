<?php
/**
 * GESTIONNAIRE DE PAIEMENT MOBILE MONEY
 * Intègre les APIs MTN, Moov, Celtiis avec système de fallback
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $orderId = $data['orderId'] ?? 0;
    $method = $data['method'] ?? '';
    $amount = $data['amount'] ?? 0;
    $phoneNumber = $data['phoneNumber'] ?? '';
    
    // Validation
    if (!$orderId || !$method || !$amount) {
        throw new Exception('Données manquantes');
    }
    
    $conn = getDBConnection();
    
    // Vérifier que la commande existe
    $stmt = $conn->prepare("
        SELECT id, user_id, total_amount, status 
        FROM orders 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Commande non trouvée');
    }
    
    // Vérifier le montant
    if (abs($order['total_amount'] - $amount) > 1) {
        throw new Exception('Montant incorrect');
    }
    
    // =============================================
    // PAIEMENT À LA LIVRAISON - PAS D'API
    // =============================================
    if ($method === 'livraison') {
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'en_attente',
                payment_status = 'pending',
                payment_method = 'cash_on_delivery',
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Commande enregistrée. Paiement à la livraison.',
            'payment_method' => 'livraison',
            'redirect' => 'order_summary.php?orderId=' . $orderId
        ]);
        exit;
    }
    
    // =============================================
    // PAIEMENTS MOBILE MONEY - AVEC API
    // =============================================
    
    $apiUrl = '';
    $providerName = '';
    
    switch ($method) {
        case 'mtn':
            $apiUrl = PAYMENT_API_MTN;
            $providerName = 'MTN Mobile Money';
            break;
        case 'moov':
            $apiUrl = PAYMENT_API_MOOV;
            $providerName = 'Moov Money';
            break;
        case 'celtiis':
            $apiUrl = PAYMENT_API_CELTIIS;
            $providerName = 'Celtiis Money';
            break;
        default:
            throw new Exception('Méthode de paiement invalide');
    }
    
    // Vérifier si l'API est accessible (TIMEOUT RAPIDE)
    $apiAvailable = checkApiAvailability($apiUrl);
    
    if (!$apiAvailable) {
        // API INDISPONIBLE - FALLBACK
        echo json_encode([
            'success' => false,
            'message' => 'Le paiement ' . $providerName . ' est temporairement indisponible. Veuillez réessayer ou choisir un autre moyen de paiement.',
            'error_code' => 'API_UNAVAILABLE',
            'fallback' => true
        ]);
        exit;
    }
    
    // =============================================
    // APPEL À L'API DE PAIEMENT
    // =============================================
    $paymentData = [
        'order_id' => $orderId,
        'amount' => $amount,
        'phone' => $phoneNumber,
        'customer_name' => $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname'],
        'customer_email' => $_SESSION['user_email'],
        'callback_url' => SITE_URL . '/payment_callback.php'
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($paymentData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => PAYMENT_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Gérer les erreurs CURL
    if ($curlError) {
        error_log("Erreur CURL paiement $method: $curlError");
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de connexion au service de paiement. Veuillez réessayer.',
            'error_code' => 'CURL_ERROR',
            'fallback' => true
        ]);
        exit;
    }
    
    // Décoder la réponse
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && isset($result['success']) && $result['success']) {
        // PAIEMENT RÉUSSI
        $transactionId = $result['transaction_id'] ?? 'TRX-' . time();
        
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'en_attente',
                payment_status = 'success',
                payment_method = ?,
                transaction_id = ?,
                payment_date = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$method, $transactionId, $orderId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Paiement effectué avec succès !',
            'transaction_id' => $transactionId,
            'payment_method' => $providerName,
            'redirect' => 'order_summary.php?orderId=' . $orderId
        ]);
        
    } else {
        // PAIEMENT ÉCHOUÉ
        error_log("Échec paiement $method pour commande $orderId: " . print_r($result, true));
        
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Le paiement a échoué. Veuillez vérifier votre solde et réessayer.',
            'error_code' => $result['error_code'] ?? 'PAYMENT_FAILED',
            'fallback' => true
        ]);
    }
    
} catch (Exception $e) {
    error_log("Exception payment_handler: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Vérifie rapidement si une API est accessible
 */
function checkApiAvailability($url, $timeout = 3) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => true, // HEAD request seulement
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => false // Pour tester, à activer en prod
    ]);
    
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Considérer l'API disponible si code HTTP entre 200-499
    // (même erreur 400 signifie que l'API répond)
    return ($httpCode >= 200 && $httpCode < 500);
}
?>