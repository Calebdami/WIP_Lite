<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    recent_activities: Array,
    active_campaigns: Array
});

// ── KPI Stats Logic ───────────────────────────────────────────────────────────
const kpiStats = computed(() => [
    {
        label: 'Employés actifs',
        value: props.stats?.active_employees || '0',
        trend: 'Temps réel',
        trendUp: true,
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`,
        color: 'blue',
    },
    {
        label: 'Campagnes en cours',
        value: props.stats?.campaigns_count || '0',
        trend: 'Activées',
        trendUp: true,
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>`,
        color: 'green',
    },
    {
        label: "Taux d'affectation",
        value: props.stats?.assignment_rate || '0%',
        trend: 'Global',
        trendUp: true,
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>`,
        color: 'gold',
    },
    {
        label: 'Utilisateurs Système',
        value: props.stats?.users_count || '0',
        trend: 'Accès gérés',
        trendUp: true,
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>`,
        color: 'orange',
    },
]);

const badgeClasses = {
    success: 'bg-emerald-100 text-emerald-700 border border-emerald-200',
    info:    'bg-blue-100 text-blue-700 border border-blue-200',
    warning: 'bg-amber-100 text-amber-700 border border-amber-200',
    danger:  'bg-red-100 text-red-600 border border-red-200',
};

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
                    <span class="text-[11px] text-charcoal-400 font-bold uppercase tracking-widest">
                        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                    </span>
                </div>
            </div>
        </template>

        <!-- ── KPI Cards ─────────────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <div
                v-for="stat in kpiStats"
                :key="stat.label"
                class="bg-white rounded-xl border p-5 flex items-start justify-between shadow-sm hover:shadow-md transition-all duration-200"
                :class="statColors[stat.color].border"
            >
                <div class="flex-1">
                    <p class="text-xs text-charcoal-400 font-black uppercase tracking-widest mb-1">{{ stat.label }}</p>
                    <p class="text-3xl font-black text-charcoal-700 leading-none mb-2">{{ stat.value }}</p>
                    <p class="text-[10px] font-bold uppercase tracking-tighter" :class="stat.trendUp ? 'text-emerald-600' : 'text-orange-500'">
                        {{ stat.trend }}
                    </p>
                </div>
                <div
                    class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 ml-3 shadow-inner"
                    :class="[statColors[stat.color].bg, statColors[stat.color].icon]"
                    v-html="stat.icon"
                ></div>
            </div>
        </div>

        <!-- ── Grille principale ──────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            <!-- Audit Logs temps réel -->
            <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium overflow-hidden">
                <div class="flex items-center gap-2 px-6 py-5 border-b border-pearl-200">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-charcoal-700">Activités Récentes</h2>
                </div>
                <div class="divide-y divide-pearl-50">
                    <div
                        v-for="log in recent_activities"
                        :key="log.time + log.action"
                        class="flex items-center gap-3 px-6 py-4 hover:bg-pearl-50/50 transition-colors duration-100"
                    >
                        <!-- Temps -->
                        <div class="flex items-center gap-1.5 w-14 flex-shrink-0">
                            <i class="pi pi-clock text-[10px] text-charcoal-400"></i>
                            <span class="text-[11px] text-charcoal-400 font-mono font-bold">{{ log.time }}</span>
                        </div>

                        <!-- Action + User -->
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-charcoal-700 truncate capitalize">{{ log.action }}</p>
                            <p class="text-[10px] text-charcoal-400 truncate uppercase tracking-tighter">{{ log.user }}</p>
                        </div>

                        <!-- Badge -->
                        <span
                            class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-lg border flex-shrink-0"
                            :class="badgeClasses[log.badge]"
                        >{{ log.badge }}</span>
                    </div>
                    <div v-if="!recent_activities?.length" class="p-12 text-center text-charcoal-300 italic text-xs">
                        Aucune activité récente enregistrée.
                    </div>
                </div>
            </div>

            <!-- Campagnes actives -->
            <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium overflow-hidden">
                <div class="flex items-center gap-2 px-6 py-5 border-b border-pearl-200">
                    <i class="pi pi-briefcase text-gold-500"></i>
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-charcoal-700">Progression des Campagnes</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div v-for="c in active_campaigns" :key="c.name">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-black text-charcoal-700 uppercase tracking-tight">{{ c.name }}</span>
                            <span class="text-[10px] font-bold text-gold-600 bg-gold-50 px-2 py-0.5 rounded-lg">{{ c.persons }} agents</span>
                        </div>
                        <div class="w-full bg-pearl-100 rounded-full h-2.5 overflow-hidden shadow-inner border border-pearl-200">
                            <div
                                class="h-full rounded-full bg-gold-gradient transition-all duration-1000 ease-out shadow-gold"
                                :style="`width: ${c.progress}%`"
                            ></div>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <p class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">{{ c.progress }}% Complété</p>
                            <i class="pi pi-arrow-right text-[10px] text-pearl-300"></i>
                        </div>
                    </div>
                    <div v-if="!active_campaigns?.length" class="p-6 text-center text-charcoal-300 italic text-xs">
                        Aucune campagne active pour le moment.
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
.shadow-gold {
    box-shadow: 0 0 15px -3px rgba(212, 160, 23, 0.4);
}
.shadow-premium {
    box-shadow: 0 10px 40px -15px rgba(0,0,0,0.08);
}
</style>
