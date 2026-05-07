<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    campaigns: Object, // Changed from Array to Object (Paginated)
    assignments: Array,
    availableEmployees: Array,
    positions: Array,
});

const activeTab = ref('hierarchy'); // 'hierarchy' or 'available'
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
    
    const role = selectedEmployee.value.position?.code; // CP, SUP, or TC
    let filtered = [];
    
    if (role === 'CP') filtered = []; 
    else if (role === 'SUP') {
        // SUP is managed by a CP
        filtered = props.assignments.filter(a => a.position?.code === 'CP');
    } else if (role === 'TC') {
        // TC is managed by a SUP
        filtered = props.assignments.filter(a => a.position?.code === 'SUP');
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
    if (!campaignSearch.value) return props.campaigns.data;
    const s = campaignSearch.value.toLowerCase();
    return props.campaigns.data.filter(c => c.name.toLowerCase().includes(s));
});

const openAssignModal = (emp) => {
    selectedEmployee.value = emp;
    form.employee_id = emp.id;
    form.position_id = emp.position_id;
    form.manager_id = '';
    form.campaign_id = '';
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
    // If it's a TC or SUP, inherit campaign from manager
    if (['TC', 'SUP'].includes(selectedEmployee.value.position?.code) && form.manager_id) {
        const managerAssignment = props.assignments.find(a => a.employee_id == form.manager_id);
        if (managerAssignment) {
            form.campaign_id = managerAssignment.campaign_id;
        }
    }

    form.post(route('admin.assignments.store'), {
        onSuccess: () => {
            showAssignModal.value = false;
            form.reset();
        }
    });
};

const confirmRelease = () => {
    router.patch(route('admin.assignments.release', selectedAssignment.value.id), {
        cascade: releaseCascade.value
    }, {
        onSuccess: () => showReleaseModal.value = false
    });
};

// Grouping assignments by campaign for hierarchical view
const campaignsStructure = computed(() => {
    const structure = {};
    
    props.campaigns.data.forEach(c => {
        structure[c.id] = {
            campaign: c,
            cps: props.assignments.filter(a => a.campaign_id === c.id && a.position?.code === 'CP')
                .map(cp => ({
                    ...cp,
                    sups: props.assignments.filter(a => a.manager_id === cp.employee_id && a.campaign_id === c.id && a.position?.code === 'SUP')
                        .map(sup => ({
                            ...sup,
                            tcs: props.assignments.filter(a => a.manager_id === sup.employee_id && a.campaign_id === c.id && a.position?.code === 'TC')
                        }))
                }))
        };
    });
    
    return structure;
});
</script>

