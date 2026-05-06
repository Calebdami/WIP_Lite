# Documentation - Page Maintenance Système

## Vue d'ensemble

La page **Maintenance** (Admin > Maintenance) est un tableau de bord technique complet pour gérer et monitorer le système WIP Lite. Elle regroupe 5 catégories majeures de fonctionnalités.

---

## 1. État de Santé du Système (System Health)

**Emplacement** : Première carte de la page

### Indicateurs en Temps Réel
- **CPU** : Pourcentage d'utilisation du processeur
- **RAM** : Pourcentage de mémoire utilisée
- **Disque** : Pourcentage d'espace disque utilisé
- **Version** : Version actuelle de l'application

### Statut des Services
Vérification automatique de l'état :
- Base de données MySQL
- Système de cache (Redis, Memcached, ou File)
- Serveur SMTP (Mail)

### Logs d'Erreurs Récentes
Tableau affichant les 50 derniers événements du fichier `storage/logs/laravel.log`
- **Niveau** : Emergency, Alert, Critical, Error, Warning, Notice, Info, Debug
- **Message** : Détail de l'erreur
- **Date** : Timestamp de l'événement

---

## 2. Gestion de la Base de Données

**Emplacement** : Deuxième carte de la page

### A. Sauvegardes (Backups)

#### Sauvegarde Manuelle
- **Bouton** : "Créer Sauvegarde Manuelle"
- **Action** : Génère un backup SQL du jour
- **Format** : `backup_YYYY-MM-DD_HH-mm-ss.sql`
- **Stockage** : `storage/backups/`

#### Sauvegardes Automatiques
- Liste des backups existants
- **Actions par backup** :
  - 📥 **Télécharger** : Récupérer le fichier SQL
  - 🔄 **Restaurer** : Restaurer cette sauvegarde (confirmer avant)

### B. Optimisation de Base de Données

#### Nettoyer Tables Temporaires
- Supprime les jobs laravel > 7 jours
- Supprime les failed_jobs > 30 jours

#### Réindexer Tables
- Analyse et optimise les tables principales
- Tables ciblées : users, employees, campaigns, assignments, timesheets

#### Supprimer Entrées Orphelines
- Nettoie les assignations, plannings, et timesheets sans employé associé

### C. Historique des Migrations
Tableau listant toutes les migrations avec :
- **Migration** : Nom du fichier
- **Batch** : Numéro du lot d'exécution
- **Date** : Quand elle a été exécutée

### D. Anonymisation des Données
- **Switch** : "Activer l'anonymisation automatique"
- **Bouton** : "Anonymiser Maintenant"
- **Action** : Remplace les données sensibles (email, téléphone) par des valeurs génériques
- ⚠️ **Utilité** : Préparer un environnement de test/démo

---

## 3. Contrôle des Utilisateurs et Sécurité

**Emplacement** : Troisième carte de la page

### A. Sessions Actives
Tableau des utilisateurs connectés dans la dernière heure
- **Utilisateur** : Nom du compte
- **IP** : Adresse IP de connexion
- **Dernière Activité** : Timestamp formaté
- **Action** : Bouton ❌ pour forcer la déconnexion d'une session

### B. Audit Log (Journal d'Événements)
Historique détaillé de toutes les actions système
- **Utilisateur** : Qui a fait l'action
- **Action** : Type d'action (backup_created, cache_cleared, etc.)
- **Ressource** : Type affecté (database, cache, files, etc.)
- **Date** : Quand l'action s'est déroulée

### C. Mode Maintenance
#### Toggle Mode Maintenance
- **Switch** : Active/Désactive le mode maintenance
- **Message** : Texte personnalisé affiché aux utilisateurs non-admin
- **Effet** : Affiche une page de maintenance pour les utilisateurs standards

#### Gestion des Permissions
- **Bouton** : "Vérifier Accès Critiques"
  - Scan les comptes admin sans 2FA
  - Retourne une liste des problèmes de sécurité

- **Bouton** : "Scanner 2FA"
  - Liste tous les utilisateurs sans 2FA activée

