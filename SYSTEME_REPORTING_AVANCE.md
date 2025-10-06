# 📊 Système de Reporting Avancé - Boxibox

## 📋 Vue d'ensemble

Le système de reporting avancé permet de générer, consulter et exporter des rapports détaillés sur toutes les activités de l'entreprise : financier, occupation, clients et sécurité.

---

## 🎯 Types de rapports disponibles

### 1. 💰 Rapport Financier

**Indicateurs clés :**
- Chiffre d'affaires total
- Nombre de factures émises
- Montant impayé
- Taux de paiement

**Analyses détaillées :**
- Évolution du CA mensuel (graphique ligne)
- CA par mode de paiement (graphique donut)
- Statut des factures (payées/impayées)
- Comparaison avec période précédente

**Exports disponibles :**
- PDF avec graphiques
- Excel avec données brutes

### 2. 📦 Rapport d'Occupation

**Indicateurs clés :**
- Taux d'occupation global
- Boxes occupés / libres / réservés
- Boxes en maintenance

**Analyses détaillées :**
- Taux d'occupation par emplacement
- Taux d'occupation par famille de boxes
- Évolution de l'occupation sur 6 mois
- Prévisions de disponibilité

**Visualisations :**
- Graphiques en barres par emplacement
- Graphique d'évolution temporelle
- Heatmap d'occupation (futur)

### 3. 👥 Rapport Clients

**Statistiques générales :**
- Total clients
- Clients actifs (avec contrats)
- Nouveaux clients ce mois

**Analyses détaillées :**
- Top 10 clients par CA
- Nouveaux clients par mois (12 derniers mois)
- Clients avec retards de paiement
- Taux de rétention (futur)

**Exports :**
- Liste complète clients Excel
- Rapport PDF des meilleurs clients

### 4. 🔒 Rapport Sécurité & Accès

**Statistiques d'accès :**
- Total accès sur période
- Accès autorisés vs refusés
- Accès par méthode (PIN, QR, Badge)

**Analyses de sécurité :**
- Liste des accès refusés récents
- Top 10 clients avec le plus d'accès
- Évolution quotidienne des accès
- Détection d'anomalies (futur)

**Alertes :**
- Tentatives d'accès inhabituelles
- Codes utilisés après expiration
- Pics d'accès refusés

---

## 🏗️ Architecture technique

### Structure des Controllers

#### `ReportController.php`

**Méthodes principales :**

```php
public function index()
// Page principale avec les 4 types de rapports

public function financial(Request $request)
// Rapport financier avec filtres date

public function occupation(Request $request)
// Rapport d'occupation avec stats par emplacement/famille

public function clients(Request $request)
// Rapport clients avec top clients et retards

public function access(Request $request)
// Rapport sécurité avec logs d'accès

public function exportPDF(Request $request)
// Export PDF de n'importe quel rapport

public function exportExcel(Request $request)
// Export Excel (nécessite Laravel Excel)
```

### Requêtes optimisées

#### Rapport Financier
```php
// CA par mode de paiement
$caParMode = Reglement::whereBetween('date_reglement', [$dateDebut, $dateFin])
    ->select('mode_paiement', DB::raw('SUM(montant) as total'))
    ->groupBy('mode_paiement')
    ->get();

// Évolution mensuelle
$evolutionCA = Reglement::whereBetween('date_reglement', [$dateDebut, $dateFin])
    ->selectRaw('DATE_FORMAT(date_reglement, "%Y-%m") as mois, SUM(montant) as total')
    ->groupBy('mois')
    ->orderBy('mois')
    ->get();
```

#### Rapport Occupation
```php
// Occupation par emplacement
$occupationParEmplacement = Box::select('emplacement_id')
    ->selectRaw('COUNT(*) as total')
    ->selectRaw('SUM(CASE WHEN statut = "occupe" THEN 1 ELSE 0 END) as occupes')
    ->with('emplacement')
    ->groupBy('emplacement_id')
    ->get()
    ->map(function ($item) {
        return [
            'emplacement' => $item->emplacement->nom ?? 'N/A',
            'total' => $item->total,
            'occupes' => $item->occupes,
            'taux' => $item->total > 0 ? ($item->occupes / $item->total) * 100 : 0,
        ];
    });
```

