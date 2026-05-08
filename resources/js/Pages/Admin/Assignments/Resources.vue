<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    employees: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const showReleaseModal = ref(false);
const selectedAssignment = ref(null);
const selectedEmployeeName = ref('');
const releaseCascade = ref(true);

const updateFilters = () => {
    router.get(route('admin.assignments.resources'), {
        search: search.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch([search, statusFilter], () => {
    // Debounce here
    setTimeout(updateFilters, 300);
});

const getStatusBadge = (employee) => {
    const activeAssignment = employee.assignments?.[0];
    if (activeAssignment) {
        return 'bg-emerald-100 text-emerald-700';
    }
    return 'bg-amber-100 text-amber-700';
};

const getStatusLabel = (employee) => {
    const activeAssignment = employee.assignments?.[0];
    if (activeAssignment) {
        return 'Affecté';
    }
    return 'Disponible';
};

const openReleaseModal = (emp) => {
    selectedAssignment.value = emp.assignments?.[0];
    selectedEmployeeName.value = `${emp.first_name} ${emp.last_name}`;
    releaseCascade.value = true;
    showReleaseModal.value = true;
};

const confirmRelease = () => {
    router.patch(route('admin.assignments.release', selectedAssignment.value.id), {
        cascade: releaseCascade.value
    }, {
        onSuccess: () => showReleaseModal.value = false
    });
};
</script>


<template>
    <Head title="Ressources disponibles — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Gestion des Ressources</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Visualisez l'état d'affectation de tout votre personnel</p>
                </div>
                <div class="flex gap-2">
                    <Link 
                        :href="route('admin.assignments.structure')"
                        class="px-4 py-2 bg-charcoal-900 text-white rounded-lg text-xs font-bold hover:bg-gold-600 transition-all flex items-center gap-2 shadow-lg"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.172-1.172a4 4 0 115.656 5.656L13.828 15.828a2 2 0 01-2.828 0l-1.172-1.172" />
                        </svg>
                        Gérer la Structure
                    </Link>
                </div>
            </div>
        </template>

        <!-- Filters -->
        <div class="mb-8 bg-white p-5 rounded-2xl border border-pearl-200 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <div class="flex gap-4 flex-1 max-w-2xl">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Nom, prénom ou matricule..." 
                        class="block w-full pl-10 pr-3 py-2.5 border border-pearl-200 rounded-xl text-xs focus:ring-gold-500 focus:border-gold-500"
                    />
                </div>
                <select 
                    v-model="statusFilter"
                    class="border border-pearl-200 rounded-xl text-xs px-4 py-2.5 focus:ring-gold-500 focus:border-gold-500 text-charcoal-600 bg-pearl-50"
                >
                    <option value="">Tous les états</option>
                    <option value="assigned">Affectés</option>
                    <option value="unassigned">Non affectés</option>
                </select>
            </div>
        </div>

        <!-- Resources Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            <div v-for="emp in employees.data" :key="emp.id" class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-xl transition-all group overflow-hidden relative">
                <!-- Status Strip -->
                <div :class="getStatusBadge(emp)" class="absolute top-0 right-0 px-4 py-1 rounded-bl-xl text-[10px] font-black uppercase tracking-widest">
                    {{ getStatusLabel(emp) }}
                </div>

                <div class="flex items-center gap-4 mb-6 pt-2">
                    <div class="w-14 h-14 rounded-2xl bg-pearl-100 flex items-center justify-center text-charcoal-700 font-black text-lg border border-pearl-200 shadow-inner group-hover:bg-gold-100 group-hover:text-gold-700 transition-colors">
                        {{ emp.first_name.charAt(0) }}{{ emp.last_name.charAt(0) }}
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-charcoal-800 leading-tight">{{ emp.first_name }} {{ emp.last_name }}</h3>
                        <p class="text-[10px] text-gold-600 font-bold uppercase tracking-widest mt-1">{{ emp.position?.name }}</p>
                    </div>
                </div>

                <div class="space-y-3 mb-6 bg-pearl-50/50 p-3 rounded-xl border border-pearl-50">
                    <div class="flex justify-between items-center text-[10px]">
                        <span class="text-charcoal-400 font-bold uppercase">Matricule</span>
                        <span class="text-charcoal-700 font-black tracking-wider">{{ emp.matricule }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[10px]">
                        <span class="text-charcoal-400 font-bold uppercase">Campagne</span>
                        <span class="text-charcoal-700 font-bold truncate max-w-[120px]">
                            {{ emp.assignments?.[0]?.campaign?.name || 'Aucune' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-[10px]">
                        <span class="text-charcoal-400 font-bold uppercase">Embauche</span>
                        <span class="text-charcoal-700">{{ new Date(emp.hire_date).toLocaleDateString('fr-FR') }}</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Link 
                        :href="route('admin.employees.index', { search: emp.matricule })"
                        class="flex-1 py-2 bg-pearl-100 text-charcoal-600 rounded-lg text-[10px] font-bold uppercase text-center hover:bg-pearl-200 transition-colors"
                    >
                        Profil
                    </Link>
                    <Link 
                        v-if="!emp.assignments?.[0] || emp.position?.code === 'CP'"
                        :href="route('admin.assignments.structure', { assign: emp.id })"
                        class="flex-[2] py-2 bg-charcoal-900 text-white rounded-lg text-[10px] font-black uppercase tracking-widest text-center hover:bg-gold-600 transition-colors"
                    >
                        Affecter
                    </Link>
                    <button 
                        v-else
                        @click="openReleaseModal(emp)"
                        class="flex-[2] py-2 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg text-[10px] font-black uppercase tracking-widest text-center hover:bg-rose-600 hover:text-white transition-colors"
                    >
                        Libérer
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="employees.data.length === 0" class="bg-white rounded-2xl border border-pearl-200 p-12 text-center shadow-sm">
            <div class="w-20 h-20 bg-pearl-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-pearl-100 text-charcoal-300">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <h3 class="text-charcoal-700 font-bold text-lg">Aucune ressource trouvée</h3>
            <p class="text-charcoal-400 text-xs mt-1">Ajustez vos filtres pour voir d'autres membres du personnel.</p>
        </div>

        <!-- Pagination -->
        <div v-if="employees.links.length > 3" class="mt-8 flex justify-center">
            <div class="flex gap-1 bg-white p-1 rounded-xl border border-pearl-200 shadow-sm">
                <Link 
                    v-for="link in employees.links" 
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
                <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-pearl-100">
                    <!-- Header -->
                    <div class="px-8 py-5 bg-rose-50/50 border-b border-rose-100 flex justify-between items-center">
                        <h2 class="text-base font-black text-rose-600 uppercase tracking-[0.1em] flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Confirmation de libération
                        </h2>
                        <button @click="showReleaseModal = false" class="text-rose-300 hover:text-rose-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    
                    <div class="p-8 space-y-8">
                        <p class="text-charcoal-600 text-sm font-medium leading-relaxed">
                            Voulez-vous vraiment libérer <strong>{{ selectedEmployeeName }}</strong> ? Cette action mettra fin à son affectation actuelle.
                        </p>

                        <!-- Choice for Managers (CP/SUP) -->
                        <div v-if="['CP', 'SUP'].includes(selectedAssignment?.position?.code)" class="bg-pearl-50/50 p-6 rounded-2xl border border-pearl-100">
                            <label class="block text-[11px] font-black text-charcoal-500 uppercase tracking-[0.15em] mb-4">Options de libération</label>
                            <div class="space-y-4">
                                <label class="flex items-start gap-4 cursor-pointer group">
                                    <div class="relative flex items-center h-5">
                                        <input type="radio" v-model="releaseCascade" :value="true" class="w-5 h-5 text-rose-600 focus:ring-rose-500 border-pearl-300 transition-all">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-charcoal-700 group-hover:text-charcoal-900 transition-colors">Libérer avec tous les subordonnés</span>
                                        <span class="text-[11px] text-charcoal-400 italic font-medium">(Cascade complète)</span>
                                    </div>
                                </label>
                                <label class="flex items-start gap-4 cursor-pointer group">
                                    <div class="relative flex items-center h-5">
                                        <input type="radio" v-model="releaseCascade" :value="false" class="w-5 h-5 text-rose-600 focus:ring-rose-500 border-pearl-300 transition-all">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-charcoal-700 group-hover:text-charcoal-900 transition-colors">Libérer lui seul</span>
                                        <span class="text-[11px] text-charcoal-400 italic font-medium">(Les subordonnés restent en poste)</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div v-else class="p-4 bg-emerald-50/50 text-emerald-700 text-[11px] font-bold rounded-xl border border-emerald-100 italic flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Libération directe sans impact sur d'autres ressources.
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex items-center justify-end gap-6 pt-6 border-t border-pearl-100">
                            <button @click="showReleaseModal = false" class="text-xs font-black uppercase tracking-widest text-charcoal-400 hover:text-charcoal-600 transition-all">
                                Annuler
                            </button>
                            <button @click="confirmRelease" class="px-8 py-3.5 bg-rose-600 text-white rounded-xl text-xs font-black uppercase tracking-[0.1em] hover:bg-rose-700 hover:shadow-xl hover:shadow-rose-500/20 active:scale-95 transition-all shadow-lg shadow-rose-600/10">
                                Confirmer la libération
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
</style>
