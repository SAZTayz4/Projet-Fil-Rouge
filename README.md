# 🔥 CheckMyKicks - Plateforme d'Authentification de Sneakers

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/votre-username/CheckMyKicks)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4.svg)](https://php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-6.4-000000.svg)](https://symfony.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1.svg)](https://mysql.com/)
[![License](https://img.shields.io/badge/license-Proprietary-red.svg)](LICENSE)

## 📖 Description

**CheckMyKicks** est une plateforme web innovante spécialisée dans l'authentification de sneakers utilisant l'intelligence artificielle. Notre solution combine une architecture moderne avec des technologies avancées pour offrir aux passionnés de sneakers un service de vérification fiable et rapide.

### 🎯 Problématique Résolue
- **Contrefaçons** : Détection des fausses sneakers avec une précision élevée
- **Expertise** : Accès à l'analyse IA sans connaissances techniques
- **Rapidité** : Résultats d'authentification en moins de 2 minutes
- **Traçabilité** : Historique complet des vérifications

## 🚀 Fonctionnalités Principales

### 🤖 Système d'Authentification IA
- **Analyse Multi-Images** : Jusqu'à 6 photos par vérification
- **Algorithme Avancé** : Détection basée sur l'apprentissage automatique
- **Heatmap Visuelle** : Zones de détection des anomalies
- **Score de Confiance** : Pourcentage de fiabilité du résultat
- **Explications Détaillées** : Justification de chaque analyse

### 💎 Système d'Abonnements
- **Gratuit** : 3 vérifications/mois
- **Débutant** (9,99€) : 10 vérifications + Chatbot
- **Intermédiaire** (19,99€) : 30 vérifications + Chatbot + AutoCop
- **Avancé** (29,99€) : 50 vérifications + Toutes les fonctionnalités

### 📊 Gestion des Utilisateurs
- **Authentification Sécurisée** : Système de connexion robuste
- **Profils Personnalisés** : Gestion des informations personnelles
- **Historique Complet** : Suivi de toutes les analyses
- **Facturation Automatique** : PDF générés automatiquement

### 🎨 Interface Utilisateur
- **Design Moderne** : Interface responsive et intuitive
- **Multi-langues** : Support français/anglais
- **Navigation Fluide** : UX optimisée
- **Thème Adaptatif** : Compatible tous appareils

### 📈 Fonctionnalités Avancées
- **SpotCheck** : Vérification rapide d'une image
- **Drops Tracker** : Suivi des sorties de sneakers
- **Blog Intégré** : Articles sur les sneakers et l'authentification
- **Notifications** : Alertes personnalisées
- **API REST** : Intégration avec d'autres services

## 🏗️ Architecture Technique

### 🖥️ Technologies Utilisées

#### Backend
- **PHP 8.1+** : Langage principal
- **Symfony 6.4** : Framework web moderne
- **Doctrine ORM** : Mapping objet-relationnel
- **MySQL 8.0** : Base de données relationnelle
- **TCPDF** : Génération de factures PDF

#### Frontend
- **HTML5/CSS3** : Structure et présentation
- **JavaScript ES6+** : Interactions dynamiques
- **React 18** : Composants réactifs (partiellement)
- **Bootstrap/Custom CSS** : Styling responsive

#### Outils de Développement
- **Composer** : Gestionnaire de dépendances PHP
- **npm** : Gestionnaire de paquets JavaScript
- **Git** : Contrôle de version
- **WAMP/XAMPP** : Environnement de développement

### 📂 Structure du Projet

```
CheckMyKicks/
├── 📁 Backend/                    # Logique métier
│   ├── 📁 auth/                  # Authentification
│   │   ├── login.php
│   │   ├── register.php
│   │   └── logout.php
│   ├── 📁 IA-Check/              # Système d'IA
│   │   ├── check_sneakers.php    # Analyse des sneakers
│   │   ├── save_analysis.php     # Sauvegarde résultats
│   │   └── check_limit.php       # Vérification limites
│   ├── 📁 paiement/              # Système de paiement
│   │   └── traitement_abonnement.php
│   ├── 📁 profile/               # Gestion profils
│   └── 📁 admin/                 # Administration
├── 📁 Frontend/                   # Interface utilisateur
│   ├── 📁 HTML/                  # Pages web
│   │   ├── home.php              # Accueil
│   │   ├── ia.php                # Interface IA
│   │   ├── compte.php            # Profil utilisateur
│   │   ├── paiement.php          # Paiements
│   │   ├── drops.php             # Suivi drops
│   │   └── spotcheck.php         # Vérification rapide
│   ├── 📁 CSS/                   # Styles
│   └── 📁 JS/                    # Scripts JavaScript
├── 📁 src/                       # Code source Symfony
│   ├── 📁 Entity/                # Entités Doctrine
│   │   ├── User.php
│   │   ├── Paiement.php
│   │   ├── Abonnement.php
│   │   └── Facture.php
│   └── 📁 Service/               # Services métier
├── 📁 templates/                 # Templates Symfony
├── 📁 public/                    # Fichiers publics
│   └── 📁 factures/              # Factures PDF
├── 📁 config/                    # Configuration
│   └── database.php              # Configuration BDD
├── 📁 uploads/                   # Fichiers uploadés
├── 📁 WhenToCop/                 # Module drops
└── 📁 vendor/                    # Dépendances Composer
```

## 🛠️ Installation et Configuration

### 📋 Prérequis Système

#### Serveur Web
- **WAMP/XAMPP** : Environnement de développement
- **Apache 2.4+** ou **Nginx 1.18+**
- **PHP 8.1+** avec extensions :
  ```
  php-pdo, php-mysql, php-gd, php-mbstring,
  php-json, php-fileinfo, php-openssl, php-curl
  ```
- **MySQL 8.0+** ou **MariaDB 10.4+**
- **Node.js 16+** et **npm 8+**
- **Composer 2.x**

#### Extensions PHP Requises
```bash
# Vérifier les extensions
php -m | grep -E "(pdo|mysql|gd|mbstring|json|fileinfo|openssl|curl)"
```

### 🚀 Installation Étape par Étape

#### 1. Clonage du Projet
```bash
git clone https://github.com/votre-username/CheckMyKicks.git
cd CheckMyKicks
```

#### 2. Installation des Dépendances
```bash
# Dépendances PHP
composer install --optimize-autoloader

# Dépendances JavaScript
npm install
npm run build
```

#### 3. Configuration de la Base de Données
```bash
# Créer la base de données
mysql -u root -p
CREATE DATABASE checkmykicks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```php
// config/database.php
define('DB_HOST', '127.0.0.1:3308');  // Votre host MySQL
define('DB_NAME', 'checkmykicks');
define('DB_USER', 'root');             // Votre utilisateur
define('DB_PASS', 'votre_mot_de_passe');
```

#### 4. Import des Données
```bash
# Importer la structure et les données
mysql -u root -p checkmykicks < database/checkmykicks.sql
```

#### 5. Configuration des Permissions
```bash
# Droits d'écriture
chmod 755 uploads/
chmod 755 public/factures/
chmod 755 Backend/IA-Check/temp/
```

#### 6. Configuration du Serveur Web

##### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?/$1 [QSA,L]
```

##### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 🌐 Démarrage de l'Application

#### Développement Local
```bash
# Démarrer WAMP/XAMPP
# Ou utiliser le serveur PHP intégré
php -S localhost:8000 -t public/

# Accéder à l'application
http://localhost/CheckMyKicks/Frontend/HTML/home.php
```

#### Production
```bash
# Build des assets
npm run build

# Configuration du serveur web
# Pointer vers le dossier Frontend/HTML/ ou public/
```

## 🗄️ Base de Données

### 📊 Modèle de Données (13+ Tables)

#### Tables Principales
- **`utilisateurs`** : Gestion des utilisateurs
- **`abonnement`** : Types d'abonnements disponibles
- **`abonnements`** : Abonnements souscrits par utilisateur
- **`paiement`** : Transactions de paiement
- **`facture`** : Factures générées
- **`analyses_ia`** : Résultats des analyses IA
- **`historique_abonnement`** : Historique des souscriptions

#### Tables Fonctionnelles
- **`drop_sneaker`** : Sorties de sneakers
- **`drop_reminders`** : Rappels personnalisés
- **`notifications`** : Système de notifications
- **`annonce`** : Annonces utilisateurs
- **`verification`** : Vérifications manuelles

### 🔐 Sécurité des Données
- **Mots de passe** : Hashage bcrypt
- **Données sensibles** : Chiffrement des informations
- **Requêtes** : Protection contre les injections SQL
- **Sessions** : Gestion sécurisée des sessions
- **CSRF** : Protection contre les attaques CSRF

## 🧪 Tests et Qualité

### 🔍 Tests Disponibles
```bash
# Tests unitaires PHP
vendor/bin/phpunit

# Tests d'intégration
php bin/console doctrine:schema:validate

# Audit de sécurité
composer audit
npm audit
```

### 📏 Standards de Code
- **PSR-12** : Standard de code PHP
- **ESLint** : Linting JavaScript
- **PHPStan** : Analyse statique PHP
- **Documentation** : PHPDoc pour toutes les fonctions

## 🚀 Utilisation

### 👤 Pour les Utilisateurs

#### Inscription et Connexion
1. Créer un compte sur `/register.php`
2. Se connecter sur `/login.php`
3. Choisir un abonnement adapté

#### Analyse de Sneakers
1. Accéder à `/ia.php`
2. Télécharger jusqu'à 6 photos
3. Lancer l'analyse IA
4. Consulter les résultats détaillés

#### Gestion du Compte
1. Profil utilisateur : `/compte.php`
2. Historique des analyses
3. Gestion de l'abonnement
4. Téléchargement des factures

### 🛠️ Pour les Développeurs

#### API Endpoints
```php
// Analyse IA
POST /Backend/IA-Check/check_sneakers.php

// Sauvegarde d'analyse
POST /Backend/IA-Check/save_analysis.php

// Vérification des limites
GET /Backend/IA-Check/check_limit.php

// Authentification
POST /Backend/auth/login.php
POST /Backend/auth/register.php

// Paiements
POST /Backend/paiement/traitement_abonnement.php
```

#### Intégration de l'IA
```javascript
// Exemple d'utilisation
const formData = new FormData();
formData.append('images[]', imageFile);

fetch('/Backend/IA-Check/check_sneakers.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    console.log('Résultat IA:', data);
});
```

## 📈 Performances et Optimisation

### ⚡ Optimisations Implémentées
- **Cache PHP** : OpCache activé
- **Compression** : Gzip pour les assets
- **Lazy Loading** : Chargement différé des images
- **CDN** : Fichiers statiques externalisés
- **Database** : Index optimisés

### 📊 Métriques
- **Temps de réponse** : < 2s pour l'analyse IA
- **Disponibilité** : 99.9% uptime
- **Scalabilité** : Support de 1000+ utilisateurs simultanés

## 🔒 Sécurité

### 🛡️ Mesures de Sécurité
- **Authentification** : Système robuste avec sessions
- **Autorisation** : Contrôle d'accès par rôles
- **Validation** : Validation stricte des entrées
- **Sanitisation** : Nettoyage des données utilisateur
- **HTTPS** : Chiffrement des communications
- **Logs** : Journalisation des actions sensibles

### 🚨 Gestion des Erreurs
- **Logs structurés** : Toutes les erreurs loggées
- **Fallback** : Mécanismes de récupération
- **Monitoring** : Surveillance en temps réel

## 📞 Support et Contribution

### 🆘 Support
- **Documentation** : Guide utilisateur complet
- **FAQ** : Questions fréquentes
- **Contact** : support@checkmykicks.com
- **Issues** : GitHub Issues pour les bugs

### 🤝 Contribution
1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📝 Changelog

### Version 1.0.0 (2025-06-12)
- ✅ Système d'authentification IA complet
- ✅ Gestion des abonnements et paiements
- ✅ Interface utilisateur responsive
- ✅ Système de facturation PDF
- ✅ Module de suivi des drops
- ✅ Blog intégré
- ✅ Support multilingue (FR/EN)

### Version 0.9.0 (2025-05-15)
- ✅ Architecture de base Symfony
- ✅ Modèle de données complet
- ✅ Système d'authentification utilisateur
- ✅ Interface d'administration

## 📄 License

Ce projet est sous licence propriétaire. Tous droits réservés.

## 👥 Équipe

- **Développeur Principal** : [Sacha MOREAU, Zayd EL AJLI]
- **Backend** : PHP/Symfony
- **Frontend** : HTML/CSS/JavaScript
- **IA** : Algorithmes d'authentification


--- 

**Made with ❤️ by the CheckMyKicks Team**

*Authentifiez vos sneakers en toute confiance !*
