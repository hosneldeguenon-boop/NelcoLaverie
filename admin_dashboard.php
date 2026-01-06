<?php
/**
 * IMPORTANT: Ce code DOIT être au TOUT DÉBUT du fichier
 * AVANT tout HTML pour fonctionner correctement
 */

// Démarrer la session
session_start();

// Empêcher la mise en cache de la page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Rediriger vers la page de connexion
    header("Location: admin_login.php");
    exit();
}

// Vérifier aussi un timeout de session (30 minutes d'inactivité)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    // Session expirée
    session_unset();
    session_destroy();
    header("Location: admin_login.php?timeout=1");
    exit();
}

// Mettre à jour le temps de dernière activité
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin</title>
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
            background-color: #f5f5f5;
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

        .navbar h2 {
            color: #fff;
            margin: 0;
            font-size: 22px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            color: #fff;
            font-size: 13px;
        }

        .logout-btn {
            background-color: rgba(255,255,255,0.2);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.4);
            padding: 8px 15px;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 12px;
        }

        .logout-btn:hover {
            background-color: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header-with-btn {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 0;
            font-weight: 600;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 10px;
        }

        .view-btn {
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }

        .comments-grid {
            display: grid;
            gap: 20px;
        }

        .comment-card {
            background: #fff;
            border-left: 5px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .comment-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .comment-user {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .comment-date {
            font-size: 12px;
            color: #999;
        }

        .comment-rating {
            display: flex;
            gap: 3px;
            margin-bottom: 10px;
        }

        .star {
            color: #ffc107;
            font-size: 14px;
        }

        .comment-text {
            color: #555;
            line-height: 1.5;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .comment-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .delete-btn {
            background-color: #ff6b6b;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .delete-btn:hover {
            background-color: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255,107,107,0.3);
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
            padding: 40px;
            color: #999;
        }

        .spinner {
            border: 3px solid rgba(59,130,246,0.2);
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .success-message {
            background-color: rgba(76,175,80,0.1);
            border: 1px solid #4caf50;
            color: #2e7d32;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: #999;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .header-with-btn {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
        <div class="navbar-right">
            <div class="user-info">
                <div id="adminName"><?php echo htmlspecialchars($_SESSION['admin_firstname'] . ' ' . $_SESSION['admin_lastname']); ?></div>
                <div style="font-size: 11px; opacity: 0.9;">Administrateur</div>
            </div>
            <button class="logout-btn" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </button>
        </div>
    </div>

    <div class="container">
        <div class="success-message" id="successMessage"></div>

        <div class="stats" id="statsContainer">
            <div class="stat-card">
                <div class="stat-number" id="totalComments">0</div>
                <div class="stat-label">Commentaires Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="avgRating">0</div>
                <div class="stat-label">Note Moyenne</div>
            </div>
        </div>

        <div class="header-with-btn">
            <h3 class="section-title">Gestion des Commentaires</h3>
            <a href="admin/view_users.php" class="view-btn">
                <i class="fas fa-eye"></i> Voir les commandes et utilisateurs
            </a>
        </div>

        <div id="loadingContainer" class="loading" style="display: none;">
            <div class="spinner"></div>
            <p>Chargement des commentaires...</p>
        </div>

        <div id="commentsContainer" class="comments-grid"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadComments();
            setInterval(loadComments, 30000);
        });

        function loadComments() {
            const loading = document.getElementById('loadingContainer');
            const container = document.getElementById('commentsContainer');
            loading.style.display = 'block';
            container.innerHTML = '';

            fetch('get_all_comments.php')
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    
                    if (data.success) {
                        if (data.comments.length === 0) {
                            container.innerHTML = '<div class="empty-state"><i class="fas fa-comments"></i><p>Aucun commentaire pour le moment</p></div>';
                        } else {
                            const comments = data.comments;
                            container.innerHTML = comments.map(comment => {
                                const stars = comment.rating ? '<div class="comment-rating">' + 
                                    Array(comment.rating).fill('<i class="fas fa-star star"></i>').join('') + 
                                    '</div>' : '';
                                
                                const date = new Date(comment.created_at).toLocaleDateString('fr-FR', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                });
                                
                                return `
                                    <div class="comment-card">
                                        <div class="comment-header">
                                            <div>
                                                <div class="comment-user">${comment.firstname} ${comment.lastname}</div>
                                                <div class="comment-date">${date}</div>
                                            </div>
                                            <div class="comment-actions">
                                                <button class="delete-btn" onclick="deleteComment(${comment.id})">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </div>
                                        </div>
                                        ${stars}
                                        <div class="comment-text">${comment.comment_text}</div>
                                    </div>
                                `;
                            }).join('');
                            
                            document.getElementById('totalComments').textContent = data.stats.total;
                            document.getElementById('avgRating').textContent = (data.stats.average_rating || 0).toFixed(1);
                        }
                    } else {
                        container.innerHTML = '<div class="empty-state"><p>Erreur lors du chargement</p></div>';
                    }
                })
                .catch(error => {
                    loading.style.display = 'none';
                    container.innerHTML = '<div class="empty-state"><p>Erreur de connexion</p></div>';
                });
        }

        function deleteComment(commentId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
                return;
            }

            fetch('delete_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ comment_id: commentId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const successMsg = document.getElementById('successMessage');
                    successMsg.textContent = 'Commentaire supprimé avec succès';
                    successMsg.style.display = 'block';
                    setTimeout(() => {
                        successMsg.style.display = 'none';
                    }, 3000);
                    loadComments();
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue');
            });
        }

        function logout() {
            if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
                fetch('admin_logout.php', { method: 'POST' })
                    .then(() => {
                        window.location.href = 'admin_login.php';
                    });
            }
        }
    </script>
</body>
</html>