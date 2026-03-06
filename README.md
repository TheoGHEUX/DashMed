# DashMed 🏥

**DashMed** est une plateforme web moderne de suivi médical permettant aux professionnels de santé de centraliser et visualiser les données vitales de leurs patients en temps réel.

> 📌 **Note :** Ce projet est développé dans un cadre pédagogique et utilise des données fictives à des fins de démonstration.

---

## 🎯 Fonctionnalités principales

### 📊 Dashboard médical
- Visualisation de 7 métriques vitales (température, tension, fréquence cardiaque, glycémie, etc.)
- Graphiques temps réel avec historique complet
- Système d'alertes automatiques sur 3 niveaux (préoccupant, urgent, critique)
- Interface personnalisable par patient

### 🤖 Suggestions intelligentes
- Recommandations d'agencement automatiques basées sur des patients similaires
- Optimisation du workflow médical

### 👥 Gestion des patients
- Suivi multi-patients
- Fiches complètes avec coordonnées et données vitales
- Navigation fluide entre patients

### 🔐 Sécurité
- Authentification sécurisée avec vérification par email
- Protection des données médicales
- Réinitialisation de mot de passe sécurisée

---

## 🚀 Cas d'usage

DashMed s'adresse aux professionnels de santé nécessitant :
- Une vue centralisée des données vitales de leurs patients
- Un système d'alertes automatiques sur les paramètres critiques
- Une interface personnalisable et intuitive
- Un suivi longitudinal avec historique complet

---

## 📋 Prérequis techniques

- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+
- Serveur web (Apache/Nginx)
- Composer

---

## 🛠️ Installation

### 1. Cloner le projet
```bash
git clone https://github.com/votre-repo/DashMed.git
cd DashMed
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configuration
Créez un fichier `.env` à la racine :
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=dashmed-site_db
DB_USER=root
DB_PASS=

APP_DEBUG=1
HEALTH_KEY=votre_cle_secrete
MAIL_FROM=votre-email@example.com
```

### 4. Base de données
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

### 6. Accès
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
```

### Analyse statique (PHPStan)
```bash
# Analyse niveau 5
vendor/bin/phpstan analyse SITE --level=5
```

### Vérification du style de code (PHPCS)
```bash
# Vérifier la conformité PSR-12
vendor/bin/phpcs --standard=PSR12 SITE

# Correction automatique
vendor/bin/phpcbf --standard=PSR12 SITE
```

---

## 🔧 Technologies utilisées

- **Backend** : PHP 8.0+
- **Base de données** : MySQL
- **Frontend** : HTML5, CSS3, JavaScript

---

## 📞 Contact

- Email : dashmed-site@alwaysdata.net

---

## 📄 Licence

Projet développé dans le cadre de notre formation.

**Made with ❤️ by Team DashMed**
