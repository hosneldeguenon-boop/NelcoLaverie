// Grille tarifaire complète
const tarifs = {
    froid: [
        { min: 0, max: 6, prix: 2500 },
        { min: 6, max: 8, prix: 3000 },
        { min: 8, max: 10, prix: 5000 }
    ],
    tiede: [
        { min: 0, max: 6, prix: 3000 },
        { min: 6, max: 8, prix: 3500 },
        { min: 8, max: 10, prix: 6000 }
    ],
    chaud: [
        { min: 0, max: 6, prix: 3500 },
        { min: 6, max: 8, prix: 4000 },
        { min: 8, max: 10, prix: 7000 }
    ]
};

const tarifsCommunePrix = {
    godomey: 500,
    cotonou: 1000,
    calavi: 800,
    autres: 1500
};

// RÉCUPÉRER LES POINTS DE FIDÉLITÉ depuis la session PHP
let userPointsFidelite = 0;

function getPointsFidelite() {
    return userPointsFidelite;
}

// Charger les points au démarrage de la page
function loadUserPoints() {
    fetch('get_user_points.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                userPointsFidelite = parseInt(data.points) || 0;
                console.log('Points de fidélité chargés:', userPointsFidelite);
                // Recalculer les prix avec les bons points
                calculerPrixTotal();
            } else {
                console.error('Erreur chargement points:', data.message);
                userPointsFidelite = 0;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            userPointsFidelite = 0;
        });
}

// CALCULER LE PRIX DU SÉCHAGE (récursif)
function calculerPrixSechage(poids) {
    if (poids <= 0) return 0;
    
    if (poids <= 2) return 1000;
    if (poids <= 3) return 1500;
    if (poids <= 4) return 2000;
    if (poids <= 6) return 2500;
    if (poids <= 8) return 3000;
    
    // Si supérieur à 8kg, calculer récursivement
    return 3000 + calculerPrixSechage(poids - 8);
}

// CALCULER LE PRIX DU PLIAGE
function calculerPrixPliage(poidsTotal) {
    if (poidsTotal < 4) return 0;
    
    const nombreTranches = Math.floor(poidsTotal / 4);
    return nombreTranches * 500;
}

// CALCULER LE PRIX DU REPASSAGE
function calculerPrixRepassage(poidsVolumineux, poidsOrdinaire) {
    let prixTotal = 0;
    
    // Repassage volumineux (200F par 4kg)
    if (poidsVolumineux >= 4) {
        const tranchesVolumineux = Math.floor(poidsVolumineux / 4);
        prixTotal += tranchesVolumineux * 200;
    }
    
    // Repassage ordinaire (150F par 4kg)
    if (poidsOrdinaire >= 4) {
        const tranchesOrdinaire = Math.floor(poidsOrdinaire / 4);
        prixTotal += tranchesOrdinaire * 150;
    }
    
    return prixTotal;
}

// CALCULER LE PRIX DU LAVAGE POUR LINGE VOLUMINEUX
function calculerPrixLavageVolumineux(poids, temperature) {
    if (poids <= 0) return 0;
    
    const grille = tarifs[temperature];
    
    // Trouver le prix pour machine 10kg
    let prix10kg = 0;
    for (let tranche of grille) {
        if (10 > tranche.min && 10 <= tranche.max) {
            prix10kg = tranche.prix;
            break;
        }
    }
    
    let prixTotal = 0;
    let poidsRestant = poids;
    
    // Traiter les tranches complètes de 10kg
    while (poidsRestant >= 10) {
        // Chaque 10kg est lavé en 2 parties
        const prixPremierPartie = prix10kg;
        const prixDeuxiemePartie = Math.ceil(prix10kg * 0.55);
        
        prixTotal += prixPremierPartie + prixDeuxiemePartie;
        poidsRestant -= 10;
    }
    
    // Traiter le reste (< 10kg)
    if (poidsRestant > 0) {
        if (poidsRestant >= 9) {
            // Si reste >= 9kg : lavé en 2 parties
            const prixPremierPartie = prix10kg;
            const prixDeuxiemePartie = Math.ceil(prix10kg * 0.55);
            prixTotal += prixPremierPartie + prixDeuxiemePartie;
        } else {
            // Si reste < 9kg : lavé en 1 seule partie
            prixTotal += prix10kg;
        }
    }
    
    return prixTotal;
}

