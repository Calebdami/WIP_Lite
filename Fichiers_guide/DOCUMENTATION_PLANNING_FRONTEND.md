# DOCUMENTATION FRONTEND DU SYSTÈME DE PLANNING

Suite de la documentation complète - Parties Frontend (Admin, CP, SUP, TC)

---

## 🎨 FRONTEND - ADMIN (SUITE)

### 3. AssignSchedule.vue (`resources/js/Pages/Admin/Assignments/AssignSchedule.vue`)

Composant pour affecter un planning à un superviseur.

```vue
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    supervisors: Array,   // Liste des superviseurs disponibles
    models: Array,        // Liste des modèles de planning
    campaigns: Array     // Liste des campagnes uniques
});

// État local pour les filtres et sélections
const searchQuery = ref('');
const selectedCampaign = ref('all');
const searchQueryModel = ref('');

// Pagination Superviseurs
const currentPageSup = ref(1);
const pageSizeSup = 4;

// Pagination Modèles
const currentPageModel = ref(1);
const pageSizeModel = 4;

// Formulaire d'affectation
const form = useForm({
    employee_id: null,           // ID du superviseur sélectionné
    planning_model_id: null,     // ID du modèle sélectionné
    start_date: '',              // Date de début
    end_date: ''                 // Date de fin (optionnelle)
});

// Filtre les superviseurs selon la recherche et la campagne
const filteredSupervisors = computed(() => {
    let list = props.supervisors;

    // Filtre par campagne
    if (selectedCampaign.value !== 'all') {
        list = list.filter(s => s.campaign === selectedCampaign.value);
    }

    // Filtre par recherche texte
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        list = list.filter(s =>
            s.name.toLowerCase().includes(query) ||
            s.campaign.toLowerCase().includes(query)
        );
    }
    return list;
});

// Filtre les modèles selon la recherche
const filteredModels = computed(() => {
    let list = props.models;
    if (searchQueryModel.value) {
        const query = searchQueryModel.value.toLowerCase();
        list = list.filter(m =>
            m.name.toLowerCase().includes(query) ||
            (m.description && m.description.toLowerCase().includes(query))
        );
    }
    return list;
});

// Pagination des superviseurs
const paginatedSupervisors = computed(() => {
    const start = (currentPageSup.value - 1) * pageSizeSup;
    return filteredSupervisors.value.slice(start, start + pageSizeSup);
});

const totalPagesSup = computed(() => Math.ceil(filteredSupervisors.value.length / pageSizeSup));

// Pagination des modèles
const paginatedModels = computed(() => {
    const start = (currentPageModel.value - 1) * pageSizeModel;
    return filteredModels.value.slice(start, start + pageSizeModel);
});

const totalPagesModel = computed(() => Math.ceil(filteredModels.value.length / pageSizeModel));

// Récupère le superviseur sélectionné
const selectedSupervisor = computed(() => {
    return props.supervisors.find(s => s.id === form.employee_id);
});

// Récupère le modèle sélectionné
const selectedModel = computed(() => {
    return props.models.find(m => m.id === form.planning_model_id);
});

// Soumission du formulaire
const submit = () => {
    form.post(route('admin.assignments.schedules.assign.store'));
};
</script>

<template>
    <Head title="Affecter un Planning — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Affecter un planning à un superviseur</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Assignation d'un modèle de planning</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.assignments.schedules')"
                        class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-bold text-charcoal-600 hover:bg-pearl-50 hover:border-pearl-300 hover:shadow-sm transition-all active:scale-95">
                        Annuler
                    </Link>
                    <button @click="submit"
                        :disabled="form.processing || !form.employee_id || !form.planning_model_id || !form.start_date"
                        class="px-4 py-2 bg-gold-gradient rounded-lg text-xs font-bold text-white hover:opacity-90 transition-all shadow-gold disabled:opacity-50">
                        Valider l'affectation
                    </button>
                </div>
            </div>
        </template>

        <!-- Container Grid 50/50 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pb-20 items-start">

            <!-- Colonne Gauche : Sélection Superviseur -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-pearl-100 flex items-center justify-between gap-3 bg-white">
                        <h2 class="text-sm font-bold text-charcoal-700 flex items-center gap-3">
                            <svg class="w-4 h-4 text-charcoal-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Sélection du superviseur
                        </h2>
                        <div class="flex items-center gap-2">
                            <!-- Filtre par campagne -->
                            <select v-model="selectedCampaign"
                                class="bg-pearl-50 border border-pearl-200 rounded-lg px-2 py-1.5 text-[10px] font-bold text-charcoal-700 outline-none focus:border-gold-400 transition-all">
                                <option value="all">Toutes les campagnes</option>
                                <option v-for="c in campaigns" :key="c" :value="c">{{ c }}</option>
                            </select>
                            <!-- Recherche -->
                            <input v-model="searchQuery" type="text" placeholder="Rechercher..."
                                class="w-32 bg-pearl-50 border border-pearl-200 rounded-lg px-3 py-1.5 text-[11px] text-charcoal-700 outline-none focus:border-gold-400 transition-all" />
                        </div>
                    </div>

                    <!-- Liste des superviseurs avec pagination -->
                    <div class="p-6 space-y-3">
                        <div v-for="sup in paginatedSupervisors" :key="sup.id"
                            @click="!sup.has_active_planning && (form.employee_id = sup.id)"
                            class="p-4 rounded-xl border border-pearl-100 transition-all duration-300 hover:border-gold-300 hover:shadow-md hover:-translate-y-0.5 group"
                            :class="[
                                form.employee_id === sup.id ? 'border-gold-400 bg-gold-50/20 shadow-sm' : 'bg-white',
                                sup.has_active_planning ? 'opacity-50 cursor-not-allowed grayscale-[0.5]' : 'cursor-pointer'
                            ]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h3 class="font-bold text-sm text-charcoal-700">{{ sup.name }}</h3>
                                        <span v-if="sup.has_active_planning"
                                            class="px-2 py-0.5 bg-orange-100 text-orange-600 rounded text-[9px] font-bold">Planning actif</span>
                                    </div>
                                    <div class="flex items-center gap-4 mt-1.5 text-[10px] uppercase tracking-wider font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-charcoal-500">Campagne:</span>
                                            <span :class="sup.campaign === 'Aucune campagne' ? 'text-charcoal-100 font-medium italic' : 'text-gold-600'">{{ sup.campaign }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 border-l border-pearl-100 pl-4">
                                            <span class="text-charcoal-300">Équipe:</span>
                                            <span :class="sup.team_count === 0 ? 'text-charcoal-300' : 'text-charcoal-700'">{{ sup.team_count }} TC</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Indicateur de sélection -->
                                <div v-if="form.employee_id === sup.id"
                                    class="w-5 h-5 rounded-full bg-gold-400 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination superviseurs -->
                    <div v-if="totalPagesSup > 1" class="px-6 py-4 border-t border-pearl-100 flex justify-center gap-2">
                        <button v-for="page in totalPagesSup" :key="page"
                            @click="currentPageSup = page"
                            class="w-8 h-8 rounded-lg text-xs font-bold transition-all"
                            :class="currentPageSup === page ? 'bg-gold-500 text-white' : 'bg-pearl-50 text-charcoal-600 hover:bg-pearl-100'">
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Colonne Droite : Sélection Modèle et Dates -->
            <div class="space-y-6">
                <!-- Sélection du modèle -->
                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-pearl-100 flex items-center justify-between gap-3 bg-white">
                        <h2 class="text-sm font-bold text-charcoal-700 flex items-center gap-3">
                            <svg class="w-4 h-4 text-charcoal-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Sélection du modèle
                        </h2>
                        <input v-model="searchQueryModel" type="text" placeholder="Rechercher..."
                            class="w-32 bg-pearl-50 border border-pearl-200 rounded-lg px-3 py-1.5 text-[11px] text-charcoal-700 outline-none focus:border-gold-400 transition-all" />
                    </div>

                    <div class="p-6 space-y-3 max-h-64 overflow-y-auto">
                        <div v-for="model in paginatedModels" :key="model.id"
                            @click="form.planning_model_id = model.id"
                            class="p-4 rounded-xl border border-pearl-100 transition-all duration-300 hover:border-gold-300 hover:shadow-md hover:-translate-y-0.5 group cursor-pointer"
                            :class="form.planning_model_id === model.id ? 'border-gold-400 bg-gold-50/20 shadow-sm' : 'bg-white'">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-sm text-charcoal-700">{{ model.name }}</h3>
                                    <p class="text-[10px] text-charcoal-400 mt-1">{{ model.total_hours }}h / semaine</p>
                                </div>
                                <div v-if="form.planning_model_id === model.id"
                                    class="w-5 h-5 rounded-full bg-gold-400 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination modèles -->
                    <div v-if="totalPagesModel > 1" class="px-6 py-4 border-t border-pearl-100 flex justify-center gap-2">
                        <button v-for="page in totalPagesModel" :key="page"
                            @click="currentPageModel = page"
                            class="w-8 h-8 rounded-lg text-xs font-bold transition-all"
                            :class="currentPageModel === page ? 'bg-gold-500 text-white' : 'bg-pearl-50 text-charcoal-600 hover:bg-pearl-100'">
                            {{ page }}
                        </button>
                    </div>
                </div>

                <!-- Dates de validité -->
                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm p-6">
                    <h2 class="text-sm font-bold text-charcoal-700 mb-4">Dates de validité</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold text-charcoal-400 uppercase tracking-widest mb-1.5">Date de début *</label>
                            <input 
                                v-model="form.start_date"
                                type="date" 
                                class="w-full bg-pearl-50 border border-pearl-200 rounded-lg px-4 py-2.5 text-sm text-charcoal-700 focus:ring-2 focus:ring-gold-400/20 focus:border-gold-400 outline-none transition-all"
                            />
                            <div v-if="form.errors.start_date" class="text-red-500 text-[10px] mt-1">{{ form.errors.start_date }}</div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-charcoal-400 uppercase tracking-widest mb-1.5">Date de fin (optionnel)</label>
                            <input 
                                v-model="form.end_date"
                                type="date" 
                                class="w-full bg-pearl-50 border border-pearl-200 rounded-lg px-4 py-2.5 text-sm text-charcoal-700 focus:ring-2 focus:ring-gold-400/20 focus:border-gold-400 outline-none transition-all"
                            />
                            <div v-if="form.errors.end_date" class="text-red-500 text-[10px] mt-1">{{ form.errors.end_date }}</div>
                        </div>
                    </div>
                </div>

                <!-- Récapitulatif -->
                <div v-if="selectedSupervisor && selectedModel" class="bg-gold-50 rounded-xl border border-gold-200 p-6">
                    <h2 class="text-sm font-bold text-charcoal-700 mb-4">Récapitulatif</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-charcoal-500">Superviseur:</span>
                            <span class="font-bold text-charcoal-700">{{ selectedSupervisor.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-charcoal-500">Modèle:</span>
                            <span class="font-bold text-charcoal-700">{{ selectedModel.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-charcoal-500">Total hebdo:</span>
                            <span class="font-bold text-gold-600">{{ selectedModel.total_hours }}h</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
```

