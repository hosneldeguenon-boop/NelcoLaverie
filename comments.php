<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commentaires Clients</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .navbar {
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            padding: 15px 30px;
            box-shadow: 0 4px 12px rgba(59,130,246,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            color: #fff;
            margin: 0;
            font-size: 22px;
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 15px;
            background-color: rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .navbar a:hover {
            background-color: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
        }

        .modal-close {
            float: right;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: #333;
        }

        .input-box {
            width: 100%;
            margin-bottom: 15px;
        }

        .input-box input,
        .input-box textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 13px;
            transition: border-color 0.3s ease;
        }

        .input-box input:focus,
        .input-box textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 8px rgba(59,130,246,0.2);
        }

        .input-box textarea {
            resize: vertical;
            min-height: 100px;
            max-height: 200px;
        }

        .star-rating {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 28px;
        }

        .star {
            cursor: pointer;
            color: #ccc;
            transition: all 0.2s ease;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .star:hover,
        .star.selected {
            color: #ffc107;
            transform: scale(1.15);
            text-shadow: 0 2px 4px rgba(255,193,7,0.3);
        }

        .char-count {
            font-size: 11px;
            color: #999;
            margin-top: 3px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .submit-btn {
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            color: #fff;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }

        .cancel-btn {
            background-color: #f0f0f0;
            color: #333;
        }

        .cancel-btn:hover {
            background-color: #e0e0e0;
        }

        .alert {
            background-color: rgba(255,193,7,0.1);
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.error {
            background-color: rgba(255,107,107,0.1);
            border-color: #ff6b6b;
            color: #c92a2a;
        }

        .alert.success {
            background-color: rgba(76,175,80,0.1);
            border-color: #4caf50;
            color: #2e7d32;
        }

        .comments-section {
            margin-top: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
        }

        .write-comment-btn {
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .write-comment-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }

        .comment-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .comment-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .comment-user {
            font-weight: 700;
            color: #333;
            font-size: 14px;
        }

        .comment-meta {
            display: flex;
            gap: 15px;
            align-items: center;
            font-size: 12px;
        }

        .comment-date {
            color: #999;
        }

        .comment-rating {
            display: flex;
            gap: 2px;
        }

        .star-small {
            color: #ffc107;
            font-size: 12px;
        }

        .comment-text {
            color: #555;
            line-height: 1.6;
            font-size: 13px;
            word-break: break-word;
        }

        .comment-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #eee;
        }

        .edit-btn,
        .delete-btn {
            background-color: transparent;
            border: 1px solid #ddd;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .edit-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .delete-btn:hover {
            border-color: #ff6b6b;
            color: #ff6b6b;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><i class="fas fa-comments"></i> Commentaires Clients</h1>
        <a href="index.html">← Retour</a>
    </div>

    <div class="container">
        <div class="alert" id="alertBox"></div>

        <div class="comments-section">
            <div class="section-header">
                <h2 class="section-title">Avis des Clients</h2>
                <button class="write-comment-btn" onclick="openCommentModal()">
                    <i class="fas fa-pen"></i> Écrire un avis
                </button>
            </div>

            <div id="commentsContainer" class="loading">
                <p>Chargement des commentaires...</p>
            </div>
        </div>
    </div>

    <div class="modal" id="commentModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeCommentModal()">&times;</span>
            <div class="modal-header">Ajouter un Avis</div>
            
            <div class="alert" id="modalAlert"></div>

            <div id="authForm" style="display: none;">
                <p style="margin-bottom: 15px; font-size: 13px; color: #666;">
                    Vous devez être connecté pour laisser un avis.
                </p>
                <div class="input-box">
                    <input type="email" id="modalEmail" placeholder="Email" required>
                </div>
                <div class="input-box">
                    <input type="password" id="modalPassword" placeholder="Mot de passe" required>
                </div>
                <button class="modal-btn submit-btn" onclick="authenticateUser()">
                    Se connecter
                </button>
            </div>

            <div id="commentForm" style="display: none;">
                <div class="input-box">
                    <label style="font-size: 12px; font-weight: 600; color: #666; display: block; margin-bottom: 8px;">
                        Votre Note (optionnel)
                    </label>
                    <div class="star-rating" id="starRating">
                        <span class="star" data-value="1">★</span>
                        <span class="star" data-value="2">★</span>
                        <span class="star" data-value="3">★</span>
                        <span class="star" data-value="4">★</span>
                        <span class="star" data-value="5">★</span>
                    </div>
                </div>

                <div class="input-box">
                    <label style="font-size: 12px; font-weight: 600; color: #666; display: block; margin-bottom: 8px;">
                        Votre Avis
                    </label>
                    <textarea id="commentText" placeholder="Partagez votre expérience..." maxlength="500" required></textarea>
                    <div class="char-count"><span id="charCount">0</span>/500</div>
                </div>

                <div class="modal-actions">
                    <button class="modal-btn cancel-btn" onclick="closeCommentModal()">Annuler</button>
                    <button class="modal-btn submit-btn" id="submitCommentBtn" onclick="submitComment()">Publier</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentRating = 0;
        let currentUserId = null;
        let editingCommentId = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadComments();
            setupStarRating();
            setupCharCount();
            checkUserAuth();
        });

        function checkUserAuth() {
            fetch('check_user_auth.php')
                .then(response => response.json())
                .then(data => {
                    currentUserId = data.user_id || null;
                    if (currentUserId) {
                        document.getElementById('authForm').style.display = 'none';
                    }
                });
        }

               // ============================================
// Remplacez la fonction loadComments() dans comments.php
// ============================================

function loadComments() {
    console.log('loadComments() appelée');
    
    const container = document.getElementById('commentsContainer');
    if (!container) {
        console.error('Container introuvable');
        return;
    }

    container.innerHTML = '<div class="loading"><p>Chargement des commentaires...</p></div>';

    fetch('get_comments.php')
        .then(response => {
            console.log('Réponse reçue:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Données reçues:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Erreur inconnue');
            }

            const comments = data.comments || [];
            console.log('Nombre de commentaires:', comments.length);

            if (comments.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-comment-slash"></i><p>Aucun commentaire pour le moment</p></div>';
                return;
            }

            // Construire le HTML des commentaires
            const commentsHTML = comments.map(comment => {
                // Formatage de la date
                const date = new Date(comment.created_at);
                const dateStr = date.toLocaleDateString('fr-FR', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                // Affichage des étoiles si note existe
                const rating = comment.rating && parseInt(comment.rating) > 0 ? 
                    '<div class="comment-rating">' + 
                    Array(parseInt(comment.rating)).fill('<i class="fas fa-star star-small"></i>').join('') + 
                    '</div>' : '';

                // Boutons d'action si l'utilisateur est propriétaire
                const isOwner = currentUserId && parseInt(currentUserId) === parseInt(comment.user_id);
                const actions = isOwner ? `
                    <div class="comment-actions">
                        <button class="edit-btn" onclick="editComment(${comment.id}, '${comment.comment_text.replace(/'/g, "\\'")}', ${comment.rating || 0})">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <button class="delete-btn" onclick="deleteComment(${comment.id})">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                ` : '';

                return `
                    <div class="comment-card">
                        <div class="comment-header">
                            <div>
                                <div class="comment-user">${comment.firstname} ${comment.lastname}</div>
                                <div class="comment-meta">
                                    <span class="comment-date">${dateStr}</span>
                                    ${rating}
                                </div>
                            </div>
                        </div>
                        <div class="comment-text">${escapeHtml(comment.comment_text)}</div>
                        ${actions}
                    </div>
                `;
            }).join('');

            container.innerHTML = commentsHTML;
            console.log('Commentaires affichés avec succès');

        })
        .catch(error => {
            console.error('Erreur complète:', error);
            container.innerHTML = `<div class="empty-state"><p>Erreur: ${error.message}</p></div>`;
        });
}

// ============================================
// Fonction utilitaire pour échapper le HTML
// ============================================

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
        function openCommentModal() {
            const modal = document.getElementById('commentModal');
            modal.classList.add('active');
            resetForm();
            
            if (currentUserId) {
                document.getElementById('authForm').style.display = 'none';
                document.getElementById('commentForm').style.display = 'block';
            } else {
                document.getElementById('authForm').style.display = 'block';
                document.getElementById('commentForm').style.display = 'none';
            }
        }

        function closeCommentModal() {
            document.getElementById('commentModal').classList.remove('active');
            resetForm();
        }

        function resetForm() {
            document.getElementById('commentText').value = '';
            currentRating = 0;
            editingCommentId = null;
            document.querySelectorAll('#starRating .star').forEach(star => {
                star.classList.remove('selected');
            });
            document.getElementById('charCount').textContent = '0';
            document.getElementById('modalAlert').style.display = 'none';
            document.getElementById('submitCommentBtn').textContent = 'Publier';
        }

        function setupStarRating() {
            const stars = document.querySelectorAll('#starRating .star');
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    currentRating = parseInt(this.dataset.value);
                    stars.forEach((s, index) => {
                        s.classList.toggle('selected', index < currentRating);
                    });
                });
            });
        }

        function setupCharCount() {
            const textarea = document.getElementById('commentText');
            textarea.addEventListener('input', function() {
                document.getElementById('charCount').textContent = this.value.length;
            });
        }

             function authenticateUser() {
    const email = document.getElementById('modalEmail').value;
    const password = document.getElementById('modalPassword').value;

    fetch('login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password, remember: false })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // ✓ Vérifier les points
            if (!data.user.points || data.user.points <= 0) {
                showAlert('❌ Vous devez passer au moins une commande pour commenter', 'error', true);
                return;
            }
            
            currentUserId = data.user.id;
            document.getElementById('authForm').style.display = 'none';
            document.getElementById('commentForm').style.display = 'block';
            showAlert('✓ Connecté avec succès !', 'success', true);
        } else {
            showAlert('Email ou mot de passe incorrect', 'error', true);
        }
    })
    .catch(error => {
        showAlert('Erreur de connexion', 'error', true);
    });
}

         // ============================================
