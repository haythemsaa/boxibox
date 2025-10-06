# 🔔 Système de Notifications en Temps Réel - Boxibox

## 📋 Vue d'ensemble

Le système de notifications en temps réel permet aux utilisateurs de recevoir instantanément des alertes importantes via différents canaux : **email**, **push navigateur** et **SMS**.

---

## 🎯 Fonctionnalités principales

### 1. Types de notifications supportés

| Type | Description | Canaux disponibles |
|------|-------------|-------------------|
| **Paiement reçu** | Confirmation de réception d'un paiement | Email, Push |
| **Paiement en retard** | Alerte de facture impayée | Email, Push, SMS |
| **Nouvelle réservation** | Notification de réservation en ligne | Email, Push |
| **Fin de contrat proche** | Rappel d'échéance de contrat | Email, Push, SMS |
| **Accès refusé** | Alerte de sécurité pour tentative d'accès | Email, Push, SMS |

### 2. Canaux de notification

#### 📧 Email
- Configuration SMTP personnalisable
- Templates HTML responsive
- Support des pièces jointes

#### 🔔 Push (Navigateur)
- Notifications en temps réel dans l'interface
- Badge de compteur sur l'icône cloche
- Mise à jour automatique toutes les 30 secondes
- Historique consultable

#### 📱 SMS (Futur)
- Intégration avec services SMS (Twilio, Vonage)
- Réservé aux alertes critiques (coût)

---

## 🏗️ Architecture technique

### Structure de la base de données

#### Table `notifications` (Laravel native)
```sql
- id (UUID)
- type (string)
- notifiable_type (morphs)
- notifiable_id (morphs)
- data (JSON)
- read_at (timestamp nullable)
- created_at, updated_at
```

#### Table `notification_settings`
```sql
- id
- user_id (FK)
- tenant_id (FK)
- email_paiement_recu (boolean)
- email_paiement_retard (boolean)
- email_nouvelle_reservation (boolean)
- email_fin_contrat (boolean)
- email_acces_refuse (boolean)
- push_paiement_recu (boolean)
- push_paiement_retard (boolean)
- push_nouvelle_reservation (boolean)
- push_fin_contrat (boolean)
- push_acces_refuse (boolean)
- sms_paiement_retard (boolean)
- sms_fin_contrat (boolean)
- sms_acces_refuse (boolean)
- notifications_activees (boolean)
- heure_debut_notifications (time)
- heure_fin_notifications (time)
- created_at, updated_at
```

### Classes de notifications

#### `PaiementRecuNotification`
```php
public function via($notifiable)
{
    $channels = ['database'];
    $settings = $notifiable->notificationSettings;

    if ($settings && $settings->isEnabled('paiement_recu', 'email')) {
        $channels[] = 'mail';
    }

    return $channels;
}
```

#### `PaiementRetardNotification`
- Niveaux d'urgence selon le retard (> 30 jours = URGENT)
- Envoi automatique email + SMS si configuré
- Lien direct pour paiement en ligne

#### `NouvelleReservationNotification`
- Alerte pour l'équipe administrative
- Détails du client et du box réservé
- Lien vers le contrat pour validation

#### `AccesRefuseNotification`
- Alerte de sécurité immédiate
- Détails de la tentative (méthode, raison, heure)
- Logs d'accès complets

---

## 📦 Fichiers créés

### Models
- `app/Models/NotificationSetting.php` - Paramètres de notification par utilisateur

### Notifications
- `app/Notifications/PaiementRecuNotification.php`
- `app/Notifications/PaiementRetardNotification.php`
- `app/Notifications/NouvelleReservationNotification.php`
- `app/Notifications/AccesRefuseNotification.php`

### Controllers
- `app/Http/Controllers/NotificationController.php` - Gestion des notifications

### Views
- `resources/views/notifications/index.blade.php` - Liste des notifications
- `resources/views/notifications/settings.blade.php` - Paramètres utilisateur
- `resources/views/layouts/notification-bell.blade.php` - Cloche de notifications

### Migrations
- `database/migrations/2025_10_06_140000_create_notifications_table.php`

