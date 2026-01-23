// Variables globales
let CONFIG = null;
let userNombreLavage = 0;
let currentStep = 1;
const totalSteps = 5;

// Initialisation
document.addEventListener('DOMContentLoaded', async function() {
    await loadConfig();
    await loadUserPoints();
    initializeForm();
    setupEventListeners();
});

// Charger configuration
async function loadConfig() {
    try {
        const response = await fetch('laverie_config.json');
        CONFIG = await response.json();
        console.log('✅ Config chargée');
    } catch (error) {
        console.error('❌ Erreur config:', error);
        alert('Erreur de chargement de la configuration');
    }
}

// Charger points utilisateur
async function loadUserPoints() {
    try {
        const response = await fetch('get_user_points.php');
        const data = await response.json();
        if (data.success) {
            userNombreLavage = parseInt(data.nombre_lavage) || 0;
            console.log('📊 Lavages client:', userNombreLavage);
        }
    } catch (error) {
        console.error('Erreur points:', error);
    }
}

// Initialiser formulaire
function initializeForm() {
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="dateCollecte"]').min = today;
    document.querySelector('input[name="dateLivraison"]').min = today;
    
    // Validation dates
    document.querySelector('input[name="dateCollecte"]').addEventListener('change', function() {
        const collecteDate = this.value;
        if (collecteDate) {
            const nextDay = new Date(collecteDate);
            nextDay.setDate(nextDay.getDate() + 1);
            document.querySelector('input[name="dateLivraison"]').min = nextDay.toISOString().split('T')[0];
        }
    });
}

// Setup Event Listeners
function setupEventListeners() {
    // Guide toggle
    document.getElementById('guideToggle').addEventListener('click', function() {
        const content = document.getElementById('guideContent');
        this.classList.toggle('active');
        content.style.display = content.style.display === 'none' ? 'block' : 'none';
    });
    
    // Type buttons
    document.querySelectorAll('.type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            this.classList.toggle('active');
            generateLingeInputs(type, this.classList.contains('active'));
        });
    });
    
    // Navigation
    document.getElementById('btnNext').addEventListener('click', nextStep);
    document.getElementById('btnPrev').addEventListener('click', prevStep);
    document.getElementById('commandeForm').addEventListener('submit', handleSubmit);
    
    // Auto-calculate
    document.getElementById('commandeForm').addEventListener('input', calculateTotal);
}

// Générer inputs pour linges
function generateLingeInputs(type, show) {
    const container = document.getElementById('lingeContainer');
    const groupPrefix = type === 'ordinaire' ? 'O' : 'V';
    const groupes = type === 'ordinaire' 
        ? ['O1', 'O2', 'O3', 'O4', 'O5']
        : ['V1', 'V2', 'V3', 'V4', 'V5'];
    
    if (!show) {
        // Retirer les groupes de ce type
        groupes.forEach(g => {
            const elem = document.getElementById(`groupe-${g}`);
            if (elem) elem.remove();
        });
        return;
    }
    
    // Ajouter les groupes
    groupes.forEach(groupe => {
        if (document.getElementById(`groupe-${groupe}`)) return;
        
        const description = CONFIG.groupes_linges[type + 's'][groupe].description;
        const card = createGroupeCard(groupe, description, type);
        container.appendChild(card);
    });
}

// Créer carte groupe
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

// Générer inputs températures
function generateTempInputs(groupe, couleur, type) {
    const temps = ['chaud', 'tiede', 'froid'];
    const icons = { chaud: '🔥', tiede: '🌡️', froid: '❄️' };
    
    return `
        <div class="temp-grid">
            ${temps.map(temp => `
                <div class="temp-item">
                    <label>${icons[temp]} ${temp.charAt(0).toUpperCase() + temp.slice(1)}</label>
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

// Toggle groupe
function toggleGroupe(groupe) {
    const content = document.getElementById(`content-${groupe}`);
    const header = content.previousElementSibling;
    header.classList.toggle('active');
    content.classList.toggle('active');
}

// Toggle couleur
function toggleColor(groupe, couleur) {
    const btn = event.currentTarget;
    btn.classList.toggle('active');
    const inputs = document.getElementById(`inputs-${groupe}-${couleur}`);
    inputs.style.display = inputs.style.display === 'none' ? 'block' : 'none';
}

// Navigation steps
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
    document.getElementById('progressBar').style.width = percent + '%';
    document.getElementById('progressText').textContent = `Étape ${currentStep}/${totalSteps}`;
    
    document.getElementById('btnPrev').style.display = currentStep > 1 ? 'block' : 'none';
    document.getElementById('btnNext').style.display = currentStep < totalSteps ? 'block' : 'none';
    document.getElementById('btnSubmit').style.display = currentStep === totalSteps ? 'block' : 'none';
}

