<?php
session_start();
require_once '../../translations.php';
require_once '../../config/database.php';

// Récupération de l'abonnement via GET
if (!isset($_SESSION['utilisateur_id']) || !isset($_GET['type'])) {
    header('Location: /ProjetFileRouge/Frontend/HTML/home.php');
    exit;
}

$type = $_GET['type'];
$stmt = $pdo->prepare("SELECT * FROM abonnement WHERE type = ?");
$stmt->execute([$type]);
$abonnement = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$abonnement) {
    header('Location: /ProjetFileRouge/Frontend/HTML/home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - CheckMyKicks</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ProjetFileRouge/Frontend/CSS/style.css">
    <div class="announcement-bar">
        <p>Authentifiez vos sneakers dès maintenant - Service premium disponible 24/7</p>
    </div>
    <style>
        .payment-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .payment-grid {
                grid-template-columns: 1fr;
            }
        }

        .payment-summary {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .payment-form {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .summary-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .summary-header h2 {
            color: #333;
            font-size: 24px;
            margin: 0;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: #666;
            font-weight: 500;
        }

        .summary-value {
            color: #333;
            font-weight: 600;
        }

        .summary-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f5f5f5;
            color: #222;
        }

        .form-control:focus {
            border-color: #000;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
            background: #fff;
        }

        .card-details {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 15px;
        }

        .btn-pay {
            background: #000;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-pay:hover {
            background: #333;
            transform: translateY(-2px);
        }

        .secure-payment {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .secure-payment i {
            color: #28a745;
        }

        .payment-methods {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .payment-method {
            width: 50px;
            height: 30px;
            background: #f8f9fa;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .error-message {
            color: #dc3545;
            font-size: 15px;
            font-weight: bold;
            margin-top: 7px;
            display: none;
            letter-spacing: 0.5px;
        }

        .form-control.error {
            border-color: #dc3545;
            box-shadow: 0 0 0 2px #ffe0e0;
        }

        .form-control.error + .error-message {
            display: block;
        }

        .card-type-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 28px;
            color: #222;
            opacity: 0.8;
            pointer-events: none;
        }

        .form-group {
            position: relative;
        }

        button:disabled {
            background: #666;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <div class="nav-left">
                <a href="javascript:void(0)" onclick="window.location.href='/ProjetFileRouge/Frontend/HTML/home.php#pricing'" class="nav-link"><?php echo t('Nos Tarifs'); ?></a>
                <a href="/ProjetFileRouge/Frontend/HTML/drops.php" class="nav-link"><?php echo t('Prochain Drop'); ?></a>
                <a href="/ProjetFileRouge/templates/blog.php" class="nav-link"><?php echo t('header_blog'); ?></a>
            </div>

            <div class="nav-center">
                <a href="/ProjetFileRouge/Frontend/HTML/home.php" class="logo">CheckMyKicks</a>
            </div>

            <div class="nav-right">
                <div class="nav-icons">
                    <?php if (isset($_SESSION['utilisateur_id'])): ?>
                        <a href="/ProjetFileRouge/Frontend/HTML/compte.php" class="icon-link">
                            <i class="fas fa-user"></i>
                        </a>
                        <a href="/ProjetFileRouge/Backend/auth/logout.php" class="nav-link">Déconnexion</a>
                        <a href="/ProjetFileRouge/Frontend/HTML/langues.html" class="nav-link">Langues</a>
                        <a href="/ProjetFileRouge/Frontend/HTML/spotcheck.php" class="nav-link">SpotCheck</a>
                    <?php else: ?>
                        <a href="/ProjetFileRouge/Backend/auth/login.php" class="nav-link">Connexion</a>
                        <a href="/ProjetFileRouge/Backend/auth/register.php" class="nav-link">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <div class="payment-container">
        <div class="payment-grid">
            <div class="payment-summary">
                <div class="summary-header">
                    <h2>Récapitulatif de votre commande</h2>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Type d'abonnement</span>
                    <span class="summary-value"><?php echo ucfirst($type); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Prix mensuel</span>
                    <span class="summary-value"><?php echo number_format($abonnement['prix'], 2); ?> €</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Durée</span>
                    <span class="summary-value">1 mois</span>
                </div>
                <div class="summary-item summary-total">
                    <span class="summary-label">Total à payer</span>
                    <span class="summary-value"><?php echo number_format($abonnement['prix'], 2); ?> €</span>
                </div>
                <div class="secure-payment">
                    <i class="fas fa-lock"></i>
                    <span>Paiement 100% sécurisé</span>
                </div>
                <div class="payment-methods">
                    <div class="payment-method">
                        <i class="fab fa-cc-visa"></i>
                    </div>
                    <div class="payment-method">
                        <i class="fab fa-cc-mastercard"></i>
                    </div>
                    <div class="payment-method">
                        <i class="fab fa-cc-paypal"></i>
                    </div>
                </div>
            </div>

            <div class="payment-form">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <form action="/ProjetFileRouge/Backend/paiement/traitement_abonnement.php" method="POST" id="paymentForm">
                    <input type="hidden" name="type_abonnement" value="<?php echo htmlspecialchars($type); ?>">
                    <input type="hidden" name="montant" value="<?php echo htmlspecialchars($abonnement['prix']); ?>">
                    <div class="form-group">
    <label for="nom">Nom</label>
    <input type="text" id="nom" name="nom" class="form-control" required placeholder="John Doe">
    <div class="error-message">Veuillez entrer votre nom</div>
</div>
<div class="form-group">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" class="form-control" required placeholder="john@email.com">
    <div class="error-message">Veuillez entrer un email valide</div>
</div>
<div class="form-group">
    <label for="adresse">Adresse</label>
    <input type="text" id="adresse" name="adresse" class="form-control" required placeholder="12 rue de la Paix, Paris">
    <div class="error-message">Veuillez entrer votre adresse</div>
</div>
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" required placeholder="0612345678">
                        <div class="error-message">Veuillez entrer un numéro de téléphone valide (ex: 0612345678)</div>
                    </div>
                    <div class="form-group">
                        <label for="cardNumber">Numéro de carte</label>
                        <input type="text" id="cardNumber" name="numero" class="form-control" maxlength="19" autocomplete="cc-number" required placeholder="4242 4242 4242 4242">
                        <i class="card-type-icon fab fa-cc-unknown"></i>
                        <div class="error-message">Veuillez entrer un numéro de carte valide (16 chiffres)</div>
                    </div>
                    <div class="card-details">
                        <div class="form-group">
                            <label for="expiryDate">Date d'expiration</label>
                            <input type="text" id="expiryDate" name="expiration" class="form-control" placeholder="MM/AA" maxlength="5" required>
                            <div class="error-message">Format invalide (MM/AA)</div>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" class="form-control" pattern="[0-9]{3}" maxlength="3" required placeholder="123">
                            <div class="error-message">Code de sécurité invalide</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="card_name">Nom sur la carte</label>
                        <input type="text" id="card_name" name="card_name" class="form-control" required placeholder="John Doe">
                        <div class="error-message">Veuillez entrer le nom figurant sur la carte</div>
                    </div>
                    <button type="submit" class="btn-pay">
                        Payer <?php echo number_format($abonnement['prix'], 2); ?> €
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('paymentForm');
        const cardNumber = document.getElementById('cardNumber');
        const cardName = document.getElementById('nom');
        const email = document.getElementById('email');
        const adresse = document.getElementById('adresse');
        const telephone = document.getElementById('telephone');
        const expiryDate = document.getElementById('expiryDate');
        const cvv = document.getElementById('cvv');
        const submitButton = form.querySelector('button[type="submit"]');

        if (telephone) {
            telephone.setAttribute('placeholder', '0612345678');
            telephone.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                // Autorise la saisie du 0 seul, puis du 06 ou 07, puis le reste
                if (value.length === 1 && value !== '0') value = '';
                if (value.length === 2 && !/^0[67]$/.test(value)) value = '0';
                if (value.length > 10) value = value.slice(0, 10);
                // Formatage : 06 12 34 56 78
                let formatted = '';
                for (let i = 0; i < value.length; i += 2) {
                    if (i > 0) formatted += ' ';
                    formatted += value.substr(i, 2);
                }
                e.target.value = formatted;
            });
        }

        function isValidPhone(phone) {
            return /^0[67]\d{8}$/.test(phone.replace(/\s/g, ''));
        }

        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Validation du nom
            if (cardName.value.trim().length < 3) {
                cardName.classList.add('error');
                cardName.nextElementSibling.style.display = 'block';
                isValid = false;
            } else {
                cardName.classList.remove('error');
                cardName.nextElementSibling.style.display = 'none';
            }

            // Validation de l'email
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                email.classList.add('error');
                email.nextElementSibling.style.display = 'block';
                isValid = false;
            } else {
                email.classList.remove('error');
                email.nextElementSibling.style.display = 'none';
            }

            // Validation de l'adresse
            if (adresse.value.trim().length < 10) {
                adresse.classList.add('error');
                adresse.nextElementSibling.style.display = 'block';
                isValid = false;
            } else {
                adresse.classList.remove('error');
                adresse.nextElementSibling.style.display = 'none';
            }

            // Validation du téléphone
            if (!isValidPhone(telephone.value)) {
                telephone.classList.add('error');
                telephone.nextElementSibling.style.display = 'block';
                isValid = false;
            } else {
                telephone.classList.remove('error');
                telephone.nextElementSibling.style.display = 'none';
            }

            // Validation de la carte
            if (!preg_match('/^(\d{4} ?){4}$/', str_replace(' ', '', cardNumber.value))) {
                cardNumber.classList.add('error');
                cardNumber.nextElementSibling.nextElementSibling.style.display = 'block';
                isValid = false;
            } else {
                cardNumber.classList.remove('error');
                cardNumber.nextElementSibling.nextElementSibling.style.display = 'none';
            }

            // Validation de la date d'expiration
            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', expiryDate.value)) {
                expiryDate.classList.add('error');
                expiryDate.nextElementSibling.style.display = 'block';
                isValid = false;
            } else {
                expiryDate.classList.remove('error');
                expiryDate.nextElementSibling.style.display = 'none';
            }

            // Validation du CVV
            if (!preg_match('/^\d{3}$/', cvv.value)) {
                cvv.classList.add('error');
                cvv.nextElementSibling.style.display = 'block';
                isValid = false;
            } else {
                cvv.classList.remove('error');
                cvv.nextElementSibling.style.display = 'none';
            }

            if (!isValid) {
                e.preventDefault();
                form.classList.add('shake');
                setTimeout(() => form.classList.remove('shake'), 500);
            } else {
                submitButton.disabled = true;
                submitButton.textContent = 'Traitement en cours...';
            }
        });
    });
    </script>
</body> 
</html> 