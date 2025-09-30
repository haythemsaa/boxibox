# Boxibox - Système de Gestion pour Centres de Self-Stockage

![Boxibox Logo](https://via.placeholder.com/300x100?text=BOXIBOX)

Boxibox est une solution complète de gestion pour centres de self-stockage, développée avec Laravel. Elle offre une interface moderne et intuitive pour gérer tous les aspects de votre activité de stockage.

## 🚀 Fonctionnalités Principales

### 📊 Dashboard Analytics
- Vue d'ensemble temps réel de l'activité
- Statistiques d'occupation et financières
- Graphiques d'évolution
- Indicateurs de performance

### 👥 Gestion Commerciale
- **Prospects** : Suivi du pipeline commercial
- **Clients** : Base de données complète avec documents
- **Contrats** : Gestion du cycle de vie complet

### 💰 Gestion Financière
- **Factures** : Création automatique et manuelle
- **Règlements** : Suivi des paiements
- **SEPA** : Gestion des prélèvements automatiques
- **Relances** : Système automatisé

### 🏢 Gestion Technique
- **Plan des Boxes** : Interface graphique interactive
- **Boxes** : Gestion des emplacements et tarification
- **Catalogue** : Services et produits

### 🔐 Sécurité & Administration
- Système de rôles et permissions granulaires
- Audit des actions utilisateurs
- Sauvegarde automatique des données

## 📋 Prérequis

- PHP >= 8.1
- Composer
- MySQL >= 8.0 ou MariaDB >= 10.3
- Node.js >= 16
- Extensions PHP : PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath

## 🛠️ Installation

### 1. Cloner le projet

```bash
git clone https://github.com/votre-username/boxibox.git
cd boxibox
```

### 2. Installer les dépendances

```bash
# Dépendances PHP
composer install

# Dépendances JavaScript (si applicable)
npm install
npm run build
```

### 3. Configuration de l'environnement

```bash
# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 4. Configurer la base de données

Éditez le fichier `.env` avec vos paramètres de base de données :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=boxibox
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Configuration SEPA (Optionnel)

Pour activer les prélèvements SEPA, ajoutez à votre `.env` :

```env
SEPA_CREDITOR_ID=votre_identifiant_creancier
SEPA_CREDITOR_NAME="Nom de votre entreprise"
SEPA_CREDITOR_IBAN=FR1234567890123456789012345
SEPA_CREDITOR_BIC=BANKFRPP
```

### 6. Initialiser la base de données

```bash
# Exécuter les migrations
php artisan migrate

# Installer les permissions et rôles
php artisan db:seed --class=RolePermissionSeeder

# (Optionnel) Données de test
php artisan db:seed
```

### 7. Configuration du serveur web

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 8. Permissions des fichiers

```bash
# Linux/macOS
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Windows (avec XAMPP)
# Assurez-vous que les dossiers storage et bootstrap/cache sont accessibles en écriture
```

## 🔑 Première Connexion

Un compte administrateur par défaut est créé lors de l'installation :

- **Email** : `admin@boxibox.com`
- **Mot de passe** : `admin123`

⚠️ **Important** : Changez immédiatement ce mot de passe après votre première connexion !

## 🎯 Configuration Initiale

### 1. Paramètres de l'entreprise
Rendez-vous dans `Administration > Paramètres` pour configurer :
- Informations de l'entreprise
- Paramètres de facturation
- Modèles de documents

### 2. Création des emplacements et boxes
1. Créez vos emplacements (bâtiments, étages, zones)
2. Définissez les familles de boxes
3. Créez vos boxes individuelles

### 3. Configuration des rôles utilisateurs
Adaptez les permissions selon vos besoins :
- **Administrateur** : Accès complet
- **Manager** : Gestion opérationnelle
- **Employé** : Opérations quotidiennes
- **Lecture seule** : Consultation uniquement

## 📚 Utilisation

### Workflow Type
1. **Prospect** → Saisie des informations de contact
2. **Client** → Conversion et complétion du dossier
3. **Contrat** → Création et signature
4. **Box** → Attribution automatique
5. **Facturation** → Génération automatique
6. **Règlement** → Suivi des paiements

### Fonctionnalités Avancées

#### Facturation en Masse
```bash
php artisan boxibox:generate-invoices --month=2024-01
```

#### Export SEPA
```bash
php artisan boxibox:export-sepa --date=2024-01-15
```

#### Relances Automatiques
```bash
php artisan boxibox:send-reminders
```

## 🔄 Maintenance

### Mises à jour
```bash
git pull origin main
composer install --no-dev
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Sauvegarde
```bash
# Base de données
mysqldump -u username -p boxibox > backup_$(date +%Y%m%d_%H%M%S).sql

# Fichiers documents
tar -czf documents_backup_$(date +%Y%m%d_%H%M%S).tar.gz storage/app/documents/
```

### Surveillance
- Logs : `storage/logs/laravel.log`
- Erreurs SEPA : `storage/logs/sepa.log`
- Performances : Utilisation de Laravel Telescope recommandée

## 🎨 Personnalisation

### Thèmes
Les couleurs et styles peuvent être personnalisés dans :
- `resources/css/app.css`
- `resources/views/layouts/app.blade.php`

### Modèles de Documents
Les modèles de factures et contrats se trouvent dans :
- `resources/views/pdf/`

## 🐛 Dépannage

### Problèmes Courants

**Erreur de permissions**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

**Erreur de base de données**
- Vérifiez les paramètres de connexion dans `.env`
- Assurez-vous que la base de données existe
- Vérifiez les permissions utilisateur MySQL

**Erreur SEPA**
- Vérifiez la configuration SEPA dans `.env`
- Contrôlez les formats IBAN/BIC
- Vérifiez les permissions d'écriture dans `storage/sepa/`

## 📞 Support

- **Documentation** : [docs.boxibox.com](https://docs.boxibox.com)
- **Issues** : [GitHub Issues](https://github.com/votre-username/boxibox/issues)
- **Email** : support@boxibox.com

## 📜 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🤝 Contribution

Les contributions sont les bienvenues ! Veuillez consulter notre guide de contribution pour plus d'informations.

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit vos changes (`git commit -m 'Add some AmazingFeature'`)
4. Push sur la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 🏆 Remerciements

- Laravel Framework
- Spatie Laravel Permission
- Bootstrap & Font Awesome
- Chart.js pour les graphiques
- Communauté open source

---

**Boxibox** - Simplifiez la gestion de votre centre de self-stockage 📦