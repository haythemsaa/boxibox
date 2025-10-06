# 🚀 Guide de Déploiement Production - Boxibox

Ce document détaille les étapes pour déployer Boxibox en environnement de production.

---

## 📋 Prérequis Serveur

### Configuration Minimale
- **OS** : Ubuntu 20.04+ / Debian 11+ / CentOS 8+
- **PHP** : 8.1 ou supérieur
- **Base de données** : MySQL 8.0+ ou MariaDB 10.6+
- **Serveur Web** : Nginx ou Apache2
- **RAM** : Minimum 2GB (4GB recommandé)
- **Disque** : Minimum 20GB SSD

### Extensions PHP Requises
```bash
php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring
php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath php8.1-redis
```

---

## 🔧 Installation Serveur

### 1. Installation des Dépendances

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install -y nginx mysql-server php8.1-fpm composer nodejs npm redis-server supervisor

# CentOS/RHEL
sudo dnf install -y nginx mysql-server php81-php-fpm composer nodejs npm redis supervisor
```

### 2. Configuration MySQL

```bash
sudo mysql_secure_installation

# Créer la base de données
sudo mysql -u root -p
```

```sql
CREATE DATABASE boxibox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'boxibox'@'localhost' IDENTIFIED BY 'VotreMotDePasseSecurise';
GRANT ALL PRIVILEGES ON boxibox.* TO 'boxibox'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Configuration Redis

```bash
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Tester Redis
redis-cli ping
# Devrait répondre "PONG"
```

---

## 📦 Déploiement de l'Application

### 1. Clonage du Repository

```bash
cd /var/www
sudo git clone https://github.com/haythemsaa/boxibox.git
cd boxibox
sudo chown -R www-data:www-data /var/www/boxibox
```

### 2. Installation des Dépendances

```bash
# Dépendances PHP
composer install --no-dev --optimize-autoloader

# Dépendances JavaScript
npm install
npm run build
```

### 3. Configuration Environnement

```bash
# Copier le fichier .env.example
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Éditer la configuration
nano .env
```

**Configuration .env Production :**
```env
APP_NAME=Boxibox
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=boxibox
DB_USERNAME=boxibox
DB_PASSWORD=VotreMotDePasseSecurise

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.votre-fournisseur.com
MAIL_PORT=587
MAIL_USERNAME=votre@email.com
MAIL_PASSWORD=VotreMotDePasseEmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@votre-domaine.com"
```

### 4. Migrations et Optimisations

```bash
# Migrations
php artisan migrate --force

# Seeders (optionnel - première installation uniquement)
php artisan db:seed --force

# Créer le lien symbolique storage
php artisan storage:link

# Optimisations production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
```

### 5. Permissions

```bash
sudo chown -R www-data:www-data /var/www/boxibox
sudo chmod -R 755 /var/www/boxibox
sudo chmod -R 775 /var/www/boxibox/storage
sudo chmod -R 775 /var/www/boxibox/bootstrap/cache
```

---

## 🌐 Configuration Nginx

### Fichier de Configuration

```bash
sudo nano /etc/nginx/sites-available/boxibox
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name votre-domaine.com www.votre-domaine.com;
    root /var/www/boxibox/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Limites de téléchargement
    client_max_body_size 20M;
}
```

### Activation du Site

```bash
sudo ln -s /etc/nginx/sites-available/boxibox /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔒 Configuration SSL (Let's Encrypt)

```bash
# Installer Certbot
sudo apt install certbot python3-certbot-nginx

# Générer le certificat SSL
sudo certbot --nginx -d votre-domaine.com -d www.votre-domaine.com

# Renouvellement automatique
sudo certbot renew --dry-run
```

---

## ⚙️ Configuration Queue Workers (Supervisor)

### Fichier de Configuration

```bash
sudo nano /etc/supervisor/conf.d/boxibox-worker.conf
```

```ini
[program:boxibox-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/boxibox/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/boxibox/storage/logs/worker.log
stopwaitsecs=3600
```

### Activation

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start boxibox-worker:*
sudo supervisorctl status
```

---

## 📅 Configuration des Tâches Planifiées (Cron)

```bash
sudo crontab -e -u www-data
```

Ajouter ces lignes :

```cron
# Laravel Scheduler (toutes les minutes)
* * * * * cd /var/www/boxibox && php artisan schedule:run >> /dev/null 2>&1

# Nettoyage des notifications (tous les jours à 2h)
0 2 * * * cd /var/www/boxibox && php artisan notifications:clean --days=90

# Rapports mensuels (1er de chaque mois à 8h)
0 8 1 * * cd /var/www/boxibox && php artisan reports:generate-monthly --email=admin@votre-domaine.com
```

---

## 🔐 Sécurité

### 1. Pare-feu (UFW)

```bash
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw enable
```

### 2. Fail2Ban

```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 3. Permissions Strictes

```bash
# Fichiers : 644
find /var/www/boxibox -type f -exec chmod 644 {} \;

# Dossiers : 755
find /var/www/boxibox -type d -exec chmod 755 {} \;

# Storage et cache : 775
chmod -R 775 /var/www/boxibox/storage
chmod -R 775 /var/www/boxibox/bootstrap/cache
```

---

## 📊 Monitoring

### 1. Logs

```bash
# Logs Laravel
tail -f /var/www/boxibox/storage/logs/laravel.log

# Logs Nginx
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Logs Queue Workers
tail -f /var/www/boxibox/storage/logs/worker.log
```

### 2. Monitoring Performance

```bash
# Installer Opcache
sudo apt install php8.1-opcache

# Configuration recommandée dans php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

---

## 🔄 Procédure de Mise à Jour

```bash
# 1. Mode maintenance
cd /var/www/boxibox
php artisan down --message="Maintenance en cours" --retry=60

# 2. Sauvegarde base de données
mysqldump -u boxibox -p boxibox > backup_$(date +%Y%m%d_%H%M%S).sql

# 3. Pull dernière version
git pull origin main

# 4. Mise à jour dépendances
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 5. Migrations
php artisan migrate --force

# 6. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 7. Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 8. Restart workers
sudo supervisorctl restart boxibox-worker:*

# 9. Sortie maintenance
php artisan up
```

---

## 📈 Optimisations Performance

### 1. Opcache

```ini
; Dans /etc/php/8.1/fpm/conf.d/10-opcache.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

### 2. PHP-FPM

```ini
; Dans /etc/php/8.1/fpm/pool.d/www.conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 15
pm.max_requests = 500
```

### 3. MySQL

```ini
; Dans /etc/mysql/my.cnf
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 0
query_cache_type = 0
```

---

## 🆘 Troubleshooting

### Erreur 500

```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier les permissions
sudo chown -R www-data:www-data /var/www/boxibox
```

### Queue Workers Ne Démarrent Pas

```bash
# Vérifier le statut
sudo supervisorctl status

# Logs
tail -f storage/logs/worker.log

# Restart
sudo supervisorctl restart boxibox-worker:*
```

### Problèmes de Connexion Base de Données

```bash
# Tester la connexion
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## 📞 Support

Pour toute question :
- **Documentation** : [GitHub Repository](https://github.com/haythemsaa/boxibox)
- **Issues** : [GitHub Issues](https://github.com/haythemsaa/boxibox/issues)

---

**Déploiement réalisé avec succès ! 🎉**