#### Rapport Clients
```php
// Top 10 clients par CA
$topClients = Client::select('clients.*')
    ->selectRaw('SUM(reglements.montant) as total_ca')
    ->join('factures', 'factures.client_id', '=', 'clients.id')
    ->join('reglements', 'reglements.facture_id', '=', 'factures.id')
    ->groupBy('clients.id')
    ->orderByDesc('total_ca')
    ->limit(10)
    ->get();
```

#### Rapport Accès
```php
// Évolution des accès par jour
$evolutionAccess = AccessLog::whereBetween('date_heure', [$dateDebut, $dateFin])
    ->selectRaw('DATE(date_heure) as jour')
    ->selectRaw('COUNT(*) as total')
    ->selectRaw('SUM(CASE WHEN statut = "autorise" THEN 1 ELSE 0 END) as autorises')
    ->selectRaw('SUM(CASE WHEN statut = "refuse" THEN 1 ELSE 0 END) as refuses')
    ->groupBy('jour')
    ->orderBy('jour')
    ->get();
```

---

## 📦 Fichiers créés

### Controllers
- `app/Http/Controllers/ReportController.php` - Contrôleur principal des rapports

### Views
- `resources/views/reports/index.blade.php` - Page d'accueil des rapports
- `resources/views/reports/financial.blade.php` - Rapport financier
- `resources/views/reports/occupation.blade.php` - Rapport occupation (à créer)
- `resources/views/reports/clients.blade.php` - Rapport clients (à créer)
- `resources/views/reports/access.blade.php` - Rapport accès (à créer)

### Routes
```php
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', 'index')->middleware('permission:view_statistics');
    Route::get('/financial', 'financial')->middleware('permission:view_statistics');
    Route::get('/occupation', 'occupation')->middleware('permission:view_statistics');
    Route::get('/clients', 'clients')->middleware('permission:view_statistics');
    Route::get('/access', 'access')->middleware('permission:view_statistics');
    Route::get('/export-pdf', 'exportPDF')->middleware('permission:view_statistics');
    Route::get('/export-excel', 'exportExcel')->middleware('permission:view_statistics');
});
```

---

## 🎨 Interface utilisateur

### Page d'accueil des rapports (`/reports`)

**4 Cartes principales :**
1. 💰 Rapport Financier (bleu)
2. 📦 Rapport Occupation (vert)
3. 👥 Rapport Clients (cyan)
4. 🔒 Rapport Sécurité (orange)

**Section Rapports Planifiés :**
- Tableau des rapports programmés
- Bouton "Planifier un rapport"
- Configuration : type, fréquence, destinataires

**Section Exports Rapides :**
- Export toutes données (Excel/PDF)
- Export période spécifique

### Rapport Financier (`/reports/financial`)

**Filtres :**
- Date de début
- Date de fin
- Bouton "Filtrer"

**4 KPI Cards :**
1. Chiffre d'Affaires
2. Factures Émises
3. Montant Impayé
4. Taux de Paiement

**Graphiques :**
1. Évolution du CA (Chart.js - Line)
2. CA par Mode de Paiement (Chart.js - Donut)

**Tableau :**
- Statut des factures (Payées, Impayées, Total)

**Actions :**
- Bouton "Export PDF"
- Bouton "Export Excel" (à venir)

---

## 📊 Visualisations (Chart.js)

### Configuration Chart.js

```javascript
// Graphique ligne - Évolution CA
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Fév', 'Mar', ...],
        datasets: [{
            label: 'Chiffre d\'Affaires',
            data: [15000, 18000, 22000, ...],
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' €';
                    }
                }
            }
        }
    }
});

// Graphique donut - CA par mode de paiement
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Carte', 'Virement', 'Chèque', 'SEPA'],
        datasets: [{
            data: [45000, 28000, 12000, 35000],
            backgroundColor: [
                'rgba(78, 115, 223, 0.8)',
                'rgba(28, 200, 138, 0.8)',
                'rgba(54, 185, 204, 0.8)',
                'rgba(246, 194, 62, 0.8)'
            ]
        }]
    }
});
```

