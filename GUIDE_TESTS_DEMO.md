# 🧪 BoxiBox - Guide de Tests et Démonstration

**Date**: 30 septembre 2025
**Version**: 2.5
**Serveur**: http://localhost:8000

---

## 🔑 Accès à l'Application

### URL Principale
**🌐 Application**: http://localhost:8000

### Compte Administrateur
- **Email**: `admin@boxibox.com`
- **Mot de passe**: À définir lors de la première connexion
- **Rôle**: Administrateur (accès complet)

### Page de Connexion
**URL**: http://localhost:8000/login

---

## 📊 État des Données

### Utilisateurs
- ✅ **1 utilisateur administrateur** créé
- Nom: Administrateur BoxiBox
- Email: admin@boxibox.com
- Rôle: administrateur

### Clients
- ✅ **2 clients de démonstration** créés
  1. Jean Dupont (jean.dupont@example.com)
  2. Marie Martin (marie.martin@example.com)

### Données à Créer via Interface
Les éléments suivants doivent être créés via l'interface web:
- Emplacements
- Familles de boxes
- Boxes
- Contrats
- Factures

---

## 🧪 Tests des Fonctionnalités Implémentées

### 1. ✅ Système Multilingue FR/EN

#### Test #1: Changement de Langue via URL
1. Accéder au dashboard: http://localhost:8000/dashboard
2. **Passer en anglais**: http://localhost:8000/dashboard?lang=en
3. **Retour en français**: http://localhost:8000/dashboard?lang=fr

**Résultat attendu**:
- L'interface change immédiatement de langue
- La préférence est sauvegardée en session
- Si connecté, la préférence est sauvegardée en base

#### Test #2: Détection Automatique
1. Déconnectez-vous
2. Changez la langue de votre navigateur
3. Reconnectez-vous

**Résultat attendu**:
- L'application détecte automatiquement la langue du navigateur
- Si français ou anglais: affiche dans cette langue
- Sinon: affiche en français (langue par défaut)

#### URLs de Test
- Dashboard FR: http://localhost:8000/dashboard?lang=fr
- Dashboard EN: http://localhost:8000/dashboard?lang=en
- Clients FR: http://localhost:8000/commercial/clients?lang=fr
- Clients EN: http://localhost:8000/commercial/clients?lang=en

---

### 2. 🗺️ Plan Interactif des Boxes

**URL**: http://localhost:8000/technique/plan

#### Test #1: Zoom et Navigation
1. Accéder au plan des boxes
2. **Zoomer**: Cliquez sur les boutons `+` / `-` ou utilisez la molette
3. **Déplacer**: Cliquez et glissez pour naviguer
4. **Réinitialiser**: Cliquez sur le bouton de réinitialisation

**Résultat attendu**:
- Zoom fluide de 0.5x à 3x
- Navigation pan fluide
- Réinitialisation instantanée

#### Test #2: Filtres
1. Filtrer par **emplacement**
2. Filtrer par **famille**
3. Filtrer par **statut** (libre, occupé, réservé)
4. Rechercher un numéro de box spécifique

**Résultat attendu**:
- Les boxes sont filtrées en temps réel
- Les statistiques se mettent à jour
- La recherche fonctionne

#### Test #3: Info-bulles
1. Survolez un box pendant 300ms
2. Observez l'info-bulle affichée

**Résultat attendu**:
- Info-bulle avec: numéro, statut, dimensions, tarif
- Si occupé: nom client, contrat, date fin
- Boutons d'actions rapides

#### Code Couleur
- 🟢 Vert: Libre
- 🔴 Rouge: Occupé
- 🟡 Jaune: Réservé
- 🟠 Orange: Maintenance
- ⚫ Gris: Hors service

---

### 3. 📧 Rappels Automatiques de Paiement

#### Test #1: Commande Artisan (Mode Simulation)
```bash
php artisan reminders:send-payment --dry-run
```

**Résultat attendu**:
- Liste des factures qui recevraient un rappel
- Aucun email réellement envoyé
- Log dans le fichier de logs

#### Test #2: Envoi Réel
```bash
php artisan reminders:send-payment
```

**Résultat attendu**:
- Emails envoyés aux clients concernés
- Enregistrement dans la table `payment_reminders`
- Logs des envois

#### Test #3: Relances pour Impayés
```bash
php artisan reminders:send-payment --type=relance
```