### 4. Validation.vue (`resources/js/Pages/Admin/Assignments/Validation.vue`)

Composant pour valider les plannings en attente.

```vue
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    assignments: Object,  // Liste des affectations (paginée)
    stats: Object,        // Statistiques (pending, validated, suspended)
    filters: {
        type: Object,
        default: () => ({ search: '', status: 'all' })
    }
});

const assignmentsData = computed(() => props.assignments.data);
const search = ref((props.filters || {}).search || '');
const statusFilter = ref((props.filters || {}).status || 'all');
const selectedIds = ref([]);  // IDs des affectations sélectionnées pour action groupée

// Debounce pour la recherche
let timeout;
watch([search, statusFilter], ([newSearch, newStatus]) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.assignments.validation'), { 
            search: newSearch, 
            status: newStatus 
        }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

// Sélectionner/désélectionner tout
const toggleSelectAll = () => {
    if (selectedIds.value.length === assignmentsData.value.length) {
        selectedIds.value = [];
    } else {
        selectedIds.value = assignmentsData.value.map(a => a.id);
    }
};

// Mettre à jour le statut d'une affectation
const updateStatus = (id, status, reason = '') => {
    router.patch(route('admin.assignments.validation.status', id), {
        status,
        reason
    }, {
        onSuccess: () => {
            selectedIds.value = selectedIds.value.filter(sid => sid !== id);
        }
    });
};

// Mise à jour groupée
const bulkUpdate = (status) => {
    if (selectedIds.value.length === 0) return;
    
    if (confirm(`Voulez-vous vraiment changer le statut de ${selectedIds.value.length} affectation(s) vers "${status}" ?`)) {
        router.patch(route('admin.assignments.validation.bulk'), {
            ids: selectedIds.value,
            status: status
        }, {
            onSuccess: () => {
                selectedIds.value = [];
            }
        });
    }
};

// Formater la période
const formatPeriod = (start, end) => {
    const s = new Date(start).toLocaleDateString();
    const e = end ? new Date(end).toLocaleDateString() : 'Indéfinie';
    return `${s} au ${e}`;
};

// Classes CSS selon le statut
const getStatusClass = (status) => {
    switch (status) {
        case 'en attente': return 'bg-yellow-100 text-yellow-700';
        case 'validé': return 'bg-green-100 text-green-700';
        case 'suspendu': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};

// Classes CSS selon le rôle
const getRoleClass = (code) => {
    return code === 'SUP' ? 'bg-blue-100 text-blue-700' : 'bg-indigo-100 text-indigo-700';
};
</script>

<template>
    <Head title="Validation des Plannings — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Validation des Plannings</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Consultation et validation des affectations de planning</p>
                </div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ stats.pending }}</div>
                    <div class="text-xs font-bold text-charcoal-400 uppercase tracking-widest">En attente</div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-50 text-green-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ stats.validated }}</div>
                    <div class="text-xs font-bold text-charcoal-400 uppercase tracking-widest">Validé</div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ stats.suspended }}</div>
                    <div class="text-xs font-bold text-charcoal-400 uppercase tracking-widest">Suspendu</div>
                </div>
            </div>
        </div>

        <!-- Filtres et Actions -->
        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-pearl-100 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="relative flex-1 max-w-2xl">
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Rechercher par employé ou modèle..."
                        class="w-full bg-pearl-50 border border-pearl-200 rounded-lg pl-10 pr-4 py-2 text-sm text-charcoal-700 focus:border-gold-400 outline-none transition-all"
                    />
                    <svg class="w-4 h-4 text-charcoal-300 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <div class="flex items-center gap-3">
                    <select 
                        v-model="statusFilter"
                        class="bg-white border border-pearl-200 rounded-lg px-4 py-2 text-xs font-bold text-charcoal-600 outline-none focus:border-gold-400 transition-all cursor-pointer"
                    >
                        <option value="all">Tous les statuts</option>
                        <option value="en attente">En attente</option>
                        <option value="validé">Validé</option>
                        <option value="suspendu">Suspendu</option>
                    </select>
                    
                    <button 
                        @click="toggleSelectAll"
                        class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-bold text-charcoal-600 hover:bg-pearl-50 transition-all active:scale-95"
                    >
                        {{ selectedIds.length === assignmentsData.length && assignmentsData.length > 0 ? 'Tout désélectionner' : 'Tout sélectionner' }}
                    </button>
                    
                    <div v-if="selectedIds.length > 0" class="flex items-center gap-2 border-l border-pearl-100 pl-3">
                        <button 
                            @click="bulkUpdate('validé')"
                            class="px-3 py-2 bg-green-600 text-white rounded-lg text-[10px] font-black uppercase tracking-wider hover:bg-green-700 transition-all shadow-sm"
                        >
                            Valider ({{ selectedIds.length }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table des affectations -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-pearl-50/50 text-[10px] font-black text-charcoal-400 uppercase tracking-widest border-b border-pearl-100">
                            <th class="px-6 py-4 w-10">
                                <input type="checkbox" 
                                    :checked="selectedIds.length === assignmentsData.length && assignmentsData.length > 0"
                                    @change="toggleSelectAll"
                                    class="rounded border-pearl-300 text-gold-500 focus:ring-gold-400"
                                />
                            </th>
                            <th class="px-6 py-4">Employé</th>
                            <th class="px-6 py-4">Rôle</th>
                            <th class="px-6 py-4">Modèle</th>
                            <th class="px-6 py-4">Période</th>
                            <th class="px-6 py-4">Statut</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="assignment in assignmentsData" :key="assignment.id"
                            class="border-b border-pearl-50 hover:bg-pearl-50/30 transition-colors"
                            :class="{ 'bg-gold-50/20': selectedIds.includes(assignment.id) }">
                            <td class="px-6 py-4">
                                <input type="checkbox" 
                                    :checked="selectedIds.includes(assignment.id)"
                                    @change="selectedIds.includes(assignment.id) 
                                        ? selectedIds = selectedIds.filter(id => id !== assignment.id)
                                        : selectedIds.push(assignment.id)"
                                    class="rounded border-pearl-300 text-gold-500 focus:ring-gold-400"
                                />
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-charcoal-700">
                                    {{ assignment.employee?.first_name }} {{ assignment.employee?.last_name }}
                                </div>
                                <div class="text-[10px] text-charcoal-400">{{ assignment.employee?.matricule }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 rounded-full text-[10px] font-bold', getRoleClass(assignment.employee?.position?.code)]">
                                    {{ assignment.employee?.position?.code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-charcoal-600">{{ assignment.planningModel?.name }}</div>
                                <div class="text-[10px] text-charcoal-400">{{ assignment.planningModel?.total_hours }}h / semaine</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-charcoal-600">
                                {{ formatPeriod(assignment.start_date, assignment.end_date) }}
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 rounded-full text-[10px] font-bold', getStatusClass(assignment.status)]">
                                    {{ assignment.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button v-if="assignment.status === 'en attente'"
                                        @click="updateStatus(assignment.id, 'validé')"
                                        class="px-3 py-1.5 bg-green-100 text-green-600 rounded-lg text-[10px] font-bold hover:bg-green-200 transition-all"
                                        title="Valider">
                                        Valider
                                    </button>
                                    <button v-if="assignment.status === 'validé'"
                                        @click="updateStatus(assignment.id, 'suspendu')"
                                        class="px-3 py-1.5 bg-red-100 text-red-600 rounded-lg text-[10px] font-bold hover:bg-red-200 transition-all"
                                        title="Suspendre">
                                        Suspendre
                                    </button>
                                    <button v-if="assignment.status === 'suspendu'"
                                        @click="updateStatus(assignment.id, 'en attente')"
                                        class="px-3 py-1.5 bg-yellow-100 text-yellow-600 rounded-lg text-[10px] font-bold hover:bg-yellow-200 transition-all"
                                        title="Réactiver">
                                        Réactiver
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="assignments.links.length > 3" class="p-4 border-t border-pearl-100 flex justify-center gap-2">
                <Link v-for="(link, k) in assignments.links" :key="k"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all"
                    :class="link.active ? 'bg-gold-500 text-white' : (link.url ? 'bg-white border border-pearl-200 text-charcoal-600 hover:bg-pearl-50' : 'bg-pearl-100 text-charcoal-300 cursor-not-allowed')"
                />
            </div>
        </div>
    </AdminLayout>
</template>
```

