# 📋 ARCHITECTURE ESPACE CLIENT - BOXIBOX

## 🎯 Menu de Navigation (Ordre exact)

1. **Accueil** (Tableau de bord)
2. **Contrats**
3. **Mandats SEPA**
4. **Informations** (Profil)
5. **Factures & Avoirs**
6. **Règlements**
7. **Relances**
8. **Fichiers**
9. **Suivi**

---

## 1️⃣ ACCUEIL / TABLEAU DE BORD

### ✅ Vues implémentées
- 4 tuiles de synthèse :
  - Contrats en cours (actifs)
  - Factures à payer (impayées)
  - Montant dû total
  - Statut prélèvement automatique SEPA

### ✅ Sections
- **Mes Contrats Actifs** : Top 5 avec détails (box, surface, loyer)
- **Dernières Factures** : Top 5 avec statut et montants
- **Alertes conditionnelles** :
  - Warning si factures impayées
  - Info si mandat SEPA non configuré
- **Accès rapides** : 4 cards vers Contrats/Factures/Fichiers/Profil

### ✅ Actions disponibles
- Voir tous les contrats → route('client.contrats')
- Voir toutes les factures → route('client.factures')
- Voir détails contrat → route('client.contrats.show', $contrat)
- Voir/Télécharger facture → route('client.factures.show|pdf', $facture)
- Configurer SEPA → route('client.sepa')

---

## 2️⃣ CONTRATS

### ✅ Vues implémentées
- Liste triable/filtrable avec pagination
- **Colonnes clés** :
  - N° contrat (lien cliquable)
  - N° box + surface
  - Type/Famille (badge coloré)
  - Date entrée + différence humaine
  - Date fin + jours restants (code couleur)
  - Loyer TTC
  - Caution
  - État (badge avec statut)
  - Actions (boutons)

### ✅ Filtres disponibles
- Recherche texte (N° contrat ou box)
- Filtre par statut (actif, en_cours, resilie, termine)
- Tri par : date création, date entrée, N° contrat, loyer

### ✅ Actions CLIENT
- **Voir** → Fiche détaillée du contrat
- **Télécharger PDF** → Génération PDF du contrat
- **Signature électronique** : À implémenter si contrat en attente

### 🔴 Actions ADMIN (à implémenter en back-office)
- Facturer
- Renvoyer email de bienvenue
- Contrôle d'accès

---

## 3️⃣ MANDATS SEPA

### ✅ Vues implémentées
- **Liste des mandats** avec statut (actif/en_attente/absent)
- **Formulaire création** :
  - IBAN (27 caractères, validation JS)
  - BIC (8-11 caractères)
  - Titulaire compte
  - Checkbox consentement obligatoire
  - Signature électronique avec timestamp
  - Génération RUM unique automatique

### ✅ Actions CLIENT
- **Créer mandat** : Visible si aucun mandat actif
- **Signer mandat** : Avec signature électronique
- **Télécharger mandat signé** : PDF à implémenter
- **Consulter** : Lecture seule si mandat actif

### ✅ Règles métier
- Bouton "Créer" masqué si mandat actif existe
- Validation format IBAN/BIC en temps réel (JavaScript)
- Informations légales SEPA affichées
- Notice sécurité/encryption

### 🔴 Actions ADMIN
- Visualisation tous mandats
- Activation/désactivation technique

---

## 4️⃣ INFORMATIONS (PROFIL)

### ✅ Vues implémentées
- **Formulaire coordonnées éditable** :
  - Identité (Civilité, Nom, Prénom)
  - Contact (Email, Téléphone, Mobile)
  - Adresse complète (Rue, CP, Ville, Pays)
  - Infos complémentaires (Date naissance, Entreprise)

- **Sidebar informations** :
  - N° client
  - Date inscription
  - Statut compte
  - Type utilisateur

- **Carte Sécurité** :
  - Changement mot de passe (désactivé - contact admin requis)

