<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        html, body {
            height: 100%;
        }

        body{
            background-image: url('img1.png');
            background-color: #dceffb;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px 0;
        }

        section{
            background-color: rgba(0,0,0,0.25);
            border: 2px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            padding: 30px;
            width: 450px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            margin: 20px 0;
        }

        section h1{
            font-size: 30px;
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }

        .input-box{
            width: 100%;
            margin-bottom: 20px;
            position: relative;
        }
        
        .input-box input,
        .input-box select{
            width: 100%;
            padding: 15px 50px 15px 20px;
            border-radius: 25px;
            outline: none;
            background-color: rgba(255,255,255,0.04);
            border: 2px solid rgba(255,255,255,0.15);
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-box select option {
            background-color: #1a1a2e;
            color: #fff;
        }

        .input-box input:focus,
        .input-box select:focus {
            border-color: rgba(96,165,250,0.8);
            box-shadow: 0 0 10px rgba(96,165,250,0.2);
        }

        .input-box input.error {
            border-color: #ff6b6b;
            box-shadow: 0 0 10px rgba(255,107,107,0.2);
        }

        .input-box input::placeholder{
            color: rgba(255,255,255,0.85);
        }

        .input-box i{
            position: absolute;
            transform: translateY(-50%);
            right: 20px;
            top: 50%;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .input-box i.fa-eye-slash,
        .input-box i.fa-eye {
            right: 45px;
        }

        .input-box i.fa-lock,
        .input-box i.fa-user,
        .input-box i.fa-envelope,
        .input-box i.fa-phone,
        .input-box i.fa-map-marker-alt,
        .input-box i.fa-venus-mars {
            pointer-events: none;
        }

        .input-box i:hover {
            color: #60a5fa;
        }

        .name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .name-row .input-box {
            margin-bottom: 0;
        }

        .terms{
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            font-size: 14px;
            color:#fff;
            margin-bottom: 25px;
        }
        .terms label {
            display: flex;
            align-items: flex-start;
            cursor: pointer;
        }
        .terms label input[type="checkbox"]{
            margin-right: 8px;
            margin-top: 3px;
        }
        .terms a{
            color: #fff;
            text-decoration: none;
            transition: 0.25s;
            font-weight: 600;
            margin-left: 10px;
        }
        .terms a:hover{
            text-decoration: underline;
            opacity: 0.9;
        }

        .login-link{
            text-align: center;
            margin-top: 18px;
            color: #fff;
            font-size: 14px;
        }
        .login-link a{
            color: #fff;
            text-decoration: none;
            transition: 0.25s;
            font-weight: 600;
        }
        .login-link a:hover{
            text-decoration: underline;
            opacity: 0.9;
        }

        .signup-btn{
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            outline: none;
            border: 0;
            font-weight: 700;
            cursor: pointer;
            background: linear-gradient(90deg,#3b82f6,#60a5fa);
            color: #fff;
            transition: 0.25s;
            box-shadow: 0 6px 18px rgba(96,165,250,0.25);
        }
        .signup-btn:hover{
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(96,165,250,0.35);
        }

        .password-requirements {
            color: rgba(255,255,255,0.8);
            font-size: 12px;
            margin-top: 5px;
            margin-left: 15px;
        }

        .error-message {
            color: #ff6b6b;
            font-size: 12px;
            margin-top: 5px;
            margin-left: 15px;
            display: none;
        }

        .password-field {
            position: relative;
        }

        .phone-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .phone-row .input-box {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <section>
        <h1>Créer un compte</h1>
        <form id="signupForm">
            <div class="name-row">
                <div class="input-box">
                    <input type="text" id="lastname" placeholder="Nom" required>
                    <i class="fas fa-user"></i>
                </div>
                
                <div class="input-box">
                    <input type="text" id="firstname" placeholder="Prénom" required>
                    <i class="fas fa-user"></i>
                </div>
            </div>
            
            <div class="input-box">
                <input type="email" id="email" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>

            <div class="phone-row">
                <div class="input-box">
                    <input type="tel" id="phone" placeholder="Téléphone" required pattern="[0-9+\s\-()]{8,}">
                    <i class="fas fa-phone"></i>
                </div>
                
                <div class="input-box">
                    <input type="tel" id="whatsapp" placeholder="WhatsApp" required pattern="[0-9+\s\-()]{8,}">
                    <i class="fab fa-whatsapp"></i>
                </div>
            </div>

            <div class="input-box">
                <input type="text" id="address" placeholder="Adresse de livraison" required>
                <i class="fas fa-map-marker-alt"></i>
            </div>

            <div class="input-box">
                <select id="gender" required>
                    <option value="" disabled selected>Genre</option>
                    <option value="homme">Homme</option>
                    <option value="femme">Femme</option>
                </select>
                <i class="fas fa-venus-mars"></i>
            </div>
            
            <div class="input-box password-field">
                <input type="password" id="password" placeholder="Mot de passe" required>
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                <div class="password-requirements">8 caractères minimum</div>
            </div>
            
            <div class="input-box password-field">
                <input type="password" id="confirmPassword" placeholder="Confirmer le mot de passe" required>
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye-slash toggle-password" id="toggleConfirmPassword"></i>
                <div class="error-message" id="passwordError">Les mots de passe ne correspondent pas</div>
            </div>
            
            <div class="terms">
                <label>
                    <input type="checkbox" id="terms" required>
                    J'accepte les <a href="#">conditions d'utilisation</a>
                </label>
            </div>
            
            <button type="submit" class="signup-btn">S'inscrire</button>
            
            <div class="login-link">
                Déjà un compte ? <a href="testht.php">Se connecter</a>
            </div>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('signupForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const passwordError = document.getElementById('passwordError');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            
            function setupPasswordToggle(toggleElement, passwordField) {
                toggleElement.addEventListener('click', function() {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    this.classList.toggle('fa-eye-slash');
                    this.classList.toggle('fa-eye');
                });
            }
            
            setupPasswordToggle(togglePassword, passwordInput);
            setupPasswordToggle(toggleConfirmPassword, confirmPasswordInput);
            
            function validatePasswords() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    confirmPasswordInput.classList.add('error');
                    passwordError.style.display = 'block';
                    return false;
                } else {
                    confirmPasswordInput.classList.remove('error');
                    passwordError.style.display = 'none';
                    return true;
                }
            }
            
            confirmPasswordInput.addEventListener('input', validatePasswords);
            passwordInput.addEventListener('input', validatePasswords);
            
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                if (!validatePasswords()) {
                    confirmPasswordInput.focus();
                    return false;
                }
                
                const formData = {
                    lastname: document.getElementById('lastname').value,
                    firstname: document.getElementById('firstname').value,
                    email: document.getElementById('email').value,
                    phone: document.getElementById('phone').value,
                    whatsapp: document.getElementById('whatsapp').value,
                    address: document.getElementById('address').value,
                    gender: document.getElementById('gender').value,
                    password: document.getElementById('password').value
                };
                fetch('register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Inscription réussie ! Vous allez être redirigé vers la page de connexion.');
                        window.location.href = 'testht.php';
                    } else {
                        alert('Erreur : ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de l\'inscription.');
                });
            });
        });
    </script>
</body>
</html>