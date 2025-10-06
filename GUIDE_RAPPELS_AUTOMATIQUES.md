# 📧 GUIDE - RAPPELS AUTOMATIQUES DE PAIEMENT

## 🎯 Vue d'Ensemble

Système automatisé d'envoi de rappels de paiement pour les factures impayées, avec escalade progressive sur 3 niveaux.

**Créé le**: 02/10/2025
**Commande**: `rappels:send-automatic`
**Planification**: Quotidienne à 09h00

---

## 📋 FONCTIONNEMENT

### Règles d'Escalade

Le système envoie automatiquement des rappels selon les délais suivants après la date d'échéance :

| Niveau | Délai | Type | Couleur | Ton |
|--------|-------|------|---------|-----|
| 1️⃣ | 7 jours | Rappel amical | 🔵 Bleu | Courtois |
| 2️⃣ | 15 jours | Relance importante | 🟠 Orange | Ferme |
| 3️⃣ | 30 jours | Mise en demeure | 🔴 Rouge | Juridique |

### Logique de Déclenchement

```
Facture échue le 01/10/2025
└─ 08/10/2025 : Rappel Niveau 1 (7 jours)
└─ 16/10/2025 : Rappel Niveau 2 (15 jours)
└─ 31/10/2025 : Rappel Niveau 3 (30 jours)
```

---

## 🚀 UTILISATION

### 1. Mode Simulation (Dry Run)

**Recommandé pour tester sans envoyer d'emails**

```bash
php artisan rappels:send-automatic --dry-run
```

**Résultat**: Affiche ce qui serait envoyé sans envoyer réellement.

**Exemple de sortie**:
```
🚀 Démarrage de l'envoi automatique des rappels...

⚠️  MODE SIMULATION - Aucun email ne sera envoyé

📧 Traitement des rappels de niveau 1 (7+ jours de retard)...
   Trouvé: 3 facture(s) éligible(s)
   [SIMULATION] Rappel niveau 1 pour client1@demo.com (Facture: FAC-2025-001)
   [SIMULATION] Rappel niveau 1 pour client2@demo.com (Facture: FAC-2025-002)
   [SIMULATION] Rappel niveau 1 pour client3@demo.com (Facture: FAC-2025-003)
   Résultat: 3 rappel(s) simulé(s)

📧 Traitement des rappels de niveau 2 (15+ jours de retard)...
   Trouvé: 1 facture(s) éligible(s)
   [SIMULATION] Rappel niveau 2 pour client4@demo.com (Facture: FAC-2025-004)
   Résultat: 1 rappel(s) simulé(s)

═══════════════════════════════════════════════════
📊 RÉSUMÉ
═══════════════════════════════════════════════════
✅ Total rappels simulés: 4

💡 Pour envoyer réellement, relancez sans --dry-run

✨ Terminé!
```

### 2. Envoi Réel

**Envoie les emails réellement**

```bash
php artisan rappels:send-automatic
```

### 3. Envoi par Niveau Spécifique

**Envoyer uniquement les rappels de niveau 1**
```bash
php artisan rappels:send-automatic --niveau=1
```

**Envoyer uniquement les mises en demeure (niveau 3)**
```bash
php artisan rappels:send-automatic --niveau=3 --dry-run
```

### 4. Forcer l'Envoi

**Envoyer même si déjà envoyé aujourd'hui**
```bash
php artisan rappels:send-automatic --force
```

⚠️ **Attention**: Utiliser avec précaution pour éviter les doublons.

---

## 🤖 AUTOMATISATION

### Planification Laravel

La commande est automatiquement planifiée dans `app/Console/Kernel.php` :

```php
$schedule->command('rappels:send-automatic')
         ->dailyAt('09:00')
         ->withoutOverlapping()
         ->onSuccess(function () {
             \Log::info('Relances automatiques traitées avec succès');
         })
         ->onFailure(function () {
             \Log::error('Échec du traitement des relances automatiques');
         });
```

### Activation du Scheduler

**Sur serveur Linux/Mac** (via Cron):
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Sur Windows** (via Planificateur de tâches):
1. Ouvrir "Planificateur de tâches"
2. Créer une tâche de base
3. Déclencheur: Quotidien à 09h00
4. Action: Lancer un programme
   - Programme: `C:\xampp\php\php.exe`
   - Arguments: `artisan schedule:run`
   - Dossier: `C:\xampp2025\htdocs\boxibox`

**En développement** (Commande manuelle):
```bash
php artisan schedule:work
```

---

## 🔍 LOGS ET MONITORING

