# 🔑 IDENTIFIANTS DE CONNEXION - TESTS ESPACE CLIENT

## 🌐 URL de l'application
```
http://127.0.0.1:8000
```

---

## 👥 COMPTES TESTEURS (Mot de passe: `test123`)

### 1. Client Premium ✨
```
Email: test.premium@boxibox.com
Mot de passe: test123
```
**Profil:** Client exemplaire
- 12 factures toutes payées
- Mandat SEPA valide
- 5 documents
- Aucune relance

---

### 2. Client en Retard ⚠️
```
Email: test.retard@boxibox.com
Mot de passe: test123
```
**Profil:** Client avec difficultés de paiement
- 3 factures payées
- 5 factures en retard
- Pas de mandat SEPA
- Plusieurs relances envoyées
- 2 documents

---

### 3. Nouveau Client 🆕
```
Email: test.nouveau@boxibox.com
Mot de passe: test123
```
**Profil:** Client récent (1 mois)
- 1 facture payée
- Contrat tout récent
- Pas encore de SEPA
- 3 documents
- Aucune relance

---

### 4. Client Mixte 🔄
```
Email: test.mixte@boxibox.com
Mot de passe: test123
```
**Profil:** Situation réaliste mixte
- 6 factures payées
- 2 factures en retard
- Mandat SEPA actif
- Quelques relances
- 4 documents

---

### 5. Client Complet 📊
```
Email: test.complet@boxibox.com
Mot de passe: test123
```
**Profil:** Historique complet pour tests exhaustifs
- 8 factures payées
- 3 factures en retard
- Mandat SEPA actif
- Relances variées
- 6 documents de types variés

---

## 👤 COMPTES CLIENTS DÉMO ORIGINAUX (Mot de passe: `password`)

### Client Démo 1
```
Email: client1@demo.com
Mot de passe: password
```

### Client Démo 2
```
Email: client2@demo.com
Mot de passe: password
```

### Client Démo 3
```
Email: client3@demo.com
Mot de passe: password
```

---

## 🔧 COMPTE ADMINISTRATEUR

### Admin Principal
```
Email: admin@boxibox.com
Mot de passe: admin123
```
OU
```
Mot de passe: password
```

---

## 📝 NOTES IMPORTANTES

### Différences entre les mots de passe
- **Testeurs:** `test123`
- **Clients démo:** `password`
- **Admin:** `admin123` ou `password`

### Recommandations de test
1. **Commencer par `test.premium@boxibox.com`** - Tout fonctionne normalement
2. **Tester `test.retard@boxibox.com`** - Vérifier les alertes et relances
3. **Tester `test.nouveau@boxibox.com`** - Expérience nouveau client
4. **Tester `test.mixte@boxibox.com`** - Cas réaliste
5. **Finir avec `test.complet@boxibox.com`** - Tests exhaustifs

### Réinitialisation
Si vous voulez recréer les testeurs:
```bash
php artisan db:seed --class=TestUsersSeeder
```

---

## 🧪 SCÉNARIOS DE TEST PAR COMPTE

### test.premium@boxibox.com - À tester:
- [ ] Dashboard sans alertes
- [ ] Toutes factures au statut "Payée"
- [ ] Mandat SEPA visible et valide
- [ ] Historique complet des règlements
- [ ] Aucune relance dans la liste
- [ ] 5 documents accessibles
- [ ] Timeline riche avec tous types d'événements

### test.retard@boxibox.com - À tester:
- [ ] Alertes de factures impayées sur dashboard
- [ ] Badge rouge "En retard" sur factures
- [ ] Message encourageant création mandat SEPA
- [ ] Liste des relances visible et complète
- [ ] Montant dû élevé affiché
- [ ] 2 documents seulement
- [ ] Timeline montrant les relances

### test.nouveau@boxibox.com - À tester:
- [ ] Dashboard minimaliste (peu de données)
- [ ] 1 seule facture payée
- [ ] Contrat récent (1 mois)
- [ ] Message invitant à créer mandat SEPA
- [ ] Aucune relance
- [ ] 3 documents
- [ ] Timeline courte

### test.mixte@boxibox.com - À tester:
- [ ] Alertes pour 2 factures impayées
- [ ] Mix de badges verts/rouges sur factures
- [ ] Mandat SEPA actif malgré retards
- [ ] Quelques relances visibles
- [ ] 4 documents
- [ ] Timeline variée

### test.complet@boxibox.com - À tester:
- [ ] Dashboard avec toutes les stats significatives
- [ ] Liste longue de factures (11 total)
- [ ] Pagination si applicable
- [ ] Tous types de badges
- [ ] Mandat SEPA + relances (contradiction à vérifier)
- [ ] 6 documents de types variés
- [ ] Timeline très riche avec filtres utiles

---

## 🔐 SÉCURITÉ - TESTS CROISÉS

**Test important:** Essayer d'accéder aux données d'un autre client

1. Se connecter avec `test.premium@boxibox.com`
2. Noter l'ID d'un contrat/facture
3. Se déconnecter
4. Se connecter avec `test.retard@boxibox.com`
5. Essayer d'accéder à l'URL du contrat/facture de premium
6. **Résultat attendu:** Erreur 403 ou 404

---

## 📞 EN CAS DE PROBLÈME

### Connexion impossible
1. Vérifier que le serveur tourne: `php artisan serve`
2. Vérifier l'URL: `http://127.0.0.1:8000`
3. Essayer en navigation privée
4. Vérifier les logs: `storage/logs/laravel.log`

### Données manquantes
1. Vérifier que le seeder a tourné: `php artisan db:seed --class=TestUsersSeeder`
2. Vérifier la base de données

### Erreur 403
1. Vérifier que l'utilisateur a le rôle "Client"
2. Vérifier que `is_active = 1`
3. Se déconnecter complètement et reconnecter

---

**Bonne chance pour les tests! 🚀**