<template>
    <Head title="Structure des Affectations — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Structure & Affectations</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Gérez la hiérarchie CP ➔ SUP ➔ TC par campagne</p>
                </div>
                <div class="flex bg-pearl-50 p-1 rounded-lg border border-pearl-200 shadow-sm">
                    <button 
                        @click="activeTab = 'hierarchy'"
                        :class="activeTab === 'hierarchy' ? 'bg-white text-gold-600 shadow-sm' : 'text-charcoal-400 hover:text-charcoal-600'"
                        class="px-4 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition-all"
                    >
                        Structure Hiérarchique
                    </button>
                    <button 
                        @click="activeTab = 'available'"
                        :class="activeTab === 'available' ? 'bg-white text-gold-600 shadow-sm' : 'text-charcoal-400 hover:text-charcoal-600'"
                        class="px-4 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition-all"
                    >
                        Ressources Disponibles ({{ availableEmployees.length }})
                    </button>
                </div>
            </div>
        </template>

        <!-- 1. TAB: HIERARCHY VIEW -->
        <div v-if="activeTab === 'hierarchy'" class="space-y-8">
            <div v-for="(struct, campaignId) in campaignsStructure" :key="campaignId" class="bg-white rounded-2xl border border-pearl-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="px-6 py-4 bg-charcoal-900 border-b border-charcoal-800 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></div>
                        <h2 class="text-sm font-black text-white uppercase tracking-widest">{{ struct.campaign.name }}</h2>
                    </div>
                    <span class="text-[10px] text-pearl-400 font-bold uppercase">{{ struct.cps.length }} CP(s) affectés</span>
                </div>

                <div class="p-6">
                    <!-- Tree view start -->
                    <div v-if="struct.cps.length === 0" class="text-center py-12 text-charcoal-300 italic text-sm">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Aucun Chef de Plateau affecté à cette campagne.
                    </div>

                    <div v-else class="space-y-8">
                        <!-- CP Level -->
                        <div v-for="cp in struct.cps" :key="cp.id" class="relative pl-6 border-l-2 border-pearl-100 last:border-l-0">
                            <div class="absolute top-0 left-0 w-6 h-0.5 bg-pearl-100 -ml-[2px] mt-4"></div>
                            
                            <div class="flex items-center justify-between bg-pearl-50 p-4 rounded-xl border border-pearl-100 group hover:border-gold-300 transition-all hover:bg-white hover:shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gold-gradient flex items-center justify-center text-[10px] font-black text-charcoal-900 shadow-md">CP</div>
                                    <div>
                                        <div class="text-xs font-black text-charcoal-800">{{ cp.employee?.first_name }} {{ cp.employee?.last_name }}</div>
                                        <div class="text-[9px] text-charcoal-400 font-bold uppercase tracking-tighter">Chef de Plateau</div>
                                    </div>
                                </div>
                                <button @click="openReleaseModal(cp)" class="px-3 py-1 bg-white border border-rose-100 text-rose-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-rose-500 hover:text-white transition-all opacity-0 group-hover:opacity-100">Libérer</button>
                            </div>

                            <!-- SUP Level -->
                            <div class="mt-4 space-y-5 ml-8">
                                <div v-for="sup in cp.sups" :key="sup.id" class="relative pl-6 border-l-2 border-pearl-100 last:border-l-0">
                                    <div class="absolute top-0 left-0 w-6 h-0.5 bg-pearl-100 -ml-[2px] mt-4"></div>
                                    
                                    <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-pearl-100 group hover:border-gold-300 transition-all shadow-sm hover:shadow-md">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-lg bg-charcoal-800 flex items-center justify-center text-[9px] font-black text-white shadow-sm">SUP</div>
                                            <div>
                                                <div class="text-[11px] font-bold text-charcoal-700">{{ sup.employee?.first_name }} {{ sup.employee?.last_name }}</div>
                                                <div class="text-[8px] text-charcoal-400 font-bold uppercase tracking-tighter">Superviseur</div>
                                            </div>
                                        </div>
                                        <button @click="openReleaseModal(sup)" class="px-2 py-0.5 text-rose-400 hover:text-rose-600 text-[9px] font-black uppercase tracking-tighter transition-all opacity-0 group-hover:opacity-100">Libérer</button>
                                    </div>

                                    <!-- TC Level -->
                                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 ml-8">
                                        <div v-for="tc in sup.tcs" :key="tc.id" class="flex items-center justify-between bg-pearl-50/50 px-3 py-2 rounded-lg border border-pearl-50 group hover:border-pearl-200 hover:bg-white transition-all shadow-sm">
                                            <div class="flex items-center gap-2">
                                                <div class="w-5 h-5 rounded bg-pearl-200 flex items-center justify-center text-[7px] font-black text-charcoal-500">TC</div>
                                                <span class="text-[10px] font-bold text-charcoal-600">{{ tc.employee?.first_name }} {{ tc.employee?.last_name }}</span>
                                            </div>
                                            <button @click="openReleaseModal(tc)" class="opacity-0 group-hover:opacity-100 text-rose-400 hover:text-rose-600 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campaign Pagination -->
            <div v-if="campaigns.links.length > 3" class="mt-8 flex justify-center">
                <div class="flex gap-1 bg-white p-1 rounded-xl border border-pearl-200 shadow-sm">
                    <Link 
                        v-for="link in campaigns.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-all"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 shadow-md': link.active,
                            'text-charcoal-500 hover:bg-pearl-50': !link.active && link.url,
                            'opacity-30 cursor-not-allowed': !link.url
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- 2. TAB: AVAILABLE RESOURCES -->
        <div v-if="activeTab === 'available'">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="emp in availableEmployees" :key="emp.id" class="bg-white rounded-2xl border border-pearl-200 p-5 shadow-sm hover:shadow-xl transition-all group overflow-hidden relative">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-pearl-100 flex items-center justify-center text-charcoal-700 font-black border border-pearl-200 group-hover:bg-gold-100 group-hover:text-gold-700 transition-colors">
                            {{ emp.first_name.charAt(0) }}{{ emp.last_name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-charcoal-800 tracking-tight">{{ emp.first_name }} {{ emp.last_name }}</h3>
                            <p class="text-[9px] text-gold-600 font-bold uppercase tracking-widest">{{ emp.position?.name }}</p>
                        </div>
                    </div>
                    <div class="space-y-2 mb-6 bg-pearl-50/50 p-3 rounded-lg border border-pearl-50">
                        <div class="flex justify-between text-[9px] font-bold">
                            <span class="text-charcoal-400 uppercase">Matricule</span>
                            <span class="text-charcoal-700">{{ emp.matricule }}</span>
                        </div>
                        <div class="flex justify-between text-[9px] font-bold">
                            <span class="text-charcoal-400 uppercase">Embauche</span>
                            <span class="text-charcoal-700">{{ new Date(emp.hire_date).toLocaleDateString('fr-FR') }}</span>
                        </div>
                    </div>
                    <button 
                        @click="openAssignModal(emp)"
                        class="w-full py-2 bg-charcoal-900 text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-gold-600 transition-all shadow-md active:scale-95"
                    >
                        Affecter
                    </button>
                </div>
            </div>
            <div v-if="availableEmployees.length === 0" class="text-center py-20 bg-white rounded-2xl border border-pearl-200">
                <p class="text-charcoal-400 text-sm italic">Aucune ressource disponible pour le moment.</p>
            </div>
        </div>

        <!-- Assign Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showAssignModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-charcoal-900/80 backdrop-blur-sm">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-pearl-200">
                    <div class="px-6 py-4 bg-pearl-50 border-b border-pearl-200 flex justify-between items-center">
                        <h2 class="text-sm font-black text-charcoal-700 uppercase tracking-widest">Affectation de Ressource</h2>
                        <button @click="showAssignModal = false" class="text-charcoal-400 hover:text-charcoal-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitAssignment" class="p-6 space-y-5">
                        <div class="flex items-center gap-3 p-3 bg-pearl-50 rounded-xl border border-pearl-100">
                            <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center font-black text-charcoal-700 shadow-sm">
                                {{ selectedEmployee?.first_name.charAt(0) }}{{ selectedEmployee?.last_name.charAt(0) }}
                            </div>
                            <div>
                                <div class="text-xs font-bold text-charcoal-800">{{ selectedEmployee?.first_name }} {{ selectedEmployee?.last_name }}</div>
                                <div class="text-[9px] text-gold-600 font-bold uppercase tracking-widest">{{ selectedEmployee?.position?.name }}</div>
                            </div>
                        </div>

                        <div v-if="selectedEmployee?.position?.code !== 'CP'">
                            <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1.5">
                                {{ selectedEmployee?.position?.code === 'SUP' ? 'Chef de Plateau Responsable' : 'Superviseur Responsable' }}
                            </label>
                            <div class="relative mb-2">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-300">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </span>
                                <input v-model="managerSearch" type="text" placeholder="Filtrer les responsables..." class="w-full border border-pearl-200 rounded-lg pl-9 pr-3 py-2 text-[10px] focus:ring-gold-500 focus:border-gold-500 italic">
                            </div>
                            <select v-model="form.manager_id" class="w-full border border-pearl-200 rounded-lg p-2.5 text-xs focus:ring-gold-500 focus:border-gold-500 bg-pearl-50/50" required>
                                <option value="">Choisir un responsable...</option>
                                <option v-for="manager in availableManagers" :key="manager.id" :value="manager.employee_id">
                                    {{ manager.employee?.first_name }} {{ manager.employee?.last_name }} ({{ manager.campaign?.name }})
                                </option>
                            </select>
                        </div>

                        <div v-if="selectedEmployee?.position?.code === 'CP'">
                            <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1.5">Campagne Cible</label>
                            <div class="relative mb-2">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-300">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </span>
                                <input v-model="campaignSearch" type="text" placeholder="Filtrer les campagnes..." class="w-full border border-pearl-200 rounded-lg pl-9 pr-3 py-2 text-[10px] focus:ring-gold-500 focus:border-gold-500 italic">
                            </div>
                            <select v-model="form.campaign_id" class="w-full border border-pearl-200 rounded-lg p-2.5 text-xs focus:ring-gold-500 focus:border-gold-500 bg-pearl-50/50" required>
                                <option value="">Choisir une campagne...</option>
                                <option v-for="c in filteredCampaigns" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1.5">Date de prise d'effet</label>
                            <input v-model="form.start_date" type="date" class="w-full border border-pearl-200 rounded-lg p-2.5 text-xs focus:ring-gold-500 focus:border-gold-500" required>
                        </div>

                        <div class="pt-6 flex justify-end gap-3 border-t border-pearl-100">
                            <button type="button" @click="showAssignModal = false" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-charcoal-400 hover:text-charcoal-600 transition-colors">Annuler</button>
                            <button type="submit" :disabled="form.processing" class="px-8 py-2 bg-gold-gradient text-charcoal-900 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-gold hover:opacity-90 transition-all">
                                Valider l'Affectation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Release Confirmation Modal (Custom style) -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showReleaseModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-charcoal-900/80 backdrop-blur-sm">
                <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden border border-pearl-200">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-rose-100">
                            <svg class="w-8 h-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h3 class="text-charcoal-800 font-black uppercase tracking-widest text-sm mb-2">Libérer la ressource ?</h3>
                        <p class="text-charcoal-400 text-xs mb-6 px-4">
                            Vous êtes sur le point de libérer <strong>{{ selectedAssignment?.employee?.first_name }} {{ selectedAssignment?.employee?.last_name }}</strong> de son affectation actuelle.
                        </p>

                        <!-- Choice for Managers (CP/SUP) -->
                        <div v-if="['CP', 'SUP'].includes(selectedAssignment?.position?.code)" class="text-left mb-6 bg-pearl-50 p-4 rounded-xl border border-pearl-100">
                            <label class="block text-[10px] font-black text-charcoal-500 uppercase tracking-widest mb-3">Options de libération</label>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" v-model="releaseCascade" :value="true" class="w-4 h-4 text-gold-600 focus:ring-gold-500 border-pearl-300">
                                    <span class="text-xs text-charcoal-600 group-hover:text-charcoal-900">Libérer avec tous les subordonnés <span class="text-[10px] text-charcoal-400 italic block">(Cascade complète)</span></span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" v-model="releaseCascade" :value="false" class="w-4 h-4 text-gold-600 focus:ring-gold-500 border-pearl-300">
                                    <span class="text-xs text-charcoal-600 group-hover:text-charcoal-900">Libérer lui seul <span class="text-[10px] text-charcoal-400 italic block">(Les subordonnés restent en poste)</span></span>
                                </label>
                            </div>
                        </div>
                        <div v-else class="mb-6 p-4 bg-emerald-50 text-emerald-700 text-xs rounded-xl border border-emerald-100 italic">
                            Libération directe sans impact sur d'autres ressources.
                        </div>

                        <div class="flex flex-col gap-2">
                            <button @click="confirmRelease" class="w-full py-2.5 bg-rose-600 text-white rounded-lg text-xs font-black uppercase tracking-widest hover:bg-rose-700 transition-all shadow-md">
                                Confirmer la libération
                            </button>
                            <button @click="showReleaseModal = false" class="w-full py-2.5 bg-white text-charcoal-400 rounded-lg text-xs font-bold uppercase tracking-widest hover:text-charcoal-600 transition-all">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
.shadow-gold {
    box-shadow: 0 4px 15px -3px rgba(212, 160, 23, 0.4);
}
</style>
