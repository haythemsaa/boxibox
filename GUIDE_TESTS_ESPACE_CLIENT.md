# 📋 GUIDE DE TESTS - ESPACE CLIENT BOXIBOX

## 🎯 Objectif
Tester toutes les fonctionnalités de l'espace client avec 5 profils différents et documenter les bugs, améliorations possibles et fonctionnalités manquantes.

---

## 👥 COMPTES TESTEURS

### 1. **test.premium@boxibox.com** (Mot de passe: `test123`)
**Profil:** Client premium avec tout en règle
- ✅ Contrat actif
- ✅ 12 factures toutes payées
- ✅ Mandat SEPA valide
- ✅ 5 documents
- **À tester:** Navigation fluide, aucune alerte, tout fonctionne normalement

### 2. **test.retard@boxibox.com** (Mot de passe: `test123`)
**Profil:** Client avec retards de paiement
- ⚠️ Contrat actif
- ⚠️ 3 factures payées, 5 factures en retard
- ❌ Pas de mandat SEPA
- ⚠️ Relances envoyées
- **À tester:** Alertes, messages d'erreur, relances visibles

### 3. **test.nouveau@boxibox.com** (Mot de passe: `test123`)
**Profil:** Nouveau client (1 mois)
- ✅ Contrat récent
- ✅ 1 facture payée
- ⚠️ Pas encore de SEPA
- ✅ 3 documents
- **À tester:** Expérience nouveau client, setup SEPA

### 4. **test.mixte@boxibox.com** (Mot de passe: `test123`)
**Profil:** Situation mixte
- ✅ Contrat actif
- ⚠️ 6 factures payées, 2 en retard
- ✅ Mandat SEPA actif
- ⚠️ Quelques relances
- **À tester:** Cas réaliste, mélange de statuts

### 5. **test.complet@boxibox.com** (Mot de passe: `test123`)
**Profil:** Historique complet
- ✅ Contrat actif
- ⚠️ 8 factures payées, 3 en retard
- ✅ Mandat SEPA actif
- ✅ 6 documents variés
- **À tester:** Toutes les fonctionnalités, volume de données

---

## 🧪 PLAN DE TESTS PAR SECTION

### 1️⃣ **ACCUEIL / TABLEAU DE BORD**
**URL:** `/client/dashboard`

**Points à tester:**
- [ ] Les 4 tuiles de statistiques s'affichent correctement
- [ ] Les chiffres sont exacts (contrats actifs, factures impayées, montant dû, SEPA)
- [ ] La liste des 5 derniers contrats s'affiche
- [ ] La liste des 5 dernières factures s'affiche
- [ ] Les alertes apparaissent si factures impayées
- [ ] Les accès rapides fonctionnent
- [ ] Le design est responsive (mobile/tablette)

**Bugs potentiels à chercher:**
- Erreurs de calcul dans les statistiques
- Mauvais formatage des montants
- Liens cassés
- Problèmes d'affichage

---

### 2️⃣ **CONTRATS**
**URL:** `/client/contrats`

**Points à tester:**
- [ ] Liste de tous les contrats avec pagination
- [ ] Filtres fonctionnent (statut, recherche)
- [ ] Tri fonctionne (date, numéro, loyer)
- [ ] Bouton "Voir" ouvre la fiche détaillée
- [ ] Bouton "Télécharger PDF" génère un PDF
- [ ] Les badges de statut sont corrects
- [ ] Les dates sont bien formatées
- [ ] Les montants sont corrects

**Fiche détaillée du contrat:**
- [ ] Toutes les informations s'affichent
- [ ] Détails du box (numéro, surface, famille)
- [ ] Liste des factures liées au contrat
- [ ] Bouton retour fonctionne

---

### 3️⃣ **MANDATS SEPA**
**URL:** `/client/sepa`

**Points à tester:**
- [ ] Liste des mandats existants
- [ ] Statut du mandat (valide/en attente)
- [ ] Bouton "Créer un mandat" visible si pas de mandat actif
- [ ] Bouton masqué si mandat actif existe
- [ ] Formulaire de création s'affiche
- [ ] Validation IBAN fonctionne (JavaScript)
- [ ] Validation BIC fonctionne
- [ ] Checkbox consentement obligatoire
- [ ] Soumission du formulaire fonctionne
- [ ] Messages de succès/erreur s'affichent
- [ ] Informations légales SEPA présentes

---

### 4️⃣ **INFORMATIONS (PROFIL)**
**URL:** `/client/profil`

**Points à tester:**
- [ ] Toutes les informations personnelles s'affichent
- [ ] Formulaire est éditable
- [ ] Validation email fonctionne
- [ ] Validation téléphone fonctionne
- [ ] Bouton "Enregistrer" sauvegarde les modifications
- [ ] Message de confirmation s'affiche
- [ ] Sidebar avec N° client, date inscription
- [ ] Carte sécurité (changement mot de passe désactivé)
- [ ] Carte contact BOXIBOX

**Bugs potentiels:**
- Champs non modifiables qui devraient l'être
- Validation trop stricte ou absente
- Problèmes de sauvegarde

---

### 5️⃣ **FACTURES & AVOIRS**
**URL:** `/client/factures`

**Points à tester:**
- [ ] 4 stats cards affichent les bons chiffres
- [ ] Liste des factures avec pagination
- [ ] Filtres fonctionnent (type, statut, dates)
- [ ] Badges de statut corrects (payée/en retard/envoyée)
- [ ] Montants HT, TVA, TTC corrects
- [ ] Dates d'échéance visibles
- [ ] Indicateur de retard si applicable
- [ ] Bouton "Voir" ouvre la fiche
- [ ] Bouton "Télécharger PDF" génère le PDF

