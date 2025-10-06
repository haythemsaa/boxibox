# 📝 Guide des Tableaux Éditables - BOXIBOX

## Vue d'ensemble

Le composant **EditableTable.vue** permet de créer des tableaux interactifs avec **édition inline**, pagination, tri et filtres.

## 🎯 Fonctionnalités

✅ **Édition inline** - Modifier les données directement dans le tableau
✅ **Types de champs** - Text, Number, Date, Select, Currency
✅ **Validation** - Validation côté client et serveur
✅ **Pagination** - Gestion automatique de la pagination
✅ **Actions personnalisées** - Boutons d'action par ligne
✅ **Formatage** - Formateurs personnalisés pour l'affichage
✅ **Responsive** - S'adapte aux mobiles et tablettes

---

## 📦 Installation et Utilisation

### 1. Importer le Composant

```vue
<script>
import EditableTable from '@/Components/EditableTable.vue';

export default {
    components: {
        EditableTable
    }
}
</script>
```

### 2. Utilisation Basique

```vue
<template>
    <EditableTable
        :data="contrats"
        :columns="columns"
        :editable="true"
        :deletable="true"
        @update="handleUpdate"
        @delete="handleDelete"
    />
</template>

<script>
export default {
    data() {
        return {
            contrats: [
                {
                    id: 1,
                    numero_contrat: 'CT-2025-001',
                    date_debut: '2025-01-01',
                    montant_loyer: 150.00,
                    statut: 'actif'
                }
            ],
            columns: [
                {
                    key: 'numero_contrat',
                    label: 'N° Contrat',
                    type: 'text'
                },
                {
                    key: 'date_debut',
                    label: 'Date Début',
                    type: 'date'
                },
                {
                    key: 'montant_loyer',
                    label: 'Loyer',
                    type: 'currency'
                },
                {
                    key: 'statut',
                    label: 'Statut',
                    type: 'select',
                    options: [
                        { value: 'actif', label: 'Actif' },
                        { value: 'resilie', label: 'Résilié' }
                    ]
                }
            ]
        }
    },
    methods: {
        handleUpdate({ id, data }) {
            // Enregistrer les modifications
            console.log('Mise à jour:', id, data);
        },
        handleDelete(row) {
            // Supprimer la ligne
            console.log('Suppression:', row);
        }
    }
}
</script>
```

---

## 🔧 Configuration des Colonnes

### Types de Colonnes Disponibles

#### 1. **Text** (Texte)
```javascript
{
    key: 'nom',
    label: 'Nom',
    type: 'text',
    inputType: 'text' // ou 'email', 'tel', etc.
}
```

#### 2. **Number** (Nombre)
```javascript
{
    key: 'quantite',
    label: 'Quantité',
    type: 'number'
}
```

#### 3. **Date**
```javascript
{
    key: 'date_emission',
    label: 'Date',
    type: 'date'
}
```

#### 4. **Currency** (Monnaie)
```javascript
{
    key: 'montant',
    label: 'Montant',
    type: 'currency',
    cellClass: 'text-end' // Aligné à droite
}
```

#### 5. **Select** (Liste déroulante)
```javascript
{
    key: 'statut',
    label: 'Statut',
    type: 'select',
    options: [
        { value: 'actif', label: 'Actif' },
        { value: 'inactif', label: 'Inactif' }
    ],
    badges: {
        'actif': { class: 'bg-success', label: 'Actif' },
        'inactif': { class: 'bg-secondary', label: 'Inactif' }
    }
}
```

#### 6. **Badge** (Affichage badge uniquement)
```javascript
{
    key: 'priorite',
    label: 'Priorité',
    type: 'badge',
    badges: {
        'haute': { class: 'bg-danger', label: 'Haute' },
        'moyenne': { class: 'bg-warning', label: 'Moyenne' },
        'basse': { class: 'bg-info', label: 'Basse' }
    }
}
```

### Formatage Personnalisé

```javascript
{
    key: 'client',
    label: 'Client',
    format: (value, row) => {
        return `${row.client.prenom} ${row.client.nom}`;
    }
}
```

### Classes CSS Personnalisées

```javascript
{
    key: 'montant',
    label: 'Montant',
    type: 'currency',
    headerClass: 'text-end',  // Classe pour l'en-tête
    cellClass: 'text-end'      // Classe pour les cellules
}
```

---