// CALCULER LE PRIX DU LAVAGE POUR LINGE ORDINAIRE
function calculerPrixLavageOrdinaire(poids, temperature) {
    if (poids <= 0) return 0;
    
    const grille = tarifs[temperature];
    
    // Calculer le nombre de tranches de 10kg
    const quotient = Math.floor(poids / 10);
    const reste = poids % 10;
    
    let prixTotal = 0;
    
    // Trouver le prix pour une tranche complète de 10kg
    let prixTranche10kg = 0;
    for (let tranche of grille) {
        if (10 > tranche.min && 10 <= tranche.max) {
            prixTranche10kg = tranche.prix;
            break;
        }
    }
    
    // Ajouter le prix des tranches complètes
    prixTotal += quotient * prixTranche10kg;
    
    // Trouver le prix pour le reste
    if (reste > 0) {
        for (let tranche of grille) {
            if (reste > tranche.min && reste <= tranche.max) {
                prixTotal += tranche.prix;
                break;
            }
        }
    }
    
    return prixTotal;
}

// FONCTION PRINCIPALE DE CALCUL
function calculerPrixTotal() {
    const form = document.getElementById('commandeForm');
    const formData = new FormData(form);
    
    let prixLavageTotal = 0;
    let poidsVolumineuxTotal = 0;
    let poidsOrdinaireTotal = 0;
    let poidsGrandTotal = 0;
    const detailsPoids = [];
    
    // Liste des champs de poids
    const poidsFieldsVolumineux = [
        { name: 'a1_chaud', temp: 'chaud', label: 'Blanc Volumineux Chaud' },
        { name: 'a1_tiede', temp: 'tiede', label: 'Blanc Volumineux Tiède' },
        { name: 'a1_froid', temp: 'froid', label: 'Blanc Volumineux Froid' },
        { name: 'b1_chaud', temp: 'chaud', label: 'Couleur Claire Volumineux Chaud' },
        { name: 'b1_tiede', temp: 'tiede', label: 'Couleur Claire Volumineux Tiède' },
        { name: 'b1_froid', temp: 'froid', label: 'Couleur Claire Volumineux Froid' },
        { name: 'c1_chaud', temp: 'chaud', label: 'Couleur Foncée Volumineux Chaud' },
        { name: 'c1_tiede', temp: 'tiede', label: 'Couleur Foncée Volumineux Tiède' },
        { name: 'c1_froid', temp: 'froid', label: 'Couleur Foncée Volumineux Froid' }
    ];
    
    const poidsFieldsOrdinaire = [
        { name: 'a2_chaud', temp: 'chaud', label: 'Blanc Ordinaire Chaud' },
        { name: 'a2_tiede', temp: 'tiede', label: 'Blanc Ordinaire Tiède' },
        { name: 'a2_froid', temp: 'froid', label: 'Blanc Ordinaire Froid' },
        { name: 'b2_chaud', temp: 'chaud', label: 'Couleur Claire Ordinaire Chaud' },
        { name: 'b2_tiede', temp: 'tiede', label: 'Couleur Claire Ordinaire Tiède' },
        { name: 'b2_froid', temp: 'froid', label: 'Couleur Claire Ordinaire Froid' },
        { name: 'c2_chaud', temp: 'chaud', label: 'Couleur Foncée Ordinaire Chaud' },
        { name: 'c2_tiede', temp: 'tiede', label: 'Couleur Foncée Ordinaire Tiède' },
        { name: 'c2_froid', temp: 'froid', label: 'Couleur Foncée Ordinaire Froid' }
    ];
    
    // Calculer le prix pour linge VOLUMINEUX
    poidsFieldsVolumineux.forEach(field => {
        const poids = parseFloat(formData.get(field.name)) || 0;
        if (poids > 0) {
            const prix = calculerPrixLavageVolumineux(poids, field.temp);
            prixLavageTotal += prix;
            poidsVolumineuxTotal += poids;
            poidsGrandTotal += poids;
            detailsPoids.push({
                label: field.label,
                poids: poids,
                temperature: field.temp,
                prix: prix,
                type: 'volumineux'
            });
        }
    });
    
    // Calculer le prix pour linge ORDINAIRE
    poidsFieldsOrdinaire.forEach(field => {
        const poids = parseFloat(formData.get(field.name)) || 0;
        if (poids > 0) {
            const prix = calculerPrixLavageOrdinaire(poids, field.temp);
            prixLavageTotal += prix;
            poidsOrdinaireTotal += poids;
            poidsGrandTotal += poids;
            detailsPoids.push({
                label: field.label,
                poids: poids,
                temperature: field.temp,
                prix: prix,
                type: 'ordinaire'
            });
        }
    });
    
    // SAUVEGARDER LE PRIX DE LAVAGE AVANT RÉDUCTION
    const prixLavageSansReduction = prixLavageTotal;
    
    // RÉDUCTION FIDÉLITÉ : ptf > 10 ET ptf % 10 === 1
    const pointsFidelite = getPointsFidelite();
    let reductionFidelite = 0;
    if (pointsFidelite > 10 && pointsFidelite % 10 === 1) {
        reductionFidelite = 2500;
    }
    
    // PRIX SÉCHAGE
    const prixSechage = calculerPrixSechage(poidsGrandTotal);
    
    // PRIX PLIAGE
    const prixPliage = calculerPrixPliage(poidsGrandTotal);
    
    // PRIX REPASSAGE
    const prixRepassage = calculerPrixRepassage(poidsVolumineuxTotal, poidsOrdinaireTotal);
    
    // PRIX COLLECTE/LIVRAISON (2 communes)
    const commune1 = formData.get('communeCollecte');
    const commune2 = formData.get('communeLivraison');
    const prixCollecte = (tarifsCommunePrix[commune1] || 0) + (tarifsCommunePrix[commune2] || 0);
    
    // TOTAL : on applique la réduction sur le total final
    const totalAvantReduction = prixLavageSansReduction + prixSechage + prixPliage + prixRepassage + prixCollecte;
    const total = Math.max(0, totalAvantReduction - reductionFidelite);
    
    // Mettre à jour l'affichage
    document.getElementById('prixLavageOutput').textContent = prixLavageSansReduction.toLocaleString();
    document.getElementById('prixSechageOutput').textContent = prixSechage.toLocaleString();
    document.getElementById('prixPliageOutput').textContent = prixPliage.toLocaleString();
    document.getElementById('prixRepassageOutput').textContent = prixRepassage.toLocaleString();
    document.getElementById('prixCollecteOutput').textContent = prixCollecte.toLocaleString();
    document.getElementById('totalPayerOutput').textContent = total.toLocaleString();
    
    // Afficher la réduction fidélité si applicable
    const reductionElement = document.getElementById('reductionFidelite');
    if (reductionElement) {
        if (reductionFidelite > 0) {
            reductionElement.style.display = 'flex';
            const spanElement = reductionElement.querySelector('span:nth-child(2)');
            if (spanElement) {
                spanElement.textContent = '-' + reductionFidelite.toLocaleString();
            }
        } else {
            reductionElement.style.display = 'none';
        }
    }
    
    return {
        prixLavage: prixLavageSansReduction,
        prixSechage: prixSechage,
        prixPliage: prixPliage,
        prixRepassage: prixRepassage,
        prixCollecte: prixCollecte,
        total: total,
        detailsPoids: detailsPoids,
        poidsTotal: poidsGrandTotal,
        reductionFidelite: reductionFidelite,
        pointsFidelite: pointsFidelite
    };
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('commandeForm');
    
    // Charger les points de fidélité de l'utilisateur
    loadUserPoints();
    
    // Définir la date minimale comme aujourd'hui
    const today = new Date().toISOString().split('T')[0];
    
    const dateCollecte = document.getElementById('dateCollecte');
    const dateLivraison = document.getElementById('dateLivraison');
    
    if (dateCollecte) {
        dateCollecte.setAttribute('min', today);
    }
    
    if (dateLivraison) {
        dateLivraison.setAttribute('min', today);
    }
    
    // Mettre à jour la date min de livraison quand la date de collecte change
    if (dateCollecte && dateLivraison) {
        dateCollecte.addEventListener('change', function() {
            const collecteDate = this.value;
            if (collecteDate) {
                // La date de livraison doit être au minimum le jour après la collecte
                const nextDay = new Date(collecteDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const minDeliveryDate = nextDay.toISOString().split('T')[0];
                dateLivraison.setAttribute('min', minDeliveryDate);
                
                // Si la date de livraison actuelle est antérieure, la réinitialiser
                if (dateLivraison.value && dateLivraison.value <= collecteDate) {
                    dateLivraison.value = '';
                }
            }
        });
    }
    
    // Écouter tous les changements dans le formulaire pour recalculer
    form.addEventListener('input', calculerPrixTotal);
    form.addEventListener('change', calculerPrixTotal);
    
    // Calculer initialement
    calculerPrixTotal();
    
    // Gérer la soumission du formulaire
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const formData = new FormData(form);
        const prix = calculerPrixTotal();
        
        // Vérifier qu'au moins un poids est renseigné
        if (prix.prixLavage === 0 && prix.reductionFidelite === 0) {
            alert('Veuillez renseigner au moins un poids de linge.');
            return;
        }
        
        // Vérifier que les dates sont valides et non antérieures à aujourd'hui
        const aujourdhui = new Date();
        aujourdhui.setHours(0, 0, 0, 0);
        
        const dateCollecteValue = formData.get('dateCollecte');
        const dateLivraisonValue = formData.get('dateLivraison');
        
        if (!dateCollecteValue || !dateLivraisonValue) {
            alert('Veuillez renseigner les dates de collecte et de livraison.');
            return;
        }
        
        const dateCollecte = new Date(dateCollecteValue);
        const dateLivraison = new Date(dateLivraisonValue);
        
        // Vérifier que les dates ne sont pas antérieures à aujourd'hui
        if (dateCollecte < aujourdhui) {
            alert('La date de collecte ne peut pas être antérieure à la date du jour.');
            return;
        }
        
        if (dateLivraison < aujourdhui) {
            alert('La date de livraison ne peut pas être antérieure à la date du jour.');
            return;
        }
        
        // Vérifier que la date de livraison est après la date de collecte
        if (dateLivraison <= dateCollecte) {
            alert('La date de livraison doit être après la date de collecte.');
            return;
        }
        
        // Préparer les données pour l'envoi
        const orderData = {
            nomClient: formData.get('nomClient'),
            telephone: formData.get('telephone'),
            adresseCollecte: formData.get('adresseCollecte'),
            dateCollecte: formData.get('dateCollecte'),
            communeCollecte: formData.get('communeCollecte'),
            adresseLivraison: formData.get('adresseLivraison'),
            dateLivraison: formData.get('dateLivraison'),
            communeLivraison: formData.get('communeLivraison'),
            poids: {},
            prixLavage: prix.prixLavage,
            prixSechage: prix.prixSechage,
            prixPliage: prix.prixPliage,
            prixRepassage: prix.prixRepassage,
            prixCollecte: prix.prixCollecte,
            total: prix.total,
            detailsPoids: JSON.stringify(prix.detailsPoids),
            poidsTotal: prix.poidsTotal,
            reductionFidelite: prix.reductionFidelite,
            pointsFidelite: prix.pointsFidelite,
            paiement: formData.get('paiement')
        };
        
        // Ajouter tous les poids
        const poidsFields = [
            'a1_chaud', 'a1_tiede', 'a1_froid',
            'a2_chaud', 'a2_tiede', 'a2_froid',
            'b1_chaud', 'b1_tiede', 'b1_froid',
            'b2_chaud', 'b2_tiede', 'b2_froid',
            'c1_chaud', 'c1_tiede', 'c1_froid',
            'c2_chaud', 'c2_tiede', 'c2_froid'
        ];
        
        poidsFields.forEach(field => {
            const value = parseFloat(formData.get(field)) || 0;
            orderData.poids[field] = value;
        });
        
        // Envoyer la commande au serveur
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
                // Rediriger vers la page de paiement
                const paymentUrl = `payment.php?orderId=${data.orderId}&orderNumber=${encodeURIComponent(data.orderNumber)}&total=${prix.total}&lavage=${prix.prixLavage}&sechage=${prix.prixSechage}&pliage=${prix.prixPliage}&repassage=${prix.prixRepassage}&collecte=${prix.prixCollecte}&method=${orderData.paiement}`;
                window.location.href = paymentUrl;
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de l\'enregistrement de la commande.');
        });
    });
});