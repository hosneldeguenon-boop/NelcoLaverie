// ============================================
// CONFIGURATION ET VARIABLES GLOBALES
// ============================================
let CONFIG = null;
let userNombreLavage = 0;
let currentStep = 1;
const totalSteps = 5;

const DEBUG_MODE = true;

function debugLog(message, data = null) {
    if (DEBUG_MODE) {
        console.log(`🔍 [DEBUG] ${message}`, data || '');
    }
}

// ============================================
// INITIALISATION
// ============================================
document.addEventListener('DOMContentLoaded', async function() {
    debugLog('🚀 Initialisation du système');
    
    await loadConfig();
    await loadUserPoints();
    
    if (!CONFIG) {
        alert('❌ ERREUR CRITIQUE : Configuration non chargée. Impossible de continuer.');
        return;
    }
    
    initializeForm();
    setupEventListeners();
    
    debugLog('✅ Système initialisé avec succès');
});

// ============================================
// CHARGEMENT CONFIGURATION
// ============================================
async function loadConfig() {
    try {
        debugLog('📥 Chargement laverie_config.json...');
        
        const response = await fetch('laverie_config.json');
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        CONFIG = await response.json();
        
        debugLog('✅ Configuration chargée', CONFIG);
        
        // VALIDATION STRUCTURE JSON
        if (!CONFIG.groupes_linges) {
            throw new Error('❌ groupes_linges manquant');
        }
        
        if (!CONFIG.groupes_linges.ordinaires) {
            throw new Error('❌ groupes_linges.ordinaires manquant');
        }
        
        if (!CONFIG.groupes_linges.volumineux) {
            throw new Error('❌ groupes_linges.volumineux manquant');
        }
        
        // Vérifier que tous les groupes existent
        const groupesO = ['O1', 'O2', 'O3', 'O4', 'O5'];
        const groupesV = ['V1', 'V2', 'V3', 'V4', 'V5'];
        
        groupesO.forEach(g => {
            if (!CONFIG.groupes_linges.ordinaires[g]) {
                throw new Error(`❌ Groupe ${g} manquant dans ordinaires`);
            }
        });
        
        groupesV.forEach(g => {
            if (!CONFIG.groupes_linges.volumineux[g]) {
                throw new Error(`❌ Groupe ${g} manquant dans volumineux`);
            }
        });
        
        debugLog('✅ Validation JSON réussie');
        
    } catch (error) {
        console.error('❌ Erreur chargement config:', error);
        alert(`Erreur de chargement de la configuration: ${error.message}`);
        CONFIG = null;
    }
}

// ============================================
// CHARGEMENT POINTS UTILISATEUR
// ============================================
async function loadUserPoints() {
    try {
        debugLog('📊 Chargement des points utilisateur...');
        
        const response = await fetch('get_user_points.php');
        const data = await response.json();
        
        if (data.success) {
            userNombreLavage = parseInt(data.nombre_lavage) || 0;
            debugLog(`✅ Lavages client: ${userNombreLavage}/11`);
        } else {
            console.warn('⚠️ Erreur points:', data.message);
            userNombreLavage = 0;
        }
    } catch (error) {
        console.error('❌ Erreur chargement points:', error);
        userNombreLavage = 0;
    }
}

// ============================================
// INITIALISATION FORMULAIRE
// ============================================
function initializeForm() {
    debugLog('🔧 Initialisation du formulaire');
    
    const today = new Date().toISOString().split('T')[0];
    const dateCollecte = document.querySelector('input[name="dateCollecte"]');
    const dateLivraison = document.querySelector('input[name="dateLivraison"]');
    
    if (dateCollecte) {
        dateCollecte.min = today;
        
        dateCollecte.addEventListener('change', function() {
            const collecteDate = this.value;
            if (collecteDate && dateLivraison) {
                const nextDay = new Date(collecteDate);
                nextDay.setDate(nextDay.getDate() + 1);
                dateLivraison.min = nextDay.toISOString().split('T')[0];
            }
        });
    }
    
    if (dateLivraison) {
        dateLivraison.min = today;
    }
}