## 🎨 Props du Composant

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `data` | Array | **required** | Données à afficher |
| `columns` | Array | **required** | Configuration des colonnes |
| `editable` | Boolean | `false` | Activer l'édition |
| `deletable` | Boolean | `false` | Activer la suppression |
| `updateRoute` | String | `null` | Route Laravel pour mise à jour |
| `emptyMessage` | String | `null` | Message si tableau vide |
| `pagination` | Object | `null` | Données de pagination Laravel |

---

## 🔔 Événements (Emits)

### @update
Déclenché quand une ligne est modifiée.

```vue
<EditableTable @update="handleUpdate" />
```

```javascript
handleUpdate({ id, data }) {
    // id: ID de la ligne
    // data: Données modifiées
    router.put(`/admin/contrats/${id}`, data);
}
```

### @delete
Déclenché quand une ligne est supprimée.

```vue
<EditableTable @delete="handleDelete" />
```

```javascript
handleDelete(row) {
    if (confirm(`Supprimer ${row.numero_contrat} ?`)) {
        router.delete(`/admin/contrats/${row.id}`);
    }
}
```

### @page-change
Déclenché lors du changement de page.

```vue
<EditableTable @page-change="handlePageChange" />
```

```javascript
handlePageChange(page) {
    router.get('/admin/contrats', { page });
}
```

---

## 🎭 Slots Personnalisés

### Slot: row-actions

Ajouter des actions personnalisées par ligne.

```vue
<EditableTable :data="factures" :columns="columns">
    <template #row-actions="{ row }">
        <a
            :href="`/admin/factures/${row.id}/pdf`"
            class="btn btn-sm btn-outline-secondary"
            title="PDF"
        >
            <i class="fas fa-file-pdf"></i>
        </a>
        <button
            @click="envoyerEmail(row)"
            class="btn btn-sm btn-outline-primary"
            title="Envoyer par email"
        >
            <i class="fas fa-envelope"></i>
        </button>
    </template>
</EditableTable>
```

---

## 📊 Pagination

### Avec Laravel Paginator

```php
// Controller Laravel
$factures = Facture::paginate(20);

return Inertia::render('Admin/FacturesManage', [
    'factures' => $factures
]);
```

```vue
<!-- Vue Component -->
<EditableTable
    :data="factures.data"
    :pagination="factures"
    @page-change="handlePageChange"
/>
```

Le composant détecte automatiquement la structure de pagination Laravel :
- `total` - Nombre total d'éléments
- `from` - Index de début
- `to` - Index de fin
- `current_page` - Page actuelle
- `last_page` - Dernière page
- `prev_page_url` - URL page précédente
- `next_page_url` - URL page suivante

---

## 🚀 Exemples Complets

### Exemple 1 : Gestion des Contrats

```vue
<template>
    <div>
        <h1>Gestion des Contrats</h1>

        <EditableTable
            :data="contrats.data"
            :columns="columns"
            :editable="true"
            :deletable="true"
            :pagination="contrats"
            :update-route="route('admin.contrats.update', ':id')"
            @delete="handleDelete"
        >
            <template #row-actions="{ row }">
                <a :href="route('admin.contrats.pdf', row.id)" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-file-pdf"></i>
                </a>
            </template>
        </EditableTable>
    </div>
</template>

<script>
import EditableTable from '@/Components/EditableTable.vue';

export default {
    components: { EditableTable },

    props: ['contrats'],

    data() {
        return {
            columns: [
                { key: 'numero_contrat', label: 'N° Contrat', type: 'text' },
                { key: 'date_debut', label: 'Date Début', type: 'date' },
                { key: 'montant_loyer', label: 'Loyer', type: 'currency' },
                {
                    key: 'statut',
                    label: 'Statut',
                    type: 'select',
                    options: [
                        { value: 'actif', label: 'Actif' },
                        { value: 'resilie', label: 'Résilié' }
                    ],
                    badges: {
                        'actif': { class: 'bg-success', label: 'Actif' },
                        'resilie': { class: 'bg-danger', label: 'Résilié' }
                    }
                }
            ]
        }
    },

    methods: {
        handleDelete(row) {
            if (confirm(`Supprimer le contrat ${row.numero_contrat} ?`)) {
                this.$inertia.delete(route('admin.contrats.destroy', row.id));
            }
        }
    }
}
</script>
```

### Exemple 2 : Tableau Lecture Seule avec Actions

```vue
<template>
    <EditableTable
        :data="factures"
        :columns="columns"
        :editable="false"
        :deletable="false"
    >
        <template #row-actions="{ row }">
            <button @click="marquerPayee(row)" class="btn btn-sm btn-success">
                <i class="fas fa-check"></i> Payer
            </button>
            <button @click="envoyerRelance(row)" class="btn btn-sm btn-warning">
                <i class="fas fa-bell"></i> Relancer
            </button>
        </template>
    </EditableTable>
</template>
```

