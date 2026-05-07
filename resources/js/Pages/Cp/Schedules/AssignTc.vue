<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useConfirm } from "primevue/useconfirm";

const confirm = useConfirm();

const props = defineProps({
    teleconsultants: Array,
    models: Array,
    campaigns: Array,
    supervisorsList: Array
});

const searchQuery = ref('');
const selectedCampaign = ref('all');
const selectedSupervisorFilter = ref('all');
const searchQueryModel = ref('');

// Pagination TC
const currentPageTC = ref(1);
const pageSizeTC = 6; 

// Pagination Modèles
const currentPageModel = ref(1);
const pageSizeModel = 4;

const form = useForm({
    employee_ids: [],
    planning_model_id: null,
    start_date: '',
    end_date: ''
});

const filteredTCs = computed(() => {
    let list = props.teleconsultants;
    
    // Filtre par campagne
    if (selectedCampaign.value !== 'all') {
        list = list.filter(tc => tc.campaign === selectedCampaign.value);
    }
    
    // Filtre par superviseur
    if (selectedSupervisorFilter.value !== 'all') {
        list = list.filter(tc => tc.supervisor === selectedSupervisorFilter.value);
    }
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        list = list.filter(tc => 
            tc.name.toLowerCase().includes(query) || 
            tc.campaign.toLowerCase().includes(query) ||
            tc.supervisor.toLowerCase().includes(query)
        );
    }
    return list;
});

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

const paginatedTCs = computed(() => {
    const start = (currentPageTC.value - 1) * pageSizeTC;
    return filteredTCs.value.slice(start, start + pageSizeTC);
});

const totalPagesTC = computed(() => Math.ceil(filteredTCs.value.length / pageSizeTC));

const paginatedModels = computed(() => {
    const start = (currentPageModel.value - 1) * pageSizeModel;
    return filteredModels.value.slice(start, start + pageSizeModel);
});

const totalPagesModel = computed(() => Math.ceil(filteredModels.value.length / pageSizeModel));

const selectedTCs = computed(() => {
    return props.teleconsultants.filter(tc => form.employee_ids.includes(tc.id));
});

const selectedModel = computed(() => {
    return props.models.find(m => m.id === form.planning_model_id);
});

const totalHoursVolume = computed(() => {
    if (!selectedModel.value) return 0;
    return (form.employee_ids.length * selectedModel.value.total_hours).toFixed(1);
});

const toggleSelectAll = () => {
    const availableTCs = filteredTCs.value.filter(tc => !tc.has_active_planning);
    if (form.employee_ids.length === availableTCs.length) {
        form.employee_ids = [];
    } else {
        form.employee_ids = availableTCs.map(tc => tc.id);
    }
};

const toggleTC = (tcId) => {
    const index = form.employee_ids.indexOf(tcId);
    if (index === -1) {
        form.employee_ids.push(tcId);
    } else {
        form.employee_ids.splice(index, 1);
    }
};

const submit = () => {
    confirm.require({
        message: `Voulez-vous vraiment affecter le modèle "${selectedModel.value.name}" à ${form.employee_ids.length} téléconseiller(s) ?`,
        header: 'Confirmation d\'affectation massive',
        icon: 'pi pi-users',
        rejectLabel: 'Annuler',
        acceptLabel: 'Confirmer',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-primary',
        accept: () => {
            form.post(route('admin.assignments.schedules.assign.store'));
        }
    });
};
</script>