// ============================================
// SETUP EVENT LISTENERS
// ============================================
function setupEventListeners() {
    debugLog('🎧 Configuration des event listeners');
    
    // Guide toggle
    const guideToggle = document.getElementById('guideToggle');
    if (guideToggle) {
        guideToggle.addEventListener('click', function() {
            const content = document.getElementById('guideContent');
            this.classList.toggle('active');
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        });
    }
    
    // ✅ TYPE BUTTONS - IDENTIQUE POUR LES DEUX
    document.querySelectorAll('.type-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const type = this.dataset.type;
            
            debugLog(`🖱️ Clic sur bouton: ${type}`);
            
            // Toggle l'état actif
            this.classList.toggle('active');
            const isActive = this.classList.contains('active');
            
            debugLog(`État bouton ${type}: ${isActive ? 'ACTIF' : 'INACTIF'}`);
            
            // Générer ou supprimer les inputs
            generateLingeInputs(type, isActive);
        });
    });
    
    // Navigation
    const btnNext = document.getElementById('btnNext');
    const btnPrev = document.getElementById('btnPrev');
    const form = document.getElementById('commandeForm');
    
    if (btnNext) btnNext.addEventListener('click', nextStep);
    if (btnPrev) btnPrev.addEventListener('click', prevStep);
    if (form) {
        form.addEventListener('submit', handleSubmit);
        form.addEventListener('input', calculateTotal);
    }
    
    debugLog('✅ Event listeners configurés');
}

// ============================================
// GÉNÉRATION INPUTS LINGES - VERSION FINALE
// ============================================
function generateLingeInputs(type, show) {
    debugLog(`🗂️ generateLingeInputs appelée`, { type, show });
    
    if (!CONFIG) {
        console.error('❌ CONFIG null');
        alert('Configuration non chargée. Veuillez recharger la page.');
        return;
    }
    
    const container = document.getElementById('lingeContainer');
    if (!container) {
        console.error('❌ Container lingeContainer introuvable');
        return;
    }
    
    // ✅ DÉTERMINER LES GROUPES SELON LE TYPE
    let groupes, typeKey;
    
    if (type === 'ordinaire') {
        groupes = ['O1', 'O2', 'O3', 'O4', 'O5'];
        typeKey = 'ordinaires';
    } else if (type === 'volumineux') {
        groupes = ['V1', 'V2', 'V3', 'V4', 'V5'];
        typeKey = 'volumineux';
    } else {
        console.error(`❌ Type inconnu: ${type}`);
        return;
    }
    
    debugLog(`📋 Type: ${type} | Clé JSON: ${typeKey} | Groupes: ${groupes.join(', ')}`);
    
    // ✅ VÉRIFIER QUE LA CLÉ EXISTE DANS LE JSON
    if (!CONFIG.groupes_linges[typeKey]) {
        console.error(`❌ CONFIG.groupes_linges.${typeKey} introuvable`);
        debugLog('Structure CONFIG:', CONFIG);
        alert(`Erreur: Configuration "${typeKey}" manquante`);
        return;
    }
    
    // ✅ SI DÉSACTIVATION : SUPPRIMER LES GROUPES
    if (!show) {
        debugLog(`🗑️ Suppression des groupes ${type}`);
        
        groupes.forEach(g => {
            const elem = document.getElementById(`groupe-${g}`);
            if (elem) {
                elem.remove();
                debugLog(`  ✅ Supprimé: groupe-${g}`);
            }
        });
        return;
    }
    
    // ✅ SI ACTIVATION : AJOUTER LES GROUPES
    debugLog(`➕ Ajout des groupes ${type}`);
    
    groupes.forEach(groupe => {
        // Éviter les doublons
        if (document.getElementById(`groupe-${groupe}`)) {
            debugLog(`  ⭕ Groupe ${groupe} déjà présent, ignoré`);
            return;
        }
        
        const groupeData = CONFIG.groupes_linges[typeKey][groupe];
        
        if (!groupeData) {
            console.error(`❌ Groupe ${groupe} manquant dans ${typeKey}`);
            debugLog('Groupes disponibles:', Object.keys(CONFIG.groupes_linges[typeKey]));
            return;
        }
        
        const description = groupeData.description || 'Description manquante';
        
        debugLog(`  🔨 Création carte: ${groupe} - ${description}`);
        
        try {
            const card = createGroupeCard(groupe, description, type);
            container.appendChild(card);
            debugLog(`  ✅ Carte ${groupe} ajoutée`);
        } catch (error) {
            console.error(`❌ Erreur création carte ${groupe}:`, error);
        }
    });
    
    debugLog(`✅ Génération terminée pour ${type}`);
}

