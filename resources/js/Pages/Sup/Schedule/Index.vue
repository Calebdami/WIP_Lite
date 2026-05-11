<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head } from '@inertiajs/vue3';
import { formatHours, formatHoursShort } from '@/Utils/formatHours';

const props = defineProps({
    currentSupervisor: Object,
    myPlanning: Object,
    teamPlannings: Array,
    stats: Object
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
        <!-- 1. Header -->
        <template #header>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Mon Planning & Équipe</h1>
                <p class="text-gray-500 text-sm mt-1">Campagne : {{ currentSupervisor?.campaign }}</p>
            </div>
        </template>

        <div class="space-y-8 pb-20">
            <!-- 2. 4 cartes de statistiques -->
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
                <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ stats?.validated_count }}</p>
                        <p class="text-[11px] text-gray-400 font-medium">Plannings validés</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ stats?.pending_count }}</p>
                        <p class="text-[11px] text-gray-400 font-medium">En attente</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ formatHours(stats?.total_hours || 0) }}</p>
                        <p class="text-[11px] text-gray-400 font-medium">Volume total</p>
                    </div>
                </div>
            </div>

            <!-- 3. Section Mon planning personnel -->
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
                        <span
                            :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border', getStatusBadge(myPlanning.status).color]">
                            {{ getStatusBadge(myPlanning.status).label }}
                        </span>
                        <span class="text-[11px] text-gray-400 font-medium">Du {{ formatDate(myPlanning.start_date) }}
                            au {{
                                formatDate(myPlanning.end_date) }}</span>
                    </div>
                </div>

                <div v-if="myPlanning?.status === 'validé'" class="p-6">
                    <div class="flex items-center justify-between mb-8">
                        <h4 class="text-sm font-bold text-gray-800">{{ myPlanning.planning_model?.name }}</h4>
                        <span
                            class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100">{{
                                myPlanning.planning_model?.total_hours }}h / semaine</span>
                    </div>
                    <div class="grid grid-cols-7 gap-4 items-end h-40">
                        <div v-for="day in daysShort" :key="day.key"
                            class="flex flex-col items-center gap-2.5 h-full justify-end">
                            <div
                                class="w-full bg-gray-100 rounded-t-lg relative overflow-hidden h-32 flex flex-col justify-end">
                                <div class="w-full bg-blue-500 transition-all duration-500"
                                    :style="{ height: getBarHeight(myPlanning.planning_model?.[day.key]) }"></div>
                            </div>
                            <div class="text-[11px] font-bold text-gray-800">{{
                                formatDisplayHours(myPlanning.planning_model?.[day.key]) }}</div>
                            <div class="text-[10px] font-medium text-gray-400">{{ day.label }}</div>
                        </div>
                    </div>
                </div>
                <div v-else-if="myPlanning" class="p-8">
                    <div
                        class="bg-orange-50 border border-orange-100 rounded-lg p-6 flex flex-col items-center text-center">
                        <svg class="w-8 h-8 text-orange-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h4 class="text-sm font-bold text-orange-800">En attente de validation par le CP</h4>
                        <p class="text-xs text-orange-600 mt-1">Les détails de votre planning seront visibles dès qu'il
                            sera
                            validé.</p>
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400 text-sm italic">Aucun planning personnel actif.</div>
            </div>

            <!-- 4. Section Plannings de mon équipe -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Plannings de mon équipe ({{ teamPlannings?.length || 0 }} TC)
                    </h3>

                    <div class="space-y-4">
                        <div v-for="tp in teamPlannings" :key="tp.id"
                            class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">{{ tp.tc_name }}</h4>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Du {{ formatDate(tp.start_date) }} au {{
                                        formatDate(tp.end_date) }}</p>
                                </div>
                                <span
                                    :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border', getStatusBadge(tp.status).color]">
                                    {{ getStatusBadge(tp.status).label }}
                                </span>
                            </div>
                            <div v-if="tp.status === 'validé'" class="p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h5 class="text-xs font-bold text-gray-700">{{ tp.planning_model?.name }}</h5>
                                    <span
                                        class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">{{
                                        tp.total_hours }}h/sem</span>
                                </div>
                                <div class="grid grid-cols-7 gap-3 items-end h-32">
                                    <div v-for="day in daysShort" :key="day.key"
                                        class="flex flex-col items-center gap-1.5 h-full justify-end">
                                        <div
                                            class="w-full bg-gray-100 rounded-t-md relative overflow-hidden h-24 flex flex-col justify-end">
                                            <div class="w-full bg-blue-400 transition-all duration-500"
                                                :style="{ height: getBarHeight(tp.planning_model?.[day.key]) }"></div>
                                        </div>
                                        <div class="text-[10px] font-medium text-gray-400 uppercase tracking-tighter">{{
                                            day.label }}</div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="p-4">
                                <div
                                    class="bg-gray-50 border border-gray-100 rounded-lg p-3 text-center text-gray-400 text-xs italic">
                                    Planning en attente de validation - Détails non disponibles
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SupLayout>
</template>
