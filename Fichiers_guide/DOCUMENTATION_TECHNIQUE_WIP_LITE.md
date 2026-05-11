# Documentation Technique WIP_Lite

## Vue d'Ensemble

WIP_Lite est une application de gestion des ressources humaines développée avec Laravel 11 et Vue 3, utilisant Inertia.js pour la navigation SPA et PrimeVue pour les composants UI.

## Architecture Technique

### Stack Technologique
- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue 3 avec Composition API
- **Navigation**: Inertia.js
- **UI**: PrimeVue
- **Base de données**: MySQL
- **Styling**: Tailwind CSS

### Structure des Répertoires

```
wip-lite/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Policies/
│   └── ...
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── ...
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   ├── Layouts/
│   │   └── Components/
│   ├── views/
│   └── css/
├── routes/
│   └── web.php
└── ...
```

## Schéma de Base de Données

### Tables Principales

#### 1. Users (Utilisateurs)
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    role_id BIGINT UNSIGNED NULL,
    status ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

#### 2. Employees (Employés)
```sql
CREATE TABLE employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    matricule VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NULL,
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    hire_date DATE NULL,
    position_id BIGINT UNSIGNED NULL,
    status ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (position_id) REFERENCES positions(id)
);
```

#### 3. Positions (Postes)
```sql
CREATE TABLE positions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL, -- 'CP', 'SUP', 'TC'
    name VARCHAR(255) NOT NULL, -- 'Chef de Plateau', 'Superviseur', 'Téléconseiller'
    description TEXT NULL,
    base_salary DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 4. Campaigns (Campagnes)
```sql
CREATE TABLE campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'finished') DEFAULT 'inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 5. Assignments (Affectations)
```sql
CREATE TABLE assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    campaign_id BIGINT UNSIGNED NOT NULL,
    position_id BIGINT UNSIGNED NOT NULL,
    manager_id BIGINT UNSIGNED NULL, -- Référence à l'assignment du manager
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('en attente', 'validé', 'suspendu', 'terminé') DEFAULT 'en attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id),
    FOREIGN KEY (position_id) REFERENCES positions(id),
    FOREIGN KEY (manager_id) REFERENCES assignments(id)
);
```

#### 6. Planning Models (Modèles de Planning)
```sql
CREATE TABLE planning_models (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 7. Planning Assignments (Affectations de Planning)
```sql
CREATE TABLE planning_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    planning_model_id BIGINT UNSIGNED NOT NULL,
    campaign_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('en attente', 'validé', 'suspendu', 'terminé') DEFAULT 'en attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (planning_model_id) REFERENCES planning_models(id),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id)
);
```

#### 8. Timesheets (Feuilles de Temps)
```sql
CREATE TABLE timesheets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    campaign_id BIGINT UNSIGNED NOT NULL,
    week_start_date DATE NOT NULL,
    total_hours DECIMAL(5,2) DEFAULT 0,
    status ENUM('brouillon', 'soumis', 'validé', 'rejeté') DEFAULT 'brouillon',
    supervisor_notes TEXT NULL,
    cp_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id)
);
```

#### 9. Timesheet Entries (Entrées de Feuille de Temps)
```sql
CREATE TABLE timesheet_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    timesheet_id BIGINT UNSIGNED NOT NULL,
    day_of_week ENUM('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche') NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    break_duration INT DEFAULT 0, -- en minutes
    hours_worked DECIMAL(5,2) DEFAULT 0,
    task_description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (timesheet_id) REFERENCES timesheets(id)
);
```

## Logique Métier

### 1. Gestion Hiérarchique des Affectations

#### Structure en 3 Niveaux
```
Chef de Plateau (CP)
    ├── Superviseur 1 (SUP)
    │   ├── Téléconseiller 1 (TC)
    │   ├── Téléconseiller 2 (TC)
    │   └── Téléconseiller N (TC)
    ├── Superviseur 2 (SUP)
    │   ├── Téléconseiller 3 (TC)
    │   └── Téléconseiller N (TC)
    └── Superviseur N (SUP)
        ├── Téléconseiller X (TC)
        └── Téléconseiller Y (TC)
```

#### Règles d'Affectation
1. **CP**: Peut être affecté à plusieurs campagnes simultanément
2. **SUP**: Ne peut être affecté qu'à une seule campagne à la fois
3. **TC**: Ne peut être affecté qu'à un seul superviseur à la fois
4. Un employé ne peut avoir qu'une seule affectation active à la fois

### 2. Cycle de Vie des Campagnes

#### États Possibles
- **Active**: Campagne en cours, peut recevoir des affectations
- **Inactive**: Campagne suspendue temporairement
- **Finished**: Campagne clôturée définitivement

#### Transitions Gérées
- Activation → Désactivation → Réactivation
- Clôture avec archivage des données
- Traçabilité de toutes les transitions

### 3. Gestion des Plannings

#### Types de Plannings
- **Hebdomadaire**: Du lundi au dimanche
- **Heures par défaut**: 8h/jour (40h/semaine)
- **Gestion des pauses**: Déduction automatique du temps de pause

#### Processus de Validation
1. **Saisie** par les superviseurs
2. **Validation** par les Chefs de Plateau
3. **Verrouillage** des données après validation
4. **Notification** automatique aux employés concernés

### 4. Gestion des Heures

#### Saisie des Heures
- **Entrées journalières** par jour de la semaine
- **Calcul automatique** des totaux
- **Gestion des pauses** et heures supplémentaires
- **Comparaison** avec le planning de référence

#### Flux de Validation
```
TC → Saisie heures → SUP → Validation → CP → Validation finale → TC (consultation)
```

## Règles de Sécurité

### 1. Contrôle d'Accès
- **Rôle par défaut**: Utilisateur simple (droits limités)
- **Rôles hiérarchiques**: Admin > CP > SUP > TC
- **Permissions** basées sur le rôle et la hiérarchie

### 2. Validation des Données
- **Format email**: Validation automatique du format
- **Format téléphone**: Validation des patterns de numéros
- **Dates**: Contrôle de cohérence (début < fin)
- **Doublons**: Détection automatique des matricules et emails

### 3. Traçabilité
- **Journalisation**: Toutes les actions importantes sont logguées
- **Historique**: Conservation des modifications avec utilisateur et date
- **Audit Trail**: Suivi des changements de statut

## Patterns de Code

### 1. Structure des Contrôleurs
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::query();
        
        // Application des filtres
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $campaigns = $query->paginate(10)->withQueryString();
        
        return Inertia::render('Admin/Campaigns/Index', [
            'campaigns' => $campaigns,
            'filters' => $request->only(['search', 'status'])
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,finished'
        ]);
        
        Campaign::create($validated);
        
        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campagne créée avec succès');
    }
}
```

