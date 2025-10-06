# 🔐 MODULE GESTION DES ACCÈS PHYSIQUES - DOCUMENTATION

**Date de création**: 06 Octobre 2025
**Version**: 1.0.0
**Statut**: ✅ Backend complet (Interface admin à créer)

---

## 🎯 VUE D'ENSEMBLE

Le **Module de Gestion des Accès** permet de contrôler les accès physiques aux boxes de stockage via différentes méthodes (codes PIN, QR codes, badges RFID). Il enregistre tous les événements d'accès pour audit et sécurité.

**Fonctionnalités clés**:
- ✅ Génération automatique codes PIN 6 chiffres
- ✅ Génération QR codes uniques
- ✅ Gestion restrictions temporelles (dates, heures, jours)
- ✅ Logging complet entrées/sorties
- ✅ Validation multi-critères
- ✅ Support badges RFID (prévu)

---

## 📊 ARCHITECTURE BASE DE DONNÉES

### **Table `access_codes`** (Codes d'accès)

```sql
CREATE TABLE access_codes (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT,
    client_id BIGINT,
    box_id BIGINT NULLABLE,
    contrat_id BIGINT NULLABLE,

    -- Code d'accès
    code_pin VARCHAR(6),           -- Ex: "123456"
    qr_code_data VARCHAR UNIQUE,   -- UUID unique
    qr_code_path VARCHAR,          -- Chemin fichier SVG

    -- Type et statut
    type ENUM('pin', 'qr', 'badge') DEFAULT 'pin',
    statut ENUM('actif', 'expire', 'suspendu', 'revoque'),

    -- Validité temporelle
    date_debut TIMESTAMP,
    date_fin TIMESTAMP,
    temporaire BOOLEAN DEFAULT false,

    -- Restrictions
    jours_autorises JSON,          -- ['lundi', 'mardi', ...]
    heure_debut TIME,              -- '08:00'
    heure_fin TIME,                -- '20:00'
    max_utilisations INT,          -- Limite usage
    nb_utilisations INT DEFAULT 0,

    -- Métadonnées
    notes TEXT,
    derniere_utilisation TIMESTAMP,

    created_at, updated_at, deleted_at,

    INDEX(client_id, statut),
    INDEX(code_pin),
    INDEX(qr_code_data)
);
```

### **Table `access_logs`** (Historique des accès)

```sql
CREATE TABLE access_logs (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT,
    access_code_id BIGINT NULLABLE,
    client_id BIGINT,
    box_id BIGINT NULLABLE,

    -- Détails accès
    type_acces ENUM('entree', 'sortie') DEFAULT 'entree',
    methode ENUM('pin', 'qr', 'badge', 'manuel', 'maintenance'),
    statut ENUM('autorise', 'refuse', 'erreur'),

    date_heure TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Informations complémentaires
    code_utilise VARCHAR,          -- Code utilisé
    terminal_id VARCHAR,           -- ID terminal/lecteur
    emplacement VARCHAR,           -- Localisation précise
    ip_address VARCHAR(45),        -- IPv4/IPv6
    user_agent TEXT,               -- Si web

    raison_refus VARCHAR,          -- Si refusé
    metadata JSON,                 -- Données additionnelles
    notes TEXT,

    created_at, updated_at,

    INDEX(client_id, date_heure),
    INDEX(box_id, date_heure),
    INDEX(date_heure)
);
```

---

## 🔧 MODÈLES ELOQUENT

### **AccessCode Model**

```php
class AccessCode extends Model
{
    use SoftDeletes;

    // Relations
    public function client() { return $this->belongsTo(Client::class); }
    public function box() { return $this->belongsTo(Box::class); }
    public function contrat() { return $this->belongsTo(Contrat::class); }
    public function logs() { return $this->hasMany(AccessLog::class); }

    // Scopes
    public function scopeActif($query)
    public function scopeValide($query)
    public function scopeExpire($query)

    // Méthodes principales
    public static function generateUniquePinCode()  // Génère PIN 6 chiffres unique
    public function generateQRCode()               // Génère QR code SVG
    public function isValid()                      // Validation multi-critères
    public function recordUsage()                  // Enregistre utilisation
    public function revoke($reason)                // Révoquer
    public function suspend($reason)               // Suspendre
    public function reactivate()                   // Réactiver
}
```

