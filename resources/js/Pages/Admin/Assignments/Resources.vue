<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    employees: Object, // Renommé de availableEmployees pour correspondre au contrôleur
    allCampaigns: Array,
    allManagers: Array,
    positions: Array,
    filters: Object,
    // Context parameters for auto-filtering
    campaign_id: [String, Number],
    manager_assignment_id: [String, Number],
    position_code: String,
});

const search = ref(props.filters?.search || '');
const showAssignModal = ref(false);
const showReleaseModal = ref(false);
const selectedEmployee = ref(null);
const selectedAssignment = ref(null);
const releaseCascade = ref(true);
const managerSearch = ref('');
const campaignSearch = ref('');

const form = useForm({
    employee_id: '',
    campaign_id: '',
    manager_id: '',
    position_id: '',
    start_date: new Date().toISOString().split('T')[0],
});

// Logic to filter managers based on the selected employee's role
const availableManagers = computed(() => {
    if (!selectedEmployee.value) return [];
    if (!props.allManagers || !Array.isArray(props.allManagers)) return [];

    const role = selectedEmployee.value.position?.code; // CP, SUP, or TC
    let filtered = [];

    if (role === 'CP') filtered = [];
    else if (role === 'SUP') {
        // SUP is managed by a CP
        filtered = props.allManagers.filter(a => a.position?.code === 'CP');
    } else if (role === 'TC') {
        // TC is managed by a SUP
        filtered = props.allManagers.filter(a => a.position?.code === 'SUP');
    }

    if (managerSearch.value) {
        const s = managerSearch.value.toLowerCase();
        filtered = filtered.filter(m =>
            m.employee?.first_name.toLowerCase().includes(s) ||
            m.employee?.last_name.toLowerCase().includes(s) ||
            m.campaign?.name.toLowerCase().includes(s)
        );
    }

    return filtered;
});

const filteredCampaigns = computed(() => {
    if (!props.allCampaigns || !Array.isArray(props.allCampaigns)) return [];
    let campaigns = props.allCampaigns;

    // Règle : Si c'est un CP, on retire les campagnes où il est déjà affecté
    if (selectedEmployee.value?.position?.code === 'CP') {
        if (!props.allManagers || !Array.isArray(props.allManagers)) return [];
        const alreadyAssignedIds = props.allManagers
            .filter(a => a.employee_id === selectedEmployee.value.id)
            .map(a => a.campaign_id);

        campaigns = campaigns.filter(c => !alreadyAssignedIds.includes(c.id));
    }

    if (!campaignSearch.value) return campaigns;
    const s = campaignSearch.value.toLowerCase();
    return campaigns.filter(c => c.name.toLowerCase().includes(s));
});

// Filter employees based on context parameters
const filteredAvailableEmployees = computed(() => {
    if (!props.employees || !props.employees.data || !Array.isArray(props.employees.data)) return [];
    let employees = props.employees.data;
    
    // Filter by position code if provided
    if (props.position_code) {
        employees = employees.filter(emp => emp.position?.code === props.position_code);
    }
    
    // Filter by search
    if (search.value) {
        const s = search.value.toLowerCase();
        employees = employees.filter(emp =>
            emp.first_name.toLowerCase().includes(s) ||
            emp.last_name.toLowerCase().includes(s) ||
            emp.matricule.toLowerCase().includes(s)
        );
    }
    
    return employees;
});

const openAssignModal = (emp) => {
    // Force a complete reset of the form to avoid any data leakage
    form.clearErrors();
    form.employee_id = emp.id;
    form.position_id = emp.position_id;
    form.campaign_id = props.campaign_id || '';
    
    // Pre-select manager if context provides one
    if (props.manager_assignment_id && ['SUP', 'TC'].includes(emp.position?.code)) {
        form.manager_id = props.manager_assignment_id;
    } else {
        form.manager_id = '';
    }

    selectedEmployee.value = emp;
    managerSearch.value = '';
    campaignSearch.value = '';
    showAssignModal.value = true;
};

const openReleaseModal = (assignment) => {
    selectedAssignment.value = assignment;
    releaseCascade.value = true;
    showReleaseModal.value = true;
};

const submitAssignment = () => {
    form.post(route('admin.assignments.store'), {
        onSuccess: () => {
            showAssignModal.value = false;
            form.reset();
            // Redirect to structure page after successful assignment
            router.get(route('admin.assignments.structure'));
        }
    });
};

