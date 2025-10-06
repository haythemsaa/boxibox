# 📍 Accès aux Formulaires de Création - BOXIBOX

## 🔍 Comment Accéder aux Formulaires

### 1. 📄 **Créer une Facture**

#### Méthode 1 : Depuis la Page des Factures
```
1. Menu → Finance → Factures
2. URL: /finance/factures
3. Cliquer sur le bouton "Nouvelle Facture" en haut à droite
```

#### Méthode 2 : URL Directe
```
http://127.0.0.1:8000/finance/factures/create
```

**Fichier Vue.js :** `resources/js/Pages/Admin/FactureCreate.vue`

---

### 2. 📝 **Créer un Contrat**

#### Méthode 1 : Depuis la Page des Contrats
```
1. Menu → Commercial → Contrats
2. URL: /commercial/contrats
3. Cliquer sur le bouton "Nouveau Contrat" en haut à droite
```

#### Méthode 2 : URL Directe
```
http://127.0.0.1:8000/commercial/contrats/create
```

**Fichier Vue.js :** `resources/js/Pages/Admin/ContratCreate.vue`

---

## 🗺️ Structure des Routes

### Routes Contrats
```php
// routes/web.php
Route::prefix('commercial')->group(function () {
    Route::resource('contrats', ContratController::class);
    // Génère automatiquement :
    // GET  /commercial/contrats         -> index (liste)
    // GET  /commercial/contrats/create  -> create (formulaire)
    // POST /commercial/contrats         -> store (enregistrement)
});
```

### Routes Factures
```php
Route::prefix('finance')->group(function () {
    Route::resource('factures', FactureController::class);
    // Génère automatiquement :
    // GET  /finance/factures         -> index (liste)
    // GET  /finance/factures/create  -> create (formulaire)
    // POST /finance/factures         -> store (enregistrement)
});
```

---

## 🎯 Pages Disponibles

### Pages de Liste (avec bouton "Nouveau")

| Page | URL | Bouton | Composant Vue |
|------|-----|--------|---------------|
| **Contrats** | `/commercial/contrats` | ✅ "Nouveau Contrat" | `ContratsManage.vue` |
| **Factures** | `/finance/factures` | ✅ "Nouvelle Facture" | `FacturesManage.vue` |

### Pages de Création (Formulaires)

| Formulaire | URL | Composant Vue |
|------------|-----|---------------|
| **Créer Contrat** | `/commercial/contrats/create` | `ContratCreate.vue` |
| **Créer Facture** | `/finance/factures/create` | `FactureCreate.vue` |

---

## 🖼️ Aperçu Visuel

### Page Liste Contrats

```
┌─────────────────────────────────────────────────┐
│  📋 Gestion des Contrats      [➕ Nouveau Contrat]│
├─────────────────────────────────────────────────┤
│  🔍 Filtres...                                   │
├─────────────────────────────────────────────────┤
│  📊 Tableau éditable des contrats               │
└─────────────────────────────────────────────────┘
```

### Formulaire Création Contrat

```
┌─────────────────────────────────────────────────┐
│  📝 Créer un Nouveau Contrat          [← Retour]│
├─────────────────────────────────────────────────┤
│  Étape 1/4 : Sélection Client                   │
│  [●]━━━[○]━━━[○]━━━[○]                         │
│  Client  Box  Détails  Valid.                   │
├─────────────────────────────────────────────────┤
│  Formulaire interactif en 4 étapes              │
└─────────────────────────────────────────────────┘
```

---

## 🔐 Permissions Requises

### Pour Créer un Contrat
```php
Permission: 'create_contrats'

// Vérification dans le contrôleur
$this->middleware('permission:create_contrats')
    ->only(['create', 'store']);
```

### Pour Créer une Facture
```php
Permission: 'create_factures'

// Vérification dans le contrôleur
$this->middleware('permission:create_factures')
    ->only(['create', 'store']);
```

**Si vous ne voyez pas le bouton**, vérifiez que votre utilisateur a la permission correspondante.

---

## 🚀 Navigation Rapide

### Depuis le Menu Principal

```
Menu Latéral (Sidebar)
│
├── 💼 Commercial
│   ├── Prospects
│   ├── Clients
│   └── 📝 Contrats  ← Cliquer ici
│       └── [Nouveau Contrat] ← Bouton en haut à droite
│
├── 💰 Finance
│   ├── 📄 Factures  ← Cliquer ici
│   │   └── [Nouvelle Facture] ← Bouton en haut à droite
│   ├── Règlements
│   └── SEPA
```

