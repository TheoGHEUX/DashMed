# DashMed 🏥

<p align="center">
	<img src="https://img.shields.io/badge/DashMed-Suivi%20m%C3%A9dical-12C9D4?style=for-the-badge&logo=healthicons" alt="DashMed banner" />
	<img src="https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php" alt="PHP 8.0+" />
	<img src="https://img.shields.io/badge/Architecture-MVC-success?style=for-the-badge" alt="MVC" />
</p>

**DashMed** est une plateforme web intelligente de suivi médical permettant aux professionnels de santé de centraliser, visualiser et analyser les données de leurs patients en temps réel.

Conçue pour optimiser la prise de décision clinique, DashMed offre des tableaux de bord personnalisables, des alertes intelligentes et une interface intuitive accessible sur tous les appareils.

---

## 🎯 Fonctionnalités principales

### 📊 Dashboard intelligent
- **Visualisation avancée** de 7 métriques vitales :
  - Température corporelle
  - Tension artérielle
  - Fréquence cardiaque
  - Fréquence respiratoire
  - Glycémie
  - Poids
  - Saturation en oxygène
- **Graphiques temps réel** avec historique de 50 dernières mesures
- **Agencement personnalisable** du dashboard par patient
- **Système de seuils d'alerte** à 3 niveaux (préoccupant, urgent, critique)
- **Notifications visuelles** pour les valeurs anormales

### 🤖 Intelligence Artificielle
- **Suggestions automatiques** d'agencement via algorithme KNN (K-Nearest Neighbors)
- Détection de **patients similaires** basée sur :
  - Âge, sexe, groupe sanguin
  - Moyennes des constantes vitales
  - Profils médicaux
- **Optimisation du workflow** médical par apprentissage des préférences

### 👥 Gestion des patients
- **Suivi multi-patients** pour chaque médecin
- **Fiches détaillées** : coordonnées, données vitales, groupe sanguin
- **Sélection rapide** avec navigation fluide entre patients
- **Historique complet** des consultations et mesures

### 🔐 Sécurité & Authentification
- **Inscription sécurisée** avec validation email
- **Authentification robuste** (sessions sécurisées, CSRF protection)
- **Gestion des mots de passe** :
  - Complexité imposée (12+ caractères, maj/min/chiffres/spéciaux)
  - Réinitialisation par email avec tokens sécurisés
  - Hashing avec `password_hash()` (bcrypt)
- **Protection avancée** :
  - En-têtes de sécurité HTTP (CSP, HSTS, X-Frame-Options)
  - Protection anti-timing attack
  - Rate limiting sur les endpoints sensibles
  - Sessions cookie HttpOnly, SameSite, Secure

### 📧 Système d'emailing
- **Emails transactionnels** :
  - Vérification de compte
  - Réinitialisation de mot de passe
  - Notifications de changement d'email
- Templates HTML responsive
- Fallback fichier pour développement local

### 👤 Gestion de profil
- Consultation des informations personnelles
- Modification sécurisée de l'email
- Changement de mot de passe avec vérification
- Gestion des spécialités médicales (34 spécialités disponibles)

### 📝 Traçabilité
- **Historique des actions** (console logs) :
  - Ajout/Suppression de graphiques
  - Redimensionnement
  - Personnalisation du dashboard
- **Audit trail** pour conformité médicale

---

## 🏗️ Architecture technique

### Stack technologique
- **Backend** : PHP 8.0+ (orienté objet, typage strict)
- **Base de données** : MySQL avec PDO
- **Frontend** : HTML5, CSS3, JavaScript (Vanilla)
- **Architecture** : MVC + Repository Pattern
- **Autoloading** : PSR-4

### Structure du projet
```
DashMed/
├── Public/
│   ├── index.php              # Point d'entrée
│   └── assets/                # CSS, JS, images
├── SITE/
│   ├── Controllers/           # 12 contrôleurs
│   ├── Core/                  # Router, Database, View, Csrf, Mailer, AutoLoader
│   ├── Models/
│   │   ├── Entities/         # User, Patient, ConsoleLog
│   │   └── Repositories/     # UserRepository, PatientRepository, etc.
│   └── Views/                # Templates PHP
└── tests/                    # Tests unitaires et d'intégration
```

