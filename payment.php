<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - Nelco Laverie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .payment-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .payment-header .order-number {
            color: #667eea;
            font-weight: bold;
            font-size: 18px;
        }

        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .summary-line:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 20px;
            color: #667eea;
            margin-top: 10px;
            padding-top: 20px;
            border-top: 2px solid #667eea;
        }

        .payment-methods {
            margin-bottom: 30px;
        }

        .payment-methods h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .payment-method {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .payment-method:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .payment-method.selected {
            border-color: #667eea;
            background: #e3f2fd;
        }

        .payment-method i {
            font-size: 24px;
            color: #667eea;
        }

        .payment-method .method-name {
            font-weight: 600;
            color: #333;
        }

        .phone-input {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }

        .phone-input.active {
            display: block;
        }

        .phone-input input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-container {
            display: flex;
            gap: 15px;
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
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .payment-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #ffc107;
        }

        .payment-info p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            border: 1px solid #f5c6cb;
        }

        .error-message.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1><i class="fas fa-credit-card"></i> Paiement</h1>
            <p class="order-number" id="orderNumber">Commande : Chargement...</p>
        </div>

        <div class="error-message" id="errorMessage"></div>

        <div class="order-summary">
            <h3>Récapitulatif de la commande</h3>
            <div class="summary-line">
                <span>Prix lavage</span>
                <span id="prixLavage">0 FCFA</span>
            </div>
            <div class="summary-line">
                <span>Prix collecte/livraison</span>
                <span id="prixCollecte">0 FCFA</span>
            </div>
            <div class="summary-line">
                <span>Total à payer</span>
                <span id="totalAmount">0 FCFA</span>
            </div>
        </div>

        <div class="payment-methods">
            <h3>Choisir un moyen de paiement</h3>
            
            <div class="payment-method" data-method="mtn" onclick="selectMethod('mtn')">
                <i class="fas fa-mobile-alt"></i>
                <div>
                    <div class="method-name">MTN Mobile Money</div>
                    <small>Paiement sécurisé via MTN Momo</small>
                </div>
            </div>
            <div class="phone-input" id="phone-mtn">
                <input type="tel" placeholder="+229 XX XX XX XX" id="phone-input-mtn">
            </div>

            <div class="payment-method" data-method="moov" onclick="selectMethod('moov')">
                <i class="fas fa-mobile-alt"></i>
                <div>
                    <div class="method-name">Moov Money</div>
                    <small>Paiement sécurisé via Moov</small>
                </div>
            </div>
            <div class="phone-input" id="phone-moov">
                <input type="tel" placeholder="+229 XX XX XX XX" id="phone-input-moov">
            </div>

            <div class="payment-method" data-method="celtiis" onclick="selectMethod('celtiis')">
                <i class="fas fa-mobile-alt"></i>
                <div>
                    <div class="method-name">Celtiis Money</div>
                    <small>Paiement sécurisé via Celtiis</small>
                </div>
            </div>
            <div class="phone-input" id="phone-celtiis">
                <input type="tel" placeholder="+229 XX XX XX XX" id="phone-input-celtiis">
            </div>

            <div class="payment-method" data-method="livraison" onclick="selectMethod('livraison')">
                <i class="fas fa-hand-holding-usd"></i>
                <div>
                    <div class="method-name">Paiement à la livraison</div>
                    <small>Payez en espèces lors de la livraison</small>
                </div>
            </div>
        </div>

        <div class="btn-container">
            <button class="btn btn-secondary" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i> Retour
            </button>
            <button class="btn btn-primary" id="btnPayer" onclick="processPaiement()">
                <i class="fas fa-check"></i> Confirmer le paiement
            </button>
        </div>

        <div class="payment-info">
            <p><i class="fas fa-info-circle"></i> Vous serez redirigé vers la page de récapitulatif après validation.</p>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h3>Traitement du paiement...</h3>
            <p>Veuillez patienter</p>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('orderId');
        const orderNumber = urlParams.get('orderNumber');
        const totalAmount = parseFloat(urlParams.get('total'));
        const prixLavage = parseFloat(urlParams.get('lavage'));
        const prixCollecte = parseFloat(urlParams.get('collecte'));

        document.getElementById('orderNumber').textContent = `Commande : ${orderNumber}`;
        document.getElementById('prixLavage').textContent = `${prixLavage.toLocaleString()} FCFA`;
        document.getElementById('prixCollecte').textContent = `${prixCollecte.toLocaleString()} FCFA`;
        document.getElementById('totalAmount').textContent = `${totalAmount.toLocaleString()} FCFA`;

        let selectedMethod = urlParams.get('method') || '';
        
        if (selectedMethod) {
            selectMethod(selectedMethod);
        }

        function selectMethod(method) {
            // Retirer la sélection des autres
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
            document.querySelectorAll('.phone-input').forEach(p => p.classList.remove('active'));
            
            // Sélectionner le nouveau
            document.querySelector(`[data-method="${method}"]`).classList.add('selected');
            
            // Afficher champ téléphone si mobile money
            if (['mtn', 'moov', 'celtiis'].includes(method)) {
                document.getElementById(`phone-${method}`).classList.add('active');
            }
            
            selectedMethod = method;
        }

        async function processPaiement() {
            if (!selectedMethod) {
                showError('Veuillez sélectionner un moyen de paiement');
                return;
            }

            // Valider le numéro de téléphone pour mobile money
            let phoneNumber = '';
            if (['mtn', 'moov', 'celtiis'].includes(selectedMethod)) {
                phoneNumber = document.getElementById(`phone-input-${selectedMethod}`).value.trim();
                if (!phoneNumber) {
                    showError('Veuillez entrer votre numéro de téléphone');
                    return;
                }
            }

            document.getElementById('loadingOverlay').classList.add('active');
            document.getElementById('btnPayer').disabled = true;

            try {
                const response = await fetch('payment_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        method: selectedMethod,
                        amount: totalAmount,
                        phoneNumber: phoneNumber
                    })
                });

                const data = await response.json();
                
                document.getElementById('loadingOverlay').classList.remove('active');
                document.getElementById('btnPayer').disabled = false;

                if (data.success) {
                    // SUCCÈS - Redirection
                    window.location.href = data.redirect || `order_summary.php?orderId=${orderId}`;
                } else {
                    // ÉCHEC - Afficher message
                    showError(data.message);
                    
                    // Si fallback activé, proposer autres options
                    if (data.fallback) {
                        // L'utilisateur peut sélectionner un autre moyen
                    }
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('loadingOverlay').classList.remove('active');
                document.getElementById('btnPayer').disabled = false;
                showError('Une erreur est survenue. Veuillez réessayer.');
            }
        }

        function showError(message) {
            const errorBox = document.getElementById('errorMessage');
            errorBox.textContent = message;
            errorBox.classList.add('show');
            
            setTimeout(() => {
                errorBox.classList.remove('show');
            }, 5000);
        }
    </script>
</body>
</html>