<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// ── KPI Stats ─────────────────────────────────────────────────────────────────
const stats = [
    {
        label: 'Employés actifs',
        value: '1,247',
        trend: '+1% vs mois dernier',
        trendUp: true,
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`,
        color: 'blue',
    },
    {
        label: 'Campagnes en cours',
        value: '8',
        trend: '+2 vs mois dernier',
        trendUp: true,
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>`,
        color: 'green',
    },
    {
        label: "Taux d'affectation",
        value: '94%',
        trend: '+2% vs mois dernier',
        trendUp: true,
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>`,
        color: 'gold',
    },
    {
        label: 'Alertes actives',
        value: '3',
        trend: '2 vs mois dernier',
        trendUp: false,
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`,
        color: 'orange',
    },
];

// ── Audit Logs ────────────────────────────────────────────────────────────────
const auditLogs = ref([
    { time: '10:14', action: 'Création employé #1246', user: 'Jean Dupont',      badge: 'success' },
    { time: '10:15', action: 'Modification rôle CP – Campagne A', user: 'Marc Morin',    badge: 'info'    },
    { time: '11:36', action: 'Clôture automatique planning – Semaine 18', user: 'System',  badge: 'warning' },
    { time: '09:41', action: 'Affectation TC → SUP sur Campagne B', user: 'Aliman',       badge: 'success' },
    { time: '09:50', action: "Tentative accès non autorisé",          user: 'Pierre Durand', badge: 'danger' },
]);

const badgeClasses = {
    success: 'bg-emerald-100 text-emerald-700 border border-emerald-200',
    info:    'bg-blue-100 text-blue-700 border border-blue-200',
    warning: 'bg-amber-100 text-amber-700 border border-amber-200',
    danger:  'bg-red-100 text-red-600 border border-red-200',
};

// ── Campagnes actives ─────────────────────────────────────────────────────────
const campaigns = ref([
    { name: 'Campagne Printemps 2026', persons: 186, progress: 80 },
    { name: 'Ventes Flash Mail',        persons: 88,  progress: 55 },
    { name: 'Support Client Premium',   persons: 244, progress: 40 },
]);

const statColors = {
    blue:   { bg: 'bg-blue-50',   icon: 'text-blue-500',   border: 'border-blue-100' },
    green:  { bg: 'bg-emerald-50', icon: 'text-emerald-500', border: 'border-emerald-100' },
    gold:   { bg: 'bg-amber-50',  icon: 'text-amber-500',  border: 'border-amber-100' },
    orange: { bg: 'bg-orange-50', icon: 'text-orange-400', border: 'border-orange-100' },
};
</script>

<template>
    <Head title="Tableau de bord — Administration" />
    <AdminLayout>

        <!-- Header -->
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Tableau de bord</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Vue d'ensemble de votre entreprise</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-charcoal-400">
                        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                    </span>
                </div>
            </div>
        </template>

        <!-- ── KPI Cards ─────────────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="bg-white rounded-xl border p-5 flex items-start justify-between shadow-sm hover:shadow-md transition-shadow duration-200"
                :class="statColors[stat.color].border"
            >
                <div class="flex-1">
                    <p class="text-xs text-charcoal-400 font-medium mb-1">{{ stat.label }}</p>
                    <p class="text-3xl font-black text-charcoal-700 leading-none mb-2">{{ stat.value }}</p>
                    <p class="text-[11px] font-medium" :class="stat.trendUp ? 'text-emerald-600' : 'text-orange-500'">
                        {{ stat.trend }}
                    </p>
                </div>
                <div
                    class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 ml-3"
                    :class="[statColors[stat.color].bg, statColors[stat.color].icon]"
                    v-html="stat.icon"
                ></div>
            </div>
        </div>

        <!-- ── Grille principale ──────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            <!-- Audit Logs temps réel -->
            <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-pearl-200">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <h2 class="text-sm font-bold text-charcoal-700">Logs d'Audit (Temps réel)</h2>
                </div>
                <div class="divide-y divide-pearl-100">
                    <div
                        v-for="log in auditLogs"
                        :key="log.time + log.action"
                        class="flex items-center gap-3 px-5 py-3 hover:bg-pearl-50 transition-colors duration-100"
                    >
                        <!-- Temps -->
                        <div class="flex items-center gap-1.5 w-14 flex-shrink-0">
                            <svg class="w-3 h-3 text-charcoal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-[11px] text-charcoal-400 font-mono">{{ log.time }}</span>
                        </div>

                        <!-- Action + User -->
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-charcoal-700 truncate">{{ log.action }}</p>
                            <p class="text-[11px] text-charcoal-400 truncate">{{ log.user }}</p>
                        </div>

                        <!-- Badge -->
                        <span
                            class="text-[10px] font-semibold px-2 py-0.5 rounded-full capitalize flex-shrink-0"
                            :class="badgeClasses[log.badge]"
                        >{{ log.badge }}</span>
                    </div>
                </div>
            </div>

            <!-- Campagnes actives -->
            <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-pearl-200">
                    <svg class="w-4 h-4 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <h2 class="text-sm font-bold text-charcoal-700">Campagnes actives</h2>
                </div>
                <div class="p-5 space-y-5">
                    <div v-for="c in campaigns" :key="c.name">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-charcoal-700">{{ c.name }}</span>
                            <span class="text-[11px] text-charcoal-400">{{ c.persons }} personnes</span>
                        </div>
                        <div class="w-full bg-pearl-200 rounded-full h-2 overflow-hidden">
                            <div
                                class="h-2 rounded-full bg-gold-gradient transition-all duration-700"
                                :style="`width: ${c.progress}%`"
                            ></div>
                        </div>
                        <p class="text-[11px] text-charcoal-400 mt-1">{{ c.progress }}% complété</p>
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