// Automatic campaign inheritance for TC/SUP when selecting a manager
watch(() => form.manager_id, (newManagerId) => {
    if (newManagerId && ['TC', 'SUP'].includes(selectedEmployee.value?.position?.code)) {
        const managerAssignment = props.allManagers.find(a => a.id == newManagerId);
        if (managerAssignment) {
            form.campaign_id = managerAssignment.campaign_id;
        }
    }
});

const confirmRelease = () => {
    router.patch(route('admin.assignments.release', selectedAssignment.value.id), {
        cascade: releaseCascade.value
    }, {
        onSuccess: () => showReleaseModal.value = false
    });
};

const updateSearch = () => {
    router.get(route('admin.assignments.resources'), {
        search: search.value,
        campaign_id: props.campaign_id,
        manager_assignment_id: props.manager_assignment_id,
        position_code: props.position_code,
    }, {
        preserveState: true,
        replace: true,
    });
};

const triggerSearch = () => {
    if (search.value === '' || search.value.length > 2) {
        updateSearch();
    }
};

// Handle Enter key press
const handleSearchKeydown = (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        triggerSearch();
    }
};

const getEmployeeStatus = (employee) => {
    if (!employee.assignments || employee.assignments.length === 0) {
        return 'Disponible'
    }
    
    // Si l'employé a des affectations
    if (employee.position.code === 'CP') {
        return 'Disponible (multi-campagnes)'
    } else {
        return 'Non disponible'
    }
};

const getEmployeeStatusClass = (employee) => {
    if (!employee.assignments || employee.assignments.length === 0) {
        return 'text-green-600'
    }
    
    // Si l'employé a des affectations
    if (employee.position.code === 'CP') {
        return 'text-yellow-600'
    } else {
        return 'text-red-600'
    }
};
</script>

