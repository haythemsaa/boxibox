# Guide Développeur - Vue.js BOXIBOX

## 🚀 Démarrage Rapide

### Installation et Build

```bash
# Installer les dépendances
npm install

# Mode développement avec hot reload
npm run dev

# Build pour production
npm run build
```

### Structure des Fichiers

```
resources/
├── js/
│   ├── Pages/
│   │   └── Client/          # Pages Vue.js de l'espace client
│   │       ├── Dashboard.vue
│   │       ├── Contrats.vue
│   │       ├── Documents.vue
│   │       ├── Factures.vue
│   │       ├── Sepa.vue
│   │       ├── Profil.vue
│   │       └── Reglements.vue
│   ├── Layouts/
│   │   └── ClientLayout.vue # Layout partagé
│   └── app.js               # Point d'entrée
└── views/                   # Templates Blade (legacy)
```

## 📝 Créer une Nouvelle Page Vue.js

### 1. Créer le Composant Vue

**`resources/js/Pages/Client/MaNouvellePage.vue`**

```vue
<template>
    <ClientLayout title="Mon Titre">
        <div class="mb-4">
            <h1 class="h3">Ma Nouvelle Page</h1>
            <p class="text-muted">Description de la page</p>
        </div>

        <!-- Contenu ici -->
        <div class="card">
            <div class="card-body">
                <p>{{ message }}</p>
            </div>
        </div>
    </ClientLayout>
</template>

<script>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { router } from '@inertiajs/vue3';

export default {
    components: {
        ClientLayout
    },

    props: {
        donnees: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            message: 'Hello Vue!'
        };
    },

    methods: {
        maMethode() {
            // Votre code
        }
    }
};
</script>

<style scoped>
/* Styles locaux à ce composant */
</style>
```

### 2. Créer la Route Laravel

**`routes/web.php`**

```php
Route::get('client/ma-page', [ClientPortalController::class, 'maPage'])
    ->name('client.ma-page');
```

### 3. Créer la Méthode Contrôleur

**`app/Http/Controllers/ClientPortalController.php`**

```php
public function maPage()
{
    $client = $this->getClient();
    if (!$client) abort(404);

    $donnees = [
        'exemple' => 'valeur'
    ];

    return Inertia::render('Client/MaNouvellePage', [
        'donnees' => $donnees
    ]);
}
```

### 4. Ajouter le Lien dans le Menu

**`resources/js/Layouts/ClientLayout.vue`**

```vue
<a :href="route('client.ma-page')"
   class="nav-link"
   :class="{ active: $page.url === '/client/ma-page' }">
    <i class="fas fa-icon-name"></i>Ma Page
</a>
```

### 5. Build et Test

```bash
npm run build
php artisan serve
```

Accéder à : `http://127.0.0.1:8000/client/ma-page`

## 🎨 Utiliser le ClientLayout

Le layout fournit automatiquement :
- Navbar avec logo et menu utilisateur
- Sidebar de navigation
- Messages flash
- Gestion d'état de navigation active

```vue
<template>
    <ClientLayout title="Titre de la Page">
        <!-- Votre contenu -->
    </ClientLayout>
</template>

<script>
import ClientLayout from '@/Layouts/ClientLayout.vue';

export default {
    components: {
        ClientLayout
    }
};
</script>
```

## 🔄 Naviguer entre Pages

### Liens Simples

```vue
<!-- Lien standard -->
<a :href="route('client.contrats')">Mes Contrats</a>

<!-- Lien Inertia (sans rechargement) -->
<Link :href="route('client.factures')" class="btn btn-primary">
    Mes Factures
</Link>
```

### Navigation Programmatique

```vue
<script>
import { router } from '@inertiajs/vue3';

export default {
    methods: {
        allerAuxFactures() {
            router.visit(route('client.factures'));
        },

        allerAvecParams() {
            router.get(route('client.contrats'), {
                statut: 'actif',
                page: 2
            });
        }
    }
};
</script>
```

## 📤 Soumettre des Formulaires

### GET (avec paramètres)

```vue
<script>
import { router } from '@inertiajs/vue3';

export default {
    data() {
        return {
            filters: {
                search: '',
                statut: ''
            }
        };
    },

    methods: {
        appliquerFiltres() {
            router.get(route('client.contrats'), this.filters, {
                preserveState: true,    // Garde l'état
                preserveScroll: true    // Garde la position scroll
            });
        }
    }
};
</script>
```

### POST (création)

```vue
<script>
import { router } from '@inertiajs/vue3';

export default {
    data() {
        return {
            form: {
                nom: '',
                email: ''
            }
        };
    },

    methods: {
        soumettre() {
            router.post(route('client.store'), this.form, {
                onSuccess: () => {
                    // Succès
                    this.form = { nom: '', email: '' };
                },
                onError: (errors) => {
                    // Erreurs de validation
                    console.log(errors);
                }
            });
        }
    }
};
</script>
```

### PUT (mise à jour)

```vue
<script>
import { router } from '@inertiajs/vue3';

export default {
    methods: {
        mettreAJour() {
            router.put(route('client.profil.update'), this.form, {
                onSuccess: () => {
                    alert('Profil mis à jour !');
                }
            });
        }
    }
};
</script>
```

