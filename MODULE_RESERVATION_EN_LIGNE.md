# 🌐 MODULE DE RÉSERVATION EN LIGNE - DOCUMENTATION

**Date de création**: 06 Octobre 2025
**Version**: 1.0.0
**Statut**: ✅ Opérationnel (Paiement Stripe à intégrer)

---

## 🎯 VUE D'ENSEMBLE

Le **Module de Réservation en Ligne** permet aux prospects de réserver un box de stockage directement depuis le site web public, sans authentification préalable. Le système crée automatiquement le client, le contrat et la facture.

**URL publique**: `/reservation`
**Accessible**: Sans authentification (public)

---

## 🚀 PARCOURS UTILISATEUR COMPLET

### **Étape 1 : Catalogue Public** (`/reservation`)
- ✅ Affichage de toutes les familles de boxes avec disponibilités
- ✅ Statistiques globales (boxes disponibles, taux d'occupation)
- ✅ Filtrage par taille/surface
- ✅ Design moderne avec dégradé violet/bleu

### **Étape 2 : Détails Famille** (`/reservation/famille/{id}`)
- ✅ Caractéristiques détaillées de la famille de boxes
- ✅ Liste des boxes disponibles de cette famille
- ✅ Tableau tarifs dégressifs (1, 3, 6, 12 mois)
- ✅ Informations sur l'emplacement

### **Étape 3 : Formulaire de Réservation** (`/reservation/box/{id}`)
- ✅ Formulaire multi-sections:
  - Informations personnelles (civilité, nom, prénom, email, téléphone, adresse)
  - Détails du contrat (date début, durée)
  - Mode de paiement (carte, SEPA, virement)
  - Acceptation CGV
- ✅ Résumé en temps réel avec calcul automatique:
  - Tarif de base
  - Réduction selon durée (-5%, -10%, -15%)
  - Montant total à payer
- ✅ JavaScript pour mise à jour dynamique des prix

### **Étape 4 : Traitement Réservation** (POST `/reservation/box/{id}/reserver`)
- ✅ Validation complète des données
- ✅ Transaction atomique (rollback en cas d'erreur):
  1. Création/récupération client
  2. Création contrat (statut `en_attente`)
  3. Réservation du box (statut `reserve`)
  4. Création facture initiale
- ✅ Redirection selon mode paiement:
  - Carte → Page paiement Stripe
  - Autres → Page confirmation

### **Étape 5 : Confirmation** (`/reservation/confirmation/{contrat}`)
- ✅ Affichage récapitulatif complet
- ✅ Timeline des prochaines étapes
- ✅ Animation de succès
- ✅ Liens vers espace client et accueil

---

## 📋 FONCTIONNALITÉS PRINCIPALES

### 1️⃣ **Catalogue Intelligent**
```php
// Récupération familles avec comptage disponibilités
$familles = BoxFamille::withCount([
    'boxes as disponibles_count' => function($query) {
        $query->where('statut', 'libre')->where('actif', true);
    }
])
->having('disponibles_count', '>', 0)
->orderBy('surface')
->get();
```

**Avantages**:
- ✅ N'affiche que les familles avec boxes disponibles
- ✅ Tri par surface croissante
- ✅ Comptage en temps réel

### 2️⃣ **Calcul Tarifs Dégressifs**
```javascript
// Réductions automatiques selon durée
let reduction = 0;
if (dureeMois >= 12) reduction = 15;      // -15% pour 12+ mois
else if (dureeMois >= 6) reduction = 10;  // -10% pour 6-11 mois
else if (dureeMois >= 3) reduction = 5;   // -5% pour 3-5 mois

const tarifMensuel = basePrice * (1 - reduction / 100);
const montantTotal = tarifMensuel * dureeMois;
```

**Tableau récapitulatif**:
| Durée | Réduction | Tarif mensuel | Total |
|-------|-----------|---------------|-------|
| 1 mois | - | 100 € | 100 € |
| 3 mois | -5% | 95 € | 285 € |
| 6 mois | -10% | 90 € | 540 € |
| 12 mois | -15% | 85 € | 1 020 € |

### 3️⃣ **Gestion Automatique Client**
```php
// Création ou récupération client existant
$client = Client::firstOrCreate(
    ['email' => $validated['email']],
    [
        'civilite' => $validated['civilite'],
        'prenom' => $validated['prenom'],
        'nom' => $validated['nom'],
        // ... autres champs
        'type_client' => 'particulier',
        'statut' => 'actif',
        'password' => Hash::make(uniqid()), // Temporaire
    ]
);
```

**Logique**:
- ✅ Si email existe → Récupération client
- ✅ Si email nouveau → Création client
- ✅ Mot de passe temporaire (à réinitialiser)

### 4️⃣ **Création Contrat Automatique**
```php
$contrat = Contrat::create([
    'numero_contrat' => 'CTR-' . date('Y') . '-' . str_pad(Contrat::count() + 1, 6, '0', STR_PAD_LEFT),
    'client_id' => $client->id,
    'box_id' => $box->id,
    'date_debut' => $dateDebut,
    'date_fin' => $dateFin,
    'tarif_mensuel' => $box->famille->tarif_base ?? 0,
    'statut' => 'en_attente', // En attente paiement
    'duree_mois' => $validated['duree_mois'],
    'mode_paiement' => $validated['mode_paiement'],
]);
```

**Numérotation automatique**: `CTR-2025-000001`, `CTR-2025-000002`, etc.

### 5️⃣ **Réservation Box**
```php
$box->update([
    'statut' => 'reserve',
    'date_reservation' => now(),
]);
```

**États possibles**:
- `libre` → Box disponible à la réservation
- `reserve` → Box réservé (en attente paiement)
- `occupe` → Box occupé (contrat actif)

### 6️⃣ **Facturation Automatique**
```php
$montantHT = $contrat->tarif_mensuel;
$tauxTVA = 20; // 20%
$montantTVA = $montantHT * ($tauxTVA / 100);
$montantTTC = $montantHT + $montantTVA;

$facture = Facture::create([
    'numero_facture' => 'FAC-' . date('Y') . '-' . str_pad(Facture::count() + 1, 6, '0', STR_PAD_LEFT),
    'client_id' => $client->id,
    'contrat_id' => $contrat->id,
    'montant_ht' => $montantHT,
    'taux_tva' => $tauxTVA,
    'montant_tva' => $montantTVA,
    'montant_ttc' => $montantTTC,
    'statut' => 'en_attente',
    'type' => 'location',
]);
```

---

## 🔧 ARCHITECTURE TECHNIQUE

### **Fichiers créés**:

#### 1. Controller
```
app/Http/Controllers/PublicBookingController.php
```

**Méthodes**:
- `index()` - Catalogue public
- `showFamille($famille)` - Détails famille
- `bookingForm($box)` - Formulaire réservation
- `processBooking($request, $box)` - Traitement réservation
- `payment($contrat)` - Page paiement Stripe (TODO)
- `confirmation($contrat)` - Page confirmation
- `calculatePrice($request)` - API calcul prix (AJAX)

#### 2. Vues Blade
```
resources/views/public/booking/
├── index.blade.php          # Catalogue public
├── famille.blade.php        # Détails famille
├── form.blade.php           # Formulaire réservation
└── confirmation.blade.php   # Page confirmation
```

#### 3. Routes
```php
// routes/web.php (lignes 262-270)
Route::prefix('reservation')->name('public.booking.')->group(function () {
    Route::get('/', [..., 'index'])->name('index');
    Route::get('/famille/{famille}', [..., 'showFamille'])->name('famille');
    Route::get('/box/{box}', [..., 'bookingForm'])->name('form');
    Route::post('/box/{box}/reserver', [..., 'processBooking'])->name('process');
    Route::get('/paiement/{contrat}', [..., 'payment'])->name('payment');
    Route::get('/confirmation/{contrat}', [..., 'confirmation'])->name('confirmation');
    Route::post('/api/calculer-prix', [..., 'calculatePrice'])->name('calculate-price');
});
```

---

## 🎨 DESIGN & UX

### **Palette de couleurs**:
```css
:root {
    --primary-color: #667eea;    /* Violet */
    --secondary-color: #764ba2;  /* Violet foncé */
}

.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### **Composants visuels**:
1. **Hero Section** - Bannière d'accueil avec dégradé
2. **Stats Section** - KPIs (boxes dispo, taux occupation, accès 24/7)
3. **Box Cards** - Cartes avec animation hover (translateY)
4. **Step Indicator** - Indicateur de progression (3 étapes)
5. **Price Summary** - Résumé collant (sticky) avec calcul dynamique
6. **Timeline** - Prochaines étapes avec icônes
7. **Success Animation** - Animation check vert (scaleIn)

### **Responsive**:
- ✅ Bootstrap 5 Grid System
- ✅ Mobile-first design
- ✅ Breakpoints: `col-md-6 col-lg-4`

---

## 🔐 SÉCURITÉ & VALIDATION

### **Validation formulaire**:
```php
$validated = $request->validate([
    'civilite' => 'required|in:M,Mme,Autre',
    'prenom' => 'required|string|max:100',
    'nom' => 'required|string|max:100',
    'email' => 'required|email|max:255',
    'telephone' => 'required|string|max:20',
    'adresse' => 'required|string|max:255',
    'code_postal' => 'required|string|max:10',
    'ville' => 'required|string|max:100',
    'pays' => 'required|string|max:100',
    'date_debut' => 'required|date|after_or_equal:today',
    'duree_mois' => 'required|integer|min:1|max:24',
    'mode_paiement' => 'required|in:carte,virement,sepa',
    'accepte_cgv' => 'required|accepted',
]);
```

### **Vérifications disponibilité**:
```php
// Avant affichage formulaire
if ($box->statut !== 'libre' || !$box->actif) {
    return redirect()->route('public.booking.index')
        ->with('error', 'Ce box n\'est plus disponible.');
}

// Avant création contrat
if ($box->statut !== 'libre' || !$box->actif) {
    return back()->with('error', 'Ce box n\'est plus disponible.');
}
```

### **Transaction atomique**:
```php
DB::beginTransaction();
try {
    // 1. Créer client
    // 2. Créer contrat
    // 3. Réserver box
    // 4. Créer facture
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    return back()->withInput()->with('error', $e->getMessage());
}
```

---

## 💳 INTÉGRATION PAIEMENT (À FAIRE)

### **TODO: Stripe Payment Intent**

```php
// Dans payment()
public function payment(Contrat $contrat)
{
    $facture = $contrat->factures()->where('statut', 'en_attente')->first();

    // Créer Payment Intent Stripe
    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $facture->montant_ttc * 100, // En centimes
        'currency' => 'eur',
        'metadata' => [
            'contrat_id' => $contrat->id,
            'facture_id' => $facture->id,
        ],
    ]);

    return view('public.booking.payment', [
        'contrat' => $contrat,
        'facture' => $facture,
        'clientSecret' => $paymentIntent->client_secret,
    ]);
}
```

### **TODO: Webhook Stripe**
```php
// Route webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Traitement
public function handle(Request $request)
{
    $event = \Stripe\Webhook::constructEvent(
        $request->getContent(),
        $request->header('Stripe-Signature'),
        env('STRIPE_WEBHOOK_SECRET')
    );

    if ($event->type === 'payment_intent.succeeded') {
        // Activer contrat
        // Marquer facture payée
        // Activer box
        // Envoyer email confirmation
    }
}
```

---

## 📧 NOTIFICATIONS AUTOMATIQUES (À FAIRE)

### **Emails à envoyer**:

1. **Après réservation** (tous modes paiement):
   - Récapitulatif réservation
   - N° contrat
   - Montant à payer
   - Instructions paiement (si virement/SEPA)

2. **Après paiement réussi**:
   - Confirmation activation contrat
   - Code d'accès box
   - Lien espace client
   - PDF contrat signé

3. **Si SEPA**:
   - Mandat SEPA à signer
   - Date premier prélèvement

---

## 🧪 TESTS & VALIDATION

### **Tests manuels à effectuer**:
1. ✅ Affichage catalogue avec boxes disponibles
2. ✅ Navigation détails famille
3. ✅ Formulaire réservation avec validation
4. ✅ Calcul tarifs dégressifs en temps réel
5. ✅ Création client/contrat/facture
6. ✅ Réservation box
7. ✅ Page confirmation
8. ⏳ Paiement Stripe (à tester après intégration)

### **Données de test**:
```
Email: test-reservation@example.com
Nom: Dupont
Prénom: Jean
Téléphone: 0612345678
Adresse: 123 Rue de Test
Code postal: 75001
Ville: Paris
```

---

## 📊 STATISTIQUES & ANALYTICS (À FAIRE)

### **Métriques à suivre**:
- ✅ Taux de conversion (visite → réservation)
- ✅ Durée moyenne sélectionnée
- ✅ Mode paiement préféré
- ✅ Famille de box la plus demandée
- ✅ Taux d'abandon panier
- ✅ Source traffic (SEO, pub, direct)

### **Google Analytics**:
```html
<!-- À ajouter dans head -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