### 2. Structure des Modèles
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'matricule',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'hire_date',
        'position_id',
        'status'
    ];
    
    protected $casts = [
        'hire_date' => 'date',
        'status' => 'string',
    ];
    
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
    
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
    
    public function user()
    {
        return $this->hasOne(User::class);
    }
    
    // Accessors pour les calculs automatiques
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
    
    public function getActiveAssignmentsCountAttribute()
    {
        return $this->assignments()->where('status', 'active')->count();
    }
}
```

### 3. Structure des Composants Vue
```vue
<template>
    <div class="bg-white rounded-xl border border-pearl-200 shadow-sm p-6">
        <!-- Search Bar -->
        <div class="relative">
            <input 
                v-model="searchQuery"
                @keydown="handleSearchKeydown"
                placeholder="Rechercher..."
                class="w-full pl-10 pr-20 py-3 border border-pearl-200 rounded-xl"
            />
            <button @click="triggerSearch"
                    class="absolute inset-y-0 right-0 px-4 bg-gold-gradient text-white rounded-r-xl hover:bg-gold-700 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>
        
        <!-- Data Table -->
        <DataTable :value="filteredData" :loading="loading">
            <!-- Columns -->
        </DataTable>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const searchQuery = ref('');
const loading = ref(false);

// Fonctions de recherche manuelle
const triggerSearch = () => {
    if (searchQuery.value === '' || searchQuery.value.length > 2) {
        fetchData();
    }
};

const handleSearchKeydown = (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        triggerSearch();
    }
};

// Logique métier
const filteredData = computed(() => {
    // Logique de filtrage
});

const fetchData = () => {
    loading.value = true;
    // Appel API avec les filtres
    loading.value = false;
};
</script>
```

## Seeds de Données

### Structure des Seeders
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\Role;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            PositionSeeder::class,
            RoleSeeder::class,
            EmployeeSeeder::class,
            CampaignSeeder::class,
            AssignmentSeeder::class,
        ]);
    }
}
```

### Exemple de Seeder
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run()
    {
        $positions = [
            [
                'code' => 'CP',
                'name' => 'Chef de Plateau',
                'description' => 'Responsable de la gestion des superviseurs et des campagnes',
                'base_salary' => 250000.00
            ],
            [
                'code' => 'SUP',
                'name' => 'Superviseur',
                'description' => 'Gestion directe des téléconseillers et saisie des heures',
                'base_salary' => 180000.00
            ],
            [
                'code' => 'TC',
                'name' => 'Téléconseiller',
                'description' => 'Conseiller les clients et saisir les heures travaillées',
                'base_salary' => 150000.00
            ]
        ];
        
        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
```

## Bonnes Pratiques

### 1. Validation des Données
- Toujours valider les entrées utilisateur
- Utiliser les Form Requests pour la validation
- Gérer les erreurs de manière élégante
- Sanitizer les entrées avant insertion

### 2. Performance
- Utiliser les eager loading pour les relations
- Indexer les colonnes fréquemment recherchées
- Paginer les résultats volumineux
- Mettre en cache les données statiques

### 3. Sécurité
- Utiliser les Policies Laravel pour les autorisations
- Valider les CSRF sur toutes les formes
- Échapper les sorties utilisateur
- Utiliser les bindings sécurisés

### 4. Organisation du Code
- Suivre les conventions PSR-12
- Documenter les méthodes complexes
- Utiliser des noms explicites pour les variables et méthodes
- Séparer la logique métier des contrôleurs

## Déploiement

### Configuration Recommandée
- **PHP**: 8.2+ avec extensions requises
- **MySQL**: 8.0+ ou MariaDB 10.3+
- **Node.js**: 18+ pour le build
- **Composer**: Dernière version stable
- **Serveur Web**: Apache/Nginx avec PHP-FPM

### Variables d'Environnement
```bash
APP_NAME=WIP_Lite
APP_ENV=production
APP_DEBUG=false
APP_URL=https://wip-lite.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wip_lite
DB_USERNAME=wip_lite_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=database
QUEUE_CONNECTION=redis
```

## Maintenance et Support

### Logs Importants
- **Laravel**: `storage/logs/laravel.log`
- **PHP**: Logs du serveur web
- **MySQL**: Slow query log
- **Nginx/Apache**: Access et error logs

### Tâches de Maintenance
- Nettoyage des logs anciens
- Optimisation de la base de données
- Sauvegarde régulière des données
- Mise à jour des dépendances

Cette documentation technique servira de référence pour le développement, la maintenance et l'évolution de l'application WIP_Lite.