// ============================================
// CRÉATION CARTE GROUPE
// ============================================
function createGroupeCard(groupe, description, type) {
    const div = document.createElement('div');
    div.className = 'groupe-card';
    div.id = `groupe-${groupe}`;
    
    div.innerHTML = `
        <div class="groupe-header" onclick="toggleGroupe('${groupe}')">
            <h4>${groupe} - ${description}</h4>
            <span class="icon">▼</span>
        </div>
        <div class="groupe-content" id="content-${groupe}">
            <div class="color-selector">
                <button type="button" class="color-btn" onclick="toggleColor('${groupe}', 'blanc')">
                    ⚪ Blanc
                </button>
                <button type="button" class="color-btn" onclick="toggleColor('${groupe}', 'couleur')">
                    🔵 Couleur
                </button>
            </div>
            <div id="inputs-${groupe}-blanc" style="display:none;">
                ${generateTempInputs(groupe, 'blanc', type)}
            </div>
            <div id="inputs-${groupe}-couleur" style="display:none;">
                ${generateTempInputs(groupe, 'couleur', type)}
            </div>
        </div>
    `;
    
    return div;
}

// ============================================
// GÉNÉRATION INPUTS TEMPÉRATURES
// ============================================
function generateTempInputs(groupe, couleur, type) {
    const temps = ['chaud', 'tiede', 'froid'];
    const icons = { chaud: '🔥', tiede: '🌡️', froid: '❄️' };
    const labels = { chaud: 'Chaud', tiede: 'Tiède', froid: 'Froid' };
    
    return `
        <div class="temp-grid">
            ${temps.map(temp => `
                <div class="temp-item">
                    <label>${icons[temp]} ${labels[temp]}</label>
                    <input type="number" 
                           name="${groupe.toLowerCase()}_${couleur}_${temp}" 
                           min="0" 
                           step="1" 
                           placeholder="0"
                           data-groupe="${groupe}"
                           data-couleur="${couleur}"
                           data-temp="${temp}"
                           data-type="${type}">
                </div>
            `).join('')}
        </div>
    `;
}

// ============================================
// FONCTIONS TOGGLE - GLOBALES
// ============================================
window.toggleGroupe = function(groupe) {
    debugLog(`🔄 Toggle groupe: ${groupe}`);
    
    const content = document.getElementById(`content-${groupe}`);
    const header = content?.previousElementSibling;
    
    if (!content || !header) {
        console.error(`❌ Éléments introuvables pour ${groupe}`);
        return;
    }
    
    header.classList.toggle('active');
    content.classList.toggle('active');
};

window.toggleColor = function(groupe, couleur) {
    debugLog(`🎨 Toggle couleur: ${groupe} - ${couleur}`);
    
    const btn = event.currentTarget;
    btn.classList.toggle('active');
    
    const inputs = document.getElementById(`inputs-${groupe}-${couleur}`);
    if (inputs) {
        inputs.style.display = inputs.style.display === 'none' ? 'block' : 'none';
    }
};