### DELETE

```vue
<script>
import { router } from '@inertiajs/vue3';

export default {
    methods: {
        supprimer(documentId) {
            if (confirm('Êtes-vous sûr ?')) {
                router.delete(route('client.documents.delete', documentId));
            }
        }
    }
};
</script>
```

## 📥 Upload de Fichiers

```vue
<template>
    <form @submit.prevent="uploadFile">
        <input
            type="file"
            ref="fileInput"
            @change="handleFileSelect"
        >
        <button type="submit" :disabled="!selectedFile">
            Envoyer
        </button>
    </form>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
    data() {
        return {
            selectedFile: null
        };
    },

    methods: {
        handleFileSelect(e) {
            this.selectedFile = e.target.files[0];
        },

        uploadFile() {
            const formData = new FormData();
            formData.append('file', this.selectedFile);

            router.post(route('client.documents.upload'), formData, {
                onSuccess: () => {
                    this.selectedFile = null;
                    this.$refs.fileInput.value = '';
                }
            });
        }
    }
};
</script>
```

## 🎯 Accéder aux Props Inertia

### Props de Page

```vue
<script>
export default {
    props: {
        contrats: Object,
        stats: Object
    },

    computed: {
        totalContrats() {
            return this.contrats.data.length;
        }
    }
};
</script>
```

### Props Partagées (Global)

```vue
<script>
export default {
    computed: {
        user() {
            return this.$page.props.auth.user;
        },

        flashSuccess() {
            return this.$page.props.flash?.success;
        }
    }
};
</script>
```

### URL Courante

```vue
<script>
export default {
    computed: {
        isActive() {
            return this.$page.url === '/client/contrats';
        }
    }
};
</script>
```

## 🔗 Helper Ziggy (Routes)

```vue
<script>
export default {
    methods: {
        exemples() {
            // Route simple
            route('client.dashboard')
            // → "/client/dashboard"

            // Route avec paramètre
            route('client.contrats.show', 5)
            // → "/client/contrats/5"

            // Route avec paramètres query
            route('client.factures', { statut: 'payee' })
            // → "/client/factures?statut=payee"

            // Vérifier route courante
            route().current('client.dashboard')
            // → true/false
        }
    }
};
</script>
```

## 🎨 Styles et Classes

### Classes Conditionnelles

```vue
<template>
    <div :class="{
        'active': isActive,
        'disabled': isDisabled,
        'bg-primary': isPrimary
    }">
        Contenu
    </div>

    <!-- Ou avec tableaux -->
    <div :class="[
        'card',
        isLarge ? 'card-lg' : 'card-sm',
        { 'shadow': hasShadow }
    ]">
        Carte
    </div>
</template>
```

### Styles Inline

```vue
<template>
    <div :style="{
        color: textColor,
        backgroundColor: bgColor,
        fontSize: fontSize + 'px'
    }">
        Texte stylé
    </div>
</template>
```

## 🛠️ Utilitaires Courants

### Formater une Date

```javascript
methods: {
    formatDate(date) {
        if (!date) return '';
        return new Date(date).toLocaleDateString('fr-FR');
    },

    formatDateTime(date) {
        if (!date) return '';
        const d = new Date(date);
        return `${d.toLocaleDateString('fr-FR')} ${d.toLocaleTimeString('fr-FR')}`;
    }
}
```

### Formater un Montant

```javascript
methods: {
    formatAmount(amount) {
        if (!amount) return '0.00';
        return parseFloat(amount).toFixed(2);
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    }
}
```

### Tronquer du Texte

```javascript
methods: {
    truncate(str, length) {
        if (!str) return '';
        return str.length > length
            ? str.substring(0, length) + '...'
            : str;
    }
}
```

## 🐛 Debugging

### Console DevTools

```vue
<script>
export default {
    mounted() {
        // Afficher props
        console.log('Props:', this.$props);

        // Afficher page Inertia
        console.log('Page:', this.$page);

        // Afficher route courante
        console.log('Route:', route().current());
    }
};
</script>
```

### Vue DevTools

Installer l'extension Vue DevTools pour Chrome/Firefox pour :
- Inspector les composants
- Voir l'état réactif
- Tracer les événements
- Analyser les performances

## ⚡ Performance

### Lazy Loading des Composants

```javascript
// Au lieu de
import MaComposant from './MaComposant.vue';

// Utiliser
const MaComposant = () => import('./MaComposant.vue');
```

### Computed vs Methods

```javascript
// ❌ Mauvais (recalculé à chaque render)
methods: {
    filteredItems() {
        return this.items.filter(i => i.active);
    }
}

// ✅ Bon (mise en cache)
computed: {
    filteredItems() {
        return this.items.filter(i => i.active);
    }
}
```

## 📚 Ressources

- [Vue.js 3 Documentation](https://vuejs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Ziggy Documentation](https://github.com/tighten/ziggy)
- [Bootstrap 5 Documentation](https://getbootstrap.com/)

---

**Bonne continuation dans le développement ! 🚀**