<template>
    <Head title="Ressources Disponibles — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Ressources Disponibles</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Affectez les employés disponibles aux campagnes et responsables</p>
                </div>

                <div class="flex flex-1 max-w-2xl items-center gap-3">
                    <!-- Search Bar -->
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-charcoal-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input v-model="search" type="text" placeholder="Rechercher une ressource..."
                            @keydown="handleSearchKeydown"
                            class="block w-full pl-12 pr-20 py-3 border border-pearl-200 rounded-xl text-sm focus:ring-gold-500 focus:border-gold-500 bg-white placeholder-charcoal-400" />
                        <button @click="triggerSearch"
                            class="absolute inset-y-0 right-0 px-4 bg-gold-gradient text-white rounded-r-xl hover:bg-gold-700 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Structure Link -->
                    <Link :href="route('admin.assignments.structure')"
                        class="inline-flex items-center px-4 py-2 bg-charcoal-900 text-white text-xs font-black uppercase tracking-wider rounded-xl hover:bg-gold-600 transition-all shrink-0">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.172-1.172a4 4 0 115.656 5.656L13.828 15.828a2 2 0 01-2.828 0l-1.172-1.172" />
                        </svg>
                        Structure
                    </Link>

                    <!-- Bulk Assign Button -->
                    <Link :href="route('admin.assignments.bulk-assign')"
                        class="inline-flex items-center px-4 py-2 bg-gold-gradient text-white text-xs font-black uppercase tracking-wider rounded-xl hover:shadow-lg transition-all shrink-0">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Affectation Multiple
                    </Link>
                </div>
            </div>
        </template>

        <!-- Context Information -->
        <div v-if="campaign_id || manager_assignment_id || position_code" class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-amber-800">
                    <span class="font-bold">Contexte d'affectation actif:</span>
                    <span v-if="position_code"> Position: {{ position_code }}</span>
                    <span v-if="campaign_id"> Campagne #{{ campaign_id }}</span>
                    <span v-if="manager_assignment_id"> Responsable #{{ manager_assignment_id }}</span>
                </div>
                <Link :href="route('admin.assignments.resources')" class="text-amber-600 hover:text-amber-800 text-sm font-bold">
                    Effacer le contexte
                </Link>
            </div>
        </div>

        <!-- Available Resources Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="emp in filteredAvailableEmployees" :key="emp.id"
                class="bg-white rounded-2xl border border-pearl-200 p-5 shadow-sm hover:shadow-xl transition-all group overflow-hidden relative">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-10 h-10 rounded-xl bg-pearl-100 flex items-center justify-center text-charcoal-700 font-black border border-pearl-200 group-hover:bg-gold-100 group-hover:text-gold-700 transition-colors">
                        {{ emp.first_name.charAt(0) }}{{ emp.last_name.charAt(0) }}
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-charcoal-800 tracking-tight">{{ emp.first_name }} {{
                            emp.last_name }}</h3>
                        <p class="text-[9px] text-gold-600 font-bold uppercase tracking-widest">{{
                            emp.position?.name }}</p>
                    </div>
                </div>
                <div class="space-y-2 mb-6 bg-pearl-50/50 p-3 rounded-lg border border-pearl-50">
                    <div class="flex justify-between text-[9px] font-bold">
                        <span class="text-charcoal-400 uppercase">Matricule</span>
                        <span class="text-charcoal-700">{{ emp.matricule }}</span>
                    </div>
                    <div class="flex justify-between text-[9px] font-bold">
                        <span class="text-charcoal-400 uppercase">Embauche</span>
                        <span class="text-charcoal-700">{{ new Date(emp.hire_date).toLocaleDateString('fr-FR')
                            }}</span>
                    </div>
                    <div class="flex justify-between text-[9px] font-bold">
                        <span class="text-charcoal-400 uppercase">Statut</span>
                        <span :class="getEmployeeStatusClass(emp)" class="font-bold">{{ getEmployeeStatus(emp) }}</span>
                    </div>
                </div>
                <button @click="openAssignModal(emp)"
                    class="w-full py-2 bg-charcoal-900 text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-gold-600 transition-all shadow-md active:scale-95">
                    Affecter
                </button>
            </div>
        </div>

        <!-- Available Employees Pagination -->
        <div v-if="employees.links.length > 3" class="mt-8 flex justify-center">
            <div class="flex gap-1 bg-white p-1 rounded-xl border border-pearl-200 shadow-sm">
                <Link v-for="link in employees.links" :key="link.label" :href="link.url || '#'"
                    v-html="link.label" class="px-4 py-2 rounded-lg text-xs font-bold transition-all" :class="{
                        'bg-gold-gradient text-charcoal-900 shadow-md': link.active,
                        'text-charcoal-500 hover:bg-pearl-50': !link.active && link.url,
                        'opacity-30 cursor-not-allowed': !link.url
                    }" />
            </div>
        </div>

        <div v-if="filteredAvailableEmployees.length === 0"
            class="text-center py-20 bg-white rounded-2xl border border-pearl-200">
            <p class="text-charcoal-400 text-sm italic">Aucune ressource disponible pour le moment.</p>
        </div>

        <!-- Assign Modal -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showAssignModal = false"></div>
                    <div
                        class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-pearl-200 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-xl font-black text-charcoal-800">Affecter un employé</h3>
                            <button @click="showAssignModal = false"
                                class="p-2 hover:bg-pearl-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-charcoal-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Employee Info -->
                        <div class="bg-pearl-50 rounded-xl p-4 mb-6 border border-pearl-100">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-xl bg-gold-gradient flex items-center justify-center text-charcoal-900 font-bold text-lg shadow-md">
                                    {{ selectedEmployee?.first_name?.charAt(0) }}{{ selectedEmployee?.last_name?.charAt(0) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-charcoal-800">{{ selectedEmployee?.first_name }}
                                        {{ selectedEmployee?.last_name }}</h4>
                                    <p class="text-xs text-charcoal-400 font-bold uppercase">{{ selectedEmployee?.position?.name }}
                                        ({{ selectedEmployee?.matricule }})</p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submitAssignment" class="space-y-6">
                            <!-- Campaign Selection -->
                            <div>
                                <label class="block text-xs font-black text-charcoal-400 uppercase tracking-wider mb-2">Campagne
                                    *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <input v-model="campaignSearch" type="text" placeholder="Filtrer les campagnes..."
                                        class="w-full border border-pearl-200 rounded-lg pl-9 pr-3 py-2 text-[10px] focus:ring-gold-500 focus:border-gold-500 italic">
                                </div>
                                <select v-model="form.campaign_id"
                                    class="mt-2 w-full border border-pearl-200 rounded-lg px-3 py-2 text-xs focus:ring-gold-500 focus:border-gold-500">
                                    <option value="">Sélectionner une campagne...</option>
                                    <option v-for="campaign in filteredCampaigns" :key="campaign.id" :value="campaign.id">
                                        {{ campaign.name }}
                                    </option>
                                </select>
                                <small v-if="form.errors.campaign_id" class="text-red-500 text-[9px] font-bold">{{
                                    form.errors.campaign_id }}</small>
                            </div>

                            <!-- Manager Selection (for SUP/TC) -->
                            <div v-if="['SUP', 'TC'].includes(selectedEmployee?.position?.code)">
                                <label class="block text-xs font-black text-charcoal-400 uppercase tracking-wider mb-2">Responsable
                                    hiérarchique *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <input v-model="managerSearch" type="text" placeholder="Filtrer les responsables..."
                                        class="w-full border border-pearl-200 rounded-lg pl-9 pr-3 py-2 text-[10px] focus:ring-gold-500 focus:border-gold-500 italic">
                                </div>
                                <select v-model="form.manager_id"
                                    class="mt-2 w-full border border-pearl-200 rounded-lg px-3 py-2 text-xs focus:ring-gold-500 focus:border-gold-500">
                                    <option value="">Sélectionner un responsable...</option>
                                    <option v-for="manager in availableManagers" :key="manager.id" :value="manager.id">
                                        {{ manager.employee?.first_name }} {{ manager.employee?.last_name }} -
                                        {{ manager.campaign?.name }}
                                    </option>
                                </select>
                                <small v-if="form.errors.manager_id" class="text-red-500 text-[9px] font-bold">{{
                                    form.errors.manager_id }}</small>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label class="block text-xs font-black text-charcoal-400 uppercase tracking-wider mb-2">Date
                                    de début *</label>
                                <input v-model="form.start_date" type="date"
                                    class="w-full border border-pearl-200 rounded-lg px-3 py-2 text-xs focus:ring-gold-500 focus:border-gold-500">
                                <small v-if="form.errors.start_date" class="text-red-500 text-[9px] font-bold">{{
                                    form.errors.start_date }}</small>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-4 pt-4">
                                <button type="submit"
                                    class="flex-1 py-3 bg-gold-gradient text-white rounded-xl text-xs font-black uppercase tracking-widest hover:shadow-lg transition-all disabled:opacity-50"
                                    :disabled="form.processing">
                                    {{ form.processing ? 'Affectation...' : 'Confirmer l\'affectation' }}
                                </button>
                                <button type="button" @click="showAssignModal = false"
                                    class="flex-1 py-3 bg-pearl-100 text-charcoal-700 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-pearl-200 transition-all">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Release Modal -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showReleaseModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showReleaseModal = false"></div>
                    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-pearl-200 p-8">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-rose-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-black text-charcoal-800 mb-2">Confirmer la libération</h3>
                            <p class="text-sm text-charcoal-600">Êtes-vous sûr de vouloir libérer cette affectation ?
                                Cette action est irréversible.</p>
                        </div>

                        <!-- Employee Info -->
                        <div class="bg-pearl-50 rounded-xl p-4 mb-6 border border-pearl-100">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 font-bold">
                                    {{ selectedAssignment?.employee?.first_name?.charAt(0) }}{{ selectedAssignment?.employee?.last_name?.charAt(0) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-charcoal-800">{{ selectedAssignment?.employee?.first_name }}
                                        {{ selectedAssignment?.employee?.last_name }}</h4>
                                    <p class="text-xs text-charcoal-400">{{ selectedAssignment?.position?.name }} -
                                        {{ selectedAssignment?.campaign?.name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cascade Option -->
                        <div class="mb-6">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" v-model="releaseCascade"
                                    class="w-4 h-4 text-gold-600 border-pearl-200 rounded focus:ring-gold-500">
                                <span class="text-sm text-charcoal-700">Libérer également toutes les affectations
                                    subordonnées</span>
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3">
                            <button @click="confirmRelease"
                                class="flex-1 py-3 bg-rose-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-rose-700 transition-all">
                                Confirmer la libération
                            </button>
                            <button @click="showReleaseModal = false"
                                class="flex-1 py-3 bg-pearl-100 text-charcoal-700 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-pearl-200 transition-all">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </AdminLayout>
</template>