#### **Méthode de validation complète**:
```php
public function isValid()
{
    // 1. Vérifier statut
    if ($this->statut !== 'actif') {
        return ['valid' => false, 'reason' => 'Code non actif'];
    }

    // 2. Vérifier dates
    if ($this->date_debut && Carbon::parse($this->date_debut)->isFuture()) {
        return ['valid' => false, 'reason' => 'Code pas encore actif'];
    }

    if ($this->date_fin && Carbon::parse($this->date_fin)->isPast()) {
        return ['valid' => false, 'reason' => 'Code expiré'];
    }

    // 3. Vérifier utilisations
    if ($this->max_utilisations && $this->nb_utilisations >= $this->max_utilisations) {
        return ['valid' => false, 'reason' => 'Limite atteinte'];
    }

    // 4. Vérifier jours autorisés
    if ($this->jours_autorises) {
        $jourActuel = strtolower(Carbon::now()->locale('fr')->dayName);
        if (!in_array($jourActuel, array_map('strtolower', $this->jours_autorises))) {
            return ['valid' => false, 'reason' => 'Jour non autorisé'];
        }
    }

    // 5. Vérifier heures
    if ($this->heure_debut && $this->heure_fin) {
        $heureActuelle = Carbon::now()->format('H:i:s');
        if ($heureActuelle < $this->heure_debut || $heureActuelle > $this->heure_fin) {
            return ['valid' => false, 'reason' => 'Hors horaire'];
        }
    }

    return ['valid' => true, 'reason' => null];
}
```

### **AccessLog Model**

```php
class AccessLog extends Model
{
    // Relations
    public function accessCode() { return $this->belongsTo(AccessCode::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function box() { return $this->belongsTo(Box::class); }

    // Scopes temporels
    public function scopeAujourdhui($query)
    public function scopeCetteSemaine($query)
    public function scopeCeMois($query)

    // Scopes par type
    public function scopeAutorise($query)
    public function scopeRefuse($query)
    public function scopeEntree($query)
    public function scopeSortie($query)

    // Méthodes de logging
    public static function logAccess($data)                                    // Log générique
    public static function verifyAndLogPinAccess($pin, $boxId, $type)         // Vérif + log PIN
    public static function verifyAndLogQrAccess($qrData, $boxId, $type)       // Vérif + log QR
}
```

---

## 🚀 UTILISATION

### **1. Créer un code PIN pour un client**

```php
use App\Models\AccessCode;
use App\Models\Client;

$client = Client::find(1);

$accessCode = AccessCode::create([
    'tenant_id' => auth()->user()->tenant_id,
    'client_id' => $client->id,
    'box_id' => 5, // Box spécifique ou null pour tous
    'contrat_id' => $client->contrats()->first()->id,
    'code_pin' => AccessCode::generateUniquePinCode(), // Ex: "482917"
    'type' => 'pin',
    'statut' => 'actif',
    'date_debut' => now(),
    'date_fin' => now()->addMonths(3), // 3 mois
    'temporaire' => false,
    'jours_autorises' => ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'], // Semaine
    'heure_debut' => '08:00',
    'heure_fin' => '20:00',
    'max_utilisations' => null, // Illimité
    'notes' => 'Code principal client',
]);
```

### **2. Créer un QR code temporaire**

```php
$accessCode = AccessCode::create([
    'tenant_id' => auth()->user()->tenant_id,
    'client_id' => $client->id,
    'box_id' => 5,
    'type' => 'qr',
    'statut' => 'actif',
    'date_debut' => now(),
    'date_fin' => now()->addDay(), // 24h
    'temporaire' => true,
    'max_utilisations' => 2, // 2 utilisations max
    'notes' => 'Code temporaire déménagement',
]);

// Générer le QR code image
$qrPath = $accessCode->generateQRCode();
// Retourne: 'qr_codes/1_1696598400.svg'

// URL publique du QR code
$qrUrl = asset('storage/' . $accessCode->qr_code_path);
```

### **3. Vérifier un code PIN (terminal d'accès)**

```php
use App\Models\AccessLog;

// Simulation terminal à l'entrée
$pin = '482917'; // Code tapé par le client
$boxId = 5;
$type = 'entree';

$log = AccessLog::verifyAndLogPinAccess($pin, $boxId, $type);

if ($log->statut === 'autorise') {
    // ✅ Ouvrir la porte
    echo "Accès autorisé - Bienvenue !";
} else {
    // ❌ Refuser l'accès
    echo "Accès refusé : " . $log->raison_refus;
}
```

### **4. Scanner un QR code (lecteur mobile)**

```php
$qrData = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d'; // UUID du QR
$boxId = 5;
$type = 'sortie';

$log = AccessLog::verifyAndLogQrAccess($qrData, $boxId, $type);

if ($log->statut === 'autorise') {
    echo "Accès autorisé";
} else {
    echo "Accès refusé : " . $log->raison_refus;
}
```

### **5. Gérer le cycle de vie d'un code**

```php
$code = AccessCode::find(1);

// Suspendre temporairement (ex: impayé)
$code->suspend("Client en impayé");

// Réactiver après paiement
$code->reactivate();

// Révoquer définitivement (ex: fin contrat)
$code->revoke("Fin de contrat");
```