---

## 🎨 Styles et Apparence

### Classes Bootstrap Intégrées

Le composant utilise Bootstrap 5 :
- `.table` - Tableau de base
- `.table-hover` - Effet hover sur les lignes
- `.table-responsive` - Responsive
- `.btn-group-sm` - Groupes de boutons compacts
- `.badge` - Badges pour statuts

### Personnalisation CSS

```vue
<style scoped>
/* Personnaliser l'apparence */
.editable-table tbody tr:hover {
    background-color: #f0f8ff;
}

.editable-table .form-control-sm {
    border-color: #0d6efd;
}
</style>
```

---

## ⚡ Performance

### Optimisations

1. **Lazy Loading** - Pagination côté serveur
2. **Debounce** - Sur les inputs de recherche
3. **Virtual Scrolling** - Pour grandes listes (à implémenter si besoin)

### Bonnes Pratiques

```javascript
// ✅ Bon : Pagination côté serveur
$contrats = Contrat::paginate(20);

// ❌ Mauvais : Tout charger en mémoire
$contrats = Contrat::all();
```

---

## 🐛 Troubleshooting

### Le tableau ne s'affiche pas

Vérifier que `data` est un **Array** et que `columns` est défini.

```javascript
// ✅ Bon
:data="contrats.data || []"

// ❌ Mauvais
:data="contrats" // Si contrats est un objet paginé
```

### L'édition ne fonctionne pas

Vérifier que :
1. `editable` est `true`
2. `updateRoute` est défini **OU** `@update` est géré
3. Les données ont un champ `id`

### Les badges ne s'affichent pas

Vérifier que la colonne a :
```javascript
{
    type: 'badge', // ou 'select'
    badges: {
        'valeur': { class: 'bg-success', label: 'Label' }
    }
}
```

---

## 📚 API Complète

### Column Object

```typescript
{
    key: string;              // Clé de la donnée
    label: string;            // Label de la colonne
    type?: 'text' | 'number' | 'date' | 'currency' | 'select' | 'badge';
    inputType?: string;       // Type d'input HTML (pour type=text)
    options?: Array<{value, label}>;  // Options (pour type=select)
    badges?: Object;          // Config badges
    format?: (value, row) => string;  // Formateur personnalisé
    headerClass?: string;     // Classe CSS en-tête
    cellClass?: string;       // Classe CSS cellule
}
```

### Pagination Object (Laravel)

```typescript
{
    data: Array;          // Données de la page
    current_page: number; // Page actuelle
    last_page: number;    // Dernière page
    from: number;         // Index début
    to: number;           // Index fin
    total: number;        // Total éléments
    prev_page_url: string | null;
    next_page_url: string | null;
}
```

---

## 🎓 Exemples de Routes Laravel

```php
// routes/web.php

// Liste avec pagination
Route::get('/admin/contrats', [AdminController::class, 'contrats'])
    ->name('admin.contrats.index');

// Mise à jour
Route::put('/admin/contrats/{contrat}', [AdminController::class, 'update'])
    ->name('admin.contrats.update');

// Suppression
Route::delete('/admin/contrats/{contrat}', [AdminController::class, 'destroy'])
    ->name('admin.contrats.destroy');
```

```php
// Controller
public function contrats(Request $request)
{
    $query = Contrat::with('client', 'box');

    if ($request->search) {
        $query->where('numero_contrat', 'LIKE', "%{$request->search}%");
    }

    $contrats = $query->paginate(20);

    return Inertia::render('Admin/ContratsManage', [
        'contrats' => $contrats
    ]);
}

public function update(Request $request, Contrat $contrat)
{
    $validated = $request->validate([
        'date_debut' => 'required|date',
        'montant_loyer' => 'required|numeric',
        'statut' => 'required|in:actif,resilie'
    ]);

    $contrat->update($validated);

    return back()->with('success', 'Contrat mis à jour');
}
```

---

## 📞 Support

Pour toute question sur les tableaux éditables :
- Voir exemples : `ContratsManage.vue`, `FacturesManage.vue`
- Composant source : `resources/js/Components/EditableTable.vue`
- Documentation : Ce fichier

---

**Dernière mise à jour :** Octobre 2025
**Version :** 1.0.0
**Compatibilité :** Vue.js 3.3+, Laravel 10+, Bootstrap 5+
