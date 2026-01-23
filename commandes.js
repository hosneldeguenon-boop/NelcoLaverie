// ============================================
// CONFIGURATION - Chargée depuis JSON
// ============================================
let CONFIG = null;
let userNombreLavage = 0;

// Charger la configuration
async function loadConfig() {
    try {
        const response = await fetch('laverie_config.json');
        CONFIG = await response.json();
        console.log('✅ Configuration chargée:', CONFIG);
    } catch (error) {
        console.error('❌ Erreur chargement config:', error);
        alert('Erreur de chargement de la configuration');
    }
}

// ============================================
// SYSTÈME DE FIDÉLITÉ - CYCLE DE 11 LAVAGES
// ============================================
function loadUserPoints() {
    fetch('get_user_points.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                userNombreLavage = parseInt(data.nombre_lavage) || 0;
                console.log('📊 Nombre de lavages client:', userNombreLavage);
                calculerPrixTotal();
            } else {
                console.error('Erreur chargement points:', data.message);
                userNombreLavage = 0;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            userNombreLavage = 0;
        });
}

// ============================================
// CALCUL DU NOMBRE DE LAVAGES REQUIS
// ============================================
function calculerNombreLavages(linges) {
    if (!CONFIG) return { nombreLavages: 0, details: [] };
    
    const details = [];
    let nombreLavagesTotal = 0;
    
    // Grouper par type_couleur_temperature
    const groupes = {};
    
    linges.forEach(item => {
        const cle = `${item.type}_${item.couleur}_${item.temperature}`;
        if (!groupes[cle]) {
            groupes[cle] = {
                type: item.type,
                couleur: item.couleur,
                temperature: item.temperature,
                proportionTotale: 0,
                items: []
            };
        }
        
        const proportion = item.nombre * item.proportionUnite;
        groupes[cle].proportionTotale += proportion;
        groupes[cle].items.push({
            groupe: item.groupe,
            nombre: item.nombre,
            proportion: proportion
        });
    });
    
    // Calculer le nombre de lavages pour chaque groupe
    for (const [cle, groupe] of Object.entries(groupes)) {
        const seuil = CONFIG.machines[groupe.type].seuil_remplissage;
        const capacite = CONFIG.machines[groupe.type].capacite_kg;
        const proportionUtile = capacite * seuil;
        
        const quotient = Math.floor(groupe.proportionTotale / proportionUtile);
        const reste = groupe.proportionTotale % proportionUtile;
        
        const nombreLavages = quotient + (reste > 0 ? 1 : 0);
        
        nombreLavagesTotal += nombreLavages;
        
        details.push({
            cle: cle,
            type: groupe.type,
            couleur: groupe.couleur,
            temperature: groupe.temperature,
            proportionTotale: groupe.proportionTotale,
            nombreLavages: nombreLavages,
            items: groupe.items
        });
    }
    
    return { nombreLavages: nombreLavagesTotal, details: details };
}

// ============================================
// CALCUL DU PRIX DE LAVAGE
// ============================================
function calculerPrixLavage(linges) {
    if (!CONFIG || linges.length === 0) return { prix: 0, lav: 0, details: [] };
    
    const resultatsLavage = calculerNombreLavages(linges);
    let prixTotal = 0;
    
    resultatsLavage.details.forEach(detail => {
        const tarif = CONFIG.tarifs_lavage[detail.type][detail.temperature];
        const prix = detail.nombreLavages * tarif;
        prixTotal += prix;
        
        detail.prix = prix;
        detail.tarif = tarif;
    });
    
    return {
        prix: prixTotal,
        lav: resultatsLavage.nombreLavages,
        details: resultatsLavage.details
    };
}

// ============================================
// CALCUL DU PRIX DE SÉCHAGE
// ============================================
function calculerPrixSechage(linges) {
    if (!CONFIG || linges.length === 0) return 0;
    
    // Calculer le poids total approximatif
    let poidsTotalKg = 0;
    linges.forEach(item => {
        poidsTotalKg += item.nombre * item.proportionUnite;
    });
    
    // Trouver le palier de tarif approprié
    for (const palier of CONFIG.tarifs_sechage) {
        if (poidsTotalKg <= palier.poids_max_kg) {
            return palier.prix;
        }
    }
    
    // Si dépassement du dernier palier, calculer récursivement
    const dernierPalier = CONFIG.tarifs_sechage[CONFIG.tarifs_sechage.length - 1];
    const prixBase = dernierPalier.prix;
    const poidsRestant = poidsTotalKg - dernierPalier.poids_max_kg;
    
    return prixBase + calculerPrixSechageRecursif(poidsRestant);
}

