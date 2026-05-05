📋 Répartition détaillée des tâches


# 👥 Équipe projet

* Dev 1 : Backend, Frontend Auth & sécurité
* Dev 2 : Backend, Frontend Module Gestion des Employés
* Dev 3 : Backend, Frontend Module Gestion des Campagnes
* Dev 4 : Backend, Frontend Module Gestion des Plannings
* Dev 5 : Backend, Frontend Module Gestion des Heures
* Dev 6 : Backend, Frontend Module Reporting & Statistiques

---

# 🗺️ Vue globale des modules

| Module                      | Description                                               |
| --------------------------- | --------------------------------------------------------- |
| Authentification & Sécurité | Gestion des comptes, rôles, permissions, sécurité globale |
| Gestion des Employés        | Base RH : fiches employés, statut, historique             |
| Gestion des Campagnes       | Création des projets/campagnes et affectations            |
| Gestion des Plannings       | Organisation du travail et validation des plannings       |
| Gestion des Heures          | Saisie, validation et calcul des heures                   |
| Reporting & Statistiques    | Tableaux de bord, exports, indicateurs                    |

---

# 📋 Répartition détaillée des tâches

---

# 🔐 Dev 1

### Objectif

Mettre en place toute la fondation sécurité et gestion des utilisateurs.

### Tâches

Contenu :

* Connexion / déconnexion
* Gestion des comptes utilisateurs
* Attribution des rôles (Admin, CP, SUP, TC)
* Middleware de protection des routes
* Gestion des permissions par rôle

### Livrables

* API Auth complète
* Middleware de sécurité
* Pages Login / Logout / Gestion utilisateurs
* Documentation des rôles & permissions

---

# ☁️ Dev 2

### Objectif

Créer le module RH complet pour la gestion des employés.

### Tâches

Contenu :

* CRUD des employés
* Informations personnelles et professionnelles
* Statuts (actif, inactif, suspendu)
* Génération de matricule unique
* Recherche et filtres avancés

Sous-parties :

* Profil employé détaillé
* Historique des modifications

### Livrables

* API Employés
* Interface liste + fiche employé
* Module de recherche et filtres

---

# 📁 Dev 3

### Objectif

Gérer les campagnes/projets et les affectations des employés.

### Tâches

Contenu :

* CRUD Campagnes
* Gestion du cycle de vie :

  * Active
  * Inactive
  * Terminée
* Affecter un employé à une campagne
* Historique des affectations

### Livrables

* API Campagnes
* Interface gestion campagnes
* Interface affectation employés

---

# 🎨 Dev 4

### Objectif

Créer le système complet de gestion des plannings.

### Tâches

* CRUD modèles de planning
* Affectation aux superviseurs :

  * Affecter un planning à un employé
  * Modifier une affectation
  * Supprimer une affectation
* Validation des plannings :

  * Valider
  * Suspendre
  * Consulter par statut

### Livrables

* API Plannings
* Calendrier / planning visuel
* Gestion des validations

---

# ⚡ Dev 5

### Objectif

Gérer la saisie et validation des heures de travail.

### Tâches

* Saisie des heures (SUP → TC)
* Saisie des heures (CP → SUP)
* Validation des heures
* Verrouillage après validation
* Calculs automatiques :

  * Heures travaillées
  * Heures supplémentaires
  * Comparaison avec planning

### Livrables

* API Heures
* Interface saisie + validation
* Moteur de calcul automatique

---

# 🚀 Dev 6

### Objectif

Créer la partie décisionnelle et reporting.

### Tâches

* Tableaux de bord analytiques :

  * Statistiques par campagne
  * Statistiques par employé
* Écarts planning vs réalisé
* Export PDF / Excel

### Livrables

* Dashboard admin
* Graphiques & indicateurs
* Module d’export

---

# 📅 Planning suggéré (3 Jours)

| Jour   | Travail                                        |
| ------ | ---------------------------------------------- |
| Jour 1 |                                                |
| Jour 2 |                                                |
| Jour 3 |                                                |

---

# 🧠 Résumé rapide

| Dev   | Rôle                        | Equipe I  | Equipe II | Equipe III| Equipe IV |
| ----- | --------------------------- | ----------| ----------| ----------| ----------| 
| Dev 1 | Authentification & sécurité | Maxson    | Marcel    | Renaud    | Christelle|
| Dev 2 | Gestion des employés        | Otniel    | Descartes | Arsene    | Raimi     |
| Dev 3 | Gestion des campagnes       | Steven    | Amiel     | Brunel    | Halil     |
| Dev 4 | Gestion des plannings       | Rogalex   | Caleb     | Kamal     | Breton    |
| Dev 5 | Gestion des heures          | Dylan     | Armel     | Florence  | Theodore  |
| Dev 6 | Reporting & statistiques    | Cédric    | Manoel    | Kenzo     | Bathez    |

---

