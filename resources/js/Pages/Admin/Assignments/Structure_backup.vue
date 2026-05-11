<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    campaigns: Object,      // Paginated for Hierarchy View
    assignments: Array,    // For Hierarchy Tree
    allCampaigns: Array,   // Full list for Modal
    allManagers: Array,    // Full list for Modal
    availableEmployees: Object,
    positions: Array,
    filters: Object,
    assign: [String, Number], // Optional employee ID to auto-open modal
});

const search = ref(props.filters?.search || '');
const activeTab = ref('hierarchy'); // 'hierarchy' or 'available'
const showAssignModal = ref(false);
const showReleaseModal = ref(false);
const selectedEmployee = ref(null);
const selectedAssignment = ref(null);
const releaseCascade = ref(true);
const managerSearch = ref('');
const campaignSearch = ref('');
const pendingContext = ref(null); // { campaign_id, manager_id, manager_assignment_id }

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
    let campaigns = props.allCampaigns;

    // Règle : Si c'est un CP, on retire les campagnes où il est déjà affecté
    if (selectedEmployee.value?.position?.code === 'CP') {
        const alreadyAssignedIds = props.allManagers
            .filter(a => a.employee_id === selectedEmployee.value.id)
            .map(a => a.campaign_id);

        campaigns = campaigns.filter(c => !alreadyAssignedIds.includes(c.id));
    }

    if (!campaignSearch.value) return campaigns;
    const s = campaignSearch.value.toLowerCase();
    return campaigns.filter(c => c.name.toLowerCase().includes(s));
});

const openAssignModal = (emp) => {
    // Force a complete reset of the form to avoid any data leakage
    form.clearErrors();
    form.employee_id = emp.id;
    form.position_id = emp.position_id;
    form.campaign_id = pendingContext.value?.campaign_id || '';

    // If it's a SUP or TC, and we have a manager assignment ID in context, pre-select it
    if (['SUP', 'TC'].includes(emp.position?.code) && pendingContext.value?.manager_assignment_id) {
        form.manager_id = pendingContext.value.manager_assignment_id;
    } else {
        form.manager_id = '';
    }

    selectedEmployee.value = emp;
    managerSearch.value = '';
    campaignSearch.value = '';
    showAssignModal.value = true;
};

const setPendingContext = (ctx) => {
    pendingContext.value = ctx;
    activeTab.value = 'available';
};

// Filtrage des ressources disponibles par position quand un contexte est actif
const filteredAvailableEmployees = computed(() => {
    if (!pendingContext.value?.position_code) {
        return props.availableEmployees.data;
    }
    return props.availableEmployees.data.filter(
        emp => emp.position?.code === pendingContext.value.position_code
    );
});

const clearPendingContext = () => {
    pendingContext.value = null;
};

// Check for auto-assign on mount
import { onMounted } from 'vue';
onMounted(() => {
    if (props.assign) {
        // Look for the employee in the available list first
        const emp = props.availableEmployees.data.find(e => e.id == props.assign);
        if (emp) {
            openAssignModal(emp);
        } else {
            // If not in available (maybe a CP with assignments), we might need to find them elsewhere
            // For now, if they are a CP and already have assignments, we should ensure they are in the available list (handled by controller)
            // If the search/pagination prevents finding them, the admin can still use the search bar.
        }
    }
});

const openReleaseModal = (assignment) => {
    selectedAssignment.value = assignment;
    releaseCascade.value = true;
    showReleaseModal.value = true;
};

