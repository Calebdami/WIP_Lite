<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
    employees: Object,
    planningModels: Array,
    stats: Object,
    filters: Object,
});

// État du formulaire
const selectedEmployees = ref([]);
const selectedPlanningModel = ref('');
const startDate = ref(new Date().toISOString().split('T')[0]);
const endDate = ref('');
const action = ref('assign'); // 'assign', 'replace', 'extend'
const search = ref(props.filters?.search || '');
const showDetailsModal = ref(false);
const selectedEmployeeDetails = ref(null);

// État pour les checkboxes
const selectAll = ref(false);

// Form pour l'envoi
const form = useForm({
    employee_ids: [],
    planning_model_id: '',
    start_date: '',
    end_date: '',
    action: 'assign',
});

// Computed properties
const filteredEmployees = computed(() => {
    let employees = props.employees.data;
    
    // Filtrage par recherche
    if (search.value) {
        employees = employees.filter(employee => 
            employee.first_name.toLowerCase().includes(search.value.toLowerCase()) ||
            employee.last_name.toLowerCase().includes(search.value.toLowerCase()) ||
            employee.matricule.toLowerCase().includes(search.value.toLowerCase())
        );
    }
    
    return employees;
});

const selectedPlanningModelDetails = computed(() => {
    return props.planningModels.find(model => model.id == selectedPlanningModel.value);
});

const canSubmit = computed(() => {
    return selectedEmployees.value.length > 0 && 
           selectedPlanningModel.value && 
           startDate.value &&
           !form.processing;
});

// Watchers
watch(selectAll, (newValue) => {
    if (newValue) {
        selectedEmployees.value = filteredEmployees.value.map(emp => emp.id);
    } else {
        selectedEmployees.value = [];
    }
});

watch(selectedEmployees, (newValue) => {
    if (newValue.length === 0) {
        selectAll.value = false;
    } else if (newValue.length === filteredEmployees.value.length) {
        selectAll.value = true;
    } else {
        selectAll.value = false;
    }
});

// Fonctions
const triggerSearch = () => {
    const currentUrl = window.location.pathname;
    const params = new URLSearchParams(window.location.search);
    
    if (search.value) {
        params.set('search', search.value);
    } else {
        params.delete('search');
    }
    
    const newUrl = currentUrl + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
};

const handleSearchKeydown = (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        triggerSearch();
    }
};

const submitForm = () => {
    form.employee_ids = selectedEmployees.value;
    form.planning_model_id = selectedPlanningModel.value;
    form.start_date = startDate.value;
    form.end_date = endDate.value || null;
    form.action = action.value;
    
    form.post(route('admin.plannings.bulk.store'), {
        onSuccess: () => {
            selectedEmployees.value = [];
            selectAll.value = false;
            selectedPlanningModel.value = '';
            endDate.value = '';
        }
    });
};

const showEmployeeDetails = async (employee) => {
    try {
        const response = await fetch(`/admin/plannings/bulk/employee-details?employee_id=${employee.id}`);
        const data = await response.json();
        selectedEmployeeDetails.value = data;
        showDetailsModal.value = true;
    } catch (error) {
        console.error('Erreur lors de la récupération des détails:', error);
    }
};

const getStatusClass = (status) => {
    if (!status) return 'bg-gray-100 text-gray-600';
    switch (status.toLowerCase()) {
        case 'en attente': return 'bg-amber-100 text-amber-700';
        case 'validé': return 'bg-emerald-100 text-emerald-700';
        case 'suspendu': return 'bg-red-100 text-red-700';
        case 'actif': return 'bg-emerald-100 text-emerald-700';
        case 'clôturé': return 'bg-blue-100 text-blue-700';
        default: return 'bg-gray-100 text-gray-600';
    }
};

const formatStatus = (status) => {
    if (!status) return 'Inconnu';
    const statusMap = {
        'en attente': 'En attente',
        'validé': 'Validé',
        'suspendu': 'Suspendu',
        'actif': 'Actif',
        'clôturé': 'Clôturé'
    };
    return statusMap[status.toLowerCase()] || status;
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const deletePlanning = (planningId) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce planning ?')) {
        router.delete(`/admin/plannings/bulk/${planningId}`, {
            onSuccess: () => {
                // Recharger la page pour mettre à jour les données
                window.location.reload();
            }
        });
    }
};
</script>