// FICHIER: Dans comments.php - Remplacez la fonction submitComment()
// ============================================

function submitComment() {
    const text = document.getElementById('commentText').value.trim();

    // Validation basique
    if (!text) {
        showAlert('Veuillez écrire un avis', 'error', true);
        return;
    }

    if (!currentUserId) {
        showAlert('Vous devez être connecté pour publier', 'error', true);
        return;
    }

    if (text.length < 10) {
        showAlert('L\'avis doit contenir au moins 10 caractères', 'error', true);
        return;
    }

    if (text.length > 500) {
        showAlert('L\'avis ne peut pas dépasser 500 caractères', 'error', true);
        return;
    }

    // Désactiver le bouton pour éviter les clics multiples
    const submitBtn = document.getElementById('submitCommentBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Envoi en cours...';

    const payload = {
        comment_text: text,
        rating: currentRating > 0 ? currentRating : null
    };

    if (editingCommentId) {
        payload.comment_id = editingCommentId;
    }

    const endpoint = editingCommentId ? 'update_comment.php' : 'add_comment.php';

    console.log('Envoi vers:', endpoint);
    console.log('Données:', payload);

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        console.log('Réponse reçue:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Données reçues:', data);
        
        submitBtn.disabled = false;
        submitBtn.textContent = editingCommentId ? 'Mettre à jour' : 'Publier';

        if (data.success) {
            showAlert(editingCommentId ? '✓ Avis modifié avec succès !' : '✓ Avis publié avec succès, merci !', 'success', true);
            setTimeout(() => {
                closeCommentModal();
                loadComments();
            }, 1500);
        } else {
            showAlert('Erreur : ' + (data.message || 'Erreur inconnue'), 'error', true);
        }
    })
    .catch(error => {
        console.error('Erreur complète:', error);
        submitBtn.disabled = false;
        submitBtn.textContent = editingCommentId ? 'Mettre à jour' : 'Publier';
        showAlert('Erreur : ' + error.message, 'error', true);
    });
}