const submitAssignment = () => {
    form.post(route('admin.assignments.store'), {
        onSuccess: () => {
            activeTab.value = 'hierarchy'; // Switch first for immediate feedback
            showAssignModal.value = false;
            form.reset();
            clearPendingContext();
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
    router.get(route('admin.assignments.structure'), {
        search: search.value,
        available_page: 1, // Reset page when searching
    }, {
        preserveState: true,
        replace: true,
    });
};

watch(search, (val) => {
    // Optional debounce if needed, but simple for now
    if (val === '' || val.length > 2) {
        setTimeout(updateSearch, 300);
    }
});

// Grouping assignments by campaign for hierarchical view
const campaignsStructure = computed(() => {
    const structure = {};

    props.campaigns.data.forEach(c => {
        const campaignAssignments = props.assignments.filter(a => a.campaign_id === c.id);

        const activeCps = campaignAssignments.filter(a => a.position?.code === 'CP');
        const activeCpEmployeeIds = activeCps.map(cp => cp.employee_id);

        const activeSups = campaignAssignments.filter(a => a.position?.code === 'SUP');
        const activeSupEmployeeIds = activeSups.map(sup => sup.employee_id);

        // Orphan SUPs (Active SUPs whose manager is NOT an active CP)
        const orphanSups = campaignAssignments.filter(a => a.position?.code === 'SUP' && !activeCpEmployeeIds.includes(a.manager_id));

        // Orphan TCs (Active TCs whose manager is NOT an active SUP)
        const orphanTcs = campaignAssignments.filter(a => a.position?.code === 'TC' && !activeSupEmployeeIds.includes(a.manager_id));

        structure[c.id] = {
            campaign: c,
            cps: activeCps.map(cp => ({
                ...cp,
                sups: campaignAssignments.filter(a => a.manager_id === cp.employee_id && a.position?.code === 'SUP')
                    .map(sup => ({
                        ...sup,
                        tcs: campaignAssignments.filter(a => a.manager_id === sup.employee_id && a.position?.code === 'TC')
                    }))
            })),
            orphanSups: orphanSups.map(sup => ({
                ...sup,
                tcs: campaignAssignments.filter(a => a.manager_id === sup.employee_id && a.position?.code === 'TC')
            })),
            orphanTcs: orphanTcs // These are TCs whose SUP is missing
        };
    });

    return structure;
});
</script>

<template>

    <Head title="Structure des Affectations — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Structure & Affectations</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Gérez la hiérarchie CP ➔ SUP ➔ TC par campagne</p>
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
                            class="block w-full pl-12 pr-4 py-3 border border-pearl-200 rounded-xl text-sm focus:ring-gold-500 focus:border-gold-500 bg-white placeholder-charcoal-400" />
                    </div>

                    <!-- Bulk Assign Button -->
                    <Link :href="route('admin.assignments.bulk-assign')"
                        class="inline-flex items-center px-4 py-2 bg-gold-gradient text-white text-xs font-black uppercase tracking-wider rounded-xl hover:shadow-lg transition-all shrink-0">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Affectation Multiple
                    </Link>

                    <div class="flex bg-pearl-50 p-1 rounded-lg border border-pearl-200 shadow-sm shrink-0">
                        <button @click="activeTab = 'hierarchy'"
                            :class="activeTab === 'hierarchy' ? 'bg-white text-gold-600 shadow-sm' : 'text-charcoal-400 hover:text-charcoal-600'"
                            class="px-4 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition-all">
                            Structure
                        </button>
                        <button @click="activeTab = 'available'"
                            :class="activeTab === 'available' ? 'bg-white text-gold-600 shadow-sm' : 'text-charcoal-400 hover:text-charcoal-600'"
                            class="px-4 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition-all">
                            Disponibles ({{ availableEmployees.total }})
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- 1. TAB: HIERARCHY VIEW -->
        <div v-if="activeTab === 'hierarchy'" class="space-y-8">
            <div v-for="(struct, campaignId) in campaignsStructure" :key="campaignId"
                class="bg-white rounded-2xl border border-pearl-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="px-6 py-4 bg-charcoal-900 border-b border-charcoal-800 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]">
                        </div>
                        <h2 class="text-sm font-black text-white uppercase tracking-widest">{{ struct.campaign.name }}
                        </h2>
                    </div>
                    <span class="text-[10px] text-pearl-400 font-bold uppercase">{{ struct.cps.length }} CP(s)
                        affectés</span>
                </div>

                <div class="p-6">
                    <!-- Tree view start -->
                    <div v-if="struct.cps.length === 0" class="text-center py-12 text-charcoal-300 italic text-sm">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Aucun Chef de Plateau affecté à cette campagne.
                    </div>

                    <!-- 1. Real CPs Level (ALWAYS AT TOP) -->
                    <div v-for="cp in struct.cps" :key="cp.id"
                        class="relative pl-6 border-l-2 border-pearl-100 last:border-l-0">
                        <div class="absolute top-0 left-0 w-6 h-0.5 bg-pearl-100 -ml-[2px] mt-4"></div>

                        <div
                            class="flex items-center justify-between bg-pearl-50 p-4 rounded-xl border border-pearl-100 group hover:border-gold-300 transition-all hover:bg-white hover:shadow-sm">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-gold-gradient flex items-center justify-center text-[10px] font-black text-charcoal-900 shadow-md">
                                    CP</div>
                                <div>
                                    <div class="text-xs font-black text-charcoal-800">{{ cp.employee?.first_name }} {{
                                        cp.employee?.last_name }}</div>
                                    <div class="text-[9px] text-charcoal-400 font-bold uppercase tracking-tighter">Chef
                                        de
                                        Plateau</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="setPendingContext({ campaign_id: campaignId, manager_assignment_id: cp.id, position_code: 'SUP' })"
                                    class="p-1.5 bg-white border border-gold-200 text-gold-600 rounded-lg hover:bg-gold-50 transition-all shadow-sm"
                                    title="Ajouter un Superviseur">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </button>
                                <button @click="openReleaseModal(cp)"
                                    class="px-3 py-1 bg-white border border-rose-100 text-rose-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-rose-500 hover:text-white transition-all opacity-0 group-hover:opacity-100">Libérer</button>
                            </div>
                        </div>

                        <!-- SUP Level -->
                        <div class="mt-4 space-y-5 ml-8">
                            <div v-for="sup in cp.sups" :key="sup.id"
                                class="relative pl-6 border-l-2 border-pearl-100 last:border-l-0">
                                <div class="absolute top-0 left-0 w-6 h-0.5 bg-pearl-100 -ml-[2px] mt-4"></div>

                                <div
                                    class="flex items-center justify-between bg-white p-3 rounded-lg border border-pearl-100 group hover:border-gold-300 transition-all shadow-sm hover:shadow-md">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-7 h-7 rounded-lg bg-charcoal-800 flex items-center justify-center text-[9px] font-black text-white shadow-sm">
                                            SUP</div>
                                        <div>
                                            <div class="text-[11px] font-bold text-charcoal-700">{{
                                                sup.employee?.first_name }}
                                                {{ sup.employee?.last_name }}</div>
                                            <div
                                                class="text-[8px] text-charcoal-400 font-bold uppercase tracking-tighter">
                                                Superviseur</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="setPendingContext({ campaign_id: campaignId, manager_assignment_id: sup.id, position_code: 'TC' })"
                                            class="p-1 bg-pearl-50 border border-pearl-200 text-charcoal-500 rounded hover:bg-pearl-100 transition-all"
                                            title="Ajouter un TC">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </button>
                                        <button @click="openReleaseModal(sup)"
                                            class="px-2 py-0.5 text-rose-400 hover:text-rose-600 text-[9px] font-black uppercase tracking-tighter transition-all opacity-0 group-hover:opacity-100">Libérer</button>
                                    </div>
                                </div>

                                <!-- TC Level -->
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 ml-8">
                                    <div v-for="tc in sup.tcs" :key="tc.id"
                                        class="flex items-center justify-between bg-pearl-50/50 px-3 py-2 rounded-lg border border-pearl-50 group hover:border-pearl-200 hover:bg-white transition-all shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-5 h-5 rounded bg-pearl-200 flex items-center justify-center text-[7px] font-black text-charcoal-500">
                                                TC</div>
                                            <span class="text-[10px] font-bold text-charcoal-600">{{
                                                tc.employee?.first_name }}
                                                {{ tc.employee?.last_name }}</span>
                                        </div>
                                        <button @click="openReleaseModal(tc)"
                                            class="opacity-0 group-hover:opacity-100 text-rose-400 hover:text-rose-600 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Orphan CP Placeholder (Only if NO CPs in campaign but Orphan SUPs exist) -->
                    <div v-if="struct.cps.length === 0 && struct.orphanSups.length > 0"
                        class="relative pl-6 border-l-2 border-rose-200 bg-rose-50/30 rounded-2xl p-6 border-dashed">
                        <div class="absolute top-0 left-0 w-6 h-0.5 bg-rose-200 -ml-[2px] mt-4"></div>
                        <div class="flex items-center gap-5 mb-8">
                            <div
                                class="w-14 h-14 rounded-2xl bg-white border-2 border-rose-200 border-dashed flex items-center justify-center text-rose-300 shadow-sm">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-black text-rose-600 tracking-tight">Aucun CP actuellement</h3>
                                <p class="text-[10px] text-rose-400 font-bold uppercase tracking-widest mt-0.5">
                                    Voudriez-vous en
                                    ajouter ? (Chef de Plateau)</p>
                            </div>
                            <button @click="setPendingContext({ campaign_id: campaignId, position_code: 'CP' })"
                                class="p-2 bg-rose-600 text-white rounded-xl hover:bg-rose-700 transition-all shadow-lg shadow-rose-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </button>
                        </div>

                        <!-- Orphan SUPs display -->
                        <div class="space-y-6">
                            <div v-for="sup in struct.orphanSups" :key="sup.id"
                                class="relative pl-6 border-l-2 border-rose-100 last:border-l-0">
                                <div class="absolute top-0 left-0 w-6 h-0.5 bg-rose-100 -ml-[2px] mt-4"></div>
                                <div
                                    class="group bg-white p-4 rounded-xl border border-pearl-200 shadow-sm hover:border-gold-300 hover:shadow-md transition-all">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-pearl-50 flex items-center justify-center text-charcoal-700 font-bold text-[10px] border border-pearl-100">
                                                {{ sup.employee?.first_name.charAt(0) }}{{
                                                    sup.employee?.last_name.charAt(0) }}
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-charcoal-700">{{
                                                    sup.employee?.first_name }}
                                                    {{ sup.employee?.last_name }}</div>
                                                <div
                                                    class="text-[9px] text-charcoal-400 font-bold uppercase tracking-tighter">
                                                    Superviseur</div>
                                            </div>
                                        </div>
                                        <button @click="openReleaseModal(sup)"
                                            class="p-1.5 text-pearl-300 hover:text-rose-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-if="sup.tcs.length > 0" class="mt-4 space-y-3">
                                        <div v-for="tc in sup.tcs" :key="tc.id"
                                            class="relative pl-6 border-l-2 border-pearl-100 last:border-l-0">
                                            <div class="absolute top-0 left-0 w-4 h-0.5 bg-pearl-100 -ml-[2px] mt-3">
                                            </div>
                                            <div
                                                class="flex items-center justify-between bg-pearl-50/50 px-3 py-2 rounded-lg border border-pearl-100 group-hover:border-pearl-200">
                                                <div class="flex items-center gap-2">
                                                    <div class="text-[10px] font-bold text-charcoal-600">{{
                                                        tc.employee?.first_name }} {{ tc.employee?.last_name }}</div>
                                                    <div
                                                        class="text-[8px] px-1.5 py-0.5 bg-white border border-pearl-200 rounded text-charcoal-400 font-bold uppercase tracking-tighter">
                                                        TC</div>
                                                </div>
                                                <button @click="openReleaseModal(tc)"
                                                    class="text-pearl-300 hover:text-rose-500 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Simple Orphan SUPs display (When CPs exist but some SUPs are not attached) -->
                    <div v-if="struct.cps.length > 0 && struct.orphanSups.length > 0" class="mt-6 space-y-4">
                        <div class="text-[10px] font-black text-rose-500 uppercase tracking-widest pl-6">Superviseurs
                            non
                            rattachés</div>
                        <div v-for="sup in struct.orphanSups" :key="sup.id"
                            class="relative pl-6 border-l-2 border-rose-100 last:border-l-0 ml-6">
                            <div class="absolute top-0 left-0 w-6 h-0.5 bg-rose-100 -ml-[2px] mt-4"></div>
                            <div
                                class="bg-white p-3 rounded-lg border border-pearl-200 shadow-sm flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <div class="text-xs font-bold text-charcoal-700">{{ sup.employee?.first_name }} {{
                                        sup.employee?.last_name }}</div>
                                    <div
                                        class="text-[8px] px-1.5 py-0.5 bg-rose-50 border border-rose-100 rounded text-rose-600 font-bold uppercase tracking-tighter">
                                        Sans CP</div>
                                </div>
                                <button @click="openReleaseModal(sup)"
                                    class="text-pearl-300 hover:text-rose-500 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Orphan TC Placeholder at Campaign Level (Missing SUP) -->
                    <div v-if="struct.orphanTcs.length > 0"
                        class="relative pl-6 border-l-2 border-amber-100 bg-amber-50/20 rounded-xl p-4 border-dashed mt-4">
                        <div class="absolute top-0 left-0 w-6 h-0.5 bg-amber-100 -ml-[2px] mt-3"></div>
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-8 h-8 rounded-lg bg-white border border-amber-200 border-dashed flex items-center justify-center text-amber-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-[10px] font-black text-amber-600 uppercase tracking-tight">Aucun
                                    Superviseur
                                    actuellement</h4>
                                <p class="text-[8px] text-amber-400 font-bold uppercase tracking-widest">Des TCs
                                    attendent un
                                    chef</p>
                            </div>
                            <button @click="setPendingContext({ campaign_id: campaignId, position_code: 'SUP' })"
                                class="ml-auto p-1 bg-amber-500 text-white rounded-md hover:bg-amber-600 transition-all shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div v-for="tc in struct.orphanTcs" :key="tc.id"
                                class="flex items-center justify-between bg-white px-3 py-2 rounded-lg border border-amber-100 shadow-sm">
                                <div class="flex items-center gap-2">
                                    <div class="text-[10px] font-bold text-charcoal-700">{{ tc.employee?.first_name }}
                                        {{
                                            tc.employee?.last_name }}</div>
                                    <div
                                        class="text-[8px] px-1.5 py-0.5 bg-pearl-50 border border-pearl-100 rounded text-charcoal-400 font-bold uppercase tracking-tighter">
                                        TC</div>
                                </div>
                                <button @click="openReleaseModal(tc)"
                                    class="text-pearl-300 hover:text-rose-500 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TAB: AVAILABLE RESOURCES -->
        <div v-if="activeTab === 'available'">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="emp in availableEmployees.data" :key="emp.id"
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
                    </div>
                    <button @click="openAssignModal(emp)"
                        class="w-full py-2 bg-charcoal-900 text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-gold-600 transition-all shadow-md active:scale-95">
                        Affecter
                    </button>
                </div>
            </div>

            <!-- Available Employees Pagination (12 per page) -->
            <div v-if="availableEmployees.links.length > 3" class="mt-8 flex justify-center">
                <div class="flex gap-1 bg-white p-1 rounded-xl border border-pearl-200 shadow-sm">
                    <Link v-for="link in availableEmployees.links" :key="link.label" :href="link.url || '#'"
                        v-html="link.label" class="px-4 py-2 rounded-lg text-xs font-bold transition-all" :class="{
                            'bg-gold-gradient text-charcoal-900 shadow-md': link.active,
                            'text-charcoal-500 hover:bg-pearl-50': !link.active && link.url,
                            'opacity-30 cursor-not-allowed': !link.url
                        }" />
                </div>
            </div>

            <div v-if="availableEmployees.data.length === 0"
                class="text-center py-20 bg-white rounded-2xl border border-pearl-200">
                <p class="text-charcoal-400 text-sm italic">Aucune ressource disponible pour le moment.</p>
            </div>
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

        <!-- Campaigns Pagination (12 per page) -->
        <div v-if="activeTab === 'hierarchy' && campaigns.links.length > 3" class="mt-8 flex items-center justify-center gap-1">
            <template v-for="(link, k) in campaigns.links" :key="k">
                <div v-if="link.url === null" class="px-4 py-2 text-xs font-bold text-charcoal-300 cursor-not-allowed" v-html="link.label" />
                <Link :href="link.url" class="px-4 py-2 text-xs font-bold rounded-lg transition-all"
                    :class="link.active ? 'bg-gold-gradient text-white shadow-gold' : 'bg-white border border-pearl-200 text-charcoal-500 hover:bg-pearl-50'"
                    v-html="link.label" />
            </template>
        </div>

    </AdminLayout>
</template>