---

## 🎨 FRONTEND - CHEF DE PLATEAU (CP)

Les composants CP sont très similaires à ceux de l'Admin, mais avec un périmètre limité aux campagnes du CP.

### 1. Templates.vue (`resources/js/Pages/Cp/Schedules/Templates.vue`)

Identique à `Admin/Assignments/Schedules.vue` mais avec :
- Layout : `CpLayout` au lieu de `AdminLayout`
- Routes : `cp.schedules.*` au lieu de `admin.assignments.schedules.*`
- Périmètre : Uniquement les modèles créés par le CP ou globaux

### 2. CreateTemplate.vue (`resources/js/Pages/Cp/Schedules/CreateTemplate.vue`)

Identique à `Admin/Assignments/CreateTemplate.vue` mais avec :
- Layout : `CpLayout`
- Route : `cp.schedules.templates.store`

### 3. AssignSup.vue (`resources/js/Pages/Cp/Schedules/AssignSup.vue`)

Similaire à `Admin/Assignments/AssignSchedule.vue` mais avec :
- Layout : `CpLayout`
- Périmètre : Uniquement les superviseurs gérés par le CP
- Route : `cp.schedules.assign.store`

### 4. AssignTc.vue (`resources/js/Pages/Cp/Schedules/AssignTc.vue`)