// ============================================
// BONUS: Améliorer la fonction showAlert()
// ============================================

function showAlert(message, type = 'error', isModal = false) {
    const alert = isModal ? document.getElementById('modalAlert') : document.getElementById('alertBox');
    
    if (!alert) {
        console.error('Élément alert non trouvé');
        return;
    }
    
    alert.textContent = message;
    alert.className = 'alert ' + type;
    alert.style.display = 'block';
    
    console.log('Alerte affichée:', message, type);
    
    if (type === 'success') {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 4000);
    }
}
       // ============================================
// Remplacez ces deux fonctions dans comments.php
// ============================================

function editComment(commentId, text, rating) {
    console.log('editComment appelée:', commentId, text, rating);
    
    editingCommentId = commentId;
    document.getElementById('commentText').value = text;
    currentRating = parseInt(rating) || 0;
    
    // Mettre à jour les étoiles visuellement
    const stars = document.querySelectorAll('#starRating .star');
    stars.forEach((star, index) => {
        star.classList.remove('selected');
        if (index < currentRating) {
            star.classList.add('selected');
        }
    });

    document.getElementById('charCount').textContent = text.length;
    document.getElementById('submitCommentBtn').textContent = 'Mettre à jour';
    
    // Ouvrir la modal
    openCommentModal();
}

