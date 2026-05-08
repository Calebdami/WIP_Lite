<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <Head title="Tableau de bord — Superviseur" />
    <SupLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Tableau de bord</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Console de supervision d'équipe</p>
                </div>
                <div class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">
                    S7 - {{ new Date().getFullYear() }}
                </div>
            </div>
        </template>

        <!-- Welcome Banner -->
        <div class="rounded-3xl p-8 mb-8 relative overflow-hidden shadow-premium"
             style="background: linear-gradient(135deg, #111827 0%, #374151 100%);">
            <div class="relative z-10">
                <p class="text-gold-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 opacity-80">Superviseur d'Équipe</p>
                <h2 class="text-3xl font-black text-white mb-3">Hello, {{ user?.name || 'Sup' }} </h2>
                <p class="text-charcoal-400 text-sm max-w-md leading-relaxed">
                    Gérez vos agents, saisissez les temps de travail et suivez les présences de votre équipe quotidiennement.
                </p>
            </div>
            <!-- Decorative elements -->
            <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-gold-500/10 blur-3xl"></div>
            <div class="absolute right-20 top-0 w-32 h-32 rounded-full bg-white/5 blur-2xl"></div>
            <i class="pi pi-chart-bar absolute right-12 top-1/2 -translate-y-1/2 text-8xl text-white/5 pointer-events-none"></i>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-50 flex items-center justify-center text-gold-600 shadow-inner">
                        <i class="pi pi-users text-lg"></i>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Mon Équipe</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">{{ stats?.team_size || 0 }}</span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-tighter">Agents Actifs</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-50 flex items-center justify-center text-amber-600 shadow-inner">
                        <i class="pi pi-file-edit text-lg"></i>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Sessions Actives</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">{{ stats?.active_sessions || 0 }}</span>
                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-tighter">Pointages en cours</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-50 flex items-center justify-center text-emerald-600 shadow-inner">
                        <i class="pi pi-percentage text-lg"></i>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Taux de Présence</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">100%</span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-tighter">Aujourd'hui</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-3xl border border-pearl-200 shadow-premium overflow-hidden">
            <div class="px-8 py-6 border-b border-pearl-100 flex items-center justify-between bg-pearl-50/20">
                <h2 class="text-xs font-black uppercase tracking-[0.2em] text-charcoal-700">Actions de gestion</h2>
                <div class="flex gap-1">
                    <div class="w-1.5 h-1.5 rounded-full bg-gold-400"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-gold-200"></div>
                </div>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <Link :href="route('sup.time-tracking')" class="flex items-center gap-5 p-6 rounded-2xl border border-pearl-100 hover:border-gold-300 hover:bg-gold-50/30 transition-premium group shadow-sm hover:shadow-gold-premium">
                    <div class="w-14 h-14 rounded-2xl bg-gold-gradient flex items-center justify-center flex-shrink-0 shadow-gold-premium group-hover:rotate-6 transition-premium">
                        <i class="pi pi-clock text-charcoal-900 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-base font-black text-charcoal-700">Saisie des Heures</p>
                        <p class="text-xs text-charcoal-400 mt-1">Gérer les temps de travail des agents</p>
                    </div>
                </Link>
                <Link :href="route('sup.team')" class="flex items-center gap-5 p-6 rounded-2xl border border-pearl-100 hover:border-charcoal-400 hover:bg-pearl-50 transition-premium group shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-pearl-200 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-premium">
                        <i class="pi pi-users text-charcoal-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-base font-black text-charcoal-700">Voir mon Équipe</p>
                        <p class="text-xs text-charcoal-400 mt-1">Liste des agents et statuts en direct</p>
                    </div>
                </Link>
            </div>
        </div>
    </SupLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
.shadow-premium {
    box-shadow: 0 15px 50px -15px rgba(0,0,0,0.1);
}
.shadow-gold-premium {
    box-shadow: 0 10px 25px -5px rgba(212,160,23,0.3);
}
.transition-premium {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