<template>
    <Head title="Affectation Multiple de Plannings" />

    <AdminLayout>
        <div class="space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-charcoal-900">Affectation Multiple de Plannings</h1>
                    <p class="text-charcoal-500 mt-2">Affectez des plannings à plusieurs employés simultanément</p>
                </div>
                <Link :href="route('admin.assignments.validation')" 
                    class="px-6 py-3 bg-gold-gradient text-white rounded-xl font-black text-xs uppercase tracking-widest shadow-gold-premium hover:opacity-90 transition-all duration-200">
                    <i class="pi pi-check-circle mr-2"></i>
                    Validation Plannings
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-3xl border border-pearl-100 p-6 shadow-premium flex items-center gap-4 hover:shadow-gold-premium transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-charcoal-50 text-charcoal-500 flex items-center justify-center">
                        <i class="pi pi-users text-lg"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-charcoal-700">{{ stats?.total_employees || 0 }}</div>
                        <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Total Employés</div>
                    </div>
                </div>
                <div class="bg-white rounded-3xl border border-pearl-100 p-6 shadow-premium flex items-center gap-4 hover:shadow-gold-premium transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                        <i class="pi pi-calendar text-lg"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-charcoal-700">{{ stats?.employees_with_planning || 0 }}</div>
                        <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Avec Planning</div>
                    </div>
                </div>
                <div class="bg-white rounded-3xl border border-pearl-100 p-6 shadow-premium flex items-center gap-4 hover:shadow-gold-premium transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center">
                        <i class="pi pi-clock text-lg"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-charcoal-700">{{ stats?.pending_validations || 0 }}</div>
                        <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">En Attente</div>
                    </div>
                </div>
                <div class="bg-white rounded-3xl border border-pearl-100 p-6 shadow-premium flex items-center gap-4 hover:shadow-gold-premium transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center">
                        <i class="pi pi-list text-lg"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-charcoal-700">{{ stats?.total_planning_models || 0 }}</div>
                        <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Modèles</div>
                    </div>
                </div>
            </div>

            <!-- Formulaire d'affectation -->
            <div class="bg-white rounded-3xl border border-pearl-100 shadow-premium p-8">
                <h2 class="text-xl font-black text-charcoal-900 mb-6">Configuration de l'Affectation</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Modèle de planning -->
                    <div>
                        <label class="block text-sm font-bold text-charcoal-700 mb-2">Modèle de Planning</label>
                        <select v-model="selectedPlanningModel" 
                            class="w-full px-4 py-3 bg-pearl-50 border-pearl-200 rounded-xl text-sm transition-all duration-200 focus:border-gold-500 focus:ring-4 focus:ring-gold-500/15 focus:bg-white outline-none">
                            <option value="">Sélectionner un modèle</option>
                            <option v-for="model in planningModels" :key="model.id" :value="model.id">
                                {{ model.name }} ({{ model.total_hours }}h)
                            </option>
                        </select>
                    </div>

                    <!-- Date de début -->
                    <div>
                        <label class="block text-sm font-bold text-charcoal-700 mb-2">Date de Début</label>
                        <input v-model="startDate" type="date" 
                            class="w-full px-4 py-3 bg-pearl-50 border-pearl-200 rounded-xl text-sm transition-all duration-200 focus:border-gold-500 focus:ring-4 focus:ring-gold-500/15 focus:bg-white outline-none">
                    </div>

                    <!-- Date de fin -->
                    <div>
                        <label class="block text-sm font-bold text-charcoal-700 mb-2">Date de Fin (optionnel)</label>
                        <input v-model="endDate" type="date" 
                            class="w-full px-4 py-3 bg-pearl-50 border-pearl-200 rounded-xl text-sm transition-all duration-200 focus:border-gold-500 focus:ring-4 focus:ring-gold-500/15 focus:bg-white outline-none">
                    </div>

                    <!-- Action -->
                    <div>
                        <label class="block text-sm font-bold text-charcoal-700 mb-2">Action</label>
                        <select v-model="action" 
                            class="w-full px-4 py-3 bg-pearl-50 border-pearl-200 rounded-xl text-sm transition-all duration-200 focus:border-gold-500 focus:ring-4 focus:ring-gold-500/15 focus:bg-white outline-none">
                            <option value="assign">Nouvelle affectation</option>
                            <option value="replace">Remplacer existant</option>
                            <option value="extend">Étendre/Modifier</option>
                        </select>
                    </div>
                </div>

                <!-- Détails du modèle sélectionné -->
                <div v-if="selectedPlanningModelDetails" class="mb-6 p-4 bg-pearl-50 rounded-2xl border border-pearl-200">
                    <h3 class="text-sm font-bold text-charcoal-700 mb-3">Détails du Modèle</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-charcoal-500">Total heures:</span>
                            <span class="ml-2 font-bold text-charcoal-700">{{ selectedPlanningModelDetails.total_hours }}h</span>
                        </div>
                        <div>
                            <span class="text-charcoal-500">Lundi:</span>
                            <span class="ml-2 font-bold text-charcoal-700">{{ selectedPlanningModelDetails.monday_hours }}h</span>
                        </div>
                        <div>
                            <span class="text-charcoal-500">Mardi:</span>
                            <span class="ml-2 font-bold text-charcoal-700">{{ selectedPlanningModelDetails.tuesday_hours }}h</span>
                        </div>
                        <div>
                            <span class="text-charcoal-500">Mercredi:</span>
                            <span class="ml-2 font-bold text-charcoal-700">{{ selectedPlanningModelDetails.wednesday_hours }}h</span>
                        </div>
                        <div>
                            <span class="text-charcoal-500">Jeudi:</span>
                            <span class="ml-2 font-bold text-charcoal-700">{{ selectedPlanningModelDetails.thursday_hours }}h</span>
                        </div>
                        <div>
                            <span class="text-charcoal-500">Vendredi:</span>
                            <span class="ml-2 font-bold text-charcoal-700">{{ selectedPlanningModelDetails.friday_hours }}h</span>
                        </div>
                        <div>
                            <span class="text-charcoal-500">Samedi:</span>
                            <span class="ml-2 font-bold text-charcoal-700">{{ selectedPlanningModelDetails.saturday_hours }}h</span>
                        </div>
                        <div>
                            <span class="text-charcoal-500">Dimanche:</span>
                            <span class="ml-2 font-bold text-charcoal-700">{{ selectedPlanningModelDetails.sunday_hours }}h</span>
                        </div>
                    </div>
                    <p v-if="selectedPlanningModelDetails.description" class="mt-3 text-xs text-charcoal-600 italic">
                        {{ selectedPlanningModelDetails.description }}
                    </p>
                </div>

                <!-- Bouton de soumission -->
                <div class="flex justify-between items-center">
                    <div class="text-sm text-charcoal-600">
                        <span class="font-bold">{{ selectedEmployees.length }}</span> employé(s) sélectionné(s)
                    </div>
                    <button @click="submitForm" 
                        :disabled="!canSubmit"
                        class="px-8 py-3 bg-gold-gradient text-white rounded-xl font-black text-xs uppercase tracking-widest shadow-gold-premium hover:opacity-90 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="pi pi-check mr-2"></i>
                        {{ form.processing ? 'Traitement...' : `Affecter Planning${selectedEmployees.length > 1 ? 's' : ''}` }}
                    </button>
                </div>
            </div>

            <!-- Barre de recherche -->
            <div class="bg-white rounded-3xl border border-pearl-100 shadow-premium p-6">
                <div class="relative max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                        <i class="pi pi-search text-xs"></i>
                    </span>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Rechercher (Nom, matricule...)" 
                        @keydown="handleSearchKeydown"
                        class="block w-full pl-10 pr-20 py-3 bg-pearl-50 border-pearl-200 rounded-xl text-sm transition-all duration-200 focus:border-gold-500 focus:ring-4 focus:ring-gold-500/15 focus:bg-white outline-none"
                    />
                    <button @click="triggerSearch"
                        class="absolute inset-y-0 right-0 px-4 bg-gold-gradient text-white rounded-r-xl font-black text-xs uppercase tracking-widest shadow-gold-premium hover:opacity-90 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Liste des employés -->
            <div class="bg-white rounded-3xl border border-pearl-100 shadow-premium overflow-hidden">
                <div class="p-6 border-b border-pearl-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-black text-charcoal-900">Liste des Employés</h2>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="selectAll" 
                                class="w-5 h-5 text-gold-500 border-pearl-200 rounded focus:ring-gold-500">
                            <span class="text-sm font-bold text-charcoal-700">Tout sélectionner</span>
                        </label>
                    </div>
                </div>

                <div v-if="filteredEmployees.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-pearl-50 border-b border-pearl-200">
                            <tr>
                                <th class="py-4 px-6 text-left">
                                    <input type="checkbox" v-model="selectAll" 
                                        class="w-5 h-5 text-gold-500 border-pearl-200 rounded focus:ring-gold-500">
                                </th>
                                <th class="py-4 px-6 text-left font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Employé</th>
                                <th class="py-4 px-6 text-left font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Position</th>
                                <th class="py-4 px-6 text-left font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Campagne</th>
                                <th class="py-4 px-6 text-left font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Planning Actuel</th>
                                <th class="py-4 px-6 text-center font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-pearl-50">
                            <tr v-for="employee in filteredEmployees" :key="employee.id" 
                                :class="{ 'bg-gold-50': selectedEmployees.includes(employee.id) }"
                                class="hover:bg-pearl-50 transition-all duration-200">
                                <td class="py-4 px-6">
                                    <input type="checkbox" :value="employee.id" v-model="selectedEmployees"
                                        class="w-5 h-5 text-gold-500 border-pearl-200 rounded focus:ring-gold-500">
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-charcoal-700 uppercase">
                                            {{ employee.first_name }} {{ employee.last_name }}
                                        </span>
                                        <span class="text-[10px] font-black text-gold-500 tracking-tighter">
                                            {{ employee.matricule }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-3 py-1 text-[10px] font-bold rounded-xl bg-pearl-100 text-charcoal-700">
                                        {{ employee.position?.name || '—' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div v-if="employee.assignments && employee.assignments.length > 0">
                                        <div class="text-sm font-bold text-charcoal-600">
                                            {{ employee.assignments[0].campaign?.name }}
                                        </div>
                                        <div v-if="employee.assignments[0].manager" class="text-xs text-charcoal-400">
                                            Manager: {{ employee.assignments[0].manager.employee?.first_name }} {{ employee.assignments[0].manager.employee?.last_name }}
                                        </div>
                                    </div>
                                    <span v-else class="text-charcoal-400">—</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div v-if="employee.planning_assignments && employee.planning_assignments.length > 0" class="space-y-2">
                                        <div v-for="planning in employee.planning_assignments" :key="planning.id" 
                                            class="flex items-center justify-between">
                                            <div>
                                                <div class="text-sm font-bold text-charcoal-600">
                                                    {{ planning.planning_model?.name }}
                                                </div>
                                                <div class="text-xs text-charcoal-400">
                                                    {{ formatDate(planning.start_date) }} - {{ formatDate(planning.end_date) }}
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 text-[8px] font-bold rounded-lg" :class="getStatusClass(planning.status)">
                                                {{ formatStatus(planning.status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <span v-else class="text-charcoal-400 italic">Aucun planning</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <button @click="showEmployeeDetails(employee)"
                                        class="px-3 py-2 bg-pearl-50 text-charcoal-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gold-gradient hover:text-white transition-all duration-200 border border-pearl-200 mr-2">
                                        <i class="pi pi-eye mr-1"></i>
                                        Détails
                                    </button>
                                    <button v-if="employee.planning_assignments && employee.planning_assignments.length > 0" 
                                        @click="deletePlanning(employee.planning_assignments[0].id)"
                                        class="px-3 py-2 bg-red-50 text-red-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-red-600 hover:text-white transition-all duration-200 border border-red-200">
                                        <i class="pi pi-trash mr-1"></i>
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-16 text-charcoal-400">
                    <i class="pi pi-users text-6xl mb-6 opacity-20"></i>
                    <p class="text-lg italic">Aucun employé trouvé.</p>
                </div>

                <!-- Pagination -->
                <div v-if="employees.links.length > 3" class="py-6 border-t border-pearl-100 flex justify-center">
                    <div class="flex gap-3">
                        <Link 
                            v-for="(link, k) in employees.links" 
                            :key="k"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 border"
                            :class="{
                                'bg-gold-gradient text-charcoal-900 border-transparent shadow-gold-premium': link.active,
                                'bg-white text-charcoal-500 border-pearl-200 hover:border-gold-500 hover:text-charcoal-900': !link.active && link.url,
                                'opacity-40 cursor-not-allowed border-pearl-100 text-charcoal-300': !link.url
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Détails Employé -->
        <div v-if="showDetailsModal && selectedEmployeeDetails" 
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showDetailsModal = false">
            <div class="bg-white rounded-3xl border border-pearl-100 shadow-premium p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-charcoal-900">
                        Détails de {{ selectedEmployeeDetails.first_name }} {{ selectedEmployeeDetails.last_name }}
                    </h3>
                    <button @click="showDetailsModal = false" 
                        class="w-10 h-10 rounded-full bg-pearl-100 text-charcoal-500 hover:bg-charcoal-100 hover:text-charcoal-700 transition-all">
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informations personnelles -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-black text-charcoal-800">Informations Personnelles</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-charcoal-500">Matricule:</span>
                                <span class="font-bold text-charcoal-700">{{ selectedEmployeeDetails.matricule }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-charcoal-500">Position:</span>
                                <span class="font-bold text-charcoal-700">{{ selectedEmployeeDetails.position?.name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-charcoal-500">Statut:</span>
                                <span class="font-bold text-charcoal-700">{{ selectedEmployeeDetails.status }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Affectations -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-black text-charcoal-800">Affectations Actives</h4>
                        <div v-if="selectedEmployeeDetails.assignments && selectedEmployeeDetails.assignments.length > 0" class="space-y-2">
                            <div v-for="assignment in selectedEmployeeDetails.assignments" :key="assignment.id" 
                                class="p-3 bg-pearl-50 rounded-xl">
                                <div class="font-bold text-charcoal-700">{{ assignment.campaign?.name }}</div>
                                <div class="text-sm text-charcoal-500">
                                    Manager: {{ assignment.manager?.employee?.first_name }} {{ assignment.manager?.employee?.last_name }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-charcoal-400 italic">Aucune affectation active</div>
                    </div>

                    <!-- Plannings -->
                    <div class="col-span-2 space-y-4">
                        <h4 class="text-lg font-black text-charcoal-800">Plannings Actuels</h4>
                        <div v-if="selectedEmployeeDetails.planning_assignments && selectedEmployeeDetails.planning_assignments.length > 0" 
                            class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="planning in selectedEmployeeDetails.planning_assignments" :key="planning.id" 
                                class="p-4 bg-pearl-50 rounded-xl border border-pearl-200">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="font-bold text-charcoal-700">{{ planning.planning_model?.name }}</div>
                                        <div class="text-sm text-charcoal-500">{{ planning.planning_model?.total_hours }}h/semaine</div>
                                    </div>
                                    <span class="px-2 py-1 text-[8px] font-bold rounded-lg" :class="getStatusClass(planning.status)">
                                        {{ formatStatus(planning.status) }}
                                    </span>
                                </div>
                                <div class="text-sm text-charcoal-600">
                                    <div>Début: {{ formatDate(planning.start_date) }}</div>
                                    <div>Fin: {{ formatDate(planning.end_date) }}</div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-charcoal-400 italic">Aucun planning actuel</div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
</style>
