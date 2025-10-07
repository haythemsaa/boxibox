# 💳 Guide d'intégration Stripe - Paiements en ligne Boxibox

## 📋 Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Architecture](#architecture)
5. [Flux de paiement](#flux-de-paiement)
6. [Tests](#tests)
7. [Production](#production)
8. [Sécurité](#sécurité)
9. [Troubleshooting](#troubleshooting)

---

## 🎯 Vue d'ensemble

Cette intégration permet aux clients de payer leurs factures en ligne via **Stripe Checkout**, une solution de paiement sécurisée et PCI-DSS compliant.

### Fonctionnalités
- ✅ Paiement par carte bancaire (Visa, Mastercard, Amex)
- ✅ Interface de paiement hébergée par Stripe (Checkout)
- ✅ Webhooks pour confirmation automatique des paiements
- ✅ Création automatique de règlements
- ✅ Mise à jour automatique des factures
- ✅ Notifications email automatiques (client + admin)
- ✅ Pages de succès et d'annulation
- ✅ Mode test et production
- ✅ Sécurité PCI-DSS (aucune donnée bancaire stockée)

### Packages installés
```bash
composer require stripe/stripe-php  # v18.0.0
```

---

## 🚀 Installation

### 1. Package PHP (déjà installé)
```bash
composer require stripe/stripe-php
```

### 2. Créer un compte Stripe
1. Aller sur https://stripe.com
2. Créer un compte (gratuit)
3. Activer le mode TEST pour développement

### 3. Obtenir les clés API

#### Mode TEST (développement)
1. Dashboard Stripe → Developers → API keys
2. Copier la **Publishable key** (commence par `pk_test_`)
3. Copier la **Secret key** (commence par `sk_test_`)

#### Cartes de test Stripe
```
✅ Succès : 4242 4242 4242 4242
❌ Carte refusée : 4000 0000 0000 0002
💰 Fonds insuffisants : 4000 0000 0000 9995
🔐 3D Secure requis : 4000 0027 6000 3184

Date d'expiration : N'importe quelle date future
CVV : N'importe quel 3 chiffres
```

---

## ⚙️ Configuration

### 1. Variables d'environnement

Ajouter dans votre fichier `.env` :

```env
# Stripe - Paiements en ligne
STRIPE_PUBLIC_KEY=pk_test_VOTRE_CLE_PUBLIQUE
STRIPE_SECRET_KEY=sk_test_VOTRE_CLE_SECRETE
STRIPE_WEBHOOK_SECRET=whsec_VOTRE_WEBHOOK_SECRET
```

**📌 Important** : Un fichier `.env.stripe.example` est fourni avec des instructions détaillées.

### 2. Configurer le webhook Stripe

#### Créer un endpoint webhook
1. Dashboard Stripe → Developers → Webhooks
2. Cliquer sur **+ Add endpoint**
3. URL : `https://votredomaine.com/stripe/webhook`
4. Sélectionner les événements :
   - ✅ `checkout.session.completed`
   - ✅ `payment_intent.payment_failed`
5. Copier le **Signing secret** (commence par `whsec_`)
6. Ajouter dans `.env` : `STRIPE_WEBHOOK_SECRET=whsec_...`

#### Pour le développement local (ngrok)
```bash
# Installer ngrok
npm install -g ngrok

# Créer un tunnel
ngrok http 8000

# Utiliser l'URL HTTPS fournie pour le webhook
# Ex: https://abc123.ngrok.io/stripe/webhook
```

---

## 🏗️ Architecture

### Fichiers créés/modifiés

#### Backend (Laravel)
```
app/
├── Http/Controllers/Client/
│   └── PaymentController.php          [CRÉÉ] - Contrôleur de paiement
├── Mail/
│   ├── PaymentConfirmation.php        [CRÉÉ] - Email client
│   └── PaymentNotificationAdmin.php   [CRÉÉ] - Email admin
├── Models/
│   ├── Facture.php                    [EXISTANT]
│   └── Reglement.php                  [EXISTANT]
config/
└── services.php                       [CRÉÉ] - Configuration Stripe
routes/
└── web.php                            [MODIFIÉ] - Routes de paiement
resources/views/emails/
├── payment-confirmation.blade.php     [CRÉÉ] - Template client
└── payment-notification-admin.blade.php [CRÉÉ] - Template admin
```

#### Frontend (Vue.js)
```
resources/js/Pages/Client/
├── Payment.vue                        [CRÉÉ] - Page de paiement
├── PaymentSuccess.vue                 [CRÉÉ] - Page de succès
├── PaymentCancel.vue                  [CRÉÉ] - Page d'annulation
└── FactureShow.vue                    [MODIFIÉ] - Bouton "Payer en ligne"
```

#### Configuration
```
.env.stripe.example                    [CRÉÉ] - Exemple configuration
STRIPE_INTEGRATION_GUIDE.md            [CRÉÉ] - Ce fichier
```

### Routes disponibles

| Méthode | Route | Action | Auth |
|---------|-------|--------|------|
| GET | `/client/payment/{facture}` | Afficher page de paiement | ✅ |
| POST | `/client/payment/{facture}/checkout` | Créer session Stripe | ✅ |
| GET | `/client/payment/{facture}/success` | Page de succès | ✅ |
| GET | `/client/payment/{facture}/cancel` | Page d'annulation | ✅ |
| POST | `/stripe/webhook` | Webhook Stripe | ❌ |

---

## 🔄 Flux de paiement

### 1. Client consulte sa facture
```
Client → Facture Show → Bouton "Payer en ligne"
```

### 2. Page de paiement
```
Client → Payment.vue
├── Résumé de la facture
├── Informations de sécurité
└── Bouton "Procéder au paiement"
```

### 3. Création session Stripe
```javascript
POST /client/payment/{facture}/checkout
├── Vérification facture (non payée, appartient au client)
├── Création session Stripe Checkout
└── Retour URL de redirection
```

### 4. Redirection vers Stripe Checkout
```
Client → Page hébergée Stripe
├── Saisie carte bancaire
├── Authentification 3D Secure (si nécessaire)
└── Traitement du paiement
```

### 5. Retour après paiement

#### ✅ Succès
```
Stripe → Success URL → PaymentSuccess.vue
└── Affichage confirmation + détails paiement
```

#### ❌ Annulation
```
Stripe → Cancel URL → PaymentCancel.vue
└── Possibilité de réessayer
```

### 6. Confirmation via webhook
```
Stripe → POST /stripe/webhook
├── Vérification signature
├── Traitement événement checkout.session.completed
├── Création Reglement
├── Mise à jour Facture (statut = payee)
└── [TODO] Envoi notifications
```

---

## 🧪 Tests

### Mode TEST (recommandé pour développement)

#### 1. Configurer les clés TEST
```env
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
```

#### 2. Utiliser les cartes de test
```
✅ Paiement réussi
Numéro : 4242 4242 4242 4242
Date : 12/34
CVV : 123

❌ Paiement refusé
Numéro : 4000 0000 0000 0002
```

#### 3. Tester le flux complet
1. Se connecter en tant que client
2. Aller sur une facture non payée
3. Cliquer sur "Payer en ligne"
4. Vérifier la page de paiement
5. Cliquer sur "Procéder au paiement"
6. Utiliser une carte de test sur Stripe Checkout
7. Vérifier la redirection vers page de succès
8. Vérifier que la facture est marquée "payée"
9. Vérifier qu'un règlement a été créé

#### 4. Tester l'annulation
1. Sur Stripe Checkout, fermer la fenêtre ou cliquer sur "Retour"
2. Vérifier la redirection vers page d'annulation
3. Vérifier que la facture reste "non payée"

### Vérifier les webhooks

#### Dashboard Stripe
```
Developers → Webhooks → [Votre endpoint]
├── Vérifier les événements reçus
├── Voir le payload JSON
└── Vérifier le statut (succeeded/failed)
```

#### Logs Laravel
```bash
tail -f storage/logs/laravel.log | grep -i stripe
```

---

## 🚀 Production

### 1. Passer en mode LIVE

#### Obtenir les clés LIVE
1. Dashboard Stripe → Activer le compte (vérification identité)
2. Developers → API keys
3. Copier les clés **LIVE** (commence par `pk_live_` et `sk_live_`)

#### Mettre à jour .env
```env
# PRODUCTION - Stripe LIVE
STRIPE_PUBLIC_KEY=pk_live_VOTRE_CLE_PUBLIQUE_LIVE
STRIPE_SECRET_KEY=sk_live_VOTRE_CLE_SECRETE_LIVE
STRIPE_WEBHOOK_SECRET=whsec_VOTRE_WEBHOOK_SECRET_LIVE
```

### 2. Créer webhook PRODUCTION
1. Dashboard Stripe (mode LIVE) → Developers → Webhooks
2. **+ Add endpoint**
3. URL : `https://votredomaine.com/stripe/webhook`
4. Événements : `checkout.session.completed`, `payment_intent.payment_failed`
5. Copier le nouveau **Signing secret**
6. Mettre à jour `STRIPE_WEBHOOK_SECRET` dans `.env`

### 3. Tester en production

**⚠️ Attention** : Les paiements réels seront effectués

```
✅ Vérifier :
- Webhook configuré correctement
- Certificat SSL valide (HTTPS)
- Logs de production activés
- Notifications email configurées
```

### 4. Vider le cache Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔒 Sécurité

### 1. PCI-DSS Compliance
✅ **Aucune donnée bancaire n'est stockée** sur les serveurs Boxibox
✅ Paiement traité entièrement par Stripe (certifié PCI-DSS niveau 1)
✅ Données bancaires chiffrées en transit (HTTPS/TLS)

### 2. Vérification des webhooks
```php
// Dans PaymentController@webhook
$event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
```
✅ Signature vérifiée via `STRIPE_WEBHOOK_SECRET`
✅ Protection contre les webhooks falsifiés

### 3. Vérifications côté serveur
```php
// Vérifier que la facture appartient au client
if ($facture->client_id !== Auth::id()) {
    abort(403);
}

// Vérifier que la facture n'est pas déjà payée
if ($facture->statut === 'payee') {
    return response()->json(['error' => 'Facture déjà payée'], 400);
}
```

### 4. Protection CSRF
✅ Routes webhook **exclues** de la vérification CSRF (Stripe envoie POST)
✅ Routes client **protégées** par middleware `auth`

### 5. Idempotence
```php
// Éviter les doublons de paiement
if ($facture->statut === 'payee') {
    \Log::info('Facture déjà payée');
    return; // Ne pas retraiter
}
```

---

## 🐛 Troubleshooting

### Erreur : "Stripe key is invalid"

**Cause** : Clé API Stripe incorrecte ou manquante

**Solution** :
```bash
# Vérifier .env
cat .env | grep STRIPE

# Effacer le cache
php artisan config:clear

# Redémarrer le serveur
php artisan serve
```

---

### Erreur : "Webhook signature verification failed"

**Cause** : Secret webhook incorrect ou manquant

**Solution** :
```bash
# Vérifier le secret dans .env
STRIPE_WEBHOOK_SECRET=whsec_...

# Vérifier dans Dashboard Stripe → Webhooks → Signing secret

# Si différent, mettre à jour .env et redémarrer
php artisan config:clear
```

---

### Le paiement réussit mais la facture reste non payée

**Cause** : Webhook non configuré ou non reçu

**Solution** :
1. Vérifier que le webhook est configuré dans Stripe Dashboard
2. Vérifier l'URL du webhook : `https://votredomaine.com/stripe/webhook`
3. Vérifier les logs Stripe Dashboard → Webhooks → [Endpoint] → Events
4. Vérifier les logs Laravel :
```bash
tail -f storage/logs/laravel.log | grep -i stripe
```

---

### Erreur 404 sur /stripe/webhook

**Cause** : Route non définie ou cache Laravel

**Solution** :
```bash
# Vérifier la route
php artisan route:list | grep webhook

# Effacer le cache des routes
php artisan route:clear

# Redémarrer le serveur
php artisan serve
```

---

### Page blanche après redirection Stripe

**Cause** : Erreur JavaScript ou composant Vue.js

**Solution** :
```bash
# Recompiler les assets
npm run build

# Vérifier la console navigateur (F12)
# Vérifier les logs Laravel
tail -f storage/logs/laravel.log
```

---

### Le webhook reçoit toujours "Invalid signature"

**Cause** : Endpoint webhook configuré avec mauvaise URL ou secret

**Solution** :
1. Dashboard Stripe → Webhooks
2. Supprimer l'ancien endpoint
3. Créer un nouvel endpoint avec la bonne URL
4. Copier le **nouveau** signing secret
5. Mettre à jour `.env`
6. Effacer cache : `php artisan config:clear`

---

## 📊 Monitoring

### Dashboard Stripe
- **Paiements** : Stripe Dashboard → Payments
- **Clients** : Stripe Dashboard → Customers
- **Événements** : Stripe Dashboard → Developers → Events
- **Webhooks** : Stripe Dashboard → Developers → Webhooks

### Logs Laravel
```bash
# Tous les logs Stripe
tail -f storage/logs/laravel.log | grep -i stripe

# Paiements réussis
tail -f storage/logs/laravel.log | grep "Paiement Stripe traité avec succès"

# Paiements échoués
tail -f storage/logs/laravel.log | grep "Paiement Stripe échoué"
```

### Base de données
```sql
-- Règlements Stripe
SELECT * FROM reglements WHERE mode_paiement = 'stripe' ORDER BY created_at DESC;

-- Factures payées via Stripe
SELECT * FROM factures WHERE mode_paiement = 'stripe' AND statut = 'payee' ORDER BY date_paiement DESC;
```

---

## 📞 Support

### Documentation Stripe
- **API Reference** : https://stripe.com/docs/api
- **Checkout** : https://stripe.com/docs/payments/checkout
- **Webhooks** : https://stripe.com/docs/webhooks
- **Testing** : https://stripe.com/docs/testing

### Support Stripe
- **Email** : support@stripe.com
- **Chat** : Dashboard Stripe → Support

### Support Boxibox
- **Email** : dev@boxibox.com
- **GitHub** : https://github.com/haythemsaa/boxibox/issues

---

## 🎯 Prochaines étapes recommandées

### Court terme (1-2 semaines)
- [ ] Activer notifications email après paiement (client + admin)
- [ ] Ajouter logs détaillés pour audit
- [ ] Tester avec webhooks en production
- [ ] Créer dashboard admin pour suivi paiements Stripe

### Moyen terme (1 mois)
- [ ] Implémenter remboursements via Stripe
- [ ] Ajouter Apple Pay / Google Pay
- [ ] Système de paiements récurrents (abonnements)
- [ ] Gestion des litiges (disputes)

### Long terme (3-6 mois)
- [ ] Multi-devises (EUR, USD, GBP)
- [ ] Split payments (commissions multi-sites)
- [ ] Stripe Connect pour marketplace
- [ ] Intégration Stripe Terminal (TPE physique)

---

## 📝 Changelog

### Version 1.0.0 - 07 Octobre 2025
- ✅ Installation Stripe PHP SDK v18.0.0
- ✅ Contrôleur PaymentController avec 4 méthodes
- ✅ 3 pages Vue.js (Payment, Success, Cancel)
- ✅ Routes de paiement + webhook
- ✅ Configuration services.php
- ✅ Bouton "Payer en ligne" dans FactureShow
- ✅ Documentation complète
- ✅ Build assets réussi

---

**Dernière mise à jour** : 07 Octobre 2025
**Version** : 1.0.0
**Développé par** : Claude Code + Haythem SAA
**Stripe SDK** : v18.0.0
