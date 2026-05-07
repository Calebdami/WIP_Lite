<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useConfirm } from "primevue/useconfirm";

const confirm = useConfirm();

const props = defineProps({
    supervisors: Array,
    models: Array,
    campaigns: Array
});

const searchQuery = ref('');
const selectedCampaign = ref('all');
const searchQueryModel = ref('');

// Pagination Superviseurs
const currentPageSup = ref(1);
const pageSizeSup = 4; 

// Pagination Modèles
const currentPageModel = ref(1);
const pageSizeModel = 4;

const form = useForm({
    employee_id: null,
    planning_model_id: null,
    start_date: '',
    end_date: ''
});

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

const paginatedSupervisors = computed(() => {
    const start = (currentPageSup.value - 1) * pageSizeSup;
    return filteredSupervisors.value.slice(start, start + pageSizeSup);
});

const totalPagesSup = computed(() => Math.ceil(filteredSupervisors.value.length / pageSizeSup));

const paginatedModels = computed(() => {
    const start = (currentPageModel.value - 1) * pageSizeModel;
    return filteredModels.value.slice(start, start + pageSizeModel);
});

const totalPagesModel = computed(() => Math.ceil(filteredModels.value.length / pageSizeModel));

const selectedSupervisor = computed(() => {
    return props.supervisors.find(s => s.id === form.employee_id);
});

const selectedModel = computed(() => {
    return props.models.find(m => m.id === form.planning_model_id);
});

const submit = () => {
    confirm.require({
        message: `Voulez-vous vraiment affecter le modèle "${selectedModel.value.name}" à ${selectedSupervisor.value.name} ?`,
        header: 'Confirmation d\'affectation',
        icon: 'pi pi-calendar-plus',
        rejectLabel: 'Annuler',
        acceptLabel: 'Confirmer',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-primary',
        accept: () => {
            form.post(route('cp.schedules.assign.store'));
        }
    });
};
</script>

