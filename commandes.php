<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Commande de Lavage</title>
    <link rel="stylesheet" href="commandes.css">
    <link rel="stylesheet" href="responsive-styles.css">
    <link rel="stylesheet" href="commandes-responsive.css">
    <script src="commandes.js"></script>
    <script>
        // RESTRICTION STRICTE DES DATES
// À ajouter dans commandes.js ou dans un <script> avant </body>

(function() {
    'use strict';
    
    // Fonction pour obtenir la date du jour au format YYYY-MM-DD
    function getDateAujourdhui() {
        const aujourd = new Date();
        const annee = aujourd.getFullYear();
        const mois = String(aujourd.getMonth() + 1).padStart(2, '0');
        const jour = String(aujourd.getDate()).padStart(2, '0');
        return `${annee}-${mois}-${jour}`;
    }
    
    // Fonction pour désactiver les dates passées (force la validation)
    function appliquerRestrictionsDates() {
        const dateCollecte = document.getElementById('dateCollecte');
        const dateLivraison = document.getElementById('dateLivraison');
        const dateMin = getDateAujourdhui();
        
        if (!dateCollecte || !dateLivraison) {
            console.error('Champs de date non trouvés');
            return;
        }
        
        // Définir l'attribut min (bloque la sélection dans le calendrier)
        dateCollecte.setAttribute('min', dateMin);
        dateLivraison.setAttribute('min', dateMin);
        
        // Événement : validation de la date de collecte
        dateCollecte.addEventListener('input', function() {
            const dateSelectionnee = this.value;
            
            if (dateSelectionnee && dateSelectionnee < dateMin) {
                this.value = '';
                this.setCustomValidity('Vous ne pouvez pas sélectionner une date passée.');
                alert('❌ Date de collecte invalide : vous ne pouvez pas choisir une date passée.');
            } else {
                this.setCustomValidity('');
                
                // Mettre à jour la date minimale de livraison
                if (dateSelectionnee) {
                    dateLivraison.setAttribute('min', dateSelectionnee);
                    
                    // Vérifier si la date de livraison est toujours valide
                    if (dateLivraison.value && dateLivraison.value < dateSelectionnee) {
                        dateLivraison.value = '';
                        alert('⚠️ La date de livraison a été réinitialisée car elle était antérieure à la date de collecte.');
                    }
                }
            }
        });
        
        // Événement : validation de la date de livraison
        dateLivraison.addEventListener('input', function() {
            const dateSelectionnee = this.value;
            const dateCollecteVal = dateCollecte.value;
            
            // Vérifier que la date n'est pas passée
            if (dateSelectionnee && dateSelectionnee < dateMin) {
                this.value = '';
                this.setCustomValidity('Vous ne pouvez pas sélectionner une date passée.');
                alert('❌ Date de livraison invalide : vous ne pouvez pas choisir une date passée.');
                return;
            }
            
            // Vérifier que la date de livraison n'est pas avant la collecte
            if (dateCollecteVal && dateSelectionnee && dateSelectionnee < dateCollecteVal) {
                this.value = '';
                this.setCustomValidity('La date de livraison doit être égale ou postérieure à la date de collecte.');
                alert('❌ La date de livraison ne peut pas être antérieure à la date de collecte.');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Événement change pour double validation
        dateCollecte.addEventListener('change', function() {
            this.dispatchEvent(new Event('input'));
        });
        
        dateLivraison.addEventListener('change', function() {
            this.dispatchEvent(new Event('input'));
        });
    }
    
    // Validation AVANT la soumission du formulaire
    function validerFormulaire(event) {
        const dateCollecte = document.getElementById('dateCollecte');
        const dateLivraison = document.getElementById('dateLivraison');
        const dateMin = getDateAujourdhui();
        
        let erreurs = [];
        
        // Vérifier date de collecte
        if (!dateCollecte.value) {
            erreurs.push('- La date de collecte est obligatoire.');
        } else if (dateCollecte.value < dateMin) {
            erreurs.push('- La date de collecte ne peut pas être une date passée.');
        }
        
        // Vérifier date de livraison
        if (!dateLivraison.value) {
            erreurs.push('- La date de livraison est obligatoire.');
        } else if (dateLivraison.value < dateMin) {
            erreurs.push('- La date de livraison ne peut pas être une date passée.');
        } else if (dateCollecte.value && dateLivraison.value < dateCollecte.value) {
            erreurs.push('- La date de livraison ne peut pas être antérieure à la date de collecte.');
        }
        
        // Si des erreurs existent, empêcher la soumission
        if (erreurs.length > 0) {
            event.preventDefault();
            alert('🚫 ERREUR DE VALIDATION :\n\n' + erreurs.join('\n'));
            return false;
        }
        
        return true;
    }
    
    // Initialisation au chargement de la page
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            appliquerRestrictionsDates();
            
            // Ajouter la validation au formulaire
            const formulaire = document.getElementById('commandeForm');
            if (formulaire) {
                formulaire.addEventListener('submit', validerFormulaire);
            }
        });
    } else {
        appliquerRestrictionsDates();
        
        // Ajouter la validation au formulaire
        const formulaire = document.getElementById('commandeForm');
        if (formulaire) {
            formulaire.addEventListener('submit', validerFormulaire);
        }
    }
})();
    </script>