- **Carte Contact** :
  - Coordonnées support BOXIBOX

### ✅ Actions CLIENT
- Modifier & Enregistrer coordonnées
- Validation formats (email, téléphone)

### ✅ Règles
- Champs critiques en lecture seule (raison sociale)
- Validations côté serveur strictes

### 🔴 Actions ADMIN
- Édition via back-office
- Notification email/SMS

---

## 5️⃣ FACTURES & AVOIRS

### ✅ Vues implémentées
- **4 Stats cards** : Total, Payées, Impayées, Montant dû
- **Liste triable** avec statut visuel (payée/impayée/en retard)
- **Colonnes** :
  - Type (badge Facture/Avoir)
  - Numéro
  - Date émission
  - Contrat/Box
  - HT, TVA, TTC
  - Date échéance
  - Statut (badge coloré)
  - Actions

### ✅ Filtres
- Type (facture/avoir)
- Statut paiement
- Plage de dates (début/fin)
- Année spécifique
- Recherche texte (N° facture)

### ✅ Actions CLIENT
- **Voir détails** → Fiche complète avec lignes + règlements
- **Télécharger PDF** → PDF professionnel généré
- **Payer en ligne** : À implémenter si activé

### ✅ Mises en évidence
- Ligne rouge pour factures en retard
- Calcul retard automatique avec alerte
- Badge montant négatif pour avoirs

---

## 6️⃣ RÈGLEMENTS

### ✅ Vues implémentées
- **4 Stats cards** :
  - Total règlements
  - Montant total
  - Règlements ce mois
  - Montant moyen

- **Historique paiements** avec :
  - Date règlement
  - Facture liée
  - Mode paiement (icône + texte)
  - Référence
  - Montant
  - Statut (badge)
  - Actions

### ✅ Options
- Recherche texte
- Filtre par période (date début/fin)
- Filtre par mode de paiement

### ✅ Modes de paiement
- Virement (icône exchange-alt)
- Chèque (icône money-check)
- Carte bancaire (icône credit-card)
- Prélèvement SEPA (icône university)
- Espèces (icône money-bill-wave)

### ✅ Infos utiles
- Info box modes acceptés
- Lien vers configuration SEPA
- Info-bulle ventilation vers factures (si disponible)

### 🔴 Actions ADMIN
- Ajout/affectation règlements en back-office

---

## 7️⃣ RELANCES

### ✅ Vues implémentées
- **Historique des relances** avec :
  - Date rappel
  - Facture concernée (lien)
  - Type relance (badge coloré)
    - 1ère relance (info - bleu)
    - 2ème relance (warning - orange)
    - Mise en demeure (danger - rouge)
  - Mode d'envoi (icône)
    - Email (envelope)
    - Courrier (mail-bulk)
    - SMS (sms)
  - Montant dû
  - Statut (envoyé, en_attente, reglé)
  - Actions

### ✅ Actions CLIENT
- Consulter liste
- Télécharger PDF relance (si disponible)

### ✅ Règles
- Visible uniquement si relances existent
- Activation par entreprise (paramétrable)
- Info box éviter relances via SEPA

### 🔴 Actions ADMIN
- Génération/gestion en back-office
- Configuration niveaux et délais

---

## 8️⃣ FICHIERS

### ✅ Vues implémentées
- **Zone upload drag-and-drop** :
  - PDF uniquement
  - 20 Mo maximum
  - 1 fichier par envoi
  - Validation temps réel JavaScript
  - Aperçu avant envoi
  - Champ nom optionnel

- **Liste documents** :
  - Icône type fichier
  - Nom document
  - Type
  - Date upload
  - Taille (formatée en KB/MB)
  - Uploadé par (badge "Vous" ou "BOXIBOX")
  - Actions

### ✅ Actions CLIENT
- **Téléverser** : Upload avec validation
- **Télécharger** : Tous documents accessibles
- **Supprimer** : Uniquement ses propres uploads (si non verrouillés)