---

## 4. Maintenance Applicative & Métier

**Emplacement** : Quatrième carte de la page

### A. Purge des Fichiers

#### Nettoyer Fichiers Temporaires
- Supprime les fichiers dans `storage/app/temp` > 24 heures

#### Supprimer Anciens Rapports
- Supprime les fichiers PDF/rapports > 30 jours depuis `storage/app/reports`

### B. Gestion du Cache

#### Vider Cache Application
- Vide **tous les caches** (Redis/File selon config)
- Efface aussi le cache des vues

#### Vider Cache Routes
- Exécute `php artisan route:clear`
- Utile après modification de routes

#### Vider Cache Config
- Exécute `php artisan config:clear`
- Utile après modification du `.env`

### C. File d'Attente (Queue Jobs)

#### Voir Tâches en Cours
- Affiche les jobs en attente dans la queue
- Liste les tâches avec leur statut (pending, processing, completed, failed)

#### Relancer Tâches Échouées
- Exécute `php artisan queue:retry`
- Rejoue les jobs qui ont échoué

### D. Configuration des Variables d'Environnement

**Interface de gestion du `.env`**
- Champs éditables pour :
  - MAIL_HOST
  - MAIL_PORT
  - MAIL_USERNAME
  - MAIL_PASSWORD
  - APP_NAME
  - APP_URL

- **Bouton** : "Sauvegarder Configuration"
- ✅ **Avantage** : Modifier la config sans SSH/FTP

---

## 5. Communication & Support

**Emplacement** : Cinquième carte de la page

### A. Notifications Système

#### Envoi de Notifications Flash
- **Textarea** : Rédiger le message
- **Dropdown** : Choisir le type (Info, Avertissement, Erreur, Succès)
- **Bouton** : "Envoyer"
- **Destinataires** : Tous les utilisateurs connectés

**Cas d'usage** : 
- "Maintenance prévue à 22h"
- "Mise à jour système en cours"
- "Erreur critique détectée"

### B. Diagnostic Mail

#### Tester la Configuration SMTP
- **Champ** : Adresse email de test
- **Bouton** : "Envoyer Mail de Test"
- **Résultat** : Message de succès/erreur avec détails

**Cas d'usage** :
- Vérifier que le serveur SMTP fonctionne
- Tester une nouvelle configuration email

---

## Routes API Disponibles

| Méthode | Route | Fonction |
|---------|-------|----------|
| GET | `/admin/maintenance/health` | État de santé |
| GET | `/admin/maintenance/logs/errors` | Logs d'erreurs |
| POST | `/admin/maintenance/database/backup` | Créer backup |
| GET | `/admin/maintenance/database/backups` | Lister backups |
| GET | `/admin/maintenance/database/backups/{id}/download` | Télécharger backup |
| POST | `/admin/maintenance/database/backups/{id}/restore` | Restaurer backup |
| POST | `/admin/maintenance/database/clean-temp` | Nettoyer tables temp |
| POST | `/admin/maintenance/database/reindex` | Réindexer tables |
| POST | `/admin/maintenance/database/remove-orphans` | Supprimer orphelins |
| GET | `/admin/maintenance/database/migrations` | Historique migrations |
| POST | `/admin/maintenance/database/anonymize` | Anonymiser données |
| GET | `/admin/maintenance/sessions` | Sessions actives |
| DELETE | `/admin/maintenance/sessions/{id}` | Forcer déconnexion |
| GET | `/admin/maintenance/audit-logs` | Logs d'audit |
| GET | `/admin/maintenance/maintenance-mode` | État mode maintenance |
| POST | `/admin/maintenance/maintenance-mode` | Activer/Désactiver maintenance |
| GET | `/admin/maintenance/permissions/check` | Vérifier accès critiques |
| GET | `/admin/maintenance/2fa/scan` | Scanner 2FA |
| POST | `/admin/maintenance/files/purge-temp` | Purger fichiers temp |
| POST | `/admin/maintenance/files/purge-reports` | Purger anciens rapports |
| POST | `/admin/maintenance/cache/clear-app` | Vider cache app |
| POST | `/admin/maintenance/cache/clear-routes` | Vider cache routes |
| POST | `/admin/maintenance/cache/clear-config` | Vider cache config |
| GET | `/admin/maintenance/queue/jobs` | Lister queue jobs |
| POST | `/admin/maintenance/queue/retry-failed` | Relancer jobs échoués |
| GET | `/admin/maintenance/config/env` | Lister vars d'env |
| POST | `/admin/maintenance/config/env` | Sauvegarder vars d'env |
| POST | `/admin/maintenance/notifications/send` | Envoyer notification |
| POST | `/admin/maintenance/mail/test` | Tester email |

