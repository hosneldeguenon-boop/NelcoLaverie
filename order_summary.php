<?php
/**
 * Page de récapitulatif de commande
 * Affiche TOUS les détails de la commande après paiement
 */

session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$orderId = $_GET['orderId'] ?? 0;

if (!$orderId) {
    die('Commande non trouvée');
}

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT o.*, u.lastname, u.firstname, u.customer_code, u.email, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        die('Commande non trouvée');
    }
    
    $orderDetails = json_decode($order['order_details'], true);
    
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif - Commande <?= htmlspecialchars($order['order_number']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header h1 {
            margin-bottom: 10px;
        }

        .success-icon {
            font-size: 60px;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .content {
            padding: 40px;
        }

        .section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 600;
            color: #333;
        }

        .info-highlight {
            background: #e3f2fd;
            padding: 4px 8px;
            border-radius: 5px;
            color: #1976d2;
        }

        .total-box {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #28a745;
            color: white;
        }

        .btn-secondary:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .poids-details {
            margin-top: 15px;
        }

        .poids-item {
            background: white;
            padding: 10px 15px;
            margin: 8px 0;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #667eea;
        }

        .poids-item .label {
            color: #495057;
            font-size: 14px;
        }

        .poids-item .details {
            text-align: right;
        }

        .poids-item .poids {
            font-weight: bold;
            color: #667eea;
            font-size: 16px;
        }

        .poids-item .prix {
            font-size: 13px;
            color: #6c757d;
        }

        @media print {
            body {
                background: white;
            }
            .btn-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Commande validée !</h1>
            <p>Votre commande a été enregistrée avec succès</p>
        </div>

        <div class="content">
            <div class="section">
                <h2><i class="fas fa-receipt"></i> Informations de commande</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Numéro de commande</div>
                        <div class="info-value"><?= htmlspecialchars($order['order_number']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date de commande</div>
                        <div class="info-value"><?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Statut</div>
                        <div class="info-value">
                            <span class="status-badge status-pending">En attente de collecte</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Code client</div>
                        <div class="info-value"><?= htmlspecialchars($order['customer_code']) ?></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2><i class="fas fa-user"></i> Informations du compte</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Titulaire du compte</div>
                        <div class="info-value"><?= htmlspecialchars($order['firstname']) ?> <?= htmlspecialchars($order['lastname']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= htmlspecialchars($order['email']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Téléphone du compte</div>
                        <div class="info-value"><?= htmlspecialchars($order['phone']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Points fidélité</div>
                        <div class="info-value"><i class="fas fa-star" style="color: #ffd700;"></i> <?= $order['points_at_order'] ?> points</div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2><i class="fas fa-user-tag"></i> Client de la commande</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nom du client</div>
                        <div class="info-value info-highlight"><?= htmlspecialchars($orderDetails['nomClientSaisi'] ?? 'Non spécifié') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Téléphone du client</div>
                        <div class="info-value info-highlight"><?= htmlspecialchars($orderDetails['telephoneSaisi'] ?? 'Non spécifié') ?></div>
                    </div>
                </div>
                <p style="font-size: 13px; color: #6c757d; margin-top: 10px;">
                    <i class="fas fa-info-circle"></i> Il s'agit des coordonnées du client pour qui cette commande est effectuée.
                </p>
            </div>

            <div class="section">
                <h2><i class="fas fa-truck"></i> Collecte et Livraison</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Date de collecte</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($order['pickup_date'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Adresse de collecte</div>
                        <div class="info-value"><?= htmlspecialchars($order['pickup_address']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date de livraison</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($order['delivery_date'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Adresse de livraison</div>
                        <div class="info-value"><?= htmlspecialchars($order['delivery_address']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Commune</div>
                        <div class="info-value"><?= htmlspecialchars($orderDetails['commune'] ?? 'Non spécifié') ?></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2><i class="fas fa-tshirt"></i> Détails du linge</h2>
                <?php if (isset($orderDetails['detailsPoidsComplets']) && count($orderDetails['detailsPoidsComplets']) > 0): ?>
                    <div class="poids-details">
                        <?php foreach ($orderDetails['detailsPoidsComplets'] as $item): ?>
                            <div class="poids-item">
                                <span class="label"><?= htmlspecialchars($item['label']) ?></span>
                                <div class="details">
                                    <div class="poids"><?= $item['poids'] ?> kg</div>
                                    <div class="prix"><?= number_format($item['prix'], 0, ',', ' ') ?> FCFA</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #6c757d; font-style: italic;">Aucun détail de poids disponible</p>
                <?php endif; ?>
            </div>

            <div class="section">
                <h2><i class="fas fa-dollar-sign"></i> Détails financiers</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Prix lavage</div>
                        <div class="info-value"><?= number_format($orderDetails['prixLavage'], 0, ',', ' ') ?> FCFA</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Prix collecte/livraison</div>
                        <div class="info-value"><?= number_format($orderDetails['prixCollecte'], 0, ',', ' ') ?> FCFA</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Moyen de paiement</div>
                        <div class="info-value"><?= htmlspecialchars($orderDetails['moyenPaiement']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">N° Transaction</div>
                        <div class="info-value"><?= htmlspecialchars($order['transaction_id'] ?? 'En attente') ?></div>
                    </div>
                </div>
                
                <div class="total-box" style="margin-top: 20px;">
                    <span>Total payé</span>
                    <span><?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA</span>
                </div>
            </div>

            <div class="btn-container">
                <button class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimer le récapitulatif
                </button>
                <a href="acceuil.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</body>
</html>