### Logs Laravel

Tous les envois sont loggés dans `storage/logs/laravel.log`:

**Succès**:
```
[2025-10-02 09:00:15] local.INFO: Relances automatiques traitées avec succès
```

**Erreur**:
```
[2025-10-02 09:00:15] local.ERROR: Erreur envoi rappel
{
  "facture_id": 123,
  "client_email": "client@example.com",
  "error": "Connection timeout"
}
```

### Vérifier les Rappels Envoyés

**Via base de données**:
```sql
SELECT
    r.id,
    r.niveau,
    r.date_envoi,
    r.statut,
    f.numero_facture,
    c.email
FROM rappels r
JOIN factures f ON r.facture_id = f.id
JOIN clients c ON r.client_id = c.id
WHERE DATE(r.date_envoi) = CURDATE()
ORDER BY r.date_envoi DESC;
```

**Via Tinker**:
```bash
php artisan tinker
>>> \App\Models\Rappel::whereDate('date_envoi', today())->count()
>>> \App\Models\Rappel::where('statut', 'echec')->get()
```

---

## 📧 CONTENU DES EMAILS

### Email Niveau 1 (Rappel Amical)

**Sujet**: `Rappel de paiement - BOXIBOX`

**Contenu**:
- Ton courtois et bienveillant
- Mention "simple oubli"
- Détails de la facture
- Bouton "Régler maintenant"
- Suggestion SEPA pour éviter les oublis