function calculerPrixSechageRecursif(poids) {
    if (poids <= 0) return 0;
    
    for (const palier of CONFIG.tarifs_sechage) {
        if (poids <= palier.poids_max_kg) {
            return palier.prix;
        }
    }
    
    const dernierPalier = CONFIG.tarifs_sechage[CONFIG.tarifs_sechage.length - 1];
    return dernierPalier.prix + calculerPrixSechageRecursif(poids - dernierPalier.poids_max_kg);
}

// ============================================
// CALCUL DU PRIX DE PLIAGE
// ============================================
function calculerPrixPliage(linges) {
    if (!CONFIG || linges.length === 0) return 0;
    
    // Convertir nombre en poids approximatif
    let poidsTotalKg = 0;
    linges.forEach(item => {
        poidsTotalKg += item.nombre * item.proportionUnite;
    });
    
    if (poidsTotalKg < CONFIG.pliage.minimum_kg) return 0;
    
    const quotient = Math.floor(poidsTotalKg / CONFIG.pliage.palier_kg);
    const reste = poidsTotalKg % CONFIG.pliage.palier_kg;
    
    let prix = quotient * CONFIG.pliage.prix_par_palier;
    
    if (reste >= CONFIG.pliage.minimum_kg) {
        prix += CONFIG.pliage.prix_par_palier;
    }
    
    return prix;
}

// ============================================
// CALCUL DU PRIX DE REPASSAGE
// ============================================
function calculerPrixRepassage(linges) {
    if (!CONFIG || linges.length === 0) return 0;
    
    let poidsOrdinaireKg = 0;
    let poidsVolumineuxKg = 0;
    
    linges.forEach(item => {
        const poids = item.nombre * item.proportionUnite;
        if (item.type === 'ordinaire') {
            poidsOrdinaireKg += poids;
        } else {
            poidsVolumineuxKg += poids;
        }
    });
    
    let prixTotal = 0;
    
    // Repassage ordinaire
    if (poidsOrdinaireKg >= CONFIG.repassage.palier_ordinaire_kg) {
        const tranches = Math.floor(poidsOrdinaireKg / CONFIG.repassage.palier_ordinaire_kg);
        prixTotal += tranches * CONFIG.repassage.prix_palier_ordinaire;
    }
    
    // Repassage volumineux
    if (poidsVolumineuxKg >= CONFIG.repassage.palier_volumineux_kg) {
        const tranches = Math.floor(poidsVolumineuxKg / CONFIG.repassage.palier_volumineux_kg);
        prixTotal += tranches * CONFIG.repassage.prix_palier_volumineux;
    }
    
    return prixTotal;
}

// ============================================
// CALCUL PRIX COLLECTE/LIVRAISON (DÉSACTIVÉ)
// ============================================
/*
const tarifsCommunePrix = {
    godomey: 500,
    cotonou: 1000,
    calavi: 800,
    autres: 1500
};

function calculerPrixCollecte(commune1, commune2) {
    return (tarifsCommunePrix[commune1] || 0) + (tarifsCommunePrix[commune2] || 0);
}
*/

// ============================================
// EXTRAIRE LES LINGES DU FORMULAIRE
// ============================================
function extraireLinguesFormulaire(formData) {
    if (!CONFIG) return [];
    
    const linges = [];
    const groupes = ['o1', 'o2', 'o3', 'o4', 'o5', 'v1', 'v2', 'v3', 'v4', 'v5'];
    const couleurs = ['blanc', 'couleur'];
    const temperatures = ['chaud', 'tiede', 'froid'];
    
    groupes.forEach(groupe => {
        const type = groupe.startsWith('o') ? 'ordinaire' : 'volumineux';
        const groupeKey = groupe.toUpperCase();
        
        let proportionUnite;
        if (type === 'ordinaire') {
            proportionUnite = CONFIG.groupes_linges.ordinaires[groupeKey].proportion_unite;
        } else {
            proportionUnite = CONFIG.groupes_linges.volumineux[groupeKey].proportion_unite;
        }
        
        couleurs.forEach(couleur => {
            temperatures.forEach(temperature => {
                const fieldName = `${groupe}_${couleur}_${temperature}`;
                const nombre = parseInt(formData.get(fieldName)) || 0;
                
                if (nombre > 0) {
                    linges.push({
                        groupe: groupeKey,
                        type: type,
                        couleur: couleur,
                        temperature: temperature,
                        nombre: nombre,
                        proportionUnite: proportionUnite,
                        fieldName: fieldName
                    });
                }
            });
        });
    });
    
    return linges;
}

