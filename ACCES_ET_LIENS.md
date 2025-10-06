# 🔗 ACCÈS ET LIENS - ESPACE CLIENT BOXIBOX

## 🌐 URL DE BASE

```
http://127.0.0.1:8000
```

---

## 🔐 IDENTIFIANTS DE TEST

### 👤 Client Test Premium (Recommandé pour débuter)
```
Email: test.premium@boxibox.com
Mot de passe: test123
```
**Profil:** Tout est parfait, aucune alerte

### ⚠️ Client Test en Retard
```
Email: test.retard@boxibox.com
Mot de passe: test123
```
**Profil:** Factures impayées, relances

### 🆕 Nouveau Client
```
Email: test.nouveau@boxibox.com
Mot de passe: test123
```
**Profil:** Historique minimal

### 🔄 Client Mixte
```
Email: test.mixte@boxibox.com
Mot de passe: test123
```
**Profil:** Situation réaliste mixte

---

## 📍 LIENS DIRECTS - ESPACE CLIENT

### 🏠 Tableau de Bord
```
http://127.0.0.1:8000/client/dashboard
```
**Vue.js:** ✅ Dashboard.vue
- Statistiques en temps réel
- Alertes contextuelles
- Activité récente

### 📄 Mes Contrats
```
http://127.0.0.1:8000/client/contrats
```
**Vue.js:** ✅ Contrats.vue
- Liste complète des contrats
- Filtres (statut, date, montant)
- Recherche par N° contrat ou box
- Pagination
- Téléchargement PDF

**Détail d'un contrat (remplacer {id}):**
```
http://127.0.0.1:8000/client/contrats/{id}
```

### 💰 Mes Factures
```
http://127.0.0.1:8000/client/factures
```
**Vue.js:** ✅ Factures.vue
- Liste des factures avec statuts
- Filtres multiples
- Recherche
- Téléchargement PDF

**Détail d'une facture (remplacer {id}):**
```
http://127.0.0.1:8000/client/factures/{id}
```

### 📁 Mes Documents
```
http://127.0.0.1:8000/client/documents
```
**Vue.js:** ✅ Documents.vue
- Upload drag & drop
- Liste des documents
- Téléchargement/Suppression
- Validation (PDF, 20MB max)

### 🏦 Mandats SEPA
```
http://127.0.0.1:8000/client/sepa
```
**Vue.js:** ✅ Sepa.vue
- Affichage mandats
- IBAN masqué sécurisé
- Téléchargement PDF mandat

**Créer un mandat:**
```
http://127.0.0.1:8000/client/sepa/create
```

### 👤 Mon Profil
```
http://127.0.0.1:8000/client/profil
```
**Vue.js:** ✅ Profil.vue
- Édition informations personnelles
- Coordonnées (email, téléphone, adresse)
- Informations entreprise
- Gestion mot de passe

### 💳 Mes Règlements
```
http://127.0.0.1:8000/client/reglements
```
**Vue.js:** ✅ Reglements.vue
- Historique paiements
- Statistiques
- Filtres (mode paiement, dates)
- Détails transactions

### 🔔 Mes Relances
```
http://127.0.0.1:8000/client/relances
```
**Blade** (non migré)
- Historique des relances
- Détails rappels

### ⏱️ Suivi
```
http://127.0.0.1:8000/client/suivi
```
**Blade** (non migré)
- Timeline d'activité
- Historique événements

---

## 🎯 NAVIGATION RAPIDE

### Menu Principal (Sidebar)

1. **🏠 Accueil** → `/client/dashboard` → Vue.js ✅
2. **📄 Contrats** → `/client/contrats` → Vue.js ✅
3. **🏦 Mandats SEPA** → `/client/sepa` → Vue.js ✅
4. **👤 Informations** → `/client/profil` → Vue.js ✅
5. **💰 Factures & Avoirs** → `/client/factures` → Vue.js ✅
6. **💳 Règlements** → `/client/reglements` → Vue.js ✅
7. **🔔 Relances** → `/client/relances` → Blade
8. **📁 Fichiers** → `/client/documents` → Vue.js ✅
9. **⏱️ Suivi** → `/client/suivi` → Blade

