# 🚀 Guide de Déploiement Boxibox

## ✅ Étapes d'Installation Complètes

### 1. Installation Automatique

**Windows :**
```cmd
install.bat
```

**Linux/macOS :**
```bash
chmod +x install.sh
./install.sh
```

### 2. Vérification Post-Installation

Après l'installation, vérifiez que tout fonctionne :

```bash
# Vérifier les tables de base de données
php artisan migrate:status

# Vérifier les permissions
php artisan boxibox:check-permissions

# Tester les commandes automatiques
php artisan boxibox:generate-invoices --dry-run
php artisan boxibox:send-reminders --dry-run
php artisan boxibox:generate-sepa --dry-run
```

### 3. Configuration des Tâches Automatisées

Ajoutez dans votre crontab (Linux/macOS) :
```bash
# Ouvrir crontab
crontab -e

# Ajouter cette ligne pour activer le scheduler Laravel
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Ou sur Windows, configurez une tâche planifiée :**
```cmd
schtasks /create /sc minute /mo 1 /tn "Laravel Scheduler" /tr "cd C:\xampp\htdocs\boxibox && php artisan schedule:run"
```

### 4. Configuration des Services

#### Configuration Email
Éditez votre `.env` :
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="Boxibox"
```

#### Configuration SEPA
```env
SEPA_CREDITOR_ID=FR12ZZZ123456
SEPA_CREDITOR_NAME="Votre Société"
SEPA_CREDITOR_IBAN=FR1234567890123456789012345
SEPA_CREDITOR_BIC=BANKFRPP
```

### 5. Première Configuration

1. **Connexion Administrateur :**
   - URL : `http://votre-domaine.com/boxibox`
   - Email : `admin@boxibox.com`
   - Mot de passe : `admin123`

2. **Configuration Initiale :**
   - Changez le mot de passe administrateur
   - Configurez les informations de l'entreprise
   - Créez vos utilisateurs
   - Configurez vos emplacements et boxes

### 6. Tests des Fonctionnalités

#### Test du Dashboard
- Vérifiez que les statistiques s'affichent
- Contrôlez les graphiques

#### Test des Prospects
```bash
# Créer un prospect de test
php artisan tinker
>>> App\Models\Prospect::factory()->create()
```

#### Test de la Facturation
```bash
# Simuler la génération de factures
php artisan boxibox:generate-invoices --dry-run
```

#### Test SEPA
```bash
# Tester la génération SEPA
php artisan boxibox:generate-sepa --dry-run
```

## 🔧 Maintenance et Administration

### Commandes Utiles

**Génération de factures :**
```bash
# Générer les factures du mois courant
php artisan boxibox:generate-invoices

# Générer pour un mois spécifique
php artisan boxibox:generate-invoices --month=2024-03

# Mode simulation
php artisan boxibox:generate-invoices --dry-run
```

**Gestion des relances :**
```bash
# Envoyer les relances automatiques
php artisan boxibox:send-reminders

# Forcer les relances
php artisan boxibox:send-reminders --force

# Mode simulation
php artisan boxibox:send-reminders --dry-run
```

**Exports SEPA :**
```bash
# Générer un fichier SEPA
php artisan boxibox:generate-sepa --date=2024-03-15

# Pour des factures spécifiques
php artisan boxibox:generate-sepa --factures=123,124,125
```

### Surveillance et Logs

**Vérifier les logs :**
```bash
tail -f storage/logs/laravel.log
```

**Nettoyer les caches :**
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Sauvegarde

**Sauvegarde manuelle :**
```bash
# Base de données
mysqldump -u username -p boxibox > backup_$(date +%Y%m%d).sql

# Fichiers documents
tar -czf documents_backup.tar.gz storage/app/documents/

# Configuration
cp .env .env.backup
```

## 🔐 Sécurité

### Permissions Recommandées

**Linux/macOS :**
```bash
# Application
chmod -R 755 /path/to/boxibox
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Configuration
chmod 600 .env
```

**Windows (XAMPP) :**
- Assurez-vous que les dossiers `storage` et `bootstrap/cache` sont accessibles en écriture
- Protégez le fichier `.env` en limitant l'accès

### Mises à Jour

```bash
# Sauvegarder avant mise à jour
php artisan down

# Récupérer les nouveautés
git pull origin main
composer install --no-dev

# Mettre à jour la base de données
php artisan migrate

# Nettoyer les caches
php artisan optimize:clear
php artisan config:cache

# Remettre en ligne
php artisan up
```

## 📞 Support et Dépannage

### Logs Importants

- **Application :** `storage/logs/laravel.log`
- **SEPA :** `storage/logs/sepa.log`
- **Scheduler :** Logs système cron/tâches planifiées

### Problèmes Courants

**Erreur 500 :**
```bash
# Vérifier les permissions
chmod -R 775 storage bootstrap/cache

# Vérifier les logs
tail -f storage/logs/laravel.log
```

**Commandes qui ne fonctionnent pas :**
```bash
# Vérifier la configuration
php artisan config:clear

# Recréer l'autoload
composer dump-autoload
```

**SEPA ne fonctionne pas :**
- Vérifiez la configuration dans `.env`
- Contrôlez les permissions du dossier `storage/sepa`
- Validez les formats IBAN/BIC

### Contact

- **Documentation** : Voir `README.md`
- **Issues** : GitHub Issues
- **Support** : support@boxibox.com

---

✅ **Installation terminée !** Votre système Boxibox est prêt à gérer votre centre de self-stockage.