// ============================================
// FONCTION PRINCIPALE - CALCUL PRIX TOTAL
// ============================================
function calculerPrixTotal() {
    if (!CONFIG) {
        console.warn('⚠️ Configuration non chargée');
        return null;
    }
    
    const form = document.getElementById('commandeForm');
    const formData = new FormData(form);
    
    // Extraire les linges
    const linges = extraireLinguesFormulaire(formData);
    
    if (linges.length === 0) {
        // Réinitialiser l'affichage
        document.getElementById('prixLavageOutput').textContent = '0';
        document.getElementById('prixSechageOutput').textContent = '0';
        document.getElementById('prixPliageOutput').textContent = '0';
        document.getElementById('prixRepassageOutput').textContent = '0';
        // document.getElementById('prixCollecteOutput').textContent = '0';
        document.getElementById('totalPayerOutput').textContent = '0';
        document.getElementById('lavCount').textContent = '0';
        document.getElementById('reductionFidelite').style.display = 'none';
        
        return null;
    }
    
    // Calculs
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
    
    // const commune1 = formData.get('communeCollecte') || '';
    // const commune2 = formData.get('communeLivraison') || '';
    // const prixCollecte = calculerPrixCollecte(commune1, commune2);
    const prixCollecte = 0; // DÉSACTIVÉ
    
    const total = prixLavageFinal + prixSechage + prixPliage + prixRepassage + prixCollecte;
    
    // Mise à jour affichage
    document.getElementById('prixLavageOutput').textContent = prixLavageBrut.toLocaleString();
    document.getElementById('prixSechageOutput').textContent = prixSechage.toLocaleString();
    document.getElementById('prixPliageOutput').textContent = prixPliage.toLocaleString();
    document.getElementById('prixRepassageOutput').textContent = prixRepassage.toLocaleString();
    // document.getElementById('prixCollecteOutput').textContent = prixCollecte.toLocaleString();
    document.getElementById('totalPayerOutput').textContent = total.toLocaleString();
    document.getElementById('lavCount').textContent = lavTotal;
    
    const reductionElement = document.getElementById('reductionFidelite');
    if (reductionFidelite > 0) {
        reductionElement.style.display = 'flex';
        document.getElementById('reductionOutput').textContent = reductionFidelite.toLocaleString();
    } else {
        reductionElement.style.display = 'none';
    }
    
    console.log('💰 Calcul:', {
        prixLavageBrut,
        lavTotal,
        reductionFidelite,
        prixLavageFinal,
        total
    });
    
    return {
        prixLavage: prixLavageBrut,
        prixLavageFinal: prixLavageFinal,
        prixSechage: prixSechage,
        prixPliage: prixPliage,
        prixRepassage: prixRepassage,
        prixCollecte: prixCollecte,
        total: total,
        linges: linges,
        detailsLavage: lavageResult.details,
        reductionFidelite: reductionFidelite,
        nombreLavageClient: userNombreLavage,
        lav: lavTotal,
        nouveauNombreLavage: nouveauNombreLavage
    };
}