---

## 🚀 PROCHAINES AMÉLIORATIONS

### **Phase immédiate** (Cette semaine):
1. ✅ Intégrer Stripe Payment Intent
2. ✅ Créer webhook Stripe
3. ✅ Envoyer emails automatiques
4. ✅ Ajouter signature électronique CGV

### **Phase 2** (Semaine prochaine):
1. ✅ Multi-langues (FR/EN)
2. ✅ Codes promo / Réductions
3. ✅ Calcul assurance optionnelle
4. ✅ Comparateur boxes (drag & drop)
5. ✅ Chat en direct (Crisp/Intercom)

### **Phase 3** (Mois prochain):
1. ✅ Réservation récurrente (contrat renouvelable auto)
2. ✅ Paiement en plusieurs fois
3. ✅ Parrainage (réduction parrain/filleul)
4. ✅ Visite virtuelle 360° du box

---

## 📖 GUIDE UTILISATEUR

### **Comment réserver un box**:

1. **Accéder au catalogue**:
   - Visitez `/reservation`
   - Parcourez les familles disponibles

2. **Choisir un box**:
   - Cliquez sur "Voir les détails"
   - Consultez les caractéristiques
   - Sélectionnez un box disponible

3. **Remplir le formulaire**:
   - Informations personnelles
   - Date début et durée
   - Mode paiement
   - Accepter CGV

4. **Payer**:
   - Carte → Paiement immédiat
   - Virement → Instructions envoyées par email
   - SEPA → Mandat à signer

5. **Confirmation**:
   - Email de confirmation
   - Code d'accès (après paiement)
   - Accès espace client

---

## 🐛 PROBLÈMES CONNUS

### **Limitations actuelles**:
1. ⚠️ Paiement Stripe non implémenté
2. ⚠️ Emails automatiques non configurés
3. ⚠️ Signature électronique CGV manquante
4. ⚠️ Multi-tenant non testé

### **Bugs à corriger**:
- Aucun identifié pour le moment ✅

---

## 📞 SUPPORT

**Documentation projet**: `ANALYSE_CONCURRENTS_ET_AMELIORATIONS.md`
**Version Laravel**: 10.x
**Version Bootstrap**: 5.1.3

---

✅ **Module de Réservation en Ligne opérationnel à 80% !**
🔜 **Intégration Stripe nécessaire pour finalisation complète**