---

## 🔧 ADMINISTRATION

### Page de Connexion
```
http://127.0.0.1:8000/login
```

### Admin Principal
```
Email: admin@boxibox.com
Mot de passe: admin123
```

### Dashboard Admin
```
http://127.0.0.1:8000/dashboard
```

### Déconnexion
```
http://127.0.0.1:8000/logout
```
*(POST uniquement - utiliser le bouton dans l'interface)*

---

## 📊 STATUS DES PAGES

### ✅ Migrées Vue.js (7 pages)
- Dashboard ✅
- Contrats ✅
- Factures ✅
- Documents ✅
- SEPA ✅
- Profil ✅
- Règlements ✅

### 📄 Restent en Blade (2 pages secondaires)
- Relances
- Suivi
- Pages de détail individuelles (show)

---

## 🚀 DÉMARRAGE RAPIDE

### 1. Démarrer le serveur
```bash
cd C:\xampp2025\htdocs\boxibox
php artisan serve
```

### 2. Accéder à l'application
```
http://127.0.0.1:8000
```

### 3. Se connecter
Utiliser les identifiants de test ci-dessus

### 4. Naviguer
Utiliser le menu latéral (sidebar)

---

## 🧪 SCÉNARIOS DE TEST

### Test 1: Navigation Complète
1. Se connecter avec `test.premium@boxibox.com`
2. Parcourir toutes les pages du menu
3. Vérifier que chaque page charge correctement
4. Tester les filtres et la recherche

### Test 2: Upload Document
1. Aller sur `/client/documents`
2. Drag & drop un fichier PDF
3. Vérifier l'upload
4. Télécharger le document
5. Supprimer le document

### Test 3: Édition Profil
1. Aller sur `/client/profil`
2. Modifier email/téléphone/adresse
3. Enregistrer
4. Vérifier message de succès
5. Actualiser la page
6. Vérifier que les modifications sont sauvegardées

### Test 4: Filtres Contrats
1. Aller sur `/client/contrats`
2. Rechercher par N° contrat
3. Filtrer par statut
4. Trier par date/montant
5. Naviguer avec pagination
6. Télécharger un contrat en PDF

### Test 5: Navigation SPA
1. Naviguer entre les pages
2. Vérifier qu'il n'y a **pas de rechargement** de page
3. Utiliser le bouton "Retour" du navigateur
4. Vérifier que l'état est conservé

---

## 🐛 RÉSOLUTION DE PROBLÈMES

### Le serveur ne démarre pas
```bash
# Vérifier qu'aucun autre processus n'utilise le port 8000
netstat -ano | findstr :8000

# Démarrer sur un autre port
php artisan serve --port=8001
```

### Page blanche
1. Vérifier les logs: `storage/logs/laravel.log`
2. Compiler les assets: `npm run build`
3. Vider le cache: `php artisan cache:clear`

### Erreur 403 "Accès réservé aux clients"
1. Vérifier que l'utilisateur a le rôle "Client"
2. Se déconnecter et reconnecter
3. Vider les cookies du navigateur

### Assets Vue.js ne chargent pas
```bash
# Recompiler
npm run build

# Vérifier que les fichiers sont dans public/build/
dir public\build
```

---

## 📞 SUPPORT

Pour toute question:
- **Logs Laravel:** `storage/logs/laravel.log`
- **Console navigateur:** F12 → Console
- **Documentation:** `GUIDE_DEVELOPPEUR_VUE.md`

---

## 🎉 PROFITEZ DE L'ESPACE CLIENT !

**Toutes les pages principales sont en Vue.js et fonctionnent parfaitement !** 🚀

---

**Dernière mise à jour:** Octobre 2025
**Version:** Vue.js 3.3 + Laravel 10