Similaire à l'affectation aux superviseurs mais pour les téléconseilleurs :
- Affiche les TC gérés par les superviseurs du CP
- Permet l'affectation groupée de plusieurs TC
- Route : `cp.schedules.assign-tc`

### 5. Validation.vue (`resources/js/Pages/Cp/Schedules/Validation.vue`)

Identique à `Admin/Assignments/Validation.vue` mais avec :
- Layout : `CpLayout`
- Périmètre : Uniquement les affectations du périmètre du CP (ses SUPs et leurs TCs)
- Route : `cp.schedules.validation.*`

---

## 🎨 FRONTEND - SUPERVISEUR (SUP)

### Index.vue (`resources/js/Pages/Sup/Schedule/Index.vue`)

Composant de lecture seule pour afficher le planning du superviseur et de son équipe.

```vue
<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    currentSupervisor: Object,  // Infos du superviseur connecté
    myPlanning: Object,         // Planning personnel du superviseur
    teamPlannings: Array,       // Plannings des TC de l'équipe
    stats: Object               // Statistiques (total, validated, pending, hours)
});

// Mapping des statuts pour les badges
const getStatusBadge = (status) => {
    switch (status) {
        case 'validé':
            return { label: 'Validé', color: 'bg-emerald-50 text-emerald-600 border-emerald-100' };
        case 'en attente':
            return { label: 'En attente', color: 'bg-yellow-50 text-yellow-600 border-yellow-100' };
        case 'suspendu':
            return { label: 'Suspendu', color: 'bg-red-50 text-red-600 border-red-100' };
        default:
            return { label: 'Création', color: 'bg-gray-50 text-gray-400 border-gray-100' };
    }
};

const daysShort = [
    { key: 'monday_hours', label: 'Lun' },
    { key: 'tuesday_hours', label: 'Mar' },
    { key: 'wednesday_hours', label: 'Mer' },
    { key: 'thursday_hours', label: 'Jeu' },
    { key: 'friday_hours', label: 'Ven' },
    { key: 'saturday_hours', label: 'Sam' },
    { key: 'sunday_hours', label: 'Dim' },
];

// Utilitaires de formatage pour les heures
const getNumericHours = (hoursVal) => {
    if (typeof hoursVal === 'number') return hoursVal;
    if (!hoursVal) return 0;
    const hoursStr = String(hoursVal).trim();
    if (hoursStr === '0' || hoursStr === '0h' || hoursStr.toLowerCase() === 'repos') return 0;
    const simpleMatch = hoursStr.match(/^(\d+(\.\d+)?)([hH])?$/);
    if (simpleMatch) return parseFloat(simpleMatch[1]);
    try {
        const ranges = hoursStr.split(/[/,]/);
        let total = 0;
        ranges.forEach(range => {
            const parts = range.match(/(\d{1,2})[:h](\d{2})?\s*-\s*(\d{1,2})[:h](\d{2})?/);
            if (parts) {
                const startH = parseInt(parts[1]);
                const startM = parts[2] ? parseInt(parts[2]) : 0;
                const endH = parseInt(parts[3]);
                const endM = parts[4] ? parseInt(parts[4]) : 0;
                total += (endH + endM / 60) - (startH + startM / 60);
            }
        });
        return total > 0 ? total : 0;
    } catch (e) { return 0; }
};

const getBarHeight = (hoursStr) => {
    const h = getNumericHours(hoursStr);
    return h === 0 ? '0%' : `${Math.min((h / 12) * 100, 100)}%`;
};

const formatDisplayHours = (hoursStr) => {
    const h = getNumericHours(hoursStr);
    return h > 0 ? `${Math.round(h * 10) / 10}` : '-';
};

const formatDate = (date) => {
    if (!date) return '';
    try {
        const d = new Date(date);
        return isNaN(d.getTime()) ? 'N/A' : d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch (e) { return 'N/A'; }
};
</script>

<template>
    <Head title="Planning & Équipe — Superviseur" />
    <SupLayout>
        <!-- Header -->
        <template #header>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Mon Planning & Équipe</h1>
                <p class="text-gray-500 text-sm mt-1">Campagne : {{ currentSupervisor?.campaign }}</p>
            </div>
        </template>

        <div class="space-y-8 pb-20">
            <!-- 4 cartes de statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ stats?.total_people }}</p>
                        <p class="text-[11px] text-gray-400 font-medium">Personnes</p>
                    </div>
                </div>
                <!-- ... autres cartes de stats ... -->
            </div>

            <!-- Section Mon planning personnel -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Mon planning personnel
                    </h3>
                    <div v-if="myPlanning" class="flex items-center gap-3">
                        <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border', getStatusBadge(myPlanning.status).color]">
                            {{ getStatusBadge(myPlanning.status).label }}
                        </span>
                        <span class="text-[11px] text-gray-400 font-medium">Du {{ formatDate(myPlanning.start_date) }} au {{ formatDate(myPlanning.end_date) }}</span>
                    </div>
                </div>

                <div v-if="myPlanning?.status === 'validé'" class="p-6">
                    <div class="flex items-center justify-between mb-8">
                        <h4 class="text-sm font-bold text-gray-800">{{ myPlanning.planning_model?.name }}</h4>
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100">
                            {{ myPlanning.planning_model?.total_hours }}h / semaine
                        </span>
                    </div>

                    <!-- Grille visuelle des jours -->
                    <div class="grid grid-cols-7 gap-4 items-end h-40 mb-8">
                        <div v-for="day in daysShort" :key="day.key" class="flex flex-col items-center gap-2.5 h-full justify-end">
                            <div class="w-full bg-gray-100 rounded-t-lg relative overflow-hidden h-32 flex flex-col justify-end">
                                <div class="w-full bg-blue-500 transition-all duration-500" 
                                     :style="{ height: getBarHeight(myPlanning.planning_model?.[day.key]) }">
                                </div>
                            </div>
                            <div class="text-[11px] font-bold text-gray-800">{{ formatDisplayHours(myPlanning.planning_model?.[day.key]) }}</div>
                            <div class="text-[10px] font-medium text-gray-400">{{ day.label }}</div>
                        </div>
                    </div>
                </div>

                <div v-else class="p-12 text-center">
                    <p class="text-sm text-gray-400 italic">Aucun planning validé pour le moment.</p>
                </div>
            </div>

            <!-- Section Plannings de l'équipe -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Plannings de mon équipe
                    </h3>
                </div>

                <div class="p-6">
                    <div v-if="teamPlannings.length > 0" class="space-y-4">
                        <div v-for="planning in teamPlannings" :key="planning.id"
                            class="p-4 rounded-xl border border-gray-100 hover:border-gold-200 transition-all">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gold-100 flex items-center justify-center text-gold-600 font-bold text-xs">
                                        {{ planning.tc_name.charAt(0) }}
                                    </div>
                                    <h4 class="text-sm font-bold text-gray-800">{{ planning.tc_name }}</h4>
                                </div>
                                <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold', getStatusBadge(planning.status).color]">
                                    {{ getStatusBadge(planning.status).label }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">{{ planning.planning_model?.name }}</span>
                                <span class="font-bold text-gray-700">{{ planning.total_hours }}h / semaine</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-sm text-gray-400 italic">Aucun planning affecté à votre équipe.</p>
                    </div>
                </div>
            </div>
        </div>
    </SupLayout>
</template>
```

