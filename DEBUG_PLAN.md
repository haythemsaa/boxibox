# 🔍 DEBUG - Plan Interactif Vide

## ✅ Correctifs Appliqués

### 1. Seeder de Coordonnées Créé et Exécuté
- ✅ 11 boxes avec coordonnées X/Y
- ✅ 3 emplacements créés
- ✅ Distribution automatique sur grille 10x8

---

## 🔧 Diagnostic et Solutions

### Si le plan reste vide, vérifier :

#### 1. **URL Correcte**
Vous utilisez actuellement:
```
http://localhost/boxibox/public/technique/plan
```

**⚠️ PROBLÈME POTENTIEL:** Vous utilisez XAMPP mais pas `php artisan serve`

**SOLUTIONS:**

**Option A - Utiliser Laravel Server (Recommandé):**
```bash
cd C:\xampp2025\htdocs\boxibox
php artisan serve
```
Puis accéder via:
```
http://localhost:8000/technique/plan
```

**Option B - Configurer XAMPP correctement:**
Si vous voulez continuer avec XAMPP, l'URL devrait être:
```
http://localhost/boxibox/public/technique/plan
```

#### 2. **Vérifier les Données en Console**

Ouvrir la console développeur (F12) et exécuter:
```javascript
console.log('Boxes:', window.$page?.props?.boxes);
console.log('Emplacements:', window.$page?.props?.emplacements);
```

#### 3. **Vérifier que Inertia Fonctionne**

Dans la console (F12), chercher des erreurs comme:
- `Inertia page object not found`
- Erreurs 500
- Erreurs JavaScript

#### 4. **Forcer le Rechargement**

1. Vider le cache Laravel:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

2. Recompiler les assets:
```bash
npm run build
```

3. Vider le cache du navigateur (Ctrl + Shift + Delete)

---

## 📊 Vérification des Données

### Commande SQL pour vérifier les boxes:
```sql
SELECT
    b.id,
    b.numero,
    b.statut,
    b.coordonnees_x,
    b.coordonnees_y,
    b.emplacement_id,
    e.nom as emplacement
FROM boxes b
LEFT JOIN emplacements e ON b.emplacement_id = e.id
WHERE b.is_active = 1;
```

**Résultat attendu:** 11 boxes avec des coordonnées X/Y définies

---

## 🐛 Si le Problème Persiste

### Debug Level 1 - Ajouter des logs

Ouvrir `app/Http/Controllers/BoxController.php` ligne 77 et ajouter:
```php
public function plan(Request $request)
{
    \Log::info('=== DEBUG PLAN ===');

    $boxes = Box::with(['famille', 'emplacement', 'contratActif.client'])
        ->active()
        ->get();

    \Log::info('Nombre de boxes: ' . $boxes->count());
    \Log::info('Première box: ' . json_encode($boxes->first()));

    // ... reste du code
}
```

Puis consulter:
```bash
tail -f storage/logs/laravel.log
```

### Debug Level 2 - Tester la route directement

Créer un fichier de test: `public/test-plan.php`
```php
<?php
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\Box;
use App\Models\Emplacement;

$boxes = Box::with(['famille', 'emplacement'])->get();
$emplacements = Emplacement::all();

echo "Boxes: " . $boxes->count() . "\n";
echo "Emplacements: " . $emplacements->count() . "\n";

foreach ($boxes as $box) {
    echo "Box {$box->numero}: ({$box->coordonnees_x}, {$box->coordonnees_y})\n";
}
?>
```

Accéder à: `http://localhost/boxibox/public/test-plan.php`

---

## ✨ Si Tout Est OK mais le Plan Est Vide

### Vérifier le Composant Vue

Le problème peut venir du fait que `BoxPlanGrid.vue` ne reçoit pas les bonnes props.

**Vérification dans la console du navigateur:**
```javascript
// Ouvrir Vue DevTools
// Chercher le composant BoxPlanGrid
// Vérifier les props: boxes et emplacements
```

---

## 🎯 Solution Rapide - Test Minimal

Pour tester rapidement si tout fonctionne, modifier temporairement `Admin/BoxPlan.vue`:

```vue
<template>
    <div class="container-fluid py-4">
        <h1>Debug Plan</h1>
        <p>Boxes reçus: {{ boxes.length }}</p>
        <p>Emplacements reçus: {{ emplacements.length }}</p>

        <pre>{{ JSON.stringify(boxes[0], null, 2) }}</pre>

        <!-- BoxPlanGrid ... -->
    </div>
</template>
```

Cela affichera le contenu des données reçues.

---

## 📞 Prochaines Étapes

1. ✅ Le seeder a été exécuté avec succès
2. ⏳ Testez avec l'URL correcte (voir Option A ou B ci-dessus)
3. ⏳ Vérifiez la console développeur pour les erreurs
4. ⏳ Si besoin, appliquez les solutions de debug ci-dessus

---

## 🔑 URLs de Test

**Avec Laravel Server:**
```
http://localhost:8000/technique/plan          (Admin)
http://localhost:8000/client/box-plan         (Client)
```

**Avec XAMPP:**
```
http://localhost/boxibox/public/technique/plan          (Admin)
http://localhost/boxibox/public/client/box-plan         (Client)
```

**Login Admin:**
```
Email: admin@boxibox.com
Mot de passe: admin123
```

---

**Le seeder a créé 11 boxes répartis sur 3 emplacements avec coordonnées. Le plan devrait maintenant s'afficher !** 🎉