**Couleur**: Bleu (#0d6efd)

---

### Email Niveau 2 (Relance Importante)

**Sujet**: `Relance 2ème niveau - Facture impayée`

**Contenu**:
- Ton plus ferme
- Mention du premier rappel ignoré
- Demande de régularisation rapide
- Calcul jours de retard
- Bouton "Régler maintenant"

**Couleur**: Orange (#fd7e14)

---

### Email Niveau 3 (Mise en Demeure)

**Sujet**: `URGENT - Mise en demeure de payer`

**Contenu**:
- Ton juridique et formel
- Mention "MISE EN DEMEURE"
- Délai de 8 jours impératif
- Conséquences détaillées:
  - Procédure contentieuse
  - Frais de recouvrement
  - Pénalités de retard
  - Suspension du contrat
- Bouton "Régler maintenant"

**Couleur**: Rouge (#dc3545)

---

## ⚙️ CONFIGURATION

### Modifier les Délais

Dans `app/Console/Commands/SendAutomaticReminders.php`:

```php
$delais = [
    1 => 7,   // 1er rappel: 7 jours
    2 => 15,  // 2ème rappel: 15 jours
    3 => 30,  // Mise en demeure: 30 jours
];
```

**Exemple - Configuration plus agressive**:
```php
$delais = [
    1 => 3,   // 1er rappel: 3 jours
    2 => 10,  // 2ème rappel: 10 jours
    3 => 20,  // Mise en demeure: 20 jours
];
```

### Configuration SMTP

Dans `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=comptabilite@boxibox.com
MAIL_PASSWORD=votre-mot-de-passe-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@boxibox.com
MAIL_FROM_NAME="BOXIBOX Comptabilité"
```

---

## 🧪 TESTS

### Test Complet en Simulation

```bash
# 1. Simuler l'envoi
php artisan rappels:send-automatic --dry-run

# 2. Vérifier qu'aucun email n'a été envoyé
php artisan tinker
>>> \App\Models\Rappel::whereDate('created_at', today())->count()

# 3. Vérifier les statuts
>>> \App\Models\Rappel::whereDate('created_at', today())->pluck('statut')
```

### Test d'Envoi Réel (Niveau 1 uniquement)

```bash
# 1. Mode simulation niveau 1
php artisan rappels:send-automatic --niveau=1 --dry-run

# 2. Si OK, envoyer réellement
php artisan rappels:send-automatic --niveau=1

# 3. Vérifier dans Mailtrap ou boîte email
```

### Test avec Données de Test

```bash
# 1. Se connecter avec test.retard@boxibox.com
# 2. Vérifier les factures en retard dans l'espace client
# 3. Lancer la commande
php artisan rappels:send-automatic --dry-run

# 4. Vérifier l'email reçu
```

---

## 🚨 DÉPANNAGE

### Problème : Aucun Email Envoyé

**Vérifications**:
1. Configuration SMTP correcte dans `.env`
2. Queue worker actif: `php artisan queue:work`
3. Factures avec statut `en_retard` existent
4. Dates d'échéance correctes

**Test SMTP**:
```bash
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); })
```

### Problème : Doublon d'Emails

**Cause**: Commande lancée plusieurs fois dans la journée

**Solution**:
- Utiliser `--force` uniquement si nécessaire
- Vérifier que `withoutOverlapping()` est activé dans le scheduler
- Consulter les rappels existants:
```sql
SELECT * FROM rappels
WHERE facture_id = 123
  AND niveau = 1
  AND DATE(date_rappel) = CURDATE();
```

### Problème : Erreurs d'Envoi

**Consulter les logs**:
```bash
tail -f storage/logs/laravel.log | grep "Erreur envoi rappel"
```

**Vérifier les rappels en échec**:
```sql
SELECT * FROM rappels WHERE statut = 'echec' ORDER BY date_rappel DESC;
```

---

## 📊 MÉTRIQUES

### Statistiques d'Envoi

```sql
-- Rappels envoyés aujourd'hui
SELECT COUNT(*) as total, niveau
FROM rappels
WHERE DATE(date_envoi) = CURDATE()
GROUP BY niveau;

-- Taux de succès
SELECT
    COUNT(*) as total,
    SUM(CASE WHEN statut = 'envoye' THEN 1 ELSE 0 END) as succes,
    SUM(CASE WHEN statut = 'echec' THEN 1 ELSE 0 END) as echecs,
    ROUND(SUM(CASE WHEN statut = 'envoye' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as taux_succes
FROM rappels
WHERE DATE(date_envoi) = CURDATE();

-- Montants en recouvrement
SELECT
    niveau,
    COUNT(*) as nb_rappels,
    SUM(montant) as montant_total
FROM rappels
WHERE statut = 'envoye'
  AND MONTH(date_envoi) = MONTH(CURDATE())
GROUP BY niveau;
```

---

## ✅ CHECKLIST PRODUCTION

### Avant Mise en Production

- [ ] Tester en `--dry-run` sur données réelles
- [ ] Configurer SMTP production (non Gmail)
- [ ] Vérifier templates emails (logos, coordonnées)
- [ ] Activer queue worker avec Supervisor
- [ ] Configurer Cron pour `schedule:run`
- [ ] Mettre en place monitoring (Sentry, Bugsnag)
- [ ] Tester escalade complète (niveau 1 → 2 → 3)
- [ ] Documenter procédure pour équipe support
- [ ] Préparer réponses types pour clients

### Le Premier Jour

- [ ] Lancer manuellement à 09h00 en mode `--dry-run`
- [ ] Vérifier la sortie console
- [ ] Si OK, lancer en mode réel
- [ ] Monitorer les logs pendant 1h
- [ ] Vérifier quelques emails reçus
- [ ] Consulter les stats en BDD

---

## 🎯 BONNES PRATIQUES

### 1. Toujours Tester en Dry-Run D'Abord
```bash
php artisan rappels:send-automatic --dry-run
```

### 2. Commencer par un Niveau
```bash
php artisan rappels:send-automatic --niveau=1
```

### 3. Surveiller les Logs
```bash
tail -f storage/logs/laravel.log
```

### 4. Utiliser Mailtrap en Développement
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
```

### 5. Rate Limiting pour Gros Volumes
Si >1000 clients, ajouter dans `SendAutomaticReminders.php`:
```php
foreach ($factures as $facture) {
    // ... code existant ...

    sleep(0.5); // 0.5 seconde entre chaque email
}
```

---

## 📝 FEUILLE DE ROUTE

### Phase 1 - Actuel ✅
- Envoi automatique 3 niveaux
- Logs et monitoring
- Mode simulation
- Planification quotidienne

### Phase 2 - Court Terme
- [ ] Dashboard métriques rappels
- [ ] Webhooks pour tracking ouverture emails
- [ ] Templates personnalisables par entreprise
- [ ] Notification Slack pour niveau 3

### Phase 3 - Moyen Terme
- [ ] Envoi SMS pour niveau 3
- [ ] Rappels téléphoniques automatiques
- [ ] Export liste recouvrement contentieux
- [ ] Intégration CRM recouvrement

---

## 📞 SUPPORT

En cas de problème avec le système de rappels automatiques:

**Email**: support-technique@boxibox.com
**Logs**: `storage/logs/laravel.log`
**Documentation**: Ce fichier

---

**Version**: 1.0
**Dernière mise à jour**: 02/10/2025
**Auteur**: Équipe Développement BOXIBOX

---

*Ce document est maintenu à jour avec chaque évolution du système de rappels automatiques.*
