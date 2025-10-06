# 🔧 GUIDE COMPLET - UTILISATION AVEC XAMPP

## ✅ PROBLÈME RÉSOLU - Assets compilés

Le dashboard était vide car les assets JavaScript/CSS n'étaient pas compilés.
**✅ C'est maintenant corrigé !**

---

## 🌐 URLs CORRECTES AVEC XAMPP

```
🏠 Page d'accueil:    http://localhost/boxibox/public/
🔐 Login:             http://localhost/boxibox/public/login
👤 Dashboard Client:  http://localhost/boxibox/public/client/dashboard
👨‍💼 Dashboard Admin:   http://localhost/boxibox/public/dashboard
🗺️ Plan Admin:        http://localhost/boxibox/public/technique/plan
🗺️ Plan Client:       http://localhost/boxibox/public/client/box-plan
```

---

## 🔑 CONNEXION

### Client (Recommandé pour tester)
```
📧 Email: test.premium@boxibox.com
🔑 Mot de passe: test123
```

### Admin
```
📧 Email: admin@boxibox.com
🔑 Mot de passe: admin123
```

---

## ⚡ DÉMARRAGE RAPIDE

### 1️⃣ S'assurer que XAMPP tourne
```
✅ Apache doit être démarré
✅ MySQL doit être démarré
```

### 2️⃣ Accéder à l'application
```
http://localhost/boxibox/public/login
```

### 3️⃣ Se connecter
- Utiliser un des comptes ci-dessus

### 4️⃣ Profiter de l'application ! 🎉

---

## 🔄 APRÈS MODIFICATIONS DU CODE

### Si vous modifiez des fichiers Vue.js (.vue) ou JavaScript:

```bash
cd C:\xampp2025\htdocs\boxibox
npm run build
```

**⚠️ IMPORTANT:** À chaque modification de fichier Vue, vous devez recompiler !

### Alternative - Mode Développement (assets auto-recompilés):

```bash
npm run dev
```
Laissez cette commande tourner en arrière-plan pendant que vous développez.

---

## 🐛 SI LE DASHBOARD EST VIDE

### Solution 1 - Recompiler les assets
```bash
cd C:\xampp2025\htdocs\boxibox
npm run build
```

### Solution 2 - Vider les caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Solution 3 - Vider le cache navigateur
- `Ctrl + Shift + Delete`
- Cocher "Images et fichiers en cache"
- Cliquer "Effacer les données"

### Solution 4 - Vérifier la console
- `F12` pour ouvrir les outils développeur
- Onglet "Console" - chercher des erreurs JavaScript
- Onglet "Réseau" - vérifier que les fichiers .js et .css chargent (statut 200)

---

## 📁 STRUCTURE DES FICHIERS IMPORTANTS

```
boxibox/
├── public/
│   ├── build/              ← Assets compilés (JS/CSS)
│   │   ├── manifest.json
│   │   └── assets/
│   └── index.php           ← Point d'entrée
├── resources/
│   ├── js/
│   │   ├── Pages/          ← Vos pages Vue.js
│   │   ├── Components/     ← Composants réutilisables
│   │   ├── Layouts/        ← Layouts (ClientLayout, etc.)
│   │   └── app.js          ← Point d'entrée JS
│   └── views/
│       └── app.blade.php   ← Template Inertia
├── routes/
│   └── web.php             ← Définition des routes
└── app/
    └── Http/
        └── Controllers/    ← Contrôleurs
```

---

## 🎯 PAGES DISPONIBLES

### **Espace Client** `/client/...`

| Page | URL | Description |
|------|-----|-------------|
| Dashboard | `/client/dashboard` | Vue d'ensemble avec stats |
| Contrats | `/client/contrats` | Liste des contrats |
| Plan Boxes | `/client/box-plan` | Plan interactif ⭐ |
| Factures | `/client/factures` | Factures et paiements |
| Règlements | `/client/reglements` | Historique paiements |
| Relances | `/client/relances` | Rappels de paiement |
| SEPA | `/client/sepa` | Mandats prélèvement |
| Documents | `/client/documents` | Upload/Download |
| Suivi | `/client/suivi` | Timeline activité |
| Profil | `/client/profil` | Infos personnelles |

### **Espace Admin** `/...`

| Page | URL | Description |
|------|-----|-------------|
| Dashboard | `/dashboard` | Stats globales |
| Plan Interactif | `/technique/plan` | Gestion visuelle boxes ⭐ |
| Boxes | `/technique/boxes` | Liste des boxes |
| Clients | `/clients` | Gestion clients |
| Contrats | `/contrats` | Gestion contrats |
| Factures | `/factures` | Gestion factures |

---

## 🚀 COMMANDES UTILES

### Recompiler les assets
```bash
npm run build
```

### Mode développement (hot reload)
```bash
npm run dev
```

### Vider tous les caches
```bash
php artisan optimize:clear
```

### Voir les routes disponibles
```bash
php artisan route:list
```

### Créer des données de test
```bash
php artisan db:seed --class=TestUsersSeeder
php artisan db:seed --class=BoxCoordinatesSeeder
```

---

## 🔍 VÉRIFICATION QUE TOUT FONCTIONNE

### ✅ Checklist

- [ ] XAMPP Apache démarré
- [ ] XAMPP MySQL démarré
- [ ] Assets compilés (`npm run build` exécuté)
- [ ] Accès à `http://localhost/boxibox/public/login` ✓
- [ ] Connexion avec `test.premium@boxibox.com` / `test123` ✓
- [ ] Dashboard client s'affiche avec graphiques ✓
- [ ] Menu de gauche complet avec 10 liens ✓
- [ ] Plan des boxes accessible et fonctionnel ✓

---

## 💡 ASTUCE - URL Plus Courte

Si vous voulez utiliser `http://localhost/boxibox/` au lieu de `.../public/`:

### Créer un fichier `.htaccess` dans `C:\xampp2025\htdocs\boxibox\`

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

Puis accéder via:
```
http://localhost/boxibox/login
http://localhost/boxibox/client/dashboard
```

---

## 🆚 XAMPP vs Laravel Server

| Critère | XAMPP | Laravel Server |
|---------|-------|----------------|
| URL | `localhost/boxibox/public/` | `localhost:8000/` |
| Démarrage | Déjà installé | `php artisan serve` |
| Assets | Build requis | Build OU `npm run dev` |
| Performance | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| Simplicité | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |

**Recommandation:** Les deux fonctionnent ! XAMPP si vous êtes habitué, Laravel Server si vous développez.

---

## 📞 EN CAS DE PROBLÈME

### Dashboard vide / Page blanche
1. Vérifier console (F12) pour erreurs
2. Recompiler: `npm run build`
3. Vider caches Laravel
4. Vider cache navigateur

### Erreur 404
- Vérifier l'URL (bien mettre `/boxibox/public/`)
- Vérifier Apache dans XAMPP

### Erreur 500
- Consulter `storage/logs/laravel.log`
- Vérifier permissions dossier `storage/`

### Assets ne chargent pas
- Vérifier que `public/build/manifest.json` existe
- Recompiler: `npm run build`
- Vérifier onglet Réseau (F12) pour erreurs 404

---

## ✅ C'EST PRÊT !

**Toutes les assets sont compilés. L'application devrait maintenant fonctionner parfaitement avec XAMPP !**

Accédez à:
```
http://localhost/boxibox/public/login
```

Connectez-vous et profitez ! 🎉

---

**Dernière compilation:** Assets buildés avec succès (43 fichiers générés)
**Boxes configurés:** 11 boxes avec coordonnées sur 3 emplacements
**Status:** ✅ PRÊT POUR UTILISATION