### **6. Consulter l'historique des accès**

```php
// Accès du jour
$logsJour = AccessLog::aujourdhui()->get();

// Accès refusés cette semaine
$refusesSemaine = AccessLog::cetteSemaine()->refuse()->get();

// Accès d'un client spécifique
$logsClient = AccessLog::where('client_id', 1)
    ->orderBy('date_heure', 'desc')
    ->limit(10)
    ->get();

// Statistiques par box
$statsBox = AccessLog::where('box_id', 5)
    ->selectRaw('
        COUNT(*) as total,
        SUM(CASE WHEN statut = "autorise" THEN 1 ELSE 0 END) as autorises,
        SUM(CASE WHEN statut = "refuse" THEN 1 ELSE 0 END) as refuses
    ')
    ->first();
```

---

## 📱 CAS D'USAGE PRATIQUES

### **Cas 1: Code permanent client**
```php
AccessCode::create([
    'client_id' => $client->id,
    'code_pin' => AccessCode::generateUniquePinCode(),
    'type' => 'pin',
    'statut' => 'actif',
    'date_debut' => $contrat->date_debut,
    'date_fin' => $contrat->date_fin,
    'jours_autorises' => null, // Tous les jours
    'heure_debut' => null,     // 24/7
    'heure_fin' => null,
    'max_utilisations' => null, // Illimité
]);
```

### **Cas 2: Code invité temporaire**
```php
AccessCode::create([
    'client_id' => $client->id,
    'code_pin' => AccessCode::generateUniquePinCode(),
    'type' => 'pin',
    'statut' => 'actif',
    'date_debut' => now(),
    'date_fin' => now()->addHours(4), // 4 heures
    'temporaire' => true,
    'max_utilisations' => 1, // Une seule utilisation
    'notes' => 'Code invité - Dépôt colis',
]);
```

### **Cas 3: Code maintenance**
```php
AccessCode::create([
    'client_id' => $technicien->id,
    'type' => 'pin',
    'statut' => 'actif',
    'date_debut' => now(),
    'date_fin' => now()->endOfDay(),
    'jours_autorises' => null, // Tous les jours
    'heure_debut' => '06:00',
    'heure_fin' => '22:00',
    'notes' => 'Technicien maintenance',
]);
```

### **Cas 4: QR code déménagement**
```php
$code = AccessCode::create([
    'client_id' => $client->id,
    'box_id' => $box->id,
    'type' => 'qr',
    'statut' => 'actif',
    'date_debut' => now(),
    'date_fin' => now()->addDays(2), // 48h
    'temporaire' => true,
    'max_utilisations' => 10, // Max 10 allers-retours
]);

$qrPath = $code->generateQRCode();
// Envoyer QR par email au client
```

---

## 🔔 ALERTES & NOTIFICATIONS

### **Alertes automatiques à implémenter**:

1. **Code expiré automatiquement** (Cron job):
```php
// Artisan command: php artisan access:expire-codes
AccessCode::where('statut', 'actif')
    ->where('date_fin', '<', now())
    ->update(['statut' => 'expire']);
```

2. **Alerte tentatives d'accès refusées**:
```php
// Si 3 refus en 5 minutes → Notification admin
$refusRecents = AccessLog::where('client_id', $clientId)
    ->where('statut', 'refuse')
    ->where('date_heure', '>', now()->subMinutes(5))
    ->count();

if ($refusRecents >= 3) {
    // Envoyer alerte admin
    Notification::send($admin, new AccessDeniedAlert($client));
}
```

3. **Notification code proche expiration**:
```php
// 24h avant expiration
$codesExpireSoon = AccessCode::actif()
    ->whereBetween('date_fin', [now(), now()->addDay()])
    ->get();

foreach ($codesExpireSoon as $code) {
    Mail::to($code->client->email)->send(new CodeExpirationReminder($code));
}
```

---

## 📊 STATISTIQUES & REPORTING

### **Dashboard admin - Métriques utiles**:

```php
// KPIs accès
$stats = [
    'codes_actifs' => AccessCode::actif()->count(),
    'acces_jour' => AccessLog::aujourdhui()->count(),
    'acces_autorises_jour' => AccessLog::aujourdhui()->autorise()->count(),
    'acces_refuses_jour' => AccessLog::aujourdhui()->refuse()->count(),
    'taux_refus' => AccessLog::aujourdhui()->refuse()->count() / max(AccessLog::aujourdhui()->count(), 1) * 100,
];

// Top 5 clients avec le plus d'accès
$topClients = AccessLog::selectRaw('client_id, COUNT(*) as nb_acces')
    ->ceMois()
    ->groupBy('client_id')
    ->orderByDesc('nb_acces')
    ->limit(5)
    ->with('client')
    ->get();

// Graphique accès par heure (24h)
$accesByHour = AccessLog::aujourdhui()
    ->selectRaw('HOUR(date_heure) as heure, COUNT(*) as nb')
    ->groupBy('heure')
    ->orderBy('heure')
    ->get();
```