---

## 📄 Export PDF

### Configuration (DomPDF)

```bash
composer require barryvdh/laravel-dompdf
```

### Utilisation dans le controller

```php
use Barryvdh\DomPDF\Facade\Pdf;

public function exportPDF(Request $request)
{
    $type = $request->input('type'); // financial, occupation, clients, access
    $data = $this->getReportData($type);

    $pdf = Pdf::loadView("reports.pdf.{$type}", $data);

    return $pdf->download("rapport_{$type}_" . now()->format('Y-m-d') . ".pdf");
}
```

### Template PDF (exemple)

```blade
<!-- resources/views/reports/pdf/financial.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .kpi { display: inline-block; width: 23%; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport Financier</h1>
        <p>Période : {{ $dateDebut }} - {{ $dateFin }}</p>
    </div>

    <div class="kpis">
        <div class="kpi">
            <h3>{{ number_format($ca, 2) }} €</h3>
            <p>Chiffre d'Affaires</p>
        </div>
        <!-- ... autres KPIs -->
    </div>

    <h2>Factures</h2>
    <table>
        <thead>
            <tr>
                <th>N° Facture</th>
                <th>Client</th>
                <th>Montant</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factures as $facture)
            <tr>
                <td>{{ $facture->numero_facture }}</td>
                <td>{{ $facture->client->nom }}</td>
                <td>{{ number_format($facture->montant_total, 2) }} €</td>
                <td>{{ $facture->statut }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
```

---

## 📈 Export Excel

### Installation Laravel Excel

```bash
composer require maatwebsite/excel
```

### Créer une classe Export

```php
// app/Exports/FinancialReportExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FinancialReportExport implements FromCollection, WithHeadings
{
    protected $dateDebut;
    protected $dateFin;

    public function __construct($dateDebut, $dateFin)
    {
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function collection()
    {
        return Facture::whereBetween('date_emission', [$this->dateDebut, $this->dateFin])
            ->with('client')
            ->get()
            ->map(function ($facture) {
                return [
                    'numero_facture' => $facture->numero_facture,
                    'client' => $facture->client->nom,
                    'date_emission' => $facture->date_emission->format('d/m/Y'),
                    'montant_total' => $facture->montant_total,
                    'montant_paye' => $facture->montant_paye,
                    'statut' => $facture->statut,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'N° Facture',
            'Client',
            'Date Émission',
            'Montant Total',
            'Montant Payé',
            'Statut',
        ];
    }
}
```

### Utilisation dans le controller

```php
use App\Exports\FinancialReportExport;
use Maatwebsite\Excel\Facades\Excel;

public function exportExcel(Request $request)
{
    $dateDebut = $request->input('date_debut');
    $dateFin = $request->input('date_fin');

    return Excel::download(
        new FinancialReportExport($dateDebut, $dateFin),
        'rapport_financier_' . now()->format('Y-m-d') . '.xlsx'
    );
}
```

---

## 🔄 Rapports planifiés (Scheduled Reports)