**Fiche détaillée facture:**
- [ ] Numéro, dates, montants
- [ ] Détails contrat/box
- [ ] Liste des règlements si payée
- [ ] Calcul "Reste à payer" si règlement partiel
- [ ] Bouton télécharger PDF

---

### 6️⃣ **RÈGLEMENTS**
**URL:** `/client/reglements`

**Points à tester:**
- [ ] 4 stats cards (total, montant total, mois, moyen)
- [ ] Historique des paiements
- [ ] Icônes mode de paiement corrects
- [ ] Filtres par date fonctionnent
- [ ] Filtres par mode de paiement
- [ ] Statut des règlements (badge)
- [ ] Lien vers facture liée fonctionne
- [ ] Info box modes acceptés

**Modes de paiement à vérifier:**
- [ ] Virement (icône exchange-alt)
- [ ] Chèque (icône money-check)
- [ ] Carte bancaire (icône credit-card)
- [ ] Prélèvement SEPA (icône university)

---

### 7️⃣ **RELANCES**
**URL:** `/client/relances`

**Points à tester:**
- [ ] Historique des relances
- [ ] Badge type relance (1ère/2ème/mise en demeure)
- [ ] Couleurs badges (bleu/orange/rouge)
- [ ] Icône mode d'envoi (email/courrier)
- [ ] Montant dû affiché
- [ ] Lien vers facture concernée
- [ ] Info box pour éviter relances (SEPA)
- [ ] Message si aucune relance

---

### 8️⃣ **FICHIERS**
**URL:** `/client/documents`

**Points à tester:**
- [ ] Zone drag-and-drop fonctionne
- [ ] Validation PDF uniquement (JavaScript)
- [ ] Validation 20 Mo maximum
- [ ] Aperçu du fichier avant envoi
- [ ] Upload réussit
- [ ] Message de succès
- [ ] Liste des documents s'affiche
- [ ] Icône type de fichier
- [ ] Taille formatée (KB/MB)
- [ ] Badge "Vous" ou "BOXIBOX"
- [ ] Bouton télécharger fonctionne
- [ ] Bouton supprimer fonctionne (ses propres docs)
- [ ] Impossibilité de supprimer docs BOXIBOX

**Bugs potentiels:**
- Upload échoue silencieusement
- Mauvaise validation fichiers
- Problèmes de permissions

---

### 9️⃣ **SUIVI**
**URL:** `/client/suivi`

**Points à tester:**
- [ ] Timeline verticale s'affiche
- [ ] Événements de tous types présents
- [ ] Ordre chronologique (plus récent en haut)
- [ ] Badges colorés par type
- [ ] Détails additionnels visibles
- [ ] Liens vers documents fonctionnent
- [ ] Filtres par type d'événement
- [ ] Filtres par date
- [ ] Animation hover
- [ ] Légende avec badges

**Types d'événements à vérifier:**
- [ ] Contrats (création, signature)
- [ ] Factures (émission, paiement)
- [ ] Règlements (validation)
- [ ] Relances (envoi)
- [ ] Documents (upload, partage)
- [ ] SEPA (signature mandat)

---

## 🐛 TEMPLATE RAPPORT DE BUGS

Pour chaque bug trouvé, documenter:

```markdown
### BUG #[numéro]

**Page:** [URL ou nom de la page]
**Compte testeur:** [email du compte]
**Sévérité:** [Critique / Majeure / Mineure / Cosmétique]

**Description:**
[Description claire du bug]

**Étapes pour reproduire:**
1. [Étape 1]
2. [Étape 2]
3. [...]

**Résultat attendu:**
[Ce qui devrait se passer]

**Résultat obtenu:**
[Ce qui se passe réellement]

**Screenshot:** [Si applicable]

**Erreur console/logs:** [Si applicable]
```

---

## ✨ TEMPLATE SUGGESTIONS D'AMÉLIORATION

```markdown
### AMÉLIORATION #[numéro]

**Page:** [URL ou nom de la page]
**Priorité:** [Haute / Moyenne / Basse]

**Description:**
[Description de l'amélioration proposée]

**Bénéfice:**
[Pourquoi cette amélioration est utile]

**Effort estimé:**
[Simple / Moyen / Complexe]
```

---

## 📊 CHECKLIST FINALE

### Tests généraux
- [ ] Navigation entre toutes les pages sans erreur
- [ ] Bouton déconnexion fonctionne
- [ ] Menu responsive (mobile/tablette/desktop)
- [ ] Pas d'erreurs JavaScript dans la console
- [ ] Temps de chargement acceptable
- [ ] Messages de succès/erreur s'affichent correctement

### Sécurité
- [ ] Impossible d'accéder aux données d'autres clients
- [ ] Validation côté serveur fonctionne
- [ ] CSRF protection active
- [ ] Sessions sécurisées

### UX/UI
- [ ] Design cohérent sur toutes les pages
- [ ] Couleurs et badges lisibles
- [ ] Formulaires intuitifs
- [ ] Messages d'aide présents
- [ ] Boutons clairement identifiables

---

## 📝 NOTES IMPORTANTES

1. **Tester avec TOUS les 5 comptes** - Chaque profil a des données différentes
2. **Noter les petits détails** - Même les problèmes cosmétiques
3. **Tester sur différents navigateurs** si possible (Chrome, Firefox, Edge)
4. **Tester en responsive** - Réduire la fenêtre du navigateur
5. **Documenter TOUT** - Bugs, suggestions, questions

---

**Bon courage pour les tests! 🚀**
