# MODÈLE DE DONNÉES

---

# PRINCIPES VALIDÉS

* ✅ `employees.user_id` (et non l’inverse)
* ✅ `assignments.manager_id → employees.id`
* ✅ séparation stricte : **User / Employee / Assignment**
* ✅ historisation complète

---

# 1. TABLE `users`

```text
id                  # Identifiant unique du compte utilisateur
role_id             # Référence au rôle système (admin, cp, sup, tc)
email               # Email utilisé pour la connexion
password            # Mot de passe hashé
email_verified_at   # Date de vérification de l’email
created_at          # Date de création
updated_at          # Date de modification
```

---

# 2. TABLE `roles`

```text
id          # Identifiant du rôle
name        # Nom du rôle (admin, cp, sup, tc)
created_at  # Date de création
updated_at  # Date de modification
```

---

# 3. TABLE `employees`

```text
id              # Identifiant unique de l’employé
user_id         # Lien vers le compte utilisateur (nullable si pas de compte)
matricule       # Identifiant interne unique RH
first_name      # Prénom
last_name       # Nom
birth_date      # Date de naissance
phone           # Numéro de téléphone
email           # Email professionnel
address         # Adresse
position_id     # Poste actuel (référence positions)
salary_base     # Salaire de base
status          # Statut (actif, suspendu, inactif)
created_at      # Date de création
updated_at      # Date de modification
```

---

# 4. TABLE `positions`

```text
id          # Identifiant du poste
name        # Nom du poste (Admin, CP, SUP, TC)
code        # Code court (ADMIN, CP, SUP, TC)
description # Description du poste
created_at  # Date de création
updated_at  # Date de modification
```

---

# 5. TABLE `campaigns`

```text
id          # Identifiant de la campagne
name        # Nom de la campagne
description # Description détaillée
start_date  # Date de début
end_date    # Date de fin
status      # Statut (active, inactive, terminée)
created_at  # Date de création
updated_at  # Date de modification
```

---

# 6. TABLE `assignments` (CŒUR)

```text
id              # Identifiant de l’affectation
employee_id     # Employé concerné
campaign_id     # Campagne liée
manager_id      # Supérieur hiérarchique (employee_id)
position_id     # Rôle dans la campagne (CP, SUP, TC)
status          # Statut (actif, terminé, suspendu)
start_date      # Début d’affectation
end_date        # Fin d’affectation
created_at      # Date de création
updated_at      # Date de modification
```

---

# 7. TABLE `planning_models`

```text
id                # Identifiant du modèle
name              # Nom du planning (ex: Semaine 35h)
description       # Description optionnelle
monday_hours      # Heures prévues lundi (0 si non travaillé)
tuesday_hours     # Heures prévues mardi
wednesday_hours   # Heures prévues mercredi
thursday_hours    # Heures prévues jeudi
friday_hours      # Heures prévues vendredi
saturday_hours    # Heures prévues samedi
sunday_hours      # Heures prévues dimanche
total_hours       # Somme des heures de la semaine (calculée)
created_by        # Auteur
created_at        # Date de création
updated_at        # Date de modification
```
---

# 8. TABLE `planning_assignments`

```text
id                  # Identifiant
planning_model_id   # Modèle utilisé
employee_id         # Employé concerné
start_date          # Début de validité
end_date            # Fin de validité
status              # Statut (en attente, validé, suspendu)
validated_by        # ID employé qui valide
validated_at        # Date de validation
created_at          # Date de création
updated_at          # Date de modification
```

---

# 9. TABLE `timesheets`

```text
id              # Identifiant
employee_id     # Employé concerné
period_start    # Début de la période (ex: lundi)
period_end      # Fin de la période (max dimanche)
status          # brouillon | soumis | validé
validated_by    # Employé (CP) qui valide
validated_at    # Date de validation
created_at      # Date de création
updated_at      # Date de modification
```

---

# 10. TABLE `timesheet_entries`