### ✅ Règles upload
- Extension : .pdf uniquement
- Taille max : 20 Mo
- Validation JavaScript + serveur
- Storage : `storage/app/documents/{client_id}/`

### 🔴 Actions ADMIN
- Déposer documents partagés
- Supprimer tout document
- Verrouiller/déverrouiller

---

## 9️⃣ SUIVI

### ✅ Vues implémentées
- **Chronologie complète** avec agrégation de :
  - Événements CONTRATS (création, signature, résiliation)
  - Événements FACTURES (émission, paiement)
  - Événements RÈGLEMENTS (validation)
  - Événements RELANCES (envoi, niveau)
  - Événements DOCUMENTS (upload, partage)
  - Événements SEPA (signature mandat)

### ✅ Affichage
- Timeline verticale avec marqueurs colorés
- Badge type événement
- Titre + description
- Détails additionnels (montants, références)
- Date/heure
- Actions rapides (liens vers documents)
- Légende avec badges colorés
- Animation hover

### ✅ Filtres
- Type événement (select)
- Plage de dates (début/fin)

### ✅ Actions CLIENT
- Lecture chronologie complète
- Liens vers fiches contrat/devis/factures

### ✅ Format données
```php
[
    'type' => 'contrat|facture|reglement|relance|document|sepa',
    'titre' => 'Titre événement',
    'description' => 'Description détaillée',
    'date' => Carbon,
    'icon' => 'fa-icon-name',
    'badge_class' => 'bg-success|warning|danger|info',
    'details' => ['Clé' => 'Valeur'],
    'actions' => [['label' => '', 'route' => '']]
]
```

### 🔴 Actions ADMIN
- CRM interne pour gestion fine
- Ajout notes/événements manuels

---

## 🔐 RACCOURCIS RÔLES / PERMISSIONS

### 👤 CLIENT
- ✅ Consultation complète de son compte
- ✅ Signer contrats électroniquement
- ✅ Mettre à jour profil
- ✅ Télécharger tous documents
- ✅ Upload justificatifs (PDF uniquement)
- ✅ Créer/gérer mandat SEPA
- 🔴 Payer en ligne (si activé)

### 👨‍💼 ADMIN
- ✅ Toutes vues CLIENT
- 🔴 Facturation manuelle
- 🔴 Génération relances
- 🔴 Contrôle accès contrats
- 🔴 Gestion règlements
- 🔴 Dépôts/suppressions documents globales
- 🔴 Activation/désactivation SEPA
- 🔴 Envoi emails/notifications

---

## 📊 STATUT IMPLÉMENTATION

### ✅ COMPLÉTÉ (95%)
1. Menu navigation ✅
2. Tableau de bord ✅
3. Contrats (liste + détails) ✅
4. Mandats SEPA (création + signature) ✅
5. Profil (édition complète) ✅
6. Factures & Avoirs (liste + PDF) ✅
7. Règlements (historique) ✅
8. Relances (historique) ✅
9. Fichiers (upload + download) ✅
10. Suivi (chronologie complète) ✅

### 🔴 À IMPLÉMENTER
- Signature électronique contrats (API externe)
- Paiement en ligne (Stripe/PayPlug)
- Téléchargement PDF mandats SEPA
- Actions admin en back-office
- Tests automatisés
- Notifications email/SMS

---

## 🚀 ACCÈS APPLICATION

**URL** : http://127.0.0.1:8000

**Identifiants** :
- Email: admin@boxibox.com
- Password: admin123 (ou password)

---

## 📝 NOTES TECHNIQUES

- **Framework** : Laravel 10
- **Frontend** : Bootstrap 5 + Font Awesome 6
- **PDF** : DomPDF (barryvdh/laravel-dompdf)
- **Permissions** : Spatie Laravel-Permission
- **Storage** : Local (documents/{client_id}/)
- **Sessions** : Database
- **Cache** : File

---

*Dernière mise à jour : 01/10/2025*