---

## Fichiers Créés/Modifiés

### Nouveaux fichiers
- `resources/Pages/Admin/Maintenance/Index.vue` - Composant Vue complet
- `app/Http/Controllers/MaintenanceController.php` - Contrôleur avec toute la logique

### Fichiers modifiés
- `routes/web.php` - Routes ajoutées (routes GET pour la page + routes API)

---

## Permissions

- ✅ **Accès** : Admin uniquement
- ✅ **Contrôle** : Middleware dans le constructeur du contrôleur
- ✅ **Audit** : Toutes les actions sont enregistrées dans `activity_logs`

---

## Utilisation Recommandée

### Maintenance Régulière
1. ✅ Vérifier l'État de Santé (hebdomadairement)
2. ✅ Créer une Sauvegarde Manuelle (avant mises à jour)
3. ✅ Nettoyer les Tables Temporaires (mensuellement)

### Dépannage
1. 🔍 Consulter les Logs d'Erreurs
2. 🔍 Vérifier le Statut des Services
3. 🧪 Tester l'Email (si problèmes d'envoi)

### Sécurité
1. 🛡️ Vérifier les Sessions Actives
2. 🛡️ Consulter l'Audit Log
3. 🛡️ Scanner 2FA régulièrement

### Performance
1. ⚡ Vider le Cache Application (après déploiement)
2. ⚡ Réindexer les Tables (mensuellement)
3. ⚡ Purger les Fichiers Temporaires (mensuellement)

---

## Notes Techniques

### Contrôleur
- **Fichier** : `MaintenanceController.php`
- **Namespace** : `App\Http\Controllers`
- **Authentification** : Vérifiée dans `__construct()`
- **Logging** : Chaque action est enregistrée via `ActivityLog::create()`

### Composant Vue
- **Framework** : Vue 3 (Composition API)
- **UI** : PrimeVue 4.5.5
- **Styling** : Tailwind CSS + PrimeVue CSS
- **Données** : Récupérées via axios depuis les endpoints

### Stockage
- **Backups** : `storage/backups/`
- **Fichiers Temp** : `storage/app/temp/`
- **Rapports** : `storage/app/reports/`
- **Logs** : `storage/logs/laravel.log`

### Sécurité
- ✅ CSRF Protection
- ✅ Auth Verification
- ✅ Role-based Access
- ✅ Activity Audit Trail
- ⚠️ TODO : 2FA implementation

---

## Limitations & Améliorations Futures

### Actuellement Limité À
- Windows/Linux CPU usage (estimé)
- Pas de support für FTP/S3 backups
- 2FA check n'est pas implémenté
- Sessions DB-based (pas compatible avec toutes les configurations)

### À Implémenter
- [ ] Sauvegarde cloud (S3, Azure Blob)
- [ ] Restauration point-in-time
- [ ] Monitoring en temps réel (WebSocket)
- [ ] Alertes email automatiques
- [ ] Authentification 2FA
- [ ] Exports de rapports (PDF, Excel)
- [ ] Intégration Slack/Discord
- [ ] Scheduling des tâches de maintenance

---

## Support

Pour toute question ou bug, consulter :
- Logs : `storage/logs/laravel.log`
- Audit Log : Page Maintenance > Audit Log
- Routes : `php artisan route:list | grep maintenance`