<template>
    <Head title="Affecter un Planning SUP — CP" />
    <CpLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Affecter un planning à un superviseur</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Assignation d'un modèle de planning</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('cp.schedules.templates')" class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-bold text-charcoal-600 hover:bg-pearl-50 hover:border-pearl-300 hover:shadow-sm transition-all active:scale-95">
                        Annuler
                    </Link>
                    <button 
                        @click="submit"
                        :disabled="form.processing || !form.employee_id || !form.planning_model_id || !form.start_date"
                        class="px-4 py-2 bg-gold-gradient rounded-lg text-xs font-bold text-white hover:opacity-90 transition-all shadow-gold disabled:opacity-50"
                    >
                        Valider l'affectation
                    </button>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pb-20 items-start">
            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-pearl-100 flex items-center justify-between gap-3 bg-white">
                        <h2 class="text-sm font-bold text-charcoal-700 flex items-center gap-3">
                            <svg class="w-4 h-4 text-charcoal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Sélection du superviseur
                        </h2>
                        <div class="flex items-center gap-2">
                            <select 
                                v-model="selectedCampaign"
                                class="bg-pearl-50 border border-pearl-200 rounded-lg px-2 py-1.5 text-[10px] font-bold text-charcoal-700 outline-none focus:border-gold-400 transition-all"
                            >
                                <option value="all">Toutes les campagnes</option>
                                <option v-for="c in campaigns" :key="c" :value="c">{{ c }}</option>
                            </select>
                            <input 
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Rechercher..."
                                class="w-32 bg-pearl-50 border border-pearl-200 rounded-lg px-3 py-1.5 text-[11px] text-charcoal-700 outline-none focus:border-gold-400 transition-all"
                            />
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-3">
                        <div 
                            v-for="sup in paginatedSupervisors" 
                            :key="sup.id"
                            @click="!sup.has_active_planning && (form.employee_id = sup.id)"
                            class="p-4 rounded-xl border border-pearl-100 transition-all duration-300 hover:border-gold-300 hover:shadow-md hover:-translate-y-0.5 group"
                            :class="[
                                form.employee_id === sup.id ? 'border-gold-400 bg-gold-50/20 shadow-sm' : 'bg-white',
                                sup.has_active_planning ? 'opacity-50 cursor-not-allowed grayscale-[0.5]' : 'cursor-pointer'
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h3 class="font-bold text-sm text-charcoal-700">{{ sup.name }}</h3>
                                        <span v-if="sup.has_active_planning" class="px-2 py-0.5 bg-orange-100 text-orange-600 rounded text-[9px] font-bold">Planning actif</span>
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
                                <div v-if="form.employee_id === sup.id" class="w-5 h-5 rounded-full bg-gold-400 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div v-if="totalPagesSup > 1" class="flex items-center justify-between pt-4 mt-2">
                            <span class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Page {{ currentPageSup }} / {{ totalPagesSup }}</span>
                            <div class="flex gap-2">
                                <button @click="currentPageSup--" :disabled="currentPageSup === 1" class="p-1.5 rounded-lg border border-pearl-100 disabled:opacity-30 hover:bg-white hover:border-gold-300 hover:shadow-sm transition-all active:scale-90"><svg class="w-4 h-4 text-charcoal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                                <button @click="currentPageSup++" :disabled="currentPageSup === totalPagesSup" class="p-1.5 rounded-lg border border-pearl-100 disabled:opacity-30 hover:bg-white hover:border-gold-300 hover:shadow-sm transition-all active:scale-90"><svg class="w-4 h-4 text-charcoal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-pearl-100 flex items-center justify-between bg-white">
                        <h2 class="text-sm font-bold text-charcoal-700 flex items-center gap-3">
                            <svg class="w-4 h-4 text-charcoal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"/></svg>
                            Étape 2 : Modèle de planning
                        </h2>
                        <input 
                            v-model="searchQueryModel"
                            type="text" 
                            :disabled="!form.employee_id"
                            placeholder="Rechercher..."
                            class="w-40 bg-pearl-50 border border-pearl-200 rounded-lg px-3 py-1.5 text-[11px] text-charcoal-700 outline-none focus:border-gold-400 transition-all disabled:opacity-40"
                        />
                    </div>

                    <div class="p-6 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div 
                                v-for="model in paginatedModels" 
                                :key="model.id"
                                @click="form.employee_id && (form.planning_model_id = model.id)"
                                class="p-4 rounded-xl border border-pearl-100 transition-all duration-300 relative group overflow-hidden"
                                :class="[
                                    form.planning_model_id === model.id ? 'border-gold-400 bg-gold-50/20 shadow-sm' : 'bg-white',
                                    !form.employee_id ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer hover:border-gold-300 hover:shadow-md hover:-translate-y-0.5'
                                ]"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold text-xs text-charcoal-700 truncate">{{ model.name }}</h4>
                                    <span class="px-1.5 py-0.5 bg-pearl-100 text-charcoal-700 rounded-md text-[9px] font-black">{{ Math.round(model.total_hours) }}h</span>
                                </div>
                                <p class="text-[10px] text-charcoal-400 truncate">{{ model.description || 'Horaires hebdomadaires' }}</p>
                            </div>
                        </div>

                        <div v-if="totalPagesModel > 1" class="flex items-center justify-between pt-4 mt-2">
                            <span class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Page {{ currentPageModel }} / {{ totalPagesModel }}</span>
                            <div class="flex gap-2">
                                <button @click="currentPageModel--" :disabled="currentPageModel === 1" class="p-1.5 rounded-lg border border-pearl-100 disabled:opacity-30 hover:bg-pearl-50 transition-all"><svg class="w-4 h-4 text-charcoal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                                <button @click="currentPageModel++" :disabled="currentPageModel === totalPagesModel" class="p-1.5 rounded-lg border border-pearl-100 disabled:opacity-30 hover:bg-pearl-50 transition-all"><svg class="w-4 h-4 text-charcoal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-pearl-100 bg-white">
                        <h2 class="text-sm font-bold text-charcoal-700 flex items-center gap-3">
                            <svg class="w-4 h-4 text-charcoal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Étape 3 : Période d'application
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
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-2">Date de fin <span class="text-red-500">*</span></label>
                                <input v-model="form.end_date" type="date" :disabled="!form.planning_model_id" :min="form.start_date"
                                    class="w-full bg-pearl-50 border border-pearl-200 rounded-lg px-4 py-2.5 text-xs text-charcoal-700 focus:border-gold-400 outline-none transition-all disabled:opacity-40 disabled:cursor-not-allowed" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky top-6">
                <div class="bg-white rounded-xl border border-pearl-200 shadow-sm">
                    <div class="px-8 py-6 border-b border-pearl-100">
                        <h2 class="text-lg font-bold text-charcoal-700">Récapitulatif</h2>
                    </div>
                    
                    <div class="p-8 space-y-8">
                        <div class="pb-6 border-b border-pearl-50">
                            <div class="text-[11px] font-bold text-charcoal-400 uppercase tracking-[0.1em] mb-2">Superviseur</div>
                            <div class="text-sm font-bold transition-all" :class="selectedSupervisor ? 'text-charcoal-700' : 'text-charcoal-300 italic font-medium'">
                                {{ selectedSupervisor ? selectedSupervisor.name : 'Non sélectionné' }}
                            </div>
                        </div>
                        
                        <div class="pb-6 border-b border-pearl-50">
                            <div class="text-[11px] font-bold text-charcoal-400 uppercase tracking-[0.1em] mb-2">Modèle de planning</div>
                            <div class="text-sm font-bold transition-all" :class="selectedModel ? 'text-charcoal-700' : 'text-charcoal-300 italic font-medium'">
                                {{ selectedModel ? selectedModel.name : 'Non sélectionné' }}
                            </div>
                        </div>
                        
                        <div class="pb-6 border-b border-pearl-50">
                            <div class="text-[11px] font-bold text-charcoal-400 uppercase tracking-[0.1em] mb-2">Période</div>
                            <div class="text-sm font-bold transition-all" :class="form.start_date ? 'text-charcoal-700' : 'text-charcoal-300 italic font-medium'">
                                <template v-if="form.start_date">
                                    Du {{ new Date(form.start_date).toLocaleDateString() }} 
                                    {{ form.end_date ? ' au ' + new Date(form.end_date).toLocaleDateString() : '' }}
                                </template>
                                <template v-else>Non définie</template>
                            </div>
                        </div>

                        <!-- Bouton déplacé dans le header -->
                        <div class="pt-4 text-center">
                            <p class="text-[9px] text-charcoal-400 font-medium">
                                Les champs marqués d'un astérisque (*) sont obligatoires.
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
