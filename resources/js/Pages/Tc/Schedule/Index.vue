<script setup>
import TcLayout from '@/Layouts/TcLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    currentTelemarketer: Object,
    activePlanning: Object,
    planningModel: Object,
    planningHistory: Array,
    pastPlannings: Array,
    message: String
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

const daysFull = [
    { key: 'monday_hours', label: 'Lundi' },
    { key: 'tuesday_hours', label: 'Mardi' },
    { key: 'wednesday_hours', label: 'Mercredi' },
    { key: 'thursday_hours', label: 'Jeudi' },
    { key: 'friday_hours', label: 'Vendredi' },
    { key: 'saturday_hours', label: 'Samedi' },
    { key: 'sunday_hours', label: 'Dimanche' },
];

// Utilitaires de formatage
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
        <!-- 1. Header -->
        <template #header>
            <div class="mb-4">
                <h1 class="text-2xl font-bold text-gray-800">Mon Planning</h1>
                <p class="text-gray-500 text-sm mt-1">{{ currentTelemarketer?.campaign }}</p>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- 2. Card informations personnelles -->
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

            <!-- 3. Section Mon planning actuel -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
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
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100">{{ planningModel?.total_hours }}h / semaine</span>
                    </div>

                    <!-- Grille 7 colonnes -->
                    <div class="grid grid-cols-7 gap-4 items-end h-40 mb-8">
                        <div v-for="day in daysShort" :key="day.key" class="flex flex-col items-center gap-2.5 h-full justify-end">
                            <div class="w-full bg-gray-100 rounded-t-lg relative overflow-hidden h-32 flex flex-col justify-end">
                                <div class="w-full bg-blue-500 transition-all duration-500" :style="{ height: getBarHeight(planningModel?.[day.key]) }"></div>
                            </div>
                            <div class="text-[11px] font-bold text-gray-800">{{ formatDisplayHours(planningModel?.[day.key]) }}</div>
                            <div class="text-[10px] font-medium text-gray-400">{{ day.label }}</div>
                        </div>
                    </div>

                    <div v-if="activePlanning.status === 'validé'" class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 flex items-center gap-3 text-emerald-800">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <p class="text-xs font-semibold text-emerald-700">Planning validé le {{ formatDateTime(activePlanning.updated_at) }} — Vous pouvez saisir vos heures.</p>
                    </div>
                </div>

                <div v-else class="p-12 text-center">
                    <p class="text-sm text-gray-400 italic">Aucun planning actif pour le moment.</p>
                </div>
            </div>

            <!-- 4. Historique du planning actuel -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Historique du planning actuel
                    </h3>
                </div>
                <div class="p-4 space-y-3 bg-gray-50/30">
                    <div v-for="log in planningHistory" :key="log.id" class="p-4 bg-gray-50 rounded-xl border border-gray-100 flex items-start gap-4 transition-all hover:border-blue-100">
                        <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-blue-500 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span v-if="log.old_status" :class="['px-2 py-0.5 rounded text-[9px] font-bold border', getStatusBadge(log.old_status).color]">
                                    {{ getStatusBadge(log.old_status).label }}
                                </span>
                                <svg v-if="log.old_status" class="w-3 h-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                <span :class="['px-2 py-0.5 rounded text-[9px] font-bold border', getStatusBadge(log.new_status).color]">
                                    {{ getStatusBadge(log.new_status).label }}
                                </span>
                            </div>
                            <p class="text-xs font-medium text-gray-700 leading-tight">{{ log.reason || (log.old_status ? 'Validation du planning' : 'Création de l\'affectation') }}</p>
                            <p class="text-[10px] text-gray-400 mt-1.5 font-medium">
                                {{ formatDateTime(log.created_at) }} • Par {{ log.changed_by_name || 'Admin' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Plannings précédents -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Plannings précédents
                    </h3>
                </div>
                <div class="p-4 space-y-2 bg-gray-50/30">
                    <div v-for="past in pastPlannings" :key="past.id" class="px-5 py-4 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between hover:border-blue-100 transition-all">
                        <div>
                            <p class="text-xs font-bold text-gray-800">{{ past.planning_model?.name }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5 font-medium">{{ formatDate(past.start_date) }} - {{ formatDate(past.end_date) }}</p>
                        </div>
                        <span class="text-[10px] font-bold text-gray-600 bg-gray-200/50 px-2.5 py-1 rounded-lg">{{ past.planning_model?.total_hours }}h/sem</span>
                    </div>
                    <div v-if="!pastPlannings?.length" class="p-8 text-center text-[10px] text-gray-400 font-medium uppercase tracking-widest">
                        Aucun historique
                    </div>
                </div>
            </div>

            <!-- 6. Card Informations importantes -->
            <div class="bg-charcoal-900 rounded-xl p-6 text-white relative overflow-hidden shadow-lg border border-charcoal-800">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl"></div>
                <h3 class="text-sm font-bold mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Informations importantes
                </h3>
                <ul class="space-y-4">
                    <li class="flex gap-4 items-start">
                        <div class="w-1 h-1 rounded-full bg-blue-400 mt-1.5 flex-shrink-0"></div>
                        <p class="text-[11px] text-gray-400 leading-relaxed font-medium">Les feuilles de temps doivent être saisies quotidiennement avant la fin de votre shift.</p>
                    </li>
                    <li class="flex gap-4 items-start">
                        <div class="w-1 h-1 rounded-full bg-blue-400 mt-1.5 flex-shrink-0"></div>
                        <p class="text-[11px] text-gray-400 leading-relaxed font-medium">Seuls les plannings avec le statut <span class="text-blue-400 font-bold">Validé</span> sont contractuels.</p>
                    </li>
                    <li class="flex gap-4 items-start">
                        <div class="w-1 h-1 rounded-full bg-blue-400 mt-1.5 flex-shrink-0"></div>
                        <p class="text-[11px] text-gray-400 leading-relaxed font-medium">Toute absence doit être justifiée auprès de votre superviseur direct.</p>
                    </li>
                </ul>
            </div>
        </div>
    </TcLayout>
</template>

<style scoped>
/* Suppression des dégradés dorés pour coller au design épuré des captures */
.shadow-lg {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