### Configuration des tâches planifiées

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Rapport financier mensuel
    $schedule->call(function () {
        $admins = User::role('admin')->get();
        $data = $this->getFinancialData(
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new MonthlyFinancialReport($data));
        }
    })->monthlyOn(1, '08:00');

    // Rapport hebdomadaire d'occupation
    $schedule->call(function () {
        // Logique similaire pour rapport occupation
    })->weeklyOn(1, '08:00'); // Chaque lundi à 8h
}
```

### Table `scheduled_reports` (à créer)

```sql
CREATE TABLE scheduled_reports (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT,
    type VARCHAR(50), -- financial, occupation, clients, access
    frequency VARCHAR(20), -- daily, weekly, monthly
    recipients TEXT, -- JSON array of emails
    filters JSON, -- date ranges, etc.
    next_run_at TIMESTAMP,
    last_run_at TIMESTAMP,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🔐 Sécurité et permissions

### Middleware de permissions

Tous les rapports nécessitent la permission `view_statistics` :

```php
Route::get('/reports/financial', [ReportController::class, 'financial'])
    ->middleware('permission:view_statistics');
```

### Isolation multi-tenant

Toutes les requêtes incluent le filtre tenant :

```php
$ca = Reglement::where('tenant_id', auth()->user()->tenant_id)
    ->whereBetween('date_reglement', [$dateDebut, $dateFin])
    ->sum('montant');
```

---

## 📊 Métriques et KPIs

### Rapport Financier
- **CA total** : Somme des règlements
- **Taux de paiement** : (Montant payé / Montant total) × 100
- **CA moyen par client** : CA total / Nombre clients actifs
- **DSO (Days Sales Outstanding)** : Délai moyen de paiement

### Rapport Occupation
- **Taux d'occupation** : (Boxes occupés / Total boxes) × 100
- **Taux de disponibilité** : (Boxes libres / Total boxes) × 100
- **Revenu par box** : CA total / Nombre boxes occupés

### Rapport Clients
- **LTV (Lifetime Value)** : CA total par client
- **Taux de rétention** : Clients actifs / Total clients
- **CAC (Customer Acquisition Cost)** : Coûts marketing / Nouveaux clients

### Rapport Accès
- **Taux de refus** : (Accès refusés / Total accès) × 100
- **Accès moyen par client** : Total accès / Nombre clients
- **Pic d'activité** : Heure de la journée avec le plus d'accès

---

## 🎯 Prochaines étapes

### 1. Compléter les vues manquantes (Priorité : Haute)
- [ ] `resources/views/reports/occupation.blade.php`
- [ ] `resources/views/reports/clients.blade.php`
- [ ] `resources/views/reports/access.blade.php`

### 2. Installer Laravel Excel (Priorité : Haute)
- [ ] `composer require maatwebsite/excel`
- [ ] Créer classes Export pour chaque rapport
- [ ] Implémenter méthode `exportExcel()` dans ReportController

### 3. Installer DomPDF (Priorité : Haute)
- [ ] `composer require barryvdh/laravel-dompdf`
- [ ] Créer templates PDF pour chaque rapport
- [ ] Tester génération PDF avec graphiques

### 4. Rapports planifiés (Priorité : Moyenne)
- [ ] Créer migration `scheduled_reports`
- [ ] Créer CRUD pour gérer rapports planifiés
- [ ] Implémenter envoi automatique par email
- [ ] Configurer tâches CRON

### 5. Analyses avancées (Priorité : Basse)
- [ ] Dashboard de comparaison périodes
- [ ] Prévisions avec régression linéaire
- [ ] Détection d'anomalies (ML)
- [ ] Rapports personnalisables (drag & drop widgets)

---

## 🛠️ Commandes utiles

```bash
# Générer un rapport financier PDF
php artisan report:generate financial --format=pdf --period=last-month

# Envoyer rapport par email
php artisan report:send financial --to=admin@boxibox.com --period=this-month

# Nettoyer anciens rapports (> 6 mois)
php artisan reports:clean --older-than=6months

# Lister rapports planifiés
php artisan reports:scheduled
```

---

## 📝 Notes importantes

1. **Performance** : Les rapports avec beaucoup de données (> 10 000 lignes) doivent être générés en arrière-plan via les queues.

2. **Cache** : Les rapports peuvent être mis en cache pendant 1 heure pour éviter les calculs répétitifs :
   ```php
   $ca = Cache::remember('ca_' . $dateDebut . '_' . $dateFin, 3600, function () {
       return Reglement::whereBetween(...)->sum('montant');
   });
   ```

3. **Pagination** : Pour les tableaux de données, toujours paginer (50-100 lignes par page max).

4. **Format de dates** : Utiliser Carbon pour la cohérence :
   ```php
   $dateDebut = Carbon::parse($request->date_debut)->startOfDay();
   $dateFin = Carbon::parse($request->date_fin)->endOfDay();
   ```

5. **Graphiques dans PDF** : DomPDF ne supporte pas Chart.js. Utiliser des images statiques générées avec Chart.js côté serveur ou des tableaux HTML.

---

**Date de création** : 06/10/2025
**Version** : 1.0
**Auteur** : Développement Boxibox avec Claude Code