// ============================================
// INITIALISATION
// ============================================
document.addEventListener('DOMContentLoaded', async function() {
    // Charger la configuration
    await loadConfig();
    
    // Charger les points utilisateur
    loadUserPoints();
    
    const form = document.getElementById('commandeForm');
    
    // Dates minimales
    const today = new Date().toISOString().split('T')[0];
    const dateCollecte = document.getElementById('dateCollecte');
    const dateLivraison = document.getElementById('dateLivraison');
    
    if (dateCollecte) dateCollecte.setAttribute('min', today);
    if (dateLivraison) dateLivraison.setAttribute('min', today);
    
    if (dateCollecte && dateLivraison) {
        dateCollecte.addEventListener('change', function() {
            const collecteDate = this.value;
            if (collecteDate) {
                const nextDay = new Date(collecteDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const minDeliveryDate = nextDay.toISOString().split('T')[0];
                dateLivraison.setAttribute('min', minDeliveryDate);
                
                if (dateLivraison.value && dateLivraison.value <= collecteDate) {
                    dateLivraison.value = '';
                }
            }
        });
    }
    
    // Recalcul automatique
    form.addEventListener('input', calculerPrixTotal);
    form.addEventListener('change', calculerPrixTotal);
    
    calculerPrixTotal();
    
    // Soumission
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const formData = new FormData(form);
        const prix = calculerPrixTotal();
        
        if (!prix || prix.lav === 0) {
            alert('Veuillez renseigner au moins un linge.');
            return;
        }
        
        // Validation dates
        const aujourdhui = new Date();
        aujourdhui.setHours(0, 0, 0, 0);
        
        const dateCollecteValue = formData.get('dateCollecte');
        const dateLivraisonValue = formData.get('dateLivraison');
        
        if (!dateCollecteValue || !dateLivraisonValue) {
            alert('Veuillez renseigner les dates.');
            return;
        }
        
        const dateCollecte = new Date(dateCollecteValue);
        const dateLivraison = new Date(dateLivraisonValue);
        
        if (dateCollecte < aujourdhui) {
            alert('La date de collecte ne peut pas être antérieure à aujourd\'hui.');
            return;
        }
        
        if (dateLivraison <= dateCollecte) {
            alert('La date de livraison doit être après la date de collecte.');
            return;
        }
        
        // Préparer les données
        const orderData = {
            nomClient: formData.get('nomClient'),
            telephone: formData.get('telephone'),
            adresseCollecte: formData.get('adresseCollecte'),
            dateCollecte: formData.get('dateCollecte'),
            // communeCollecte: formData.get('communeCollecte') || '',
            adresseLivraison: formData.get('adresseLivraison'),
            dateLivraison: formData.get('dateLivraison'),
            // communeLivraison: formData.get('communeLivraison') || '',
            linges: prix.linges,
            detailsLavage: prix.detailsLavage,
            nombreLavageClient: prix.nombreLavageClient,
            lav: prix.lav,
            paiement: formData.get('paiement')
        };
        
        console.log('📤 Envoi commande:', orderData);
        
        fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const paymentUrl = `payment.php?orderId=${data.orderId}&orderNumber=${encodeURIComponent(data.orderNumber)}&method=${orderData.paiement}`;
                window.location.href = paymentUrl;
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue.');
        });
    });
});

// ============================================
// INTERACTIONS UI - Toggle protocole
// ============================================
document.getElementById('protocoleToggle')?.addEventListener('click', function() {
    const content = document.getElementById('protocoleContent');
    const icon = this.querySelector('.toggle-icon');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.textContent = '▲';
    } else {
        content.style.display = 'none';
        icon.textContent = '▼';
    }
});

// Toggle type de linge
document.getElementById('btnOrdinaire')?.addEventListener('click', function() {
    this.classList.toggle('active');
    const section = document.getElementById('ordinaireSection');
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('btnVolumineux')?.addEventListener('click', function() {
    this.classList.toggle('active');
    const section = document.getElementById('volumineuxSection');
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
});

// Toggle groupes
document.querySelectorAll('.groupe-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const groupe = this.dataset.groupe;
        const content = document.getElementById(groupe + 'Content');
        const icon = this.querySelector('.toggle-icon');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.textContent = '▲';
        } else {
            content.style.display = 'none';
            icon.textContent = '▼';
        }
    });
});

// Toggle couleurs
document.querySelectorAll('.color-card').forEach(btn => {
    btn.addEventListener('click', function() {
        const groupe = this.dataset.groupe;
        const couleur = this.dataset.couleur;
        const id = `${groupe}_${couleur}`;
        
        this.classList.toggle('active');
        const section = document.getElementById(id);
        if (section) {
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
        }
    });
});