function validateStep(step) {
    const section = document.querySelector(`[data-step="${step}"]`);
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

// Extraction linges du formulaire
function extractLinges() {
    const linges = [];
    const inputs = document.querySelectorAll('input[data-groupe]');
    
    inputs.forEach(input => {
        const nombre = parseInt(input.value) || 0;
        if (nombre > 0) {
            const groupe = input.dataset.groupe;
            const type = input.dataset.type;
            const couleur = input.dataset.couleur;
            const temperature = input.dataset.temp;
            
            const proportionUnite = CONFIG.groupes_linges[type + 's'][groupe].proportion_unite;
            
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
    
    return linges;
}

// Calcul nombre de lavages
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

// Calcul prix lavage
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

// Calcul séchage
function calculerPrixSechage(linges) {
    if (!CONFIG || linges.length === 0) return 0;
    
    let poidsTotalKg = linges.reduce((sum, item) => sum + (item.nombre * item.proportionUnite), 0);
    
    for (const palier of CONFIG.tarifs_sechage) {
        if (poidsTotalKg <= palier.poids_max_kg) {
            return palier.prix;
        }
    }
    
    const dernierPalier = CONFIG.tarifs_sechage[CONFIG.tarifs_sechage.length - 1];
    return dernierPalier.prix + calculerPrixSechage([{ nombre: (poidsTotalKg - dernierPalier.poids_max_kg) / linges[0].proportionUnite, proportionUnite: linges[0].proportionUnite }]);
}

// Calcul pliage
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

// Calcul repassage
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

// Calcul total
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
    
    // Fidélité
    const totalLavages = userNombreLavage + lavTotal;
    const nombreReductions = Math.floor(totalLavages / 11);
    const nouveauNombreLavage = totalLavages % 11;
    const reductionFidelite = nombreReductions * 2500;
    
    const prixLavageFinal = Math.max(0, prixLavageBrut - reductionFidelite);
    
    const prixSechage = calculerPrixSechage(linges);
    const prixPliage = calculerPrixPliage(linges);
    const prixRepassage = calculerPrixRepassage(linges);
    
    const total = prixLavageFinal + prixSechage + prixPliage + prixRepassage;
    
    // Affichage
    document.getElementById('prixLavage').textContent = prixLavageBrut.toLocaleString();
    document.getElementById('prixSechage').textContent = prixSechage.toLocaleString();
    document.getElementById('prixPliage').textContent = prixPliage.toLocaleString();
    document.getElementById('prixRepassage').textContent = prixRepassage.toLocaleString();
    document.getElementById('total').textContent = total.toLocaleString();
    document.getElementById('lavCount').textContent = lavTotal;
    
    const reductionLine = document.getElementById('reductionLine');
    if (reductionFidelite > 0) {
        reductionLine.style.display = 'flex';
        document.getElementById('reduction').textContent = reductionFidelite.toLocaleString();
    } else {
        reductionLine.style.display = 'none';
    }
}

function resetDisplay() {
    document.getElementById('prixLavage').textContent = '0';
    document.getElementById('prixSechage').textContent = '0';
    document.getElementById('prixPliage').textContent = '0';
    document.getElementById('prixRepassage').textContent = '0';
    document.getElementById('total').textContent = '0';
    document.getElementById('lavCount').textContent = '0';
    document.getElementById('reductionLine').style.display = 'none';
}

// Soumission
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