---

## 🔧 Contrôleurs Laravel

### ContratController

**Fichier :** `app/Http/Controllers/ContratController.php`

**Méthodes pour création :**
```php
// Affiche le formulaire
public function create(Request $request)
{
    // Charge les clients, boxes, familles
    return Inertia::render('Admin/ContratCreate', [
        'clients' => $clients,
        'boxes' => $boxes,
        'familles' => $familles
    ]);
}

// Enregistre le contrat
public function store(Request $request)
{
    // Validation et création
    $contrat = Contrat::create($validated);
    return redirect()->route('contrats.index');
}
```

### FactureController

**Méthode pour création :**
```php
public function create()
{
    $clients = Client::all();
    return Inertia::render('Admin/FactureCreate', [
        'clients' => $clients
    ]);
}

public function store(Request $request)
{
    // Création de la facture
    $facture = Facture::create($validated);
    return redirect()->route('factures.index');
}
```

---

## 📱 Accès Mobile/Responsive

Les formulaires sont **entièrement responsives** :

- **Desktop** : Stepper horizontal, grille de boxes 3 colonnes
- **Tablette** : Stepper horizontal, grille 2 colonnes
- **Mobile** : Stepper vertical, grille 1 colonne

---

## ⚙️ Configuration Serveur

### Démarrer le Serveur Laravel

```bash
cd C:\xampp2025\htdocs\boxibox
php artisan serve
```

### Accéder à l'Application

```
http://127.0.0.1:8000
```

### Se Connecter

**Admin :**
```
Email: admin@boxibox.com
Mot de passe: admin123
```

---

## 🐛 Problème : Je Ne Vois Pas le Bouton

### Vérifications à Faire

#### 1. **Vérifier les Permissions**
```bash
# Dans Tinker
php artisan tinker

# Vérifier les permissions de l'utilisateur
$user = User::where('email', 'votre@email.com')->first();
$user->permissions; // Doit contenir 'create_contrats' ou 'create_factures'
```

#### 2. **Vérifier que Vue.js est Chargé**
```
F12 (Console Navigateur)
→ Pas d'erreur JavaScript
→ Vérifier que les fichiers /build/assets/*.js sont chargés
```

#### 3. **Vider le Cache**
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

#### 4. **Recompiler les Assets**
```bash
npm run build
```

#### 5. **Vérifier la Route**
```bash
php artisan route:list | grep contrats
# Doit afficher :
# GET|HEAD  commercial/contrats/create  ... contrats.create
```

---

## 📊 Tableau Récapitulatif

| Élément | Contrats | Factures |
|---------|----------|----------|
| **Menu** | Commercial → Contrats | Finance → Factures |
| **URL Liste** | `/commercial/contrats` | `/finance/factures` |
| **URL Création** | `/commercial/contrats/create` | `/finance/factures/create` |
| **Bouton** | "Nouveau Contrat" | "Nouvelle Facture" |
| **Permission** | `create_contrats` | `create_factures` |
| **Composant Liste** | `ContratsManage.vue` | `FacturesManage.vue` |
| **Composant Création** | `ContratCreate.vue` | `FactureCreate.vue` |
| **Contrôleur** | `ContratController` | `FactureController` |
| **Étapes** | 4 étapes | 4 étapes |

---

## 💡 Astuce : Ajouter au Menu Rapide

Si vous utilisez souvent ces formulaires, vous pouvez :

1. **Ajouter un raccourci dans la sidebar**
2. **Créer un widget "Actions Rapides" sur le Dashboard**
3. **Utiliser les URLs directes en favoris navigateur**

Exemple de widget Dashboard :
```vue
<div class="quick-actions">
    <a :href="route('contrats.create')" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouveau Contrat
    </a>
    <a :href="route('factures.create')" class="btn btn-success">
        <i class="fas fa-plus"></i> Nouvelle Facture
    </a>
</div>
```

---

## 📞 Support

**Formulaires Créés :**
- ✅ Création Contrat (4 étapes)
- ✅ Création Facture (4 étapes)
- ✅ Tableaux éditables
- ✅ Calculs automatiques

**Fichiers de Documentation :**
- `GUIDE_CREATION_FACTURE.md` - Guide factures
- `GUIDE_TABLEAUX_EDITABLES.md` - Guide tableaux
- `ACCES_ET_LIENS.md` - Tous les liens

---

**🎉 Les formulaires de création sont maintenant accessibles depuis l'interface !**

**Dernière mise à jour :** Octobre 2025
