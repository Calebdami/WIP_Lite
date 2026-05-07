<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <Head title="Tableau de bord — Superviseur" />
    <SupLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Tableau de bord</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Console de supervision d'équipe</p>
            </div>
        </template>

        <!-- Welcome Banner -->
        <div class="rounded-3xl p-8 mb-8 relative overflow-hidden shadow-premium"
             style="background: linear-gradient(135deg, #111827 0%, #374151 100%);">
            <div class="relative z-10">
                <p class="text-gold-400 text-xs font-black uppercase tracking-[0.2em] mb-2 opacity-80">Supervision</p>
                <h2 class="text-3xl font-black text-white mb-3">Hello, {{ user?.name || 'Sup' }} 👋</h2>
                <p class="text-charcoal-400 text-sm max-w-md leading-relaxed">
                    Gérez vos agents, saisissez les temps de travail et suivez les présences de votre équipe quotidiennement.
                </p>
            </div>
            <!-- Decorative elements -->
            <div class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full bg-gold-500/10 blur-2xl"></div>
            <div class="absolute right-20 top-0 w-20 h-20 rounded-full bg-white/5 blur-xl"></div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-100 flex items-center justify-center text-gold-600">
                        <i class="pi pi-users text-lg"></i>
                    </div>
                    <p class="text-xs font-black uppercase tracking-widest text-charcoal-400">Mon Équipe</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">18</span>
                    <span class="text-[10px] font-bold text-emerald-600">Agents actifs</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-100 flex items-center justify-center text-amber-600">
                        <i class="pi pi-file-edit text-lg"></i>
                    </div>
                    <p class="text-xs font-black uppercase tracking-widest text-charcoal-400">À Saisir</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">5</span>
                    <span class="text-[10px] font-bold text-amber-500 underline cursor-pointer">Feuilles en brouillon</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-100 flex items-center justify-center text-emerald-600">
                        <i class="pi pi-percentage text-lg"></i>
                    </div>
                    <p class="text-xs font-black uppercase tracking-widest text-charcoal-400">Présence</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">100%</span>
                    <span class="text-[10px] font-bold text-emerald-600">Aujourd'hui</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-3xl border border-pearl-200 shadow-premium overflow-hidden">
            <div class="px-8 py-6 border-b border-pearl-100 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest text-charcoal-700">Actions de gestion</h2>
                <i class="pi pi-bolt text-gold-500"></i>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <Link :href="route('sup.time-tracking')" class="flex items-center gap-5 p-6 rounded-2xl border border-pearl-100 hover:border-gold-300 hover:bg-gold-50/30 transition-premium group shadow-sm">
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
</style>
