<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        html, body {
            height: 100%;
        }

        body {
            background-image: url('img1.png');
            background-color: #dceffb;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        section {
            background-color: rgba(0,0,0,0.25);
            border: 2px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            padding: 30px;
            width: 100%;
            max-width: 500px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        section h1 {
            font-size: 28px;
            text-align: center;
            color: #fff;
            margin-bottom: 25px;
        }

        .input-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .input-row.full {
            grid-template-columns: 1fr;
        }

        .input-box {
            position: relative;
            width: 100%;
        }

        .input-box input,
        .input-box select {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border-radius: 20px;
            outline: none;
            background-color: rgba(255,255,255,0.04);
            border: 2px solid rgba(255,255,255,0.15);
            color: #fff;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .input-box input::placeholder {
            color: rgba(255,255,255,0.85);
        }

        .input-box select {
            cursor: pointer;
        }

        .input-box select option {
            background-color: #333;
            color: #fff;
        }

        .input-box input:focus,
        .input-box select:focus {
            border-color: rgba(96,165,250,0.8);
            box-shadow: 0 0 10px rgba(96,165,250,0.2);
        }

        .input-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #fff;
            font-size: 14px;
            pointer-events: none;
        }

        .input-box i.toggle-password {
            pointer-events: all;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .input-box i.toggle-password:hover {
            color: #60a5fa;
        }

        .error-message {
            color: #ff6b6b;
            font-size: 11px;
            margin-top: 3px;
            margin-left: 10px;
            display: none;
        }

        .signup-btn {
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            outline: none;
            border: 0;
            font-weight: 700;
            cursor: pointer;
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            color: #fff;
            transition: 0.25s;
            box-shadow: 0 6px 18px rgba(96,165,250,0.25);
            margin-top: 15px;
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(96,165,250,0.35);
        }

        .signup-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
            color: #fff;
            font-size: 13px;
        }

        .login-link a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: 0.25s;
        }

        .login-link a:hover {
            text-decoration: underline;
            opacity: 0.9;
        }

        .alert-message {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: none;
            font-size: 13px;
        }

        .alert-success {
            background-color: rgba(76,175,80,0.3);
            border: 2px solid rgba(76,175,80,0.6);
            color: #a5d6a7;
        }

        .alert-error {
            background-color: rgba(255,107,107,0.2);
            border: 2px solid rgba(255,107,107,0.6);
            color: #ffaaaa;
        }
    </style>
</head>
<body>
    <section>
        <h1>Inscription Admin</h1>
        <div class="alert-message alert-success" id="successMessage"></div>
        <div class="alert-message alert-error" id="errorMessage"></div>

        <form id="signupForm">
            <div class="input-row">
                <div class="input-box">
                    <input type="text" id="lastname" placeholder="Nom" required>
                    <i class="fas fa-user"></i>
                </div>
                <div class="input-box">
                    <input type="text" id="firstname" placeholder="Prénom" required>
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <div class="input-row full">
                <div class="input-box">
                    <input type="text" id="username" placeholder="Pseudonyme" required>
                    <i class="fas fa-at"></i>
                </div>
            </div>

            <div class="input-row full">
                <div class="input-box">
                    <input type="email" id="email" placeholder="Email" required>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="input-row full">
                <div class="input-box">
                    <input type="tel" id="phone" placeholder="Numéro de téléphone" required>
                    <i class="fas fa-phone"></i>
                </div>
            </div>

            <div class="input-row full">
                <div class="input-box">
                    <select id="gender" required>
                        <option value="">-- Sélectionner le sexe --</option>
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                    </select>
                    <i class="fas fa-venus-mars"></i>
                </div>
            </div>

            <div class="input-row full">
                <div class="input-box">
                    <input type="password" id="password" placeholder="Mot de passe (minimum 8 caractères)" required>
                    <i class="fas fa-lock"></i>
                    <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" class="signup-btn" id="submitBtn">S'inscrire</button>

            <div class="login-link">
                Déjà inscrit ? <a href="admin_login.php">Se connecter</a>
            </div>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('signupForm');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const submitBtn = document.getElementById('submitBtn');
            const successMsg = document.getElementById('successMessage');
            const errorMsg = document.getElementById('errorMessage');

            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
                this.classList.toggle('fa-eye');
            });

            // Form submission
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                // Validation côté client
                const lastname = document.getElementById('lastname').value.trim();
                const firstname = document.getElementById('firstname').value.trim();
                const username = document.getElementById('username').value.trim();
                const email = document.getElementById('email').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const gender = document.getElementById('gender').value;
                const password = document.getElementById('password').value;

                // Vérifications
                if (!lastname || !firstname || !username || !email || !phone || !gender || !password) {
                    showError('Tous les champs sont obligatoires');
                    return;
                }

                if (password.length < 8) {
                    showError('Le mot de passe doit contenir au moins 8 caractères');
                    return;
                }

                if (!isValidEmail(email)) {
                    showError('Email invalide');
                    return;
                }

                if (!isValidPhone(phone)) {
                    showError('Numéro de téléphone invalide (minimum 10 chiffres)');
                    return;
                }

                // Désactiver le bouton
                submitBtn.disabled = true;
                submitBtn.textContent = 'Inscription en cours...';
                hideMessages();

                // Envoyer les données
                const formData = {
                    lastname: lastname,
                    firstname: firstname,
                    username: username,
                    email: email,
                    phone: phone,
                    gender: gender,
                    password: password
                };

                console.log('Envoi vers admin_signup_process.php:', formData);

                fetch('admin_signup_process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    console.log('Réponse reçue:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Données reçues:', data);
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'S\'inscrire';

                    if (data.success) {
                        showSuccess(data.message);
                        form.reset();
                        setTimeout(() => {
                            window.location.href = 'admin_login.php';
                        }, 2000);
                    } else {
                        showError(data.message || 'Erreur lors de l\'inscription');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'S\'inscrire';
                    showError('Erreur de connexion : ' + error.message);
                });
            });

            function showSuccess(message) {
                successMsg.textContent = '✓ ' + message;
                successMsg.style.display = 'block';
                errorMsg.style.display = 'none';
            }

            function showError(message) {
                errorMsg.textContent = '✗ ' + message;
                errorMsg.style.display = 'block';
                successMsg.style.display = 'none';
            }

            function hideMessages() {
                successMsg.style.display = 'none';
                errorMsg.style.display = 'none';
            }

            function isValidEmail(email) {
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return regex.test(email);
            }

            function isValidPhone(phone) {
                const regex = /^[0-9]{10,}$/;
                return regex.test(phone.replace(/[\s\-\.]/g, ''));
            }
        });
    </script>
</body>
</html>