// ============================================
// NAVIGATION STEPS
// ============================================
function nextStep() {
    if (!validateStep(currentStep)) return;
    
    if (currentStep < totalSteps) {
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        currentStep++;
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        updateProgress();
        
        if (currentStep === totalSteps) {
            calculateTotal();
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        currentStep--;
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        updateProgress();
    }
}

function updateProgress() {
    const percent = (currentStep / totalSteps) * 100;
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    if (progressBar) progressBar.style.width = percent + '%';
    if (progressText) progressText.textContent = `Étape ${currentStep}/${totalSteps}`;
    
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const btnSubmit = document.getElementById('btnSubmit');
    
    if (btnPrev) btnPrev.style.display = currentStep > 1 ? 'block' : 'none';
    if (btnNext) btnNext.style.display = currentStep < totalSteps ? 'block' : 'none';
    if (btnSubmit) btnSubmit.style.display = currentStep === totalSteps ? 'block' : 'none';
}

function validateStep(step) {
    const section = document.querySelector(`[data-step="${step}"]`);
    if (!section) return true;
    
    const required = section.querySelectorAll('[required]');
    
    for (let input of required) {
        if (!input.value) {
            input.focus();
            alert('Veuillez remplir tous les champs requis');
            return false;
        }
    }
    
    if (step === 3) {
        const hasLinges = extractLinges().length > 0;
        if (!hasLinges) {
            alert('Veuillez sélectionner au moins un linge');
            return false;
        }
    }
    
    return true;
}

// ============================================
// EXTRACTION LINGES
// ============================================
function extractLinges() {
    if (!CONFIG) return [];
    
    const linges = [];
    const inputs = document.querySelectorAll('input[data-groupe]');
    
    debugLog(`🔎 Extraction: ${inputs.length} inputs trouvés`);
    
    inputs.forEach(input => {
        const nombre = parseInt(input.value) || 0;
        if (nombre > 0) {
            const groupe = input.dataset.groupe;
            const type = input.dataset.type;
            const couleur = input.dataset.couleur;
            const temperature = input.dataset.temp;
            
            // ✅ Déterminer la bonne clé JSON
            const typeKey = type === 'ordinaire' ? 'ordinaires' : 'volumineux';
            const groupeData = CONFIG.groupes_linges[typeKey]?.[groupe];
            
            if (!groupeData) {
                console.warn(`⚠️ Données manquantes pour ${groupe}`);
                return;
            }
            
            const proportionUnite = groupeData.proportion_unite;
            
            linges.push({
                groupe,
                type,
                couleur,
                temperature,
                nombre,
                proportionUnite,
                fieldName: input.name
            });
        }
    });
    
    debugLog(`✅ ${linges.length} linges extraits`, linges);
    
    return linges;
}

// ============================================
// CALCULS
// ============================================
function calculerNombreLavages(linges) {
    const groupes = {};
    
    linges.forEach(item => {
        const key = `${item.type}_${item.couleur}_${item.temperature}`;
        if (!groupes[key]) {
            groupes[key] = {
                type: item.type,
                couleur: item.couleur,
                temperature: item.temperature,
                proportionTotale: 0,
                items: []
            };
        }
        
        const proportion = item.nombre * item.proportionUnite;
        groupes[key].proportionTotale += proportion;
        groupes[key].items.push({
            groupe: item.groupe,
            nombre: item.nombre,
            proportion
        });
    });
    
    let nombreLavagesTotal = 0;
    const details = [];
    
    Object.values(groupes).forEach(groupe => {
        const seuil = CONFIG.machines[groupe.type].seuil_remplissage;
        const capacite = CONFIG.machines[groupe.type].capacite_kg;
        const proportionUtile = capacite * seuil;
        
        const quotient = Math.floor(groupe.proportionTotale / proportionUtile);
        const reste = groupe.proportionTotale % proportionUtile;
        const nombreLavages = quotient + (reste > 0 ? 1 : 0);
        
        nombreLavagesTotal += nombreLavages;
        
        details.push({
            ...groupe,
            nombreLavages,
            tarif: CONFIG.tarifs_lavage[groupe.type][groupe.temperature],
            prix: nombreLavages * CONFIG.tarifs_lavage[groupe.type][groupe.temperature]
        });
    });
    
    return { nombreLavages: nombreLavagesTotal, details };
}

function calculerPrixLavage(linges) {
    if (!CONFIG || linges.length === 0) return { prix: 0, lav: 0, details: [] };
    
    const result = calculerNombreLavages(linges);
    const prixTotal = result.details.reduce((sum, d) => sum + d.prix, 0);
    
    return {
        prix: prixTotal,
        lav: result.nombreLavages,
        details: result.details
    };
}

function calculerPrixSechage(linges) {
    if (!CONFIG || linges.length === 0) return 0;
    
    let poidsTotalKg = linges.reduce((sum, item) => sum + (item.nombre * item.proportionUnite), 0);
    
    for (const palier of CONFIG.tarifs_sechage) {
        if (poidsTotalKg <= palier.poids_max_kg) {
            return palier.prix;
        }
    }
    
    const dernierPalier = CONFIG.tarifs_sechage[CONFIG.tarifs_sechage.length - 1];
    return dernierPalier.prix;
}

function calculerPrixPliage(linges) {
    if (!CONFIG || linges.length === 0) return 0;
    
    let poidsTotalKg = linges.reduce((sum, item) => sum + (item.nombre * item.proportionUnite), 0);
    
    if (poidsTotalKg < CONFIG.pliage.minimum_kg) return 0;
    
    const quotient = Math.floor(poidsTotalKg / CONFIG.pliage.palier_kg);
    const reste = poidsTotalKg % CONFIG.pliage.palier_kg;
    
    let prix = quotient * CONFIG.pliage.prix_par_palier;
    
    if (reste >= CONFIG.pliage.minimum_kg) {
        prix += CONFIG.pliage.prix_par_palier;
    }
    
    return prix;
}

function calculerPrixRepassage(linges) {
    if (!CONFIG || linges.length === 0) return 0;
    
    let poidsOrdinaire = 0;
    let poidsVolumineux = 0;
    
    linges.forEach(item => {
        const poids = item.nombre * item.proportionUnite;
        if (item.type === 'ordinaire') {
            poidsOrdinaire += poids;
        } else {
            poidsVolumineux += poids;
        }
    });
    
    let prixTotal = 0;
    
    if (poidsOrdinaire >= CONFIG.repassage.palier_ordinaire_kg) {
        const tranches = Math.floor(poidsOrdinaire / CONFIG.repassage.palier_ordinaire_kg);
        prixTotal += tranches * CONFIG.repassage.prix_palier_ordinaire;
    }
    
    if (poidsVolumineux >= CONFIG.repassage.palier_volumineux_kg) {
        const tranches = Math.floor(poidsVolumineux / CONFIG.repassage.palier_volumineux_kg);
        prixTotal += tranches * CONFIG.repassage.prix_palier_volumineux;
    }
    
    return prixTotal;
}

// ============================================
// CALCUL TOTAL
// ============================================
function calculateTotal() {
    if (!CONFIG) return;
    
    const linges = extractLinges();
    if (linges.length === 0) {
        resetDisplay();
        return;
    }
    
    const lavageResult = calculerPrixLavage(linges);
    const prixLavageBrut = lavageResult.prix;
    const lavTotal = lavageResult.lav;
    
    const totalLavages = userNombreLavage + lavTotal;
    const nombreReductions = Math.floor(totalLavages / 11);
    const reductionFidelite = nombreReductions * 2500;
    
    const prixLavageFinal = Math.max(0, prixLavageBrut - reductionFidelite);
    
    const prixSechage = calculerPrixSechage(linges);
    const prixPliage = calculerPrixPliage(linges);
    const prixRepassage = calculerPrixRepassage(linges);
    
    const total = prixLavageFinal + prixSechage + prixPliage + prixRepassage;
    
    const elements = {
        prixLavage: document.getElementById('prixLavage'),
        prixSechage: document.getElementById('prixSechage'),
        prixPliage: document.getElementById('prixPliage'),
        prixRepassage: document.getElementById('prixRepassage'),
        total: document.getElementById('total'),
        lavCount: document.getElementById('lavCount'),
        reduction: document.getElementById('reduction'),
        reductionLine: document.getElementById('reductionLine')
    };
    
    if (elements.prixLavage) elements.prixLavage.textContent = prixLavageBrut.toLocaleString();
    if (elements.prixSechage) elements.prixSechage.textContent = prixSechage.toLocaleString();
    if (elements.prixPliage) elements.prixPliage.textContent = prixPliage.toLocaleString();
    if (elements.prixRepassage) elements.prixRepassage.textContent = prixRepassage.toLocaleString();
    if (elements.total) elements.total.textContent = total.toLocaleString();
    if (elements.lavCount) elements.lavCount.textContent = lavTotal;
    
    if (elements.reductionLine) {
        if (reductionFidelite > 0) {
            elements.reductionLine.style.display = 'flex';
            if (elements.reduction) elements.reduction.textContent = reductionFidelite.toLocaleString();
        } else {
            elements.reductionLine.style.display = 'none';
        }
    }
}

function resetDisplay() {
    const ids = ['prixLavage', 'prixSechage', 'prixPliage', 'prixRepassage', 'total', 'lavCount'];
    ids.forEach(id => {
        const elem = document.getElementById(id);
        if (elem) elem.textContent = '0';
    });
    
    const reductionLine = document.getElementById('reductionLine');
    if (reductionLine) reductionLine.style.display = 'none';
}

// ============================================
// SOUMISSION FORMULAIRE
// ============================================
async function handleSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const linges = extractLinges();
    
    if (linges.length === 0) {
        alert('Veuillez sélectionner au moins un linge');
        return;
    }
    
    const lavageResult = calculerPrixLavage(linges);
    
    const orderData = {
        nomClient: formData.get('nomClient'),
        telephone: formData.get('telephone'),
        adresseCollecte: formData.get('adresseCollecte'),
        dateCollecte: formData.get('dateCollecte'),
        adresseLivraison: formData.get('adresseLivraison'),
        dateLivraison: formData.get('dateLivraison'),
        linges: linges,
        detailsLavage: lavageResult.details,
        nombreLavageClient: userNombreLavage,
        lav: lavageResult.lav,
        paiement: formData.get('paiement')
    };
    
    try {
        const response = await fetch('process_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = `payment.php?orderId=${data.orderId}&orderNumber=${encodeURIComponent(data.orderNumber)}&method=${orderData.paiement}`;
        } else {
            alert('Erreur : ' + data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    }
}