```text
id              # Identifiant
timesheet_id    # Référence à la feuille de temps
date            # Jour concerné
check_in        # Heure d’arrivée
check_out       # Heure de départ
break_duration  # Durée de pause (en minutes ou heures)
total_hours     # Heures réellement travaillées ce jour
planned_hours   # Heures prévues ce jour (copie du planning)
overtime_hours  # Heures supplémentaires (calculées)
absence_type    # Type d’absence (maladie, congé…)
comment         # Remarque éventuelle
created_at      # Date de création
updated_at      # Date de modification
```
# Processus de saisie des heures

---

##  Étapes

1. **Récupérer le planning**

   * Identifier le jour (ex: lundi)
   * Lire les heures prévues depuis `planning_models`

2. **Créer / utiliser la feuille (`timesheet`)**

   * Vérifier la période (semaine)

3. **Saisir les heures**

   * `check_in`, `check_out`, `break_duration`

4. **Calculer**

   * `total_hours`
   * `planned_hours` (copié du planning)
   * `overtime_hours`

5. **Enregistrer dans `timesheet_entries`**

---

## Schéma simplifié

```text
PLANNING_MODELS
    ↓
(heures prévues du jour)
    ↓
TIMESHEETS (période)
    ↓
TIMESHEET_ENTRIES
    ├── heures réelles (check_in/out)
    ├── planned_hours (copié)
    └── overtime (calculé)
```
---

# 11. TABLE `activity_logs`

```text
id              # Identifiant
user_id         # Utilisateur ayant fait l’action
action          # Type d’action (create, update, delete)
model_type      # Type de modèle impacté
model_id        # ID de l’élément concerné
description     # Détail de l’action
ip_address      # Adresse IP
created_at      # Date de l’action
```

---

# 12. TABLE `notifications`

```text
id              # Identifiant
type            # Type de notification
notifiable_type # Type de modèle ciblé
notifiable_id   # ID du modèle ciblé
data            # Contenu (JSON)
read_at         # Date de lecture
created_at      # Date de création
```

---

# 13. TABLE `employee_historys`

```text
id                  # Identifiant
employee_id         # Employé concerné
old_position_id     # Ancien poste
new_position_id     # Nouveau poste
old_status          # Ancien statut
new_status          # Nouveau statut
changed_by          # ID utilisateur ayant fait le changement
reason              # Raison
created_at          # Date
```

---

# 14. TABLE `assignment_historys`

```text
id                  # Identifiant
assignment_id       # Affectation concernée
employee_id         # Employé
old_manager_id      # Ancien manager
new_manager_id      # Nouveau manager
old_campaign_id     # Ancienne campagne
new_campaign_id     # Nouvelle campagne
action_type         # assign, release, transfer
changed_by          # Auteur
reason              # Raison
created_at          # Date
```

---

# 15. TABLE `planning_historys`

```text
id                      # Identifiant
planning_assignment_id  # Planning concerné
old_status              # Ancien statut
new_status              # Nouveau statut
changed_by              # Auteur
reason                  # Raison
created_at              # Date
```

---

# 16. TABLE `timesheet_historys`

```text
id              # Identifiant
timesheet_id    # Feuille concernée
employee_id     # Employé
old_status      # Ancien statut
new_status      # Nouveau statut
changed_by      # Auteur
reason          # Raison
created_at      # Date
```

---

# RELATIONS PRINCIPALES

```text
users.role_id → roles.id

employees.user_id → users.id
employees.position_id → positions.id

assignments.employee_id → employees.id
assignments.manager_id → employees.id
assignments.campaign_id → campaigns.id
assignments.position_id → positions.id

planning_assignments.employee_id → employees.id
planning_assignments.planning_model_id → planning_models.id

timesheets.employee_id → employees.id

timesheet_entries.timesheet_id → timesheets.id

employee_histories.employee_id → employees.id
assignment_histories.assignment_id → assignments.id
planning_history.planning_assignment_id → planning_assignments.id
timesheet_history.timesheet_id → timesheets.id
```








