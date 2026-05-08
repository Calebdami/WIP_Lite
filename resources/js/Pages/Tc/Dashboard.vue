<script setup>
import TcLayout from '@/Layouts/TcLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { formatHours, formatHoursShort } from '@/Utils/formatHours';

const props = defineProps({
    personal_stats: Object
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const weekProgress = computed(() => {
    const hours = props.personal_stats?.hours_week || 0;
    const target = 35; // Standard 35h
    return Math.min(Math.round((hours / target) * 100), 100);
});
</script>

<template>
    <Head title="Tableau de bord — Téléconseiller" />
    <TcLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Tableau de bord</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Espace personnel & planification</p>
                </div>
                <div class="text-[10px] font-black uppercase tracking-widest text-charcoal-400 bg-pearl-200/50 px-3 py-1 rounded-full">
                    S7 - {{ new Date().getFullYear() }}
                </div>
            </div>
        </template>

        <!-- Welcome Banner -->
        <div class="rounded-3xl p-8 mb-8 relative overflow-hidden shadow-premium"
             style="background: linear-gradient(135deg, #111827 0%, #374151 100%);">
            <div class="relative z-10">
                <p class="text-gold-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 opacity-80">Ma Session Active</p>
                <h2 class="text-3xl font-black text-white mb-3">Hello, {{ user?.name || user?.email?.split('@')[0] }} 👋</h2>
                <p class="text-charcoal-400 text-sm max-w-md leading-relaxed">
                    Consultez vos plannings et suivez vos heures de travail validées en toute simplicité.
                </p>
            </div>
            <!-- Decorative elements -->
            <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-gold-500/10 blur-3xl"></div>
            <div class="absolute right-20 top-0 w-32 h-32 rounded-full bg-white/5 blur-2xl"></div>
            <i class="pi pi-user absolute right-10 top-1/2 -translate-y-1/2 text-9xl text-white/5 pointer-events-none"></i>
        </div>

        <!-- Planning du jour & Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-4 text-gold-500">
                    <i class="pi pi-briefcase text-sm"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Ma campagne</p>
                </div>
                <p class="text-base font-black text-charcoal-700 leading-tight">{{ personal_stats?.campaign || 'Non affecté' }}</p>
                <p class="text-[10px] font-bold text-emerald-600 mt-2 uppercase tracking-tighter flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Statut Actif
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-3 text-charcoal-700">
                    <i class="pi pi-clock text-sm"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Heures de la semaine</p>
                </div>
                <div class="flex items-end gap-2">
                    <p class="text-3xl font-black text-charcoal-700">{{ formatHours(personal_stats?.hours_week || 0) }}</p>
                    <p class="text-[10px] font-bold text-charcoal-400 mb-1.5 uppercase">/ 35h</p>
                </div>
                <div class="w-full bg-pearl-100 h-1.5 rounded-full mt-4 overflow-hidden shadow-inner">
                    <div class="h-full bg-gold-gradient transition-all duration-1000" :style="`width: ${weekProgress}%`"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-4 text-charcoal-700">
                    <i class="pi pi-shield text-sm"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Mon superviseur</p>
                </div>
                <p class="text-base font-black text-charcoal-700">{{ personal_stats?.supervisor || 'N/A' }}</p>
                <p class="text-[10px] font-bold text-charcoal-400 mt-2 uppercase tracking-widest">Support hiérarchique</p>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="bg-white rounded-3xl border border-pearl-200 shadow-premium overflow-hidden">
            <div class="px-8 py-6 border-b border-pearl-100 flex items-center justify-between bg-pearl-50/30">
                <h2 class="text-xs font-black uppercase tracking-[0.2em] text-charcoal-700">Raccourcis Utiles</h2>
                <div class="flex gap-1">
                    <div class="w-1.5 h-1.5 rounded-full bg-gold-400"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-gold-200"></div>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link
                    :href="route('tc.hours')"
                    class="flex items-center gap-4 p-5 rounded-2xl border border-pearl-100 hover:border-gold-300 hover:bg-gold-50/30 transition-premium text-left group shadow-sm hover:shadow-gold-premium">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-premium shadow-gold-sm"
                        style="background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);">
                        <i class="pi pi-calendar-plus text-charcoal-900 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-charcoal-700">Mes heures validées</p>
                        <p class="text-xs text-charcoal-400 mt-1">Consulter l'historique approuvé</p>
                    </div>
                </Link>
                <Link
                    :href="route('tc.schedule')"
                    class="flex items-center gap-4 p-5 rounded-2xl border border-pearl-100 hover:border-charcoal-400 hover:bg-pearl-50 transition-premium text-left group shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-pearl-100 text-charcoal-500 flex items-center justify-center flex-shrink-0 group-hover:bg-charcoal-900 group-hover:text-white transition-premium">
                        <i class="pi pi-calendar text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-charcoal-700">Voir mon planning</p>
                        <p class="text-xs text-charcoal-400 mt-1">Planning hebdomadaire détaillé</p>
                    </div>
                </Link>
            </div>
        </div>
    </TcLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
.shadow-premium {
    box-shadow: 0 15px 50px -20px rgba(0,0,0,0.15);
}
.shadow-gold-premium {
    box-shadow: 0 10px 25px -5px rgba(212,160,23,0.4);
}
.shadow-gold-sm {
    box-shadow: 0 4px 12px -2px rgba(212,160,23,0.3);
}
.transition-premium {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