<template>
    <Head title="Affecter des Plannings aux TC — CP" />
    <CpLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Affecter des plannings aux TC</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Assignation massive de modèles de planning</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('cp.schedules.templates')" class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-bold text-charcoal-600 hover:bg-pearl-50 transition-all">
                        Annuler
                    </Link>
                    <button 
                        @click="submit"
                        :disabled="form.processing || form.employee_ids.length === 0 || !form.planning_model_id || !form.start_date"
                        class="px-4 py-2 bg-gold-gradient rounded-lg text-xs font-bold text-white hover:opacity-90 transition-all shadow-gold disabled:opacity-50"
                    >
                        Valider l'affectation massive
                    </button>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-20 items-start">
            
            <!-- Colonne Gauche : Configuration (Étapes) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Étape 1 : Sélection des TCs -->
                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-pearl-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
                        <h2 class="text-sm font-bold text-charcoal-700 flex items-center gap-3">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-gold-100 text-gold-600 text-[10px]">1</span>
                            Sélection des téléconseillers
                        </h2>
                        <div class="flex items-center gap-3">
                            <button 
                                @click="toggleSelectAll"
                                class="text-[10px] font-bold text-gold-600 uppercase tracking-widest hover:text-gold-700 transition-colors"
                            >
                                {{ form.employee_ids.length === filteredTCs.filter(tc => !tc.has_active_planning).length ? 'Tout désélectionner' : 'Tout sélectionner' }}
                            </button>
                            <div class="flex items-center gap-2">
                                <select 
                                    v-model="selectedCampaign"
                                    class="bg-pearl-50 border border-pearl-200 rounded-lg px-2 py-1.5 text-[10px] font-bold text-charcoal-700 outline-none focus:border-gold-400 transition-all"
                                >
                                    <option value="all">Toutes les campagnes</option>
                                    <option v-for="c in campaigns" :key="c" :value="c">{{ c }}</option>
                                </select>
                                <select 
                                    v-model="selectedSupervisorFilter"
                                    class="bg-pearl-50 border border-pearl-200 rounded-lg px-2 py-1.5 text-[10px] font-bold text-charcoal-700 outline-none focus:border-gold-400 transition-all"
                                >
                                    <option value="all">Tous les superviseurs</option>
                                    <option v-for="s in supervisorsList" :key="s" :value="s">{{ s }}</option>
                                </select>
                                <div class="relative">
                                    <input 
                                        v-model="searchQuery"
                                        type="text" 
                                        placeholder="Rechercher..."
                                        class="w-32 bg-pearl-50 border border-pearl-200 rounded-lg px-3 py-1.5 pl-8 text-[11px] text-charcoal-700 outline-none focus:border-gold-400 transition-all"
                                    />
                                    <svg class="w-3.5 h-3.5 text-charcoal-300 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div 
                                v-for="tc in paginatedTCs" 
                                :key="tc.id"
                                @click="!tc.has_active_planning && toggleTC(tc.id)"
                                class="p-4 rounded-xl border transition-all duration-300 group relative"
                                :class="[
                                    form.employee_ids.includes(tc.id) ? 'border-gold-400 bg-gold-50/20 shadow-sm' : 'border-pearl-100 bg-white hover:border-gold-300 hover:shadow-md',
                                    tc.has_active_planning ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                                ]"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="mt-1">
                                        <div 
                                            class="w-4 h-4 rounded border flex items-center justify-center transition-colors"
                                            :class="form.employee_ids.includes(tc.id) ? 'bg-gold-500 border-gold-500' : 'bg-pearl-50 border-pearl-200 group-hover:border-gold-400'"
                                        >
                                            <svg v-if="form.employee_ids.includes(tc.id)" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="font-bold text-xs text-charcoal-700">{{ tc.name }}</h3>
                                            <span v-if="tc.has_active_planning" class="text-[9px] font-black text-orange-500 uppercase tracking-tighter">Planning Actif</span>
                                        </div>
                                        <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-[9px] font-bold uppercase tracking-widest text-charcoal-400">
                                            <span class="text-gold-600">{{ tc.campaign }}</span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                {{ tc.supervisor }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="totalPagesTC > 1" class="flex items-center justify-between pt-6 mt-4 border-t border-pearl-50">
                            <span class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Page {{ currentPageTC }} / {{ totalPagesTC }}</span>
                            <div class="flex gap-2">
                                <button @click="currentPageTC--" :disabled="currentPageTC === 1" class="p-1.5 rounded-lg border border-pearl-100 disabled:opacity-30 hover:bg-pearl-50 transition-all"><svg class="w-4 h-4 text-charcoal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                                <button @click="currentPageTC++" :disabled="currentPageTC === totalPagesTC" class="p-1.5 rounded-lg border border-pearl-100 disabled:opacity-30 hover:bg-pearl-50 transition-all"><svg class="w-4 h-4 text-charcoal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Étape 2 : Choix du modèle -->
                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-pearl-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
                        <h2 class="text-sm font-bold text-charcoal-700 flex items-center gap-3">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-gold-100 text-gold-600 text-[10px]">2</span>
                            Choix du modèle de planning
                        </h2>
                        <div class="relative">
                            <input 
                                v-model="searchQueryModel"
                                type="text" 
                                :disabled="form.employee_ids.length === 0"
                                placeholder="Rechercher un modèle..."
                                class="w-48 bg-pearl-50 border border-pearl-200 rounded-lg px-3 py-1.5 pl-8 text-[11px] text-charcoal-700 outline-none focus:border-gold-400 transition-all disabled:opacity-40"
                            />
                            <svg class="w-3.5 h-3.5 text-charcoal-300 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div 
                                v-for="model in paginatedModels" 
                                :key="model.id"
                                @click="form.employee_ids.length > 0 && (form.planning_model_id = model.id)"
                                class="p-4 rounded-xl border transition-all duration-300 relative group overflow-hidden"
                                :class="[
                                    form.planning_model_id === model.id ? 'border-gold-400 bg-gold-50/20 shadow-sm' : 'border-pearl-100 bg-white hover:border-gold-300 hover:shadow-md',
                                    form.employee_ids.length === 0 ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer hover:-translate-y-0.5'
                                ]"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold text-xs text-charcoal-700 truncate">{{ model.name }}</h4>
                                    <span class="px-1.5 py-0.5 bg-pearl-100 text-charcoal-700 rounded-md text-[9px] font-black">{{ Math.round(model.total_hours) }}h</span>
                                </div>
                                <p class="text-[10px] text-charcoal-400 truncate">{{ model.description || 'Horaires hebdomadaires' }}</p>
                            </div>
                        </div>

                        <div v-if="totalPagesModel > 1" class="flex items-center justify-between pt-6 mt-4 border-t border-pearl-50">
                            <span class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Page {{ currentPageModel }} / {{ totalPagesModel }}</span>
                            <div class="flex gap-2">
                                <button @click="currentPageModel--" :disabled="currentPageModel === 1" class="p-1.5 rounded-lg border border-pearl-100 disabled:opacity-30 hover:bg-pearl-50 transition-all"><svg class="w-4 h-4 text-charcoal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                                <button @click="currentPageModel++" :disabled="currentPageModel === totalPagesModel" class="p-1.5 rounded-lg border border-pearl-100 disabled:opacity-30 hover:bg-pearl-50 transition-all"><svg class="w-4 h-4 text-charcoal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Étape 3 : Période -->
                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-pearl-100 bg-white">
                        <h2 class="text-sm font-bold text-charcoal-700 flex items-center gap-3">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-gold-100 text-gold-600 text-[10px]">3</span>
                            Période d'application
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-2">Date de début <span class="text-red-500">*</span></label>
                                <input v-model="form.start_date" type="date" :disabled="!form.planning_model_id"
                                    class="w-full bg-pearl-50 border border-pearl-200 rounded-lg px-4 py-2.5 text-xs text-charcoal-700 focus:border-gold-400 outline-none transition-all disabled:opacity-40 disabled:cursor-not-allowed" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-2">Date de fin</label>
                                <input v-model="form.end_date" type="date" :disabled="!form.planning_model_id" :min="form.start_date"
                                    class="w-full bg-pearl-50 border border-pearl-200 rounded-lg px-4 py-2.5 text-xs text-charcoal-700 focus:border-gold-400 outline-none transition-all disabled:opacity-40 disabled:cursor-not-allowed" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne Droite : Récapitulatif -->
            <div class="sticky top-6">
                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-pearl-100 bg-pearl-50/30">
                        <h2 class="text-lg font-bold text-charcoal-700 tracking-tight">Récapitulatif</h2>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <!-- TCs Sélectionnés -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Collaborateurs</div>
                                <span class="px-2 py-0.5 bg-gold-100 text-gold-700 rounded-full text-[10px] font-bold">{{ form.employee_ids.length }}</span>
                            </div>
                            <div v-if="selectedTCs.length > 0" class="space-y-2">
                                <div 
                                    v-for="tc in selectedTCs.slice(0, 3)" 
                                    :key="tc.id"
                                    class="text-xs font-bold text-charcoal-700 flex items-center gap-2"
                                >
                                    <div class="w-1.5 h-1.5 rounded-full bg-gold-400"></div>
                                    {{ tc.name }}
                                </div>
                                <div v-if="selectedTCs.length > 3" class="text-[10px] text-charcoal-400 font-bold ml-3.5 italic">
                                    + {{ selectedTCs.length - 3 }} autres...
                                </div>
                            </div>
                            <div v-else class="text-xs text-charcoal-300 italic">Aucun TC sélectionné</div>
                        </div>

                        <hr class="border-pearl-50" />

                        <!-- Modèle & Volume -->
                        <div>
                            <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-2">Planning & Volume</div>
                            <div v-if="selectedModel" class="space-y-2">
                                <div class="text-xs font-bold text-charcoal-700">{{ selectedModel.name }}</div>
                                <div class="flex items-center justify-between py-2 px-3 bg-pearl-50 rounded-lg">
                                    <span class="text-[10px] text-charcoal-500 font-bold uppercase tracking-tighter">Volume total</span>
                                    <span class="text-sm font-black text-gold-600">{{ totalHoursVolume }}h</span>
                                </div>
                                <div class="text-[9px] text-charcoal-400 text-center italic">Calcul : {{ form.employee_ids.length }} TC × {{ selectedModel.total_hours }}h</div>
                            </div>
                            <div v-else class="text-xs text-charcoal-300 italic">Modèle non sélectionné</div>
                        </div>

                        <hr class="border-pearl-50" />

                        <!-- Période -->
                        <div>
                            <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-2">Période d'application</div>
                            <div v-if="form.start_date" class="text-xs font-bold text-charcoal-700 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Du {{ new Date(form.start_date).toLocaleDateString() }} 
                                {{ form.end_date ? ' au ' + new Date(form.end_date).toLocaleDateString() : '' }}
                            </div>
                            <div v-else class="text-xs text-charcoal-300 italic">Période non définie</div>
                        </div>

                        <!-- Bouton déplacé dans le header -->
                        <div class="pt-6 text-center">
                            <p v-if="form.employee_ids.length > 0" class="text-[9px] text-charcoal-400 font-medium">
                                En validant, vous allez créer {{ form.employee_ids.length }} demandes d'affectation en attente de validation.
                            </p>
                            <p v-else class="text-[9px] text-charcoal-400 font-medium italic">
                                Veuillez sélectionner au moins un collaborateur et un modèle.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </CpLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
.shadow-gold {
    box-shadow: 0 4px 15px -3px rgba(212, 160, 23, 0.4);
}
</style>