function deleteComment(commentId) {
    console.log('deleteComment appelée:', commentId);
    
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')) {
        return;
    }

    fetch('delete_user_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ comment_id: commentId })
    })
    .then(response => {
        console.log('Réponse delete:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Réponse reçue:', data);
        
        if (data.success) {
            showAlert('✓ Avis supprimé avec succès', 'success');
            setTimeout(() => {
                loadComments();
            }, 1000);
        } else {
            showAlert('Erreur : ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la suppression', 'error');
    });
}

// ============================================
// Améliorez aussi submitComment() pour gérer les updates
// ============================================

function submitComment() {
    const text = document.getElementById('commentText').value.trim();

    // Validation basique
    if (!text) {
        showAlert('Veuillez écrire un avis', 'error', true);
        return;
    }

    if (!currentUserId) {
        showAlert('Vous devez être connecté pour publier', 'error', true);
        return;
    }

    if (text.length < 10) {
        showAlert('L\'avis doit contenir au moins 10 caractères', 'error', true);
        return;
    }

    if (text.length > 500) {
        showAlert('L\'avis ne peut pas dépasser 500 caractères', 'error', true);
        return;
    }

    // Désactiver le bouton pour éviter les clics multiples
    const submitBtn = document.getElementById('submitCommentBtn');
    submitBtn.disabled = true;
    
    const isEditing = editingCommentId !== null;
    submitBtn.textContent = isEditing ? 'Mise à jour en cours...' : 'Envoi en cours...';

    const payload = {
        comment_text: text,
        rating: currentRating > 0 ? currentRating : null
    };

    // Si on modifie, ajouter l'ID
    if (isEditing) {
        payload.comment_id = editingCommentId;
    }

    const endpoint = isEditing ? 'update_comment.php' : 'add_comment.php';

    console.log('Envoi vers:', endpoint);
    console.log('Données:', payload);
    console.log('Mode édition:', isEditing);

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        console.log('Réponse reçue:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Données reçues:', data);
        
        submitBtn.disabled = false;
        submitBtn.textContent = editingCommentId ? 'Mettre à jour' : 'Publier';

        if (data.success) {
            const message = isEditing ? 
                '✓ Avis modifié avec succès !' : 
                '✓ Avis publié avec succès, merci !';
            showAlert(message, 'success', true);
            
            setTimeout(() => {
                closeCommentModal();
                loadComments();
            }, 1500);
        } else {
            showAlert('Erreur : ' + (data.message || 'Erreur inconnue'), 'error', true);
        }
    })
    .catch(error => {
        console.error('Erreur complète:', error);
        submitBtn.disabled = false;
        submitBtn.textContent = editingCommentId ? 'Mettre à jour' : 'Publier';
        showAlert('Erreur : ' + error.message, 'error', true);
    });
}

// ============================================
// Améliorez resetForm() pour bien réinitialiser
// ============================================

function resetForm() {
    console.log('resetForm appelée');
    
    document.getElementById('commentText').value = '';
    currentRating = 0;
    editingCommentId = null;
    
    document.querySelectorAll('#starRating .star').forEach(star => {
        star.classList.remove('selected');
    });
    
    document.getElementById('charCount').textContent = '0';
    document.getElementById('modalAlert').style.display = 'none';
    document.getElementById('submitCommentBtn').textContent = 'Publier';
    document.getElementById('submitCommentBtn').disabled = false;
}

                function showAlert(message, type = 'error', isModal = false) {
    const alert = isModal ? document.getElementById('modalAlert') : document.getElementById('alertBox');
    alert.innerHTML = message; // Permet le HTML/emojis
    alert.className = 'alert ' + type;
    alert.style.display = 'block';
    
    if (type === 'success') {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 3000);
    }
}

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('commentModal');
            if (event.target == modal) {
                closeCommentModal();
            }
        });
    </script>
</body>
</html>