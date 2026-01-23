<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande - Nelco Laverie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="commandes_v2.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🧼 Nouvelle Commande</h1>
            <p>Système basé sur le nombre de linges</p>
        </header>

        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
            <div class="progress-text" id="progressText">Étape 1/5</div>
        </div>

        <!-- Guide Toggle -->
        <button class="guide-toggle" id="guideToggle">
            📋 Guide de classification <span class="icon">▼</span>
        </button>
        <div class="guide-content" id="guideContent" style="display:none;">
            <div class="guide-section">
                <h3>Linge Ordinaire</h3>
                <ul>
                    <li><strong>O1:</strong> Très petits (sous-vêtements, chaussettes)</li>
                    <li><strong>O2:</strong> Hauts légers (t-shirts, chemisettes)</li>
                    <li><strong>O3:</strong> Bas légers (shorts, jupes)</li>
                    <li><strong>O4:</strong> Tenues complètes (robes, chemises)</li>
                    <li><strong>O5:</strong> Épais (jeans, pantalons épais)</li>
                </ul>
            </div>
            <div class="guide-section">
                <h3>Linge Volumineux</h3>
                <ul>
                    <li><strong>V1:</strong> Légers (rideaux fins, nappes)</li>
                    <li><strong>V2:</strong> Literie légère (draps simples)</li>
                    <li><strong>V3:</strong> Literie standard (draps doubles)</li>
                    <li><strong>V4:</strong> Lourds (serviettes, couvertures)</li>
                    <li><strong>V5:</strong> Très volumineux (édredons, couettes)</li>
                </ul>
            </div>
            <div class="guide-section">
                <h3>Températures</h3>
                <ul>
                    <li><strong>FROID:</strong> Couleurs foncées, délicats</li>
                    <li><strong>TIÈDE:</strong> Couleurs normales, sportswear</li>
                    <li><strong>CHAUD:</strong> Blanc, très sale</li>
                </ul>
            </div>
        </div>

        <form id="commandeForm">
            <!-- ÉTAPE 1: Info Client -->
            <div class="step-section active" data-step="1">
                <h2>👤 Informations Client</h2>
                <div class="form-group">
                    <label>Nom complet <span class="req">*</span></label>
                    <input type="text" name="nomClient" required>
                </div>
                <div class="form-group">
                    <label>Téléphone <span class="req">*</span></label>
                    <input type="tel" name="telephone" required>
                </div>
            </div>

            <!-- ÉTAPE 2: Adresses -->
            <div class="step-section" data-step="2">
                <h2>📍 Adresses et Dates</h2>
                <div class="form-group">
                    <label>Adresse de collecte <span class="req">*</span></label>
                    <input type="text" name="adresseCollecte" required>
                </div>
                <div class="form-group">
                    <label>Date de collecte <span class="req">*</span></label>
                    <input type="date" name="dateCollecte" required>
                </div>
                <div class="form-group">
                    <label>Adresse de livraison <span class="req">*</span></label>
                    <input type="text" name="adresseLivraison" required>
                </div>
                <div class="form-group">
                    <label>Date de livraison <span class="req">*</span></label>
                    <input type="date" name="dateLivraison" required>
                </div>
            </div>

            <!-- ÉTAPE 3: Sélection Linges -->
            <div class="step-section" data-step="3">
                <h2>👕 Sélection des Linges</h2>
                <p class="info">Sélectionnez le type, puis renseignez les quantités</p>
                
                <!-- Sélecteur Type -->
                <div class="type-selector">
                    <button type="button" class="type-btn" data-type="ordinaire">
                        <i class="fas fa-tshirt"></i>
                        <span>Linge Ordinaire</span>
                    </button>
                    <button type="button" class="type-btn" data-type="volumineux">
                        <i class="fas fa-bed"></i>
                        <span>Linge Volumineux</span>
                    </button>
                </div>

                <!-- Container pour les linges -->
                <div id="lingeContainer"></div>
            </div>

            <!-- ÉTAPE 4: Paiement -->
            <div class="step-section" data-step="4">
                <h2>💳 Moyen de Paiement</h2>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="paiement" value="livraison" required>
                        <span>💵 Paiement à la livraison</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="paiement" value="mtn">
                        <span>📱 MTN Mobile Money</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="paiement" value="moov">
                        <span>📱 Moov Money</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="paiement" value="celtiis">
                        <span>📱 Celtiis Money</span>
                    </label>
                </div>
            </div>

            <!-- ÉTAPE 5: Récapitulatif -->
            <div class="step-section recap" data-step="5">
                <h2>💰 Récapitulatif</h2>
                <div class="summary-line">
                    <span>Prix lavage:</span>
                    <span><span id="prixLavage">0</span> FCFA</span>
                </div>
                <div class="summary-line discount" id="reductionLine" style="display:none;">
                    <span>🎁 Réduction fidélité:</span>
                    <span>-<span id="reduction">0</span> FCFA</span>
                </div>
                <div class="summary-line">
                    <span>Prix séchage:</span>
                    <span><span id="prixSechage">0</span> FCFA</span>
                </div>
                <div class="summary-line">
                    <span>Prix pliage:</span>
                    <span><span id="prixPliage">0</span> FCFA</span>
                </div>
                <div class="summary-line">
                    <span>Prix repassage:</span>
                    <span><span id="prixRepassage">0</span> FCFA</span>
                </div>
                <div class="summary-line total">
                    <span><strong>Total:</strong></span>
                    <span><strong><span id="total">0</span> FCFA</strong></span>
                </div>
                <div class="info-lavages">
                    <i class="fas fa-info-circle"></i>
                    <span>Cette commande = <strong id="lavCount">0</strong> lavage(s)</span>
                </div>
            </div>

            <!-- Navigation -->
            <div class="nav-buttons">
                <button type="button" class="btn-nav btn-prev" id="btnPrev" style="display:none;">
                    ← Précédent
                </button>
                <button type="button" class="btn-nav btn-next" id="btnNext">
                    Suivant →
                </button>
                <button type="submit" class="btn-submit" id="btnSubmit" style="display:none;">
                    Valider la commande
                </button>
            </div>
        </form>
    </div>

    <script src="commandes_v2.js"></script>
</body>
</html>