**Résultat attendu**:
- Relances envoyées uniquement pour factures impayées
- Type de relance progressif (relance_1, relance_2, mise_en_demeure)

#### Vérification des Données
**Accès base de données**: http://localhost/phpmyadmin

Tables à vérifier:
- `reminders_config`: Configuration des rappels
- `payment_reminders`: Historique des rappels envoyés

---

### 4. 💰 Facturation en Masse

**URL**: http://localhost:8000/finance/factures/bulk-generate

#### Prérequis
Créer d'abord:
1. Au moins 1 contrat actif
2. Via: http://localhost:8000/commercial/contrats

#### Test #1: Génération pour Tous les Contrats
1. Accéder à la page de facturation en masse
2. Cocher "Générer pour tous les contrats actifs"
3. Définir date d'émission et d'échéance
4. Cliquer sur "Générer les factures"

**Résultat attendu**:
- Factures générées pour chaque contrat actif
- Calcul automatique: loyer + services additionnels
- Évitement des doublons (même période)
- Message de succès avec nombre de factures créées

#### Test #2: Génération Sélective
1. Décocher "Tous les contrats"
2. Sélectionner manuellement certains contrats
3. Générer

**Résultat attendu**:
- Factures créées uniquement pour contrats sélectionnés

#### Test #3: Gestion des Erreurs
1. Essayer de générer deux fois pour la même période

**Résultat attendu**:
- Erreur indiquant que la facture existe déjà
- Les autres contrats continuent d'être traités

---

### 5. 🔏 Signatures Électroniques (Backend)

**URL Admin**: http://localhost:8000/signatures

#### Test #1: Vérification des Routes
Tester ces URLs (authentifié):
- Liste: http://localhost:8000/signatures
- Demande signature contrat: POST /signatures/contrats/{id}/request
- Demande signature SEPA: POST /signatures/sepa/{id}/request

#### Test #2: Modèle et Relations
```bash
php artisan tinker
```

Puis:
```php
// Vérifier le modèle Signature
App\Models\Signature::all();

// Vérifier les relations
$contrat = App\Models\Contrat::first();
$contrat->signatures; // Doit retourner une collection

// Créer une signature de test
$client = App\Models\Client::first();
$signature = App\Models\Signature::create([
    'signable_type' => 'App\Models\Contrat',
    'signable_id' => 1,
    'client_id' => $client->id,
    'signataire_nom' => $client->nom . ' ' . $client->prenom,
    'signataire_email' => $client->email,
    'statut' => 'en_attente',
    'signature_method' => 'simple',
    'token' => App\Models\Signature::generateToken(),
    'date_envoi' => now(),
    'date_expiration' => now()->addDays(7)
]);

echo "Signature créée avec token: " . $signature->token;
```

**Résultat attendu**:
- Signature créée avec succès
- Token unique généré
- Relations fonctionnelles

#### Test #3: URL Publique de Signature
Format: http://localhost:8000/sign/{token}

**Résultat attendu**:
- Page accessible sans authentification
- Vérification expiration
- Formulaire de signature (si vue créée)

#### Test #4: Scopes et Méthodes
```php
// Dans tinker
App\Models\Signature::pending()->count(); // Signatures en attente
App\Models\Signature::signed()->count(); // Signées
App\Models\Signature::expired()->count(); // Expirées

$signature = App\Models\Signature::first();
$signature->isPending(); // true/false
$signature->isSigned(); // true/false
$signature->isExpired(); // true/false
```

---

## 🔧 Tests des Commandes Artisan

### Migrations
```bash
# Vérifier l'état des migrations
php artisan migrate:status

# Rollback dernière migration
php artisan migrate:rollback --step=1
```

### Cache
```bash
# Vider tous les caches
php artisan optimize:clear

# Cache de configuration
php artisan config:cache
```

### Rappels
```bash
# Mode simulation
php artisan reminders:send-payment --dry-run

# Envoi réel
php artisan reminders:send-payment

# Relances
php artisan reminders:send-payment --type=relance
```

---

## 📍 Navigation Complète

### Modules Commerciaux
- **Prospects**: http://localhost:8000/commercial/prospects
- **Clients**: http://localhost:8000/commercial/clients
- **Contrats**: http://localhost:8000/commercial/contrats

