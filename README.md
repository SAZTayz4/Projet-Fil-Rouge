# Projet File Rouge

## Description
Ce projet est une plateforme innovante spécialisée dans la revente de sneakers, combinant une architecture MVC moderne avec des technologies avancées d'IA et d'automatisation. La plateforme offre une solution complète pour les revendeurs professionnels de sneakers, intégrant un chatbot Vinted intelligent, un système d'authentification IA, et des outils d'automatisation avancés.

## Fonctionnalités Principales

### 🤖 Bot Vinted Intelligent
- Recherche automatique de produits sur Vinted
- Gestion des conversations avec les acheteurs
- Publication automatique d'annonces (jusqu'à 500)
- Négociations automatisées
- Suivi des ventes en temps réel
- Notifications instantanées

### 🔍 Système d'Authentification IA
- Vérification automatique de l'authenticité des sneakers
- Base de données de plus de 30 000 modèles
- Résultats d'authentification en moins de 2min
- Formation professionnelle à l'authentification
- Historique des vérifications

### 📊 Analytics et Automatisation
- Tableau de bord administrateur complet
- Statistiques détaillées des ventes
- Suivi des performances en temps réel
- Optimisation automatique des prix
- Rapports personnalisés

### 💼 Fonctionnalités Premium
- Publication d'annonces illimitée
- Authentifications 50/mois
- Support 24/7 dédié
- Accès anticipé aux nouvelles fonctionnalités
- Accès API personnalisé
- Formation professionnelle

### 🔒 Sécurité et Conformité
- Protection des données utilisateurs
- Système de paiement sécurisé
- Conformité RGPD
- Journalisation des actions
- Sauvegardes automatiques

## Prérequis Techniques
- PHP 8.0 ou supérieur
- MySQL/MariaDB 10.4 ou supérieur
- Node.js 16.x ou supérieur
- npm 8.x ou supérieur
- Composer 2.x
- Serveur web (Apache 2.4+ / Nginx 1.18+)
- WAMP (Windows, Apache, MySQL, PHP)
- Extensions PHP requises :
  - PDO
  - MySQLi
  - GD
  - OpenSSL
  - mbstring
  - json
  - fileinfo

## Installation Détaillée

1. **Préparation de l'environnement**
   ```bash
   # Vérifier la version de PHP
   php -v
   
   # Vérifier la version de Node.js
   node -v
   
   # Vérifier la version de Composer
   composer -V
   ```

2. **Cloner le repository**
   ```bash
   git clone https://github.com/votre-username/ProjetFileRouge.git
   cd ProjetFileRouge
   ```

3. **Installation des dépendances PHP**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Installation des dépendances JavaScript**
   ```bash
   npm install
   npm run build
   ```

5. **Configuration de la base de données**
   - Créer une nouvelle base de données MySQL
   - Copier le fichier `.env.example` vers `.env`
   - Configurer les variables d'environnement dans `.env` :
     ```
     DB_HOST=localhost
     DB_NAME=votre_base
     DB_USER=votre_utilisateur
     DB_PASS=votre_mot_de_passe
     ```

6. **Configuration du serveur web**
   - Pour Apache, assurez-vous que le module rewrite est activé :
     ```bash
     sudo a2enmod rewrite
     sudo service apache2 restart
     ```
   - Configurez le VirtualHost pour pointer vers le dossier `public/`
   - Exemple de configuration Apache :
     ```apache
     <VirtualHost *:80>
         ServerName projetfilerouge.local
         DocumentRoot /chemin/vers/ProjetFileRouge/public
         <Directory /chemin/vers/ProjetFileRouge/public>
             AllowOverride All
             Require all granted
         </Directory>
     </VirtualHost>
     ```

## Structure Détaillée du Projet
```
ProjetFileRouge/
├── Backend/                 # Backend de l'application
│   ├── Controllers/        # Contrôleurs de l'application
│   ├── Models/            # Modèles de données
│   ├── Services/          # Services métier
│   └── chatbot/           # Système de chatbot Vinted
├── Frontend/               # Interface utilisateur
│   ├── assets/            # Fichiers statiques
│   ├── components/        # Composants réutilisables
│   └── views/             # Templates de vues
├── includes/              # Fichiers d'inclusion PHP
│   ├── config/           # Configuration
│   ├── helpers/          # Fonctions utilitaires
│   └── middleware/       # Middleware d'authentification
├── public/               # Point d'entrée public
│   ├── index.php        # Point d'entrée principal
│   ├── assets/          # Assets compilés
│   └── uploads/         # Fichiers uploadés
├── src/                 # Code source principal
│   ├── Entity/         # Entités de données
│   └── Services/       # Services métier
├── templates/           # Templates de vues
├── tests/              # Tests unitaires et d'intégration
├── vendor/             # Dépendances Composer
└── node_modules/       # Dépendances npm
```

## Commandes Utiles

### Développement
```bash
# Démarrer le serveur de développement PHP
php -S localhost:8000 -t public/

# Compiler les assets en mode développement
npm run dev

# Compiler les assets pour la production
npm run build

# Lancer les tests
php vendor/bin/phpunit
```

### Maintenance
```bash
# Mettre à jour les dépendances PHP
composer update

# Mettre à jour les dépendances JavaScript
npm update

# Nettoyer le cache
php bin/console cache:clear

# Vérifier la sécurité des dépendances
composer audit
npm audit
```

## Sécurité
- 🔒 Mots de passe hashés avec bcrypt
- 🛡️ Protection CSRF sur tous les formulaires
- ✅ Validation stricte des entrées utilisateur
- 🔐 Gestion sécurisée des sessions
- 📝 Journalisation des actions sensibles
- 🔑 Authentification à deux facteurs (2FA)
- 🚫 Protection contre les injections SQL
- 🔍 Protection XSS
- 📤 Validation des fichiers uploadés

## Bonnes Pratiques de Développement
- Suivre les standards PSR-12 pour le code PHP
- Utiliser ESLint pour le code JavaScript
- Écrire des tests unitaires
- Documenter le code avec PHPDoc
- Utiliser Git Flow pour la gestion des versions
- Faire des revues de code
- Maintenir un changelog

## Support et Contribution
- 📝 Pour signaler un bug : Créer une issue avec le template approprié
- 💡 Pour proposer une fonctionnalité : Utiliser le template "Feature Request"
- 🤝 Pour contribuer : 
  1. Fork le projet
  2. Créer une branche (`git checkout -b feature/AmazingFeature`)
  3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
  4. Push vers la branche (`git push origin feature/AmazingFeature`)
  5. Ouvrir une Pull Request

## Licence
Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## Auteurs
- Sacha MOREAU - Développeur Full Stack
- Zayd El Ajli - Développeur Full Stack


## API et Intégrations
- API Vinted pour la gestion des annonces
- API de paiement sécurisée
- Intégration avec les services d'authentification
- Webhooks pour les notifications
- API RESTful pour les développeurs

## Support et Formation
- Documentation technique complète
- Guides d'utilisation détaillés
- Formation professionnelle à l'authentification
- Support technique 24/7
- Communauté active d'utilisateurs
