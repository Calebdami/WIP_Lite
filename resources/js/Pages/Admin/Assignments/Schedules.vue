<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    models: Object,
    filters: Object
});

const search = ref(props.filters.search || '');

const modelsData = computed(() => props.models.data);

// Custom debounce function to avoid lodash dependency
const debounce = (fn, delay) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

// Debounced search
watch(search, debounce((value) => {
    router.get(route('admin.assignments.schedules'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));

// Formater les jours (ex: Lun-Ven)
const formatDays = (model) => {
    const days = [
        { key: 'monday_hours', label: 'Lun' },
        { key: 'tuesday_hours', label: 'Mar' },
        { key: 'wednesday_hours', label: 'Mer' },
        { key: 'thursday_hours', label: 'Jeu' },
        { key: 'friday_hours', label: 'Ven' },
        { key: 'saturday_hours', label: 'Sam' },
        { key: 'sunday_hours', label: 'Dim' }
    ];
    
    const activeDays = days.filter(d => parseFloat(model[d.key]) > 0);
    
    if (activeDays.length === 0) return 'Aucun jour';
    
    // Cas particuliers courants
    const labels = activeDays.map(d => d.label);
    if (labels.length === 5 && labels.includes('Lun') && labels.includes('Ven') && !labels.includes('Sam')) {
        return 'Lun-Ven';
    }
    if (labels.length === 2 && labels.includes('Sam') && labels.includes('Dim')) {
        return 'Sam-Dim';
    }
    if (labels.length === 7) {
        return 'Lun-Dim';
    }
    
    return labels.join(', ');
};

// Vérifier si les heures sont constantes pour afficher un résumé
const getHoursSummary = (model) => {
    const hours = [
        model.monday_hours, model.tuesday_hours, model.wednesday_hours,
        model.thursday_hours, model.friday_hours, model.saturday_hours, model.sunday_hours
    ].map(h => parseFloat(h)).filter(h => h > 0);
    
    const uniqueHours = [...new Set(hours)];
    if (uniqueHours.length === 1) {
        return `${uniqueHours[0]}h / jour`;
    }
    return 'Horaires variables';
};

// Formater la date de dernière affectation
const formatLatestDate = (date) => {
    if (!date) return null;
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short'
    });
};

const deleteModel = (model) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le modèle "${model.name}" ?`)) {
        router.delete(route('admin.assignments.schedules.destroy', model.id), {
            onSuccess: () => {
                // Flash message will handle success notification
            }
        });
    }
};
</script>

<template>
    <Head title="Bibliothèque de Plannings — Admin" />
    <AdminLayout>
        <!-- Affichage des erreurs flash (facultatif si géré globalement) -->
        <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg text-xs font-bold">
            {{ $page.props.flash.error }}
        </div>

        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Bibliothèque de Plannings</h1>
                    <div class="flex items-center gap-3 mt-1">
                        <p class="text-xs text-charcoal-400">Gérez vos semaines types de travail</p>
                        <div class="h-1 w-1 rounded-full bg-pearl-300"></div>
                        <div class="relative group">
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Rechercher un modèle..." 
                                class="pl-8 pr-4 py-1.5 bg-pearl-50 border border-pearl-200 rounded-lg text-[11px] text-charcoal-700 focus:border-gold-400 focus:bg-white outline-none transition-all w-48 md:w-64"
                            />
                            <svg class="w-3.5 h-3.5 text-charcoal-300 absolute left-2.5 top-1/2 -translate-y-1/2 group-focus-within:text-gold-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.assignments.schedules.assign')" class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-bold text-charcoal-600 hover:bg-pearl-50 transition-all flex items-center gap-2 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Affecter SUP
                    </Link>
                    <Link :href="route('cp.schedules.assign')" class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-bold text-charcoal-600 hover:bg-pearl-50 transition-all flex items-center gap-2 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Affecter TC
                    </Link>
                    <Link :href="route('admin.assignments.schedules.create')" class="px-4 py-2 bg-gold-gradient rounded-lg text-xs font-bold text-white hover:opacity-90 transition-all flex items-center gap-2 shadow-gold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nouveau modèle
                    </Link>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div v-for="model in modelsData" :key="model.id" class="group bg-white rounded-xl border border-pearl-200 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">
                <!-- Header de la carte -->
                <div class="p-5 border-b border-pearl-100 bg-pearl-50/50 relative">
                    <div v-if="model.latest_assignment_date" class="absolute top-0 right-0 transform translate-x-[-1.25rem] translate-y-[-50%] bg-pearl-700 text-white text-[8px] font-bold px-2 py-0.5 rounded-full shadow-sm z-10">
                        Dernière util. : {{ formatLatestDate(model.latest_assignment_date) }}
                    </div>
                    <div class="flex items-start justify-between mb-1">
                        <h3 class="font-bold text-charcoal-700 group-hover:text-gold-600 transition-colors">{{ model.name }}</h3>
                        <span class="px-2 py-0.5 bg-gold-100 text-gold-700 rounded-full text-[10px] font-black tracking-wider uppercase shadow-sm">
                            {{ Math.round(model.total_hours) }}h
                        </span>
                    </div>
                    <p class="text-xs text-charcoal-400 line-clamp-2 min-h-[2rem]">{{ model.description || 'Aucune description' }}</p>
                </div>

                <!-- Détails -->
                <div class="p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-medium text-charcoal-400 uppercase tracking-widest">Jours :</span>
                        <span class="text-xs font-bold text-charcoal-700">{{ formatDays(model) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-medium text-charcoal-400 uppercase tracking-widest">Horaires :</span>
                        <span class="text-xs font-bold text-charcoal-600 italic">{{ model.hours_summary || getHoursSummary(model) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-medium text-charcoal-400 uppercase tracking-widest">Utilisé par :</span>
                        <span class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-charcoal-700">{{ model.assignments_count || 0 }}</span>
                            <span class="text-xs text-charcoal-400">employés</span>
                        </span>
                    </div>
                </div>

                <!-- Actions footer -->
                <div class="flex border-t border-pearl-100">
                    <Link :href="route('admin.assignments.schedules.edit', model.id)" class="flex-1 py-3 text-[10px] font-bold text-charcoal-500 uppercase tracking-widest hover:bg-pearl-50 hover:text-gold-600 transition-colors flex items-center justify-center gap-2 border-r border-pearl-100">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Modifier
                    </Link>
                    <button 
                        @click="deleteModel(model)"
                        class="px-4 py-3 text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors flex items-center justify-center"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="props.models.links.length > 3" class="flex items-center justify-center gap-1">
            <template v-for="(link, k) in props.models.links" :key="k">
                <div v-if="link.url === null" 
                     class="px-4 py-2 text-xs font-bold text-charcoal-300 cursor-not-allowed"
                     v-html="link.label" />
                <Link v-else
                      :href="link.url"
                      class="px-4 py-2 text-xs font-bold rounded-lg transition-all"
                      :class="link.active 
                        ? 'bg-gold-gradient text-white shadow-gold' 
                        : 'bg-white border border-pearl-200 text-charcoal-500 hover:bg-pearl-50'"
                      v-html="link.label" />
            </template>
        </div>
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
