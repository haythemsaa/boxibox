# Guide d'Installation Boxibox

## 🚀 Installation Automatique (Recommandée)

### Windows
```cmd
install.bat
```

### Linux/macOS
```bash
chmod +x install.sh
./install.sh
```

## 📋 Installation Manuelle

Si l'installation automatique ne fonctionne pas, suivez ces étapes :

### 1. Prérequis
- PHP >= 8.1
- MySQL >= 8.0 ou MariaDB >= 10.3
- Composer
- Extensions PHP : PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath

### 2. Installation des dépendances
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configuration
```bash
# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 4. Configuration de la base de données
Éditez le fichier `.env` :
```env
DB_DATABASE=boxibox
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
```

### 5. Migration et données initiales
```bash
# Créer les tables
php artisan migrate

# Installer les données de base
php artisan db:seed --class=DatabaseSeeder
```

### 6. Permissions (Linux/macOS uniquement)
```bash
chmod -R 755 storage bootstrap/cache
```

### 7. Optimisation
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔑 Premier accès

**Compte administrateur :**
- Email : `admin@boxibox.com`
- Mot de passe : `admin123`

⚠️ **Changez immédiatement ce mot de passe !**

## 🌐 Configuration serveur web

### Apache
Assurez-vous que le module `mod_rewrite` est activé et pointez votre DocumentRoot vers le dossier `public/`.

### Nginx
Exemple de configuration :
```nginx
server {
    listen 80;
    index index.php;
    root /path/to/boxibox/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔧 Configuration SEPA (Optionnel)

Pour activer les prélèvements SEPA, ajoutez dans `.env` :
```env
SEPA_CREDITOR_ID=votre_identifiant_creancier
SEPA_CREDITOR_NAME="Nom de votre entreprise"
SEPA_CREDITOR_IBAN=FR1234567890123456789012345
SEPA_CREDITOR_BIC=BANKFRPP
```

## 📧 Configuration Email

```env
MAIL_MAILER=smtp
MAIL_HOST=votre.serveur.smtp
MAIL_PORT=587
MAIL_USERNAME=votre@email.com
MAIL_PASSWORD=votre_mot_de_passe
MAIL_ENCRYPTION=tls
```

## 🐛 Résolution de problèmes

### Erreur de permissions
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

### Erreur de base de données
1. Vérifiez que MySQL/MariaDB est démarré
2. Vérifiez les paramètres de connexion dans `.env`
3. Créez la base de données manuellement si nécessaire :
```sql
CREATE DATABASE boxibox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Cache corrompu
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## 📞 Support

En cas de problème, consultez :
- [Documentation complète](README.md)
- [Issues GitHub](https://github.com/votre-repo/boxibox/issues)
- Email : support@boxibox.com