---

## 🎨 FRONTEND - TÉLÉCONSEILLEUR (TC)

### Index.vue (`resources/js/Pages/Tc/Schedule/Index.vue`)

Composant de lecture seule pour afficher le planning personnel du téléconseiller.

```vue
<script setup>
import TcLayout from '@/Layouts/TcLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    currentTelemarketer: Object,  // Infos du TC connecté
    activePlanning: Object,       // Planning actif
    planningModel: Object,        // Détails du modèle
    planningHistory: Array,       // Historique des changements
    pastPlannings: Array,         // Plannings précédents
    message: String               // Message d'erreur éventuel
});

// Mapping des statuts pour les badges (identique au SUP)
const getStatusBadge = (status) => {
    switch (status) {
        case 'validé':
            return { label: 'Validé', color: 'bg-emerald-50 text-emerald-600 border-emerald-100' };
        case 'en attente':
            return { label: 'En attente', color: 'bg-yellow-50 text-yellow-600 border-yellow-100' };
        case 'suspendu':
            return { label: 'Suspendu', color: 'bg-red-50 text-red-600 border-red-100' };
        default:
            return { label: 'Création', color: 'bg-gray-50 text-gray-400 border-gray-100' };
    }
};

const daysShort = [
    { key: 'monday_hours', label: 'Lun' },
    { key: 'tuesday_hours', label: 'Mar' },
    { key: 'wednesday_hours', label: 'Mer' },
    { key: 'thursday_hours', label: 'Jeu' },
    { key: 'friday_hours', label: 'Ven' },
    { key: 'saturday_hours', label: 'Sam' },
    { key: 'sunday_hours', label: 'Dim' },
];

// Utilitaires de formatage (identiques au SUP)
const getNumericHours = (hoursVal) => {
    if (typeof hoursVal === 'number') return hoursVal;
    if (!hoursVal) return 0;
    const hoursStr = String(hoursVal).trim();
    if (hoursStr === '0' || hoursStr === '0h' || hoursStr.toLowerCase() === 'repos') return 0;
    const simpleMatch = hoursStr.match(/^(\d+(\.\d+)?)([hH])?$/);
    if (simpleMatch) return parseFloat(simpleMatch[1]);
    try {
        const ranges = hoursStr.split(/[/,]/);
        let total = 0;
        ranges.forEach(range => {
            const parts = range.match(/(\d{1,2})[:h](\d{2})?\s*-\s*(\d{1,2})[:h](\d{2})?/);
            if (parts) {
                const startH = parseInt(parts[1]);
                const startM = parts[2] ? parseInt(parts[2]) : 0;
                const endH = parseInt(parts[3]);
                const endM = parts[4] ? parseInt(parts[4]) : 0;
                total += (endH + endM/60) - (startH + startM/60);
            }
        });
        return total > 0 ? total : 0;
    } catch (e) { return 0; }
};

const getBarHeight = (hoursStr) => {
    const h = getNumericHours(hoursStr);
    return h === 0 ? '0%' : `${Math.min((h / 12) * 100, 100)}%`;
};

const formatDisplayHours = (hoursStr) => {
    const h = getNumericHours(hoursStr);
    return h > 0 ? `${Math.round(h * 10) / 10}` : '-';
};

const formatDate = (date) => {
    if (!date) return '';
    try {
        const d = new Date(date);
        return isNaN(d.getTime()) ? 'N/A' : d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch (e) { return 'N/A'; }
};

const formatDateTime = (date) => {
    if (!date) return '';
    try {
        const d = new Date(date);
        return isNaN(d.getTime()) ? 'N/A' : d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(',', '');
    } catch (e) { return 'N/A'; }
};
</script>

<template>
    <Head title="Mon Planning — Téléconseiller" />
    <TcLayout>
        <!-- Header -->
        <template #header>
            <div class="mb-4">
                <h1 class="text-2xl font-bold text-gray-800">Mon Planning</h1>
                <p class="text-gray-500 text-sm mt-1">{{ currentTelemarketer?.campaign }}</p>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Card informations personnelles -->
            <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm flex items-center gap-5">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] text-gray-400 font-medium">Téléconseiller</p>
                    <h2 class="text-base font-bold text-gray-800">{{ currentTelemarketer?.name }}</h2>
                    <p class="text-[11px] text-gray-400">Superviseur: {{ currentTelemarketer?.supervisor }}</p>
                </div>
            </div>

            <!-- Section Mon planning actuel -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Mon planning actuel
                    </h3>
                    <div v-if="activePlanning" class="flex items-center gap-3">
                        <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border', getStatusBadge(activePlanning.status).color]">
                            {{ getStatusBadge(activePlanning.status).label }}
                        </span>
                        <span class="text-[11px] text-gray-400 font-medium">Du {{ formatDate(activePlanning.start_date) }} au {{ formatDate(activePlanning.end_date) }}</span>
                    </div>
                </div>

                <div v-if="activePlanning" class="p-6">
                    <div class="flex items-center justify-between mb-8">
                        <h4 class="text-sm font-bold text-gray-800">{{ planningModel?.name }}</h4>
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100">
                            {{ planningModel?.total_hours }}h / semaine
                        </span>
                    </div>

                    <!-- Grille visuelle des jours -->
                    <div class="grid grid-cols-7 gap-4 items-end h-40 mb-8">
                        <div v-for="day in daysShort" :key="day.key" class="flex flex-col items-center gap-2.5 h-full justify-end">
                            <div class="w-full bg-gray-100 rounded-t-lg relative overflow-hidden h-32 flex flex-col justify-end">
                                <div class="w-full bg-blue-500 transition-all duration-500" 
                                     :style="{ height: getBarHeight(planningModel?.[day.key]) }">
                                </div>
                            </div>
                            <div class="text-[11px] font-bold text-gray-800">{{ formatDisplayHours(planningModel?.[day.key]) }}</div>
                            <div class="text-[10px] font-medium text-gray-400">{{ day.label }}</div>
                        </div>
                    </div>

                    <!-- Message de validation -->
                    <div v-if="activePlanning.status === 'validé'" class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 flex items-center gap-3 text-emerald-800">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs font-semibold text-emerald-700">Planning validé le {{ formatDateTime(activePlanning.updated_at) }} — Vous pouvez saisir vos heures.</p>
                    </div>
                </div>

                <div v-else class="p-12 text-center">
                    <p class="text-sm text-gray-400 italic">Aucun planning actif pour le moment.</p>
                </div>
            </div>

            <!-- Historique du planning actuel -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Historique des changements
                    </h3>
                </div>

                <div class="p-6">
                    <div v-if="planningHistory.length > 0" class="space-y-3">
                        <div v-for="history in planningHistory" :key="history.id" 
                            class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-gray-700">{{ history.changed_by_name }}</span>
                                <span class="text-[10px] text-gray-400">{{ formatDateTime(history.created_at) }}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                <span :class="history.old_status ? 'text-gray-400 line-through' : 'text-gray-400'">
                                    {{ history.old_status || 'Initial' }}
                                </span>
                                <span class="mx-2 text-gray-400">→</span>
                                <span class="font-bold text-emerald-600">{{ history.new_status }}</span>
                            </div>
                            <div v-if="history.reason" class="text-[10px] text-gray-500 italic mt-1">
                                "{{ history.reason }}"
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <p class="text-sm text-gray-400 italic">Aucun historique disponible.</p>
                    </div>
                </div>
            </div>

            <!-- Plannings précédents -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Plannings précédents
                    </h3>
                </div>

                <div class="p-6">
                    <div v-if="pastPlannings.length > 0" class="space-y-3">
                        <div v-for="planning in pastPlannings" :key="planning.id"
                            class="p-4 rounded-lg border border-gray-100 hover:border-gray-200 transition-all">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">{{ planning.planning_model?.name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Du {{ formatDate(planning.start_date) }} au {{ formatDate(planning.end_date) }}
                                    </p>
                                </div>
                                <span class="text-xs font-bold text-gray-600">{{ planning.planning_model?.total_hours }}h</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <p class="text-sm text-gray-400 italic">Aucun planning précédent.</p>
                    </div>
                </div>
            </div>
        </div>
    </TcLayout>
</template>
```

