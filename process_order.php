<?php
/**
 * ✅ TRAITEMENT COMMANDES - SYSTÈME BASÉ SUR NOMBRE DE LINGES
 * Cycle fidélité 11 lavages maintenu
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

try {
    // Vérification session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Utilisateur non connecté');
    }

    $userId = $_SESSION['user_id'];
    
    // Récupération données
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Données invalides');
    }
    
    $requiredFields = [
        'nomClient', 'telephone', 'adresseCollecte', 'dateCollecte',
        'adresseLivraison', 'dateLivraison', 'paiement', 'linges'
    ];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Le champ $field est requis");
        }
    }
    
    // Connexion BDD
    $conn = getDBConnection();
    $conn->beginTransaction();
    
    // ✅ Récupérer utilisateur avec points_counter
    $stmt = $conn->prepare("
        SELECT customer_code, points_counter 
        FROM users 
        WHERE id = ? 
        FOR UPDATE
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }
    
    // ✅ points_counter = nombre de lavages
    $ancienNombreLavage = intval($user['points_counter']);
    
    // ============================================
    // CHARGEMENT CONFIGURATION
    // ============================================
    $configJson = file_get_contents(__DIR__ . '/laverie_config.json');
    $config = json_decode($configJson, true);
    
    if (!$config) {
        throw new Exception('Erreur chargement configuration');
    }
    
    // ============================================
    // FONCTIONS DE CALCUL
    // ============================================
    
    /**
     * Calcule le nombre de lavages requis
     */
    function calculerNombreLavages($linges, $config) {
        $details = [];
        $nombreLavagesTotal = 0;
        
        // Grouper par type_couleur_temperature
        $groupes = [];
        
        foreach ($linges as $item) {
            $cle = $item['type'] . '_' . $item['couleur'] . '_' . $item['temperature'];
            
            if (!isset($groupes[$cle])) {
                $groupes[$cle] = [
                    'type' => $item['type'],
                    'couleur' => $item['couleur'],
                    'temperature' => $item['temperature'],
                    'proportionTotale' => 0,
                    'items' => []
                ];
            }
            
            $proportion = $item['nombre'] * $item['proportionUnite'];
            $groupes[$cle]['proportionTotale'] += $proportion;
            $groupes[$cle]['items'][] = [
                'groupe' => $item['groupe'],
                'nombre' => $item['nombre'],
                'proportion' => $proportion
            ];
        }
        
        // Calculer le nombre de lavages pour chaque groupe
        foreach ($groupes as $cle => $groupe) {
            $seuil = $config['machines'][$groupe['type']]['seuil_remplissage'];
            $capacite = $config['machines'][$groupe['type']]['capacite_kg'];
            $proportionUtile = $capacite * $seuil;
            
            $quotient = floor($groupe['proportionTotale'] / $proportionUtile);
            $reste = $groupe['proportionTotale'] - ($quotient * $proportionUtile);
            
            $nombreLavages = $quotient + ($reste > 0 ? 1 : 0);
            
            $nombreLavagesTotal += $nombreLavages;
            
            $details[] = [
                'cle' => $cle,
                'type' => $groupe['type'],
                'couleur' => $groupe['couleur'],
                'temperature' => $groupe['temperature'],
                'proportionTotale' => $groupe['proportionTotale'],
                'nombreLavages' => $nombreLavages,
                'items' => $groupe['items']
            ];
        }
        
        return ['nombreLavages' => $nombreLavagesTotal, 'details' => $details];
    }
    
    /**
     * Calcule le prix de lavage
     */
    function calculerPrixLavage($linges, $config) {
        if (empty($linges)) return ['prix' => 0, 'lav' => 0, 'details' => []];
        
        $resultatsLavage = calculerNombreLavages($linges, $config);
        $prixTotal = 0;
        
        foreach ($resultatsLavage['details'] as &$detail) {
            $tarif = $config['tarifs_lavage'][$detail['type']][$detail['temperature']];
            $prix = $detail['nombreLavages'] * $tarif;
            $prixTotal += $prix;
            
            $detail['prix'] = $prix;
            $detail['tarif'] = $tarif;
        }
        
        return [
            'prix' => $prixTotal,
            'lav' => $resultatsLavage['nombreLavages'],
            'details' => $resultatsLavage['details']
        ];
    }
    
    /**
     * Calcule le prix de séchage
     */
    function calculerPrixSechage($linges, $config) {
        if (empty($linges)) return 0;
        
        // Calculer le poids total approximatif
        $poidsTotalKg = 0;
        foreach ($linges as $item) {
            $poidsTotalKg += $item['nombre'] * $item['proportionUnite'];
        }
        
        // Trouver le palier approprié
        foreach ($config['tarifs_sechage'] as $palier) {
            if ($poidsTotalKg <= $palier['poids_max_kg']) {
                return $palier['prix'];
            }
        }
        
        // Si dépassement, calculer récursivement
        $dernierPalier = end($config['tarifs_sechage']);
        $prixBase = $dernierPalier['prix'];
        $poidsRestant = $poidsTotalKg - $dernierPalier['poids_max_kg'];
        
        return $prixBase + calculerPrixSechageRecursif($poidsRestant, $config);
    }
    
    function calculerPrixSechageRecursif($poids, $config) {
        if ($poids <= 0) return 0;
        
        foreach ($config['tarifs_sechage'] as $palier) {
            if ($poids <= $palier['poids_max_kg']) {
                return $palier['prix'];
            }
        }
        
        $dernierPalier = end($config['tarifs_sechage']);
        return $dernierPalier['prix'] + calculerPrixSechageRecursif($poids - $dernierPalier['poids_max_kg'], $config);
    }
    
    /**
     * Calcule le prix de pliage
     */
    function calculerPrixPliage($linges, $config) {
        if (empty($linges)) return 0;
        
        $poidsTotalKg = 0;
        foreach ($linges as $item) {
            $poidsTotalKg += $item['nombre'] * $item['proportionUnite'];
        }
        
        if ($poidsTotalKg < $config['pliage']['minimum_kg']) return 0;
        
        $quotient = floor($poidsTotalKg / $config['pliage']['palier_kg']);
        $reste = $poidsTotalKg - ($quotient * $config['pliage']['palier_kg']);
        
        $prix = $quotient * $config['pliage']['prix_par_palier'];
        
        if ($reste >= $config['pliage']['minimum_kg']) {
            $prix += $config['pliage']['prix_par_palier'];
        }
        
        return $prix;
    }
    
    /**
     * Calcule le prix de repassage
     */
    function calculerPrixRepassage($linges, $config) {
        if (empty($linges)) return 0;
        
        $poidsOrdinaireKg = 0;
        $poidsVolumineuxKg = 0;
        
        foreach ($linges as $item) {
            $poids = $item['nombre'] * $item['proportionUnite'];
            if ($item['type'] === 'ordinaire') {
                $poidsOrdinaireKg += $poids;
            } else {
                $poidsVolumineuxKg += $poids;
            }
        }
        
        $prixTotal = 0;
        
        // Repassage ordinaire
        if ($poidsOrdinaireKg >= $config['repassage']['palier_ordinaire_kg']) {
            $tranches = floor($poidsOrdinaireKg / $config['repassage']['palier_ordinaire_kg']);
            $prixTotal += $tranches * $config['repassage']['prix_palier_ordinaire'];
        }
        
        // Repassage volumineux
        if ($poidsVolumineuxKg >= $config['repassage']['palier_volumineux_kg']) {
            $tranches = floor($poidsVolumineuxKg / $config['repassage']['palier_volumineux_kg']);
            $prixTotal += $tranches * $config['repassage']['prix_palier_volumineux'];
        }
        
        return $prixTotal;
    }
    
    // ============================================
    // CALCULS PRINCIPAUX
    // ============================================
    
    $linges = $data['linges'];
    
    if (empty($linges)) {
        throw new Exception('Aucun linge spécifié');
    }
    
    $lavageResult = calculerPrixLavage($linges, $config);
    $prixLavageTotal = $lavageResult['prix'];
    $lavTotal = $lavageResult['lav'];
    
    // ============================================
    // ✅ LOGIQUE FIDÉLITÉ - CYCLE DE 11 LAVAGES
    // ============================================
    $totalLavages = $ancienNombreLavage + $lavTotal;
    $nombreReductions = floor($totalLavages / 11);
    $nouveauNombreLavage = $totalLavages % 11;
    $reductionFidelite = $nombreReductions * 2500;
    
    // Appliquer réduction
    $prixLavageFinal = max(0, $prixLavageTotal - $reductionFidelite);
    
    // Autres calculs
    $prixSechage = calculerPrixSechage($linges, $config);
    $prixPliage = calculerPrixPliage($linges, $config);
    $prixRepassage = calculerPrixRepassage($linges, $config);
    
    // Prix collecte DÉSACTIVÉ
    // $commune1 = $data['communeCollecte'] ?? '';
    // $commune2 = $data['communeLivraison'] ?? '';
    // $tarifsCommunePrix = ['godomey' => 500, 'cotonou' => 1000, 'calavi' => 800, 'autres' => 1500];
    // $prixCollecte = ($tarifsCommunePrix[$commune1] ?? 0) + ($tarifsCommunePrix[$commune2] ?? 0);
    $prixCollecte = 0;
    
    $totalCommande = $prixLavageFinal + $prixSechage + $prixPliage + $prixRepassage + $prixCollecte;
    
    // ============================================
    // GÉNÉRATION NUMÉRO COMMANDE
    // ============================================
    $orderNumber = 'CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // ============================================
    // PRÉPARATION ORDER_DETAILS
    // ============================================
    $orderDetails = [
        'nomClientSaisi' => cleanInput($data['nomClient']),
        'telephoneSaisi' => cleanInput($data['telephone']),
        'linges' => $linges,
        'detailsLavage' => $lavageResult['details'],
        // 'communeCollecte' => $commune1,
        // 'communeLivraison' => $commune2,
        'prixLavageBrut' => $prixLavageTotal,
        'prixSechage' => $prixSechage,
        'prixPliage' => $prixPliage,
        'prixRepassage' => $prixRepassage,
        'prixCollecte' => $prixCollecte,
        'reductionFidelite' => $reductionFidelite,
        'moyenPaiement' => cleanInput($data['paiement']),
        'ancienNombreLavage' => $ancienNombreLavage,
        'lavCommande' => $lavTotal,
        'nouveauNombreLavage' => $nouveauNombreLavage
    ];
    
    // ============================================
    // INSERTION COMMANDE
    // ============================================
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id,
            order_number,
            customer_code,
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
            nombre_lavage_commande,
            nombre_lavage_avant,
            nombre_lavage_apres
        ) VALUES (
            ?, ?, ?, 'lavage_complet',
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, 'pending', ?, ?, ?
        )
    ");
    
    // Calcul poids total approximatif
    $poidsTotal = 0;
    foreach ($linges as $item) {
        $poidsTotal += $item['nombre'] * $item['proportionUnite'];
    }
    
    $stmt->execute([
        $userId,
        $orderNumber,
        $user['customer_code'],
        cleanInput($data['adresseCollecte']),
        $data['dateCollecte'],
        cleanInput($data['adresseLivraison']),
        $data['dateLivraison'],
        $totalCommande,
        $prixLavageFinal,
        $prixSechage,
        $prixPliage,
        $prixRepassage,
        $prixCollecte,
        $reductionFidelite,
        $poidsTotal,
        json_encode($orderDetails, JSON_UNESCAPED_UNICODE),
        cleanInput($data['paiement']),
        $lavTotal,
        $ancienNombreLavage,
        $nouveauNombreLavage
    ]);
    
    $orderId = $conn->lastInsertId();
    
    // Valider transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Commande enregistrée avec succès',
        'orderId' => $orderId,
        'orderNumber' => $orderNumber,
        'debug' => [
            'ancienNombreLavage' => $ancienNombreLavage,
            'lavCommande' => $lavTotal,
            'totalLavages' => $totalLavages,
            'nombreReductions' => $nombreReductions,
            'nouveauNombreLavage' => $nouveauNombreLavage,
            'reductionFidelite' => $reductionFidelite,
            'prixLavageBrut' => $prixLavageTotal,
            'prixLavageFinal' => $prixLavageFinal
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Erreur process_order: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>