</body>
</head>
<body>
    <div class="container">
        <header>
            <h1>🧼 Formulaire de Commande de Lavage chez Nelco Laverie</h1>
        </header>

        <!-- PROTOCOLE DE TRI -->
        <section class="protocole">
            <h2>🧼 PROTOCOLE</h2>
            
            <div class="protocole-section">
                <h3>📋 RÉSUMÉ DES TEMPÉRATURES</h3>
                <ul>
                    <li><strong>FROID</strong> = Couleurs foncées, délicat, jeans, économie</li>
                    <li><strong>TIÈDE</strong> = Couleurs normales, serviettes, sportwear</li>
                    <li><strong>CHAUD</strong> = Blanc, très sale, hygiène (linge de maison)</li>
                </ul>
            </div>

            <div class="protocole-section">
                <h3>🔄 PROTOCOLE DE TRI ÉTAPE PAR ÉTAPE</h3>
                
                <h4>ÉTAPE 1 : SÉPARATION PAR COULEUR</h4>
                <ul>
                    <li>Tas A → LINGE BLANC</li>
                    <li>Tas B → LINGE COULEUR CLAIRE</li>
                    <li>Tas C → LINGE COULEUR FONCÉE</li>
                </ul>

                <h4>ÉTAPE 2 : POUR CHAQUE TAS (A, B, C) - SÉPARATION PAR VOLUME</h4>
                <div class="sous-section">
                    <p><strong>Sous-tas 1 → LINGE VOLUMINEUX</strong></p>
                    <ul>
                        <li>Draps et housses de couette</li>
                        <li>Serviettes de bain</li>
                        <li>Couvertures</li>
                        <li>Sweats et pulls épais</li>
                    </ul>
                </div>

                <div class="sous-section">
                    <p><strong>Sous-tas 2 → LINGE ORDINAIRE</strong></p>
                    <ul>
                        <li>T-shirts et hauts</li>
                        <li>Sous-vêtements</li>
                        <li>Chaussettes</li>
                        <li>Leggings et shorts</li>
                        <li>Chemises</li>
                    </ul>
                </div>

                <h4>ÉTAPE 3 : POUR CHAQUE SOUS-TAS (1, 2) - SÉPARATION PAR TEMPÉRATURE</h4>
                <ul>
                    <li>Groupe FINAL 1 → LAVAGE CHAUD (50-60°C)</li>
                    <li>Groupe FINAL 2 → LAVAGE TIÈDE (30-40°C)</li>
                    <li>Groupe FINAL 3 → LAVAGE FROID (0-20°C)</li>
                </ul>
            </div>
        </section>

        <!-- FORMULAIRE -->
        <form id="commandeForm">
            
            <!-- INFORMATIONS CLIENT -->
            <section class="form-section">
                <h2>👤 Informations Client</h2>
                
                <div class="form-group">
                    <label for="nomClient">Nom complet <span class="required">*</span></label>
                    <input type="text" id="nomClient" name="nomClient" required>
                </div>

                <div class="form-group">
                    <label for="telephone">Numéro de téléphone <span class="required">*</span></label>
                    <input type="tel" id="telephone" name="telephone" required>
                </div>
            </section>

            <!-- ADRESSES -->
            <section class="form-section">
                <h2>📍 Adresses</h2>
                
                <div class="form-group">
                    <label for="adresseCollecte">Adresse de collecte <span class="required">*</span></label>
                    <input type="text" id="adresseCollecte" name="adresseCollecte" required>
                </div>

                <div class="form-group">
                    <label for="communeCollecte">Commune de collecte <span class="required">*</span></label>
                    <select id="communeCollecte" name="communeCollecte" required>
                        <option value="">-- Sélectionnez --</option>
                        <option value="godomey">Godomey (500 FCFA)</option>
                        <option value="cotonou">Cotonou (1000 FCFA)</option>
                        <option value="calavi">Calavi (800 FCFA)</option>
                        <option value="autres">Autres zones (1500 FCFA)</option>
                    </select>
                </div>

                <div class="form-group">
                  <label for="dateCollecte">Date de collecte <span class="required">*</span></label>
                  <input type="date" id="dateCollecte" name="dateCollecte" required>
                </div>

                <div class="form-group">
                    <label for="adresseLivraison">Adresse de livraison <span class="required">*</span></label>
                    <input type="text" id="adresseLivraison" name="adresseLivraison" required>
                </div>

                <div class="form-group">
                    <label for="communeLivraison">Commune de livraison <span class="required">*</span></label>
                    <select id="communeLivraison" name="communeLivraison" required>
                        <option value="">-- Sélectionnez --</option>
                        <option value="godomey">Godomey (500 FCFA)</option>
                        <option value="cotonou">Cotonou (1000 FCFA)</option>
                        <option value="calavi">Calavi (800 FCFA)</option>
                        <option value="autres">Autres zones (1500 FCFA)</option>
                    </select>
                </div>

                <div class="form-group">
                  <label for="dateLivraison">Date de livraison <span class="required">*</span></label>
                  <input type="date" id="dateLivraison" name="dateLivraison" required>
                </div>
            </section>

            <!-- POIDS ET TEMPÉRATURES -->
            <section class="form-section">
                <h2>⚖️ Poids et Températures de Lavage</h2>
                <p class="instruction">Indiquez le poids (en kg) de chaque sous-tas selon le protocole ci-dessus. Les champs vides seront considérés comme 0 kg.</p>

                <!-- TAS A : BLANC -->
                <div class="tas-group">
                    <h3>Tas A - LINGE BLANC</h3>
                    
                    <div class="sous-tas-group">
                        <h4>Sous-tas A1 - Volumineux</h4>
                        <div class="groupe-final">
                            <label>Groupe A1-1 (Chaud)</label>
                            <input type="number" name="a1_chaud" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe A1-2 (Tiède)</label>
                            <input type="number" name="a1_tiede" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe A1-3 (Froid)</label>
                            <input type="number" name="a1_froid" min="0" step="0.1" placeholder="kg">
                        </div>
                    </div>

                    <div class="sous-tas-group">
                        <h4>Sous-tas A2 - Ordinaire</h4>
                        <div class="groupe-final">
                            <label>Groupe A2-1 (Chaud)</label>
                            <input type="number" name="a2_chaud" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe A2-2 (Tiède)</label>
                            <input type="number" name="a2_tiede" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe A2-3 (Froid)</label>
                            <input type="number" name="a2_froid" min="0" step="0.1" placeholder="kg">
                        </div>
                    </div>
                </div>

                <!-- TAS B : COULEUR CLAIRE -->
                <div class="tas-group">
                    <h3>Tas B - LINGE COULEUR CLAIRE</h3>
                    
                    <div class="sous-tas-group">
                        <h4>Sous-tas B1 - Volumineux</h4>
                        <div class="groupe-final">
                            <label>Groupe B1-1 (Chaud)</label>
                            <input type="number" name="b1_chaud" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe B1-2 (Tiède)</label>
                            <input type="number" name="b1_tiede" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe B1-3 (Froid)</label>
                            <input type="number" name="b1_froid" min="0" step="0.1" placeholder="kg">
                        </div>
                    </div>

                    <div class="sous-tas-group">
                        <h4>Sous-tas B2 - Ordinaire</h4>
                        <div class="groupe-final">
                            <label>Groupe B2-1 (Chaud)</label>
                            <input type="number" name="b2_chaud" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe B2-2 (Tiède)</label>
                            <input type="number" name="b2_tiede" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe B2-3 (Froid)</label>
                            <input type="number" name="b2_froid" min="0" step="0.1" placeholder="kg">
                        </div>
                    </div>
                </div>

                <!-- TAS C : COULEUR FONCÉE -->
                <div class="tas-group">
                    <h3>Tas C - LINGE COULEUR FONCÉE</h3>
                    
                    <div class="sous-tas-group">
                        <h4>Sous-tas C1 - Volumineux</h4>
                        <div class="groupe-final">
                            <label>Groupe C1-1 (Chaud)</label>
                            <input type="number" name="c1_chaud" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe C1-2 (Tiède)</label>
                            <input type="number" name="c1_tiede" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe C1-3 (Froid)</label>
                            <input type="number" name="c1_froid" min="0" step="0.1" placeholder="kg">
                        </div>
                    </div>

                    <div class="sous-tas-group">
                        <h4>Sous-tas C2 - Ordinaire</h4>
                        <div class="groupe-final">
                            <label>Groupe C2-1 (Chaud)</label>
                            <input type="number" name="c2_chaud" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe C2-2 (Tiède)</label>
                            <input type="number" name="c2_tiede" min="0" step="0.1" placeholder="kg">
                        </div>
                        <div class="groupe-final">
                            <label>Groupe C2-3 (Froid)</label>
                            <input type="number" name="c2_froid" min="0" step="0.1" placeholder="kg">
                        </div>
                    </div>
                </div>
            </section>

            <!-- MOYEN DE PAIEMENT -->
            <section class="form-section">
                <h2>💳 Moyen de Paiement</h2>
                
                <div class="form-group">
                    <label>Choisissez votre moyen de paiement <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="paiement" value="mtn" required>
                            <span>MTN Momo</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="paiement" value="moov">
                            <span>Moov Money</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="paiement" value="celtiis">
                            <span>Celtiis Money</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="paiement" value="livraison">
                            <span>Paiement à la livraison</span>
                        </label>
                    </div>
                </div>
            </section>

            <!-- RÉCAPITULATIF -->
            <section class="form-section recap">
                <h2>💰 Récapitulatif des Prix</h2>
                
                <div class="prix-ligne">
                    <label>Prix lavage :</label>
                    <span><span id="prixLavageOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne" id="reductionFidelite" style="display: none;">
                    <label>🎁 Réduction fidélité :</label>
                    <span><span>0</span> FCFA</span>
                </div>

                <div class="prix-ligne">
                    <label>Prix séchage :</label>
                    <span><span id="prixSechageOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne">
                    <label>Prix pliage :</label>
                    <span><span id="prixPliageOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne">
                    <label>Prix repassage :</label>
                    <span><span id="prixRepassageOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne">
                    <label>Prix collecte/livraison :</label>
                    <span><span id="prixCollecteOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne total">
                    <label><strong>Total à payer :</strong></label>
                    <span><strong><span id="totalPayerOutput">0</span> FCFA</strong></span>
                </div>
            </section>

            <!-- BOUTON VALIDATION -->
            <div class="form-actions">
                <button type="submit" class="btn-principal">Valider la commande</button>
            </div>
        </form>
    </div>
    <script src="commandes.js"></script>
</body>
</html>