### Principes de conception
- ✅ **Séparation des responsabilités** (MVC strict)
- ✅ **Injection de dépendances**
- ✅ **Single Responsibility Principle**
- ✅ **DRY (Don't Repeat Yourself)**
- ✅ **Code propre et maintenable**

### Routes disponibles

#### Pages publiques
- `/` - Accueil
- `/map` - Plan du site
- `/legal-notices` - Mentions légales

#### Authentification
- `/login`, `/register` - Connexion/Inscription
- `/logout` - Déconnexion (POST uniquement)
- `/forgotten-password` - Mot de passe oublié
- `/reset-password` - Réinitialisation
- `/verify-email` - Vérification email
- `/resend-verification` - Renvoi email

#### Espace protégé (authentification requise)
- `/home` - Accueil connecté
- `/dashboard` - Tableau de bord patients
- `/profile` - Profil utilisateur
- `/change-password` - Modification mot de passe
- `/change-email` - Modification email

#### API REST
- `POST /api/log-graph-action` - Log des actions
- `GET /api/dashboard-layout` - Récupération agencement
- `POST /api/save-dashboard-layout` - Sauvegarde agencement
- `GET /api/suggest-layout` - Suggestion IA
- `GET /api/check-ai-availability` - Statut IA

---

## 🚀 Pour qui

- **Médecins généralistes** et spécialistes
- **Équipes soignantes** en établissement
- **Professionnels de santé** nécessitant un suivi patient optimisé
- **Structures médicales** cherchant une solution de centralisation

---

## ✨ Avantages

### Pour les professionnels
- ⚡ **Gain de temps** : informations clés visibles instantanément
- 🎨 **Personnalisation** : chaque médecin adapte son interface
- 📱 **Mobilité** : accès depuis n'importe quel appareil
- 🔔 **Alertes intelligentes** : détection automatique des anomalies
- 🤖 **IA intégrée** : suggestions d'optimisation du workflow

### Pour les patients
- 🔒 **Confidentialité** : données sécurisées et chiffrées
- 📊 **Suivi précis** : historique complet des mesures
- ✅ **Fiabilité** : système robuste et testé

---

## 📋 Prérequis

- **PHP** 8.0 ou supérieur
- **MySQL** 5.7+ ou MariaDB 10.3+
- **Composer** (gestionnaire de dépendances PHP)
- **Serveur web** (Apache/Nginx) avec mod_rewrite
- **Node.js** (pour JSDoc - optionnel)

---

## 🛠️ Installation

### 1. Cloner le projet
```bash
git clone https://github.com/votre-repo/DashMed.git
cd DashMed
```

### 2. Installer les dépendances
```bash
# Dépendances PHP (PHPUnit, PHPStan, PHPCS)
composer install

# Dépendances JS pour la documentation (optionnel)
npm install
```

### 3. Configuration de la base de données

Créez un fichier `.env` à la racine du projet :
```env
# Base de données
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=dashmed-site_db
DB_USER=root
DB_PASS=

# Application
APP_DEBUG=1              # 0 en production
HEALTH_KEY=votre_cle_secrete

# Email (optionnel en dev)
MAIL_FROM=dashmed-site@alwaysdata.net
```

### 4. Importer la structure de la base de données
```bash
mysql -u root -p dashmed-site_db < database/schema.sql
```

### 5. Configurer le serveur web

**Apache** (.htaccess déjà configuré) :
- Document root : `/path/to/DashMed/Public`
- AllowOverride All

**Nginx** :
```nginx
server {
    listen 80;
    server_name dashmed.local;
    root /path/to/DashMed/Public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### 6. Accéder à l'application
```
http://localhost/DashMed/Public/
ou
http://dashmed.local (selon configuration)
```

---

## 🧪 Tests & Qualité du code

### Tests unitaires et d'intégration
```bash
# Lancer tous les tests
composer test

# Avec couverture de code
composer test-coverage
# Résultats dans : coverage/index.html
```

### Analyse statique (PHPStan)
```bash
# Analyse niveau 5 (strict)
vendor/bin/phpstan analyse SITE --level=5

# Analyse avec baseline
vendor/bin/phpstan analyse SITE --configuration phpstan.neon
```

### Vérification du style de code (PHPCS)
```bash
# Vérifier la conformité PSR-12
vendor/bin/phpcs --standard=PSR12 SITE

# Correction automatique
vendor/bin/phpcbf --standard=PSR12 SITE
```

### Health checks
```bash
# Vérification basique
curl http://localhost/health

# Vérification DB (nécessite APP_DEBUG=1 et clé)
curl "http://localhost/health/db?key=votre_cle_secrete"
```

---

## 📚 Documentation

### Génération de la documentation PHP
```bash
# Télécharger phpDocumentor (si pas déjà fait)
wget https://phpdoc.org/phpDocumentor.phar

# Générer la documentation
php phpDocumentor.phar -d SITE -t docs/php --title="DashMed API"

# Ouvrir dans le navigateur
start docs/php/index.html  # Windows
xdg-open docs/php/index.html  # Linux
open docs/php/index.html  # macOS
```

### Génération de la documentation JavaScript
```bash
# Générer avec JSDoc
npx jsdoc Public/assets/script -d docs/javascript -R README.md

# Ouvrir
start docs/javascript/index.html
```

---

## 🔧 Configuration avancée

### Variables d'environnement (.env)
```env
# Base de données
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=dashmed-site_db
DB_USER=root
DB_PASS=

# Debug
APP_DEBUG=1                    # 1 = mode dev, 0 = production
HEALTH_KEY=secret_key_123      # Clé pour /health/db

# Email configuration (production)
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USER=noreply@dashmed.com
MAIL_PASS=password
MAIL_FROM=noreply@dashmed.com

# Sécurité
SESSION_LIFETIME=0             # 0 = jusqu'à fermeture navigateur
CSRF_TTL=7200                  # Durée de vie token CSRF (2h)
```

### Activation HTTPS (production)
Le projet est configuré pour détecter automatiquement HTTPS et activer :
- `Strict-Transport-Security` (HSTS)
- Cookies sécurisés
- Redirections HTTPS

### Personnalisation des seuils d'alerte
Les seuils sont configurables directement en base de données (table `seuil_alerte`) pour chaque patient et type de mesure.

---

## 🛡️ Sécurité

### Mesures implémentées
- ✅ **Protection CSRF** sur tous les formulaires
- ✅ **Sessions sécurisées** (HttpOnly, SameSite, Secure)
- ✅ **Headers de sécurité** (CSP, HSTS, X-Frame-Options, X-Content-Type-Options)
- ✅ **Validation des entrées** côté serveur
- ✅ **Échappement des sorties** (protection XSS)
- ✅ **Requêtes préparées** (protection SQL injection)
- ✅ **Rate limiting** sur endpoints sensibles
- ✅ **Tokens à usage unique** (réinitialisation mot de passe)
- ✅ **Hashing sécurisé** des mots de passe (bcrypt)
- ✅ **Vérification email** obligatoire

### Bonnes pratiques
- Ne jamais commiter le fichier `.env`
- Utiliser des mots de passe forts en base de données
- Activer HTTPS en production
- Configurer les backups réguliers
- Monitorer les logs d'erreurs

---

## 📊 Base de données

### Tables principales
- `medecin` - Utilisateurs (médecins)
- `patient` - Données des patients
- `suivre` - Relation médecin-patient
- `mesures` - Types de mesures par patient
- `valeurs_mesures` - Valeurs des mesures dans le temps
- `seuil_alerte` - Seuils d'alerte personnalisés
- `dashboard_layouts` - Agencements personnalisés
- `historique_console` - Logs des actions
- `password_resets` - Tokens de réinitialisation

### Scripts de génération de données
Pour des raisons de confidentialité, les scripts de génération de données de test sont disponibles séparément pour les membres du projet.

Un script de base est fourni dans `SITE/Scripts/generate_data.php` pour créer des valeurs de mesures aléatoires.

---

## 🎨 Personnalisation

### Mode sombre
DashMed inclut un mode sombre automatique basé sur les préférences système de l'utilisateur.

### Thèmes
Les fichiers CSS sont modulaires dans `Public/assets/style/` :
- `body_main_container.css` - Structure principale
- `dashboard.css` - Tableau de bord
- `dark-mode.css` - Thème sombre
- etc.

### Graphiques
Les graphiques sont générés en JavaScript pur (pas de dépendances externes) dans `Public/assets/script/dashboard_charts.js`.

---

## 🤝 Contribution

### Standards de code
- **PSR-12** pour le code PHP
- **Commentaires** obligatoires (PHPDoc)
- **Tests unitaires** pour les nouvelles fonctionnalités
- Type hinting strict (`declare(strict_types=1)`)

### Workflow Git
1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit (`git commit -m 'Add AmazingFeature'`)
4. Push (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

---

## 📞 Support & Contact

- **Email** : dashmed-site@alwaysdata.net
- **Documentation** : Voir `/docs` après génération
- **Issues** : GitHub Issues

---

## 📝 Changelog

### Version actuelle (Février 2026)
- ✅ Architecture MVC + Repository Pattern
- ✅ Système d'authentification complet
- ✅ Dashboard avec 7 métriques vitales
- ✅ IA de suggestion d'agencement (KNN)
- ✅ Seuils d'alerte à 3 niveaux
- ✅ Personnalisation dashboard par patient
- ✅ Historique des actions
- ✅ API REST pour le frontend
- ✅ Sécurité renforcée (CSRF, HSTS, CSP)
- ✅ Tests unitaires et d'intégration
- ✅ Documentation auto-générée

---

## 📄 Licence

Projet développé dans le cadre de notre formation.

**Made with ❤️ by Team DashMed**

---

## 🙏 Remerciements

- Équipe de développement DashMed
- Formateurs et mentors
- Communauté open-source PHP