---

## 🛡️ SÉCURITÉ

### **Mesures de sécurité implémentées**:

1. ✅ **Codes PIN uniques** - Génération avec vérification unicité
2. ✅ **QR codes UUID** - Impossible à deviner
3. ✅ **Soft delete** - Codes supprimés conservés pour audit
4. ✅ **Logging complet** - Toutes tentatives enregistrées (IP, user-agent)
5. ✅ **Validation multi-critères** - Date, heure, jour, utilisations
6. ✅ **Révocation immédiate** - Désactivation instantanée
7. ✅ **Isolation multi-tenant** - Codes isolés par tenant_id

### **Recommandations additionnelles**:

- 🔒 **Chiffrer les codes PIN** en base (Laravel Crypt)
- 🔒 **Rate limiting** sur API de vérification (max 5 tentatives/min)
- 🔒 **Authentification 2FA** pour révocation de codes
- 🔒 **Audit trail** immuable (blockchain future)
- 🔒 **Caméras synchronisées** avec logs d'accès

---

## 🚀 INTÉGRATIONS MATÉRIELLES

### **Terminaux d'accès compatibles**:

#### **1. Terminal PIN (clavier physique)**
```php
// API endpoint pour vérification
POST /api/access/verify-pin
{
    "pin": "482917",
    "box_id": 5,
    "type": "entree",
    "terminal_id": "TERM-001"
}

Response:
{
    "authorized": true,
    "message": "Accès autorisé",
    "client_name": "Jean Dupont",
    "box_numero": "A-05"
}
```

#### **2. Lecteur QR code (scanner mobile)**
```php
// API endpoint pour scan QR
POST /api/access/verify-qr
{
    "qr_data": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
    "box_id": 5,
    "type": "sortie",
    "terminal_id": "SCANNER-002"
}
```

#### **3. Lecteur RFID/NFC (à venir)**
```php
// Prévu pour badges physiques
POST /api/access/verify-badge
{
    "badge_uid": "04:3A:B2:1C",
    "box_id": 5,
    "type": "entree"
}
```

---

## 📱 APPLICATION MOBILE (Future)

### **Fonctionnalités app mobile**:
- ✅ Affichage code PIN client
- ✅ Génération QR code dynamique
- ✅ Scan QR code invité
- ✅ Historique accès personnel
- ✅ Notifications accès temps réel
- ✅ Génération codes temporaires invités

---

## 🧪 TESTS

### **Scénarios de test essentiels**:

```php
// Test validation code PIN
public function test_code_pin_valid()
public function test_code_pin_expire()
public function test_code_pin_hors_horaire()
public function test_code_pin_jour_non_autorise()
public function test_code_pin_limite_utilisations()

// Test génération
public function test_generation_pin_unique()
public function test_generation_qr_code()

// Test logging
public function test_log_acces_autorise()
public function test_log_acces_refuse()
public function test_log_avec_ip()

// Test révocation
public function test_revocation_code()
public function test_suspension_code()
```

---

## 📞 API DOCUMENTATION

### **Endpoints principaux**:

```
POST   /api/access/verify-pin      - Vérifier code PIN
POST   /api/access/verify-qr       - Vérifier QR code
GET    /api/access/codes           - Liste codes client
POST   /api/access/codes           - Créer code
PUT    /api/access/codes/{id}      - Modifier code
DELETE /api/access/codes/{id}      - Révoquer code
GET    /api/access/logs            - Historique accès
GET    /api/access/logs/{id}       - Détails log
GET    /api/access/stats           - Statistiques
```

---

## 📚 COMMANDES ARTISAN

```bash
# Créer tables
php artisan migrate

# Expirer codes automatiquement (Cron)
php artisan access:expire-codes

# Nettoyer logs anciens (> 1 an)
php artisan access:clean-logs

# Générer rapport accès mensuel
php artisan access:monthly-report
```

---

## 🎯 PROCHAINES AMÉLIORATIONS

### **Phase 2** (Semaine prochaine):
1. ✅ Interface admin gestion codes
2. ✅ API REST complète
3. ✅ Dashboard temps réel (WebSocket)
4. ✅ Notifications push

### **Phase 3** (Mois prochain):
5. ✅ Support badges RFID
6. ✅ Intégration caméras
7. ✅ Reconnaissance faciale (IA)
8. ✅ App mobile (React Native)

---

✅ **Module Gestion des Accès - Backend 100% opérationnel !**
🔜 **Interface admin et API REST à créer**

---

*Documentation créée le 06/10/2025*
