<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Commande - Nelco Laverie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="commandes.css">
    <link rel="stylesheet" href="progressive-form.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🧼 Formulaire de Commande - Nelco Laverie</h1>
            <p>Système basé sur le nombre de linges</p>
        </header>

        <!-- BARRE DE PROGRESSION -->
        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar"></div>
            <div class="progress-text" id="progressText">Étape 1/5</div>
        </div>

        <!-- PROTOCOLE DE TRI -->
        <section class="protocole">
            <button type="button" class="protocole-toggle" id="protocoleToggle">
                <span>📋 Guide de classification des linges</span>
                <span class="toggle-icon">▼</span>
            </button>
            
            <div class="protocole-content" id="protocoleContent" style="display: none;">
                <div class="protocole-section">
                    <h3>📋 GROUPES DE LINGES</h3>
                    
                    <h4>Linges Ordinaires</h4>
                    <ul>
                        <li><strong>O1</strong> - Très petits linges (sous-vêtements, chaussettes)</li>
                        <li><strong>O2</strong> - Hauts légers (t-shirts, chemisettes)</li>
                        <li><strong>O3</strong> - Bas légers (shorts, jupes légères)</li>
                        <li><strong>O4</strong> - Tenues complètes (robes, chemises)</li>
                        <li><strong>O5</strong> - Ordinaires épais (jeans, pantalons épais)</li>
                    </ul>

                    <h4>Linges Volumineux</h4>
                    <ul>
                        <li><strong>V1</strong> - Volumineux légers (rideaux fins, nappes)</li>
                        <li><strong>V2</strong> - Literie légère (draps simples)</li>
                        <li><strong>V3</strong> - Literie standard (draps doubles, taies)</li>
                        <li><strong>V4</strong> - Volumineux lourds (serviettes de bain, couvertures)</li>
                        <li><strong>V5</strong> - Très volumineux (édredons, couettes)</li>
                    </ul>
                </div>

                <div class="protocole-section">
                    <h3>🌡️ TEMPÉRATURES DE LAVAGE</h3>
                    <ul>
                        <li><strong>FROID (0-20°C)</strong> - Couleurs foncées, délicats, jeans</li>
                        <li><strong>TIÈDE (30-40°C)</strong> - Couleurs normales, serviettes, sportswear</li>
                        <li><strong>CHAUD (50-60°C)</strong> - Blanc, très sale, hygiène</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- FORMULAIRE -->
        <form id="commandeForm">
            
            <!-- ÉTAPE 1: INFORMATIONS CLIENT -->
            <section class="form-section step-section active" data-step="1">
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

            <!-- ÉTAPE 2: ADRESSES ET DATES -->
            <section class="form-section step-section" data-step="2">
                <h2>📍 Adresses et Dates</h2>
                
                <div class="form-group">
                    <label for="adresseCollecte">Adresse de collecte <span class="required">*</span></label>
                    <input type="text" id="adresseCollecte" name="adresseCollecte" required>
                </div>

                <!-- COMMUNE COLLECTE DÉSACTIVÉE -->
                <!-- <div class="form-group">
                    <label for="communeCollecte">Commune de collecte</label>
                    <select id="communeCollecte" name="communeCollecte">
                        <option value="godomey">Godomey (500 FCFA)</option>
                        <option value="cotonou">Cotonou (1000 FCFA)</option>
                        <option value="calavi">Calavi (800 FCFA)</option>
                        <option value="autres">Autres zones (1500 FCFA)</option>
                    </select>
                </div> -->

                <div class="form-group">
                    <label for="dateCollecte">Date de collecte <span class="required">*</span></label>
                    <input type="date" id="dateCollecte" name="dateCollecte" required>
                </div>

                <div class="form-group">
                    <label for="adresseLivraison">Adresse de livraison <span class="required">*</span></label>
                    <input type="text" id="adresseLivraison" name="adresseLivraison" required>
                </div>

                <!-- COMMUNE LIVRAISON DÉSACTIVÉE -->
                <!-- <div class="form-group">
                    <label for="communeLivraison">Commune de livraison</label>
                    <select id="communeLivraison" name="communeLivraison">
                        <option value="godomey">Godomey (500 FCFA)</option>
                        <option value="cotonou">Cotonou (1000 FCFA)</option>
                        <option value="calavi">Calavi (800 FCFA)</option>
                        <option value="autres">Autres zones (1500 FCFA)</option>
                    </select>
                </div> -->

                <div class="form-group">
                    <label for="dateLivraison">Date de livraison <span class="required">*</span></label>
                    <input type="date" id="dateLivraison" name="dateLivraison" required>
                </div>
            </section>

            <!-- ÉTAPE 3: SÉLECTION DES LINGES -->
            <section class="form-section step-section" data-step="3">
                <h2>👕 Sélection des Linges</h2>
                <p class="instruction">✨ Sélectionnez le type de linge, puis renseignez les quantités par groupe, couleur et température</p>

                <!-- BOUTONS TYPE DE LINGE -->
                <div class="linge-type-selector">
                    <button type="button" class="linge-type-card" id="btnOrdinaire" data-type="ordinaire">
                        <div class="card-icon">👕</div>
                        <div class="card-title">Linge Ordinaire</div>
                        <div class="card-desc">T-shirts, sous-vêtements, chemises, pantalons</div>
                    </button>

                    <button type="button" class="linge-type-card" id="btnVolumineux" data-type="volumineux">
                        <div class="card-icon">🛏️</div>
                        <div class="card-title">Linge Volumineux</div>
                        <div class="card-desc">Draps, couvertures, serviettes, édredons</div>
                    </button>
                </div>

                <!-- SECTION ORDINAIRE -->
                <div class="linge-category-section" id="ordinaireSection" style="display: none;">
                    <h3 class="category-title">👕 Linge Ordinaire - Groupes et Températures</h3>
                    
                    <!-- Groupe O1 -->
                    <div class="groupe-container">
                        <button type="button" class="groupe-toggle" data-groupe="o1">
                            <span>O1 - Très petits linges</span>
                            <span class="toggle-icon">▼</span>
                        </button>
                        <div class="groupe-content" id="o1Content" style="display: none;">
                            <div class="color-selector">
                                <button type="button" class="color-card" data-color="blanc" data-groupe="o1">⚪ Blanc</button>
                                <button type="button" class="color-card" data-color="couleur" data-groupe="o1">🔵 Couleur</button>
                            </div>
                            <div class="poids-group" id="o1_blanc" style="display: none;">
                                <h4>⚪ Blanc - Températures</h4>
                                <div class="temperature-grid">
                                    <div class="temp-item">
                                        <label>🔥 Chaud</label>
                                        <input type="number" name="o1_blanc_chaud" min="0" step="1" placeholder="0 unités">
                                    </div>
                                    <div class="temp-item">
                                        <label>🌡️ Tiède</label>
                                        <input type="number" name="o1_blanc_tiede" min="0" step="1" placeholder="0 unités">
                                    </div>
                                    <div class="temp-item">
                                        <label>❄️ Froid</label>
                                        <input type="number" name="o1_blanc_froid" min="0" step="1" placeholder="0 unités">
                                    </div>
                                </div>
                            </div>
                            <div class="poids-group" id="o1_couleur" style="display: none;">
                                <h4>🔵 Couleur - Températures</h4>
                                <div class="temperature-grid">
                                    <div class="temp-item">
                                        <label>🔥 Chaud</label>
                                        <input type="number" name="o1_couleur_chaud" min="0" step="1" placeholder="0 unités">
                                    </div>
                                    <div class="temp-item">
                                        <label>🌡️ Tiède</label>
                                        <input type="number" name="o1_couleur_tiede" min="0" step="1" placeholder="0 unités">
                                    </div>
                                    <div class="temp-item">
                                        <label>❄️ Froid</label>
                                        <input type="number" name="o1_couleur_froid" min="0" step="1" placeholder="0 unités">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Groupes O2 à O5 suivent le même modèle -->
                    <!-- O2 -->
                    <div class="groupe-container">
                        <button type="button" class="groupe-toggle" data-groupe="o2">
                            <span>O2 - Hauts légers</span>
                            <span class="toggle-icon">▼</span>
                        </button>
                        <div class="groupe-content" id="o2Content" style="display: none;">
                            <div class="color-selector">
                                <button type="button" class="color-card" data-color="blanc" data-groupe="o2">⚪ Blanc</button>
                                <button type="button" class="color-card" data-color="couleur" data-groupe="o2">🔵 Couleur</button>
                            </div>
                            <div class="poids-group" id="o2_blanc" style="display: none;">
                                <h4>⚪ Blanc - Températures</h4>
                                <div class="temperature-grid">
                                    <div class="temp-item"><label>🔥 Chaud</label><input type="number" name="o2_blanc_chaud" min="0" step="1" placeholder="0 unités"></div>
                                    <div class="temp-item"><label>🌡️ Tiède</label><input type="number" name="o2_blanc_tiede" min="0" step="1" placeholder="0 unités"></div>
                                    <div class="temp-item"><label>❄️ Froid</label><input type="number" name="o2_blanc_froid" min="0" step="1" placeholder="0 unités"></div>
                                </div>
                            </div>
                            <div class="poids-group" id="o2_couleur" style="display: none;">
                                <h4>🔵 Couleur - Températures</h4>
                                <div class="temperature-grid">
                                    <div class="temp-item"><label>🔥 Chaud</label><input type="number" name="o2_couleur_chaud" min="0" step="1" placeholder="0 unités"></div>
                                    <div class="temp-item"><label>🌡️ Tiède</label><input type="number" name="o2_couleur_tiede" min="0" step="1" placeholder="0 unités"></div>
                                    <div class="temp-item"><label>❄️ Froid</label><input type="number" name="o2_couleur_froid" min="0" step="1" placeholder="0 unités"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- O3, O4, O5 suivent le même pattern -->
                    <!-- ... (répéter pour O3, O4, O5) ... -->
                </div>

                <!-- SECTION VOLUMINEUX (même structure avec V1-V5) -->
                <div class="linge-category-section" id="volumineuxSection" style="display: none;">
                    <h3 class="category-title">🛏️ Linge Volumineux - Groupes et Températures</h3>
                    <!-- V1 à V5 avec même structure que O1-O5 -->
                </div>

                <p class="help-text">💡 Astuce : Laissez vide les champs non utilisés</p>
            </section>

            <!-- ÉTAPE 4: MOYEN DE PAIEMENT -->
            <section class="form-section step-section" data-step="4">
                <h2>💳 Moyen de Paiement</h2>
                
                <div class="form-group">
                    <label>Choisissez votre moyen de paiement <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="paiement" value="livraison" required>
                            <span>Paiement à la livraison</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="paiement" value="mtn">
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
                    </div>
                </div>
            </section>

            <!-- ÉTAPE 5: RÉCAPITULATIF -->
            <section class="form-section step-section recap" data-step="5">
                <h2>💰 Récapitulatif des Prix</h2>
                
                <div class="prix-ligne">
                    <label>Prix lavage :</label>
                    <span><span id="prixLavageOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne" id="reductionFidelite" style="display: none;">
                    <label>🎁 Réduction fidélité :</label>
                    <span>-<span id="reductionOutput">0</span> FCFA</span>
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

                <!-- PRIX COLLECTE/LIVRAISON DÉSACTIVÉ -->
                <!-- <div class="prix-ligne">
                    <label>Prix collecte/livraison :</label>
                    <span><span id="prixCollecteOutput">0</span> FCFA</span>
                </div> -->

                <div class="prix-ligne total">
                    <label><strong>Total à payer :</strong></label>
                    <span><strong><span id="totalPayerOutput">0</span> FCFA</strong></span>
                </div>

                <div class="info-lavages">
                    <i class="fas fa-info-circle"></i>
                    <span id="infoLavagesText">Cette commande représente <strong id="lavCount">0</strong> lavage(s)</span>
                </div>
            </section>

            <!-- NAVIGATION BUTTONS -->
            <div class="form-navigation">
                <button type="button" class="btn-nav btn-prev" id="btnPrev" style="display: none;">
                    ← Précédent
                </button>
                <button type="button" class="btn-nav btn-next" id="btnNext">
                    Suivant →
                </button>
                <button type="submit" class="btn-principal" id="btnSubmit" style="display: none;">
                    Valider la commande
                </button>
            </div>
        </form>
    </div>

    <script src="commandes.js"></script>
    <script src="progressive-form.js"></script>
</body>
</html>