---

## 📝 RÉSUMÉ DES FICHIERS DOCUMENTÉS

### Backend (Laravel/PHP)
- ✅ Modèles : `PlanningModel.php`, `PlanningAssignment.php`, `PlanningHistorys.php`
- ✅ Migrations : 4 fichiers de migration
- ✅ Controllers : `PlanningModelController.php`, `PlanningAssignmentController.php`, `CpPlanningAssignmentController.php`, `SupPlanningController.php`, `TcPlanningController.php`, `PlanningHistoryController.php`

### Frontend (Vue.js)
- ✅ Admin : `CreateTemplate.vue`, `Schedules.vue`, `AssignSchedule.vue`, `Validation.vue`
- ⚠️ Admin (à documenter) : `EditTemplate.vue`, `History.vue`
- ⚠️ CP : `Templates.vue`, `CreateTemplate.vue`, `AssignSup.vue`, `AssignTc.vue`, `Validation.vue`, `History.vue` (similaires à Admin)
- ✅ Superviseur : `Index.vue`
- ✅ Téléconseiller : `Index.vue`

---

## 🎯 POINTS CLÉS À RETENIR

1. **Hiérarchie des rôles** : Admin (accès total) → CP (périmètre campagne) → SUP (lecture équipe) → TC (lecture perso)

2. **Flux de validation** : En attente → Validé → Suspendu → En attente

3. **Historisation automatique** : Chaque changement de statut crée une entrée dans `planning_histories`

4. **Calcul automatique** : Le total des heures est calculé côté backend lors de la création/modification

5. **Frontend réactif** : Utilisation de Vue 3 Composition API avec computed properties pour les calculs en temps réel

6. **Pagination** : Gérée côté backend avec Laravel et affichée côté frontend

7. **Filtres** : Recherche avec debounce (300ms) pour éviter les requêtes excessives

---

**Fin de la documentation du système de planning**