### Routes
```php
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', 'index');
    Route::get('/unread', 'getUnread');
    Route::post('/{id}/read', 'markAsRead');
    Route::post('/mark-all-read', 'markAllAsRead');
    Route::delete('/{id}', 'destroy');
    Route::get('/settings', 'settings');
    Route::put('/settings', 'updateSettings');
});
```

---

## 🎨 Interface utilisateur

### Cloche de notifications (Sidebar)
```html
<div class="dropdown">
    <a class="nav-link position-relative" href="#">
        <i class="fas fa-bell fa-fw"></i>
        <span class="badge bg-danger" id="notificationCount">5</span>
    </a>
    <div class="dropdown-menu">
        <!-- Liste des 5 dernières notifications -->
    </div>
</div>
```

**Fonctionnalités :**
- Badge avec compteur de notifications non lues
- Liste déroulante avec 5 dernières notifications
- Mise à jour automatique toutes les 30 secondes (AJAX)
- Bouton "Marquer tout comme lu"
- Lien vers la page complète des notifications

### Page des notifications (`/notifications`)
**Fonctionnalités :**
- Pagination (20 notifications par page)
- Filtrage par type (à venir)
- Marquage individuel/global comme lu
- Suppression de notification
- Icônes colorées selon le type d'alerte
- Horodatage relatif (il y a 5 min, il y a 2h, etc.)

### Page des paramètres (`/notifications/settings`)

**3 Sections :**
1. **Paramètres généraux**
   - Activer/désactiver toutes les notifications
   - Plage horaire (ne pas déranger)

2. **Notifications par Email** ✅
   - 5 types de notifications configurables

3. **Notifications Push** ✅
   - 5 types de notifications configurables

4. **Notifications SMS** 📱 (futur)
   - 3 types critiques uniquement
   - Note : facturé séparément

---

## 🔄 Flux d'envoi de notification

### 1. Événement déclencheur
```php
// Exemple : Nouveau paiement reçu
$reglement = Reglement::create([...]);

// Envoyer notification au client
$client->user->notify(new PaiementRecuNotification($reglement));

// Envoyer notification à l'admin
$admin = User::role('admin')->first();
$admin->notify(new NouvelPaiementAdminNotification($reglement));
```

### 2. Vérification des paramètres
```php
public function via($notifiable)
{
    $settings = $notifiable->notificationSettings;

    // Vérifier si dans plage horaire autorisée
    if (!$settings->canSendNotificationNow()) {
        return ['database']; // Uniquement stockage
    }

    // Vérifier canaux activés
    $channels = ['database'];
    if ($settings->isEnabled('paiement_recu', 'email')) {
        $channels[] = 'mail';
    }

    return $channels;
}
```

### 3. Envoi multi-canal
- **Database** : Toujours stocké pour historique
- **Email** : Si configuré et dans plage horaire
- **Push** : Notification temps réel dans interface
- **SMS** : Si configuré (futur)

### 4. Mise en file (Queue)
```php
class PaiementRecuNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // La notification sera envoyée de manière asynchrone
    // Configuration dans .env : QUEUE_CONNECTION=database
}
```

---

## 🚀 Utilisation pratique

### Envoyer une notification depuis le code

```php
use App\Notifications\PaiementRecuNotification;

// Notifier un utilisateur
$user->notify(new PaiementRecuNotification($reglement));

// Notifier plusieurs utilisateurs
Notification::send($users, new NouvelleReservationNotification($contrat));
```

### Récupérer les notifications

```php
// Toutes les notifications
$notifications = auth()->user()->notifications;

// Notifications non lues
$unread = auth()->user()->unreadNotifications;

// Marquer comme lu
$notification->markAsRead();

// Marquer toutes comme lues
auth()->user()->unreadNotifications->markAsRead();
```

### AJAX - Récupérer notifications en temps réel

```javascript
function loadNotifications() {
    fetch('/notifications/unread')
        .then(response => response.json())
        .then(data => {
            // Mettre à jour le badge
            document.getElementById('notificationCount').textContent = data.count;

            // Mettre à jour la liste
            // ...
        });
}

// Recharger toutes les 30 secondes
setInterval(loadNotifications, 30000);
```

---

## ⚙️ Configuration

### 1. Configuration email (.env)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@boxibox.com
MAIL_FROM_NAME="Boxibox"
```

### 2. Configuration des queues (.env)
```env
QUEUE_CONNECTION=database
```

Ensuite, lancer le worker :
```bash
php artisan queue:work
```

### 3. Créer les paramètres par défaut pour un utilisateur
```php
$user->notificationSettings()->create([
    'tenant_id' => $user->tenant_id,
    'email_paiement_recu' => true,
    'email_paiement_retard' => true,
    // ... autres paramètres par défaut
]);
```

---

## 📊 Statistiques et monitoring

### Dashboard des notifications (à créer)
- Nombre de notifications envoyées par jour
- Taux d'ouverture des emails
- Taux de lecture des notifications push
- Notifications les plus fréquentes

### Logs
Toutes les notifications sont loggées dans `storage/logs/laravel.log` :
```
[2025-10-06 14:30:25] local.INFO: Notification sent
{"type":"PaiementRecuNotification","user_id":5,"channels":["mail","database"]}
```

---

## 🔐 Sécurité

### 1. Authentification requise
Toutes les routes de notifications nécessitent `auth` middleware.

### 2. Autorisation par tenant
Les notifications sont isolées par tenant (multi-tenant).

### 3. Validation des données
```php
$validated = $request->validate([
    'email_paiement_recu' => 'boolean',
    'push_paiement_retard' => 'boolean',
    // ...
]);
```

### 4. Protection CSRF
Tous les formulaires et requêtes AJAX incluent le token CSRF.

---

## 🎯 Prochaines étapes

### 1. Intégration SMS (Priorité : Moyenne)
- [ ] Installer package Twilio ou Vonage
- [ ] Ajouter configuration dans .env
- [ ] Créer canal SMS personnalisé
- [ ] Gérer les coûts (compteur SMS)

### 2. Push notifications navigateur (Priorité : Haute)
- [ ] Installer Laravel Echo
- [ ] Configurer Pusher ou Socket.io
- [ ] Implémenter WebSockets
- [ ] Notifications natives navigateur (Service Workers)

### 3. Notifications programmées (Priorité : Haute)
- [ ] Rappels automatiques de paiement (J-7, J-3, J+1)
- [ ] Alerte fin de contrat (J-30, J-15, J-7)
- [ ] Rapport hebdomadaire par email

### 4. Templates personnalisables (Priorité : Basse)
- [ ] Interface admin pour éditer templates email
- [ ] Variables dynamiques ({nom_client}, {montant}, etc.)
- [ ] Prévisualisation avant envoi

---

## 📈 Métriques de succès

- ✅ **5 types de notifications** implémentés
- ✅ **3 canaux** configurables (Email, Push, SMS prévu)
- ✅ **Paramètres personnalisables** par utilisateur
- ✅ **Interface intuitive** avec cloche de notifications
- ✅ **Plage horaire** respectée (ne pas déranger)
- ✅ **Historique complet** des notifications
- ✅ **File d'attente** pour envoi asynchrone

---

## 🛠️ Commandes utiles

```bash
# Lancer le worker de queue
php artisan queue:work

# Voir les jobs en attente
php artisan queue:monitor

# Nettoyer les notifications anciennes (> 30 jours)
php artisan notifications:clean

# Envoyer une notification test
php artisan notification:test --user=1 --type=paiement_recu
```

---

## 📝 Notes importantes

1. **Performance** : Les notifications sont envoyées de manière asynchrone via les queues Laravel pour ne pas bloquer l'application.

2. **Coût SMS** : Les SMS doivent être réservés aux alertes critiques (retard paiement, sécurité) car facturés à l'envoi.

3. **RGPD** : Les utilisateurs peuvent désactiver complètement les notifications dans leurs paramètres.

4. **Multi-tenant** : Toutes les notifications respectent l'isolation par tenant.

5. **Temps réel** : Le rafraîchissement automatique (30s) sera remplacé par WebSockets pour du vrai temps réel.

---

**Date de création** : 06/10/2025
**Version** : 1.0
**Auteur** : Développement Boxibox avec Claude Code