### Modules Financiers
- **Factures**: http://localhost:8000/finance/factures
- **Facturation en masse**: http://localhost:8000/finance/factures/bulk-generate
- **Règlements**: http://localhost:8000/finance/reglements
- **SEPA**: http://localhost:8000/finance/sepa

### Module Technique
- **Liste boxes**: http://localhost:8000/technique/boxes
- **Plan interactif**: http://localhost:8000/technique/plan

### Signatures (Nouveau)
- **Liste signatures**: http://localhost:8000/signatures

### Administration
- **Utilisateurs**: http://localhost:8000/admin/users
- **Statistiques**: http://localhost:8000/admin/statistics
- **Paramètres**: http://localhost:8000/admin/settings

---

## ✅ Checklist de Tests

### Fonctionnalités Principales
- [ ] Connexion avec admin@boxibox.com
- [ ] Changement de langue FR ↔ EN
- [ ] Navigation dans tous les modules
- [ ] Création d'un prospect
- [ ] Conversion prospect → client
- [ ] Création d'un contrat
- [ ] Génération facture unique
- [ ] Facturation en masse
- [ ] Création règlement
- [ ] Visualisation plan des boxes
- [ ] Test zoom/pan sur le plan
- [ ] Filtrage des boxes
- [ ] Commande rappels (dry-run)
- [ ] Vérification signatures dans BDD

### Backend (via Tinker)
- [ ] Comptage des enregistrements
- [ ] Test relations Eloquent
- [ ] Création signature de test
- [ ] Test scopes (pending, signed, expired)
- [ ] Génération token unique

### Base de Données (phpMyAdmin)
- [ ] Table `users` → 1 admin
- [ ] Table `clients` → 2 clients
- [ ] Table `signatures` → structure OK
- [ ] Table `reminders_config` → configuration
- [ ] Table `payment_reminders` → historique
- [ ] Table `users` → champ `locale` présent

---

## 🐛 Résolution des Problèmes

### Le serveur ne démarre pas
```bash
# Vérifier si port 8000 est libre
netstat -an | findstr :8000

# Utiliser un autre port
php artisan serve --port=8001
```

### Erreur de migration
```bash
# Vérifier la connexion DB
php artisan migrate:status

# Réexécuter les migrations
php artisan migrate:fresh
# ⚠️ ATTENTION: perte de données!
```

### Erreur 500
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Ou dans Windows
type storage\logs\laravel.log
```

### Problème de permissions
```bash
# Donner les droits sur storage et cache
chmod -R 775 storage bootstrap/cache
# Ou Windows: vérifier droits manuellement
```

---

## 📊 Résumé de l'Implémentation

### ✅ Complété (65%)
1. **Infrastructure**: Laravel 10.49, MySQL, Bootstrap 5
2. **Modules commerciaux**: Prospects, clients, contrats
3. **Modules financiers**: Factures, règlements, SEPA
4. **Gestion technique**: Boxes, plan interactif
5. **Plan interactif avancé**: Zoom, pan, filtres, info-bulles
6. **Rappels automatiques**: Email/SMS, commande Artisan
7. **Facturation en masse**: Tous contrats, calcul auto
8. **Système multilingue**: FR/EN, détection auto 4 niveaux
9. **Signatures électroniques**: Backend complet (modèle, contrôleur, routes)

### 🔄 En Cours (20%)
- Vues frontend signatures
- Statistiques avancées
- Interfaces complètes

### ⏳ À Faire (15%)
- Rapprochement bancaire
- Service SMS
- Authentification 2FA
- API mobile

---

## 🚀 Démarrage Rapide

```bash
# 1. Démarrer XAMPP (Apache + MySQL)

# 2. Démarrer le serveur Laravel
cd C:\xampp2025\htdocs\boxibox
php artisan serve --host=0.0.0.0 --port=8000

# 3. Accéder à l'application
# Ouvrir http://localhost:8000

# 4. Se connecter
# Email: admin@boxibox.com
# Mot de passe: (à définir)

# 5. Tester le multilingue
# Ajouter ?lang=en ou ?lang=fr à l'URL
```

---

## 📞 Support

**Base de données**: http://localhost/phpmyadmin
**Logs**: `storage/logs/laravel.log`
**Documentation**: `FONCTIONNALITES_AVANCEES.md`

---

**Dernière mise à jour**: 30 septembre 2025
**Développé avec**: Laravel 10.49, PHP 8.1, Bootstrap 5, MySQL 8.0

🤖 *Document généré avec Claude Code*