# DICTIONNAIRE DES DONNÉES - CheckMyKicks

## ENTITÉS PRINCIPALES

### UTILISATEURS
| Attribut | Type | Taille | Contraintes | Description |
|----------|------|--------|-------------|-------------|
| id | INT | 11 | PK, AUTO_INCREMENT | Identifiant unique utilisateur |
| nom | VARCHAR | 100 | NOT NULL | Nom complet de l'utilisateur |
| email | VARCHAR | 100 | NOT NULL, UNIQUE | Adresse email (login) |
| motDePasse | VARCHAR | 255 | NOT NULL | Mot de passe hashé |
| role | ENUM | - | ('admin','client') | Rôle de l'utilisateur |
| token | VARCHAR | 64 | NULL | Token de session/réinitialisation |
| abonnement_id | INT | 11 | FK → abonnement.id | Type d'abonnement actuel |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | - | ON UPDATE CURRENT_TIMESTAMP | Date de modification |
| last_login | DATETIME | - | NULL | Dernière connexion |
| photo_profil | VARCHAR | 255 | NULL | Nom du fichier photo |

### ABONNEMENT (Types d'abonnements)
| Attribut | Type | Taille | Contraintes | Description |
|----------|------|--------|-------------|-------------|
| id | INT | 11 | PK, AUTO_INCREMENT | Identifiant unique |
| type | ENUM | - | ('gratuit','débutant','intermédiaire','avancé') | Type d'abonnement |
| prix | DECIMAL | 10,2 | NOT NULL | Prix mensuel |
| limitesVerifications | INT | 11 | NOT NULL | Nombre de vérifications autorisées |
| accesChatbot | BOOLEAN | - | DEFAULT 0 | Accès au chatbot IA |
| accesAutoCop | BOOLEAN | - | DEFAULT 0 | Accès à l'AutoCop |
| duree | INT | 11 | NULL | Durée en mois |

### ABONNEMENTS (Abonnements souscrits)
| Attribut | Type | Taille | Contraintes | Description |
|----------|------|--------|-------------|-------------|
| id | INT | 11 | PK, AUTO_INCREMENT | Identifiant unique |
| utilisateur_id | INT | 11 | FK → utilisateurs.id | Utilisateur concerné |
| type_abonnement | ENUM | - | ('gratuit','débutant','intermédiaire','avancé') | Type souscrit |
| prix | DECIMAL | 10,2 | NOT NULL | Prix payé |
| date_debut | DATETIME | - | NOT NULL | Date de début |
| date_fin | DATETIME | - | NOT NULL | Date de fin |
| statut | ENUM | - | ('actif','inactif','annule') | Statut actuel |
| numero_facture | VARCHAR | 50 | NOT NULL | Référence facture |

### PAIEMENT
| Attribut | Type | Taille | Contraintes | Description |
|----------|------|--------|-------------|-------------|
| id | INT | 11 | PK, AUTO_INCREMENT | Identifiant unique |
| utilisateur_id | INT | 11 | FK → utilisateurs.id | Utilisateur payeur |
| montant | DECIMAL | 10,2 | NOT NULL | Montant payé |
| typeAbonnement | ENUM | - | ('gratuit','débutant','intermédiaire','avancé') | Type acheté |
| methodePaiement | ENUM | - | ('MangoPay','Stripe') | Méthode de paiement |
| statut | ENUM | - | ('réussi','échoué') | Résultat du paiement |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Date du paiement |

### FACTURE
| Attribut | Type | Taille | Contraintes | Description |
|----------|------|--------|-------------|-------------|
| id | INT | 11 | PK, AUTO_INCREMENT | Identifiant unique |
| paiement_id | INT | 11 | FK → paiement.id | Paiement associé |
| utilisateur_id | INT | 11 | FK → utilisateurs.id | Client facturé |
| numeroFacture | VARCHAR | 50 | UNIQUE | Numéro de facture |
| montantTotal | DECIMAL | 10,2 | NOT NULL | Montant total TTC |
| details | JSON | - | NOT NULL | Détails de la facture |
| statut | ENUM | - | ('payée','en_attente','annulée') | Statut facture |

### ANALYSES_IA
| Attribut | Type | Taille | Contraintes | Description |
|----------|------|--------|-------------|-------------|
| id | INT | 11 | PK, AUTO_INCREMENT | Identifiant unique |
| utilisateur_id | INT | 11 | FK → utilisateurs.id | Utilisateur demandeur |
| date_analyse | DATETIME | - | NOT NULL | Date/heure d'analyse |
| nombre_images | INT | 11 | NOT NULL | Nombre d'images analysées |
| resultats | JSON | - | NOT NULL | Résultats de l'IA |

## RÈGLES DE GESTION

### RG01 - Authentification
- Un utilisateur doit avoir un email unique
- Le mot de passe doit être hashé (bcrypt)
- Un token peut être généré pour la réinitialisation

### RG02 - Abonnements
- Un utilisateur peut avoir plusieurs abonnements dans l'historique
- Un seul abonnement peut être actif à la fois
- L'abonnement gratuit a des limitations (3 vérifications)

### RG03 - Paiements
- Chaque paiement génère une facture
- Les paiements peuvent être via MangoPay ou Stripe
- Un paiement réussi active l'abonnement

### RG04 - Analyses IA
- Les analyses consomment les crédits de l'abonnement
- Les résultats sont stockés au format JSON
- Historique complet conservé

### RG05 - Facturation
- Chaque facture a un numéro unique (FACT-YYYYMMDD-XXXX)
- Les factures sont générées en PDF
- Statut: payée, en_attente, annulée

## CARDINALITÉS DES ASSOCIATIONS

- UTILISATEURS (1,1) ←→ (0,n) ABONNEMENTS
- UTILISATEURS (1,1) ←→ (0,n) PAIEMENT  
- PAIEMENT (1,1) ←→ (1,1) FACTURE
- UTILISATEURS (1,1) ←→ (0,n) ANALYSES_IA
- ABONNEMENT (1,1) ←→ (0,n) HISTORIQUE_ABONNEMENT
- UTILISATEURS (1,1) ←→ (0,n) HISTORIQUE_ABONNEMENT 