<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <Head title="Tableau de bord — Chef de Plateau" />
    <CpLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Tableau de bord</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Console de pilotage du plateau</p>
            </div>
        </template>

        <!-- Welcome Banner -->
        <div class="rounded-3xl p-8 mb-8 relative overflow-hidden shadow-premium"
             style="background: linear-gradient(135deg, #111827 0%, #374151 100%);">
            <div class="relative z-10">
                <p class="text-gold-400 text-xs font-black uppercase tracking-[0.2em] mb-2 opacity-80">Chef de Plateau</p>
                <h2 class="text-3xl font-black text-white mb-3">Hello, {{ user?.name || 'CP' }} 👋</h2>
                <p class="text-charcoal-400 text-sm max-w-md leading-relaxed">
                    Validez les heures de vos agents, gérez vos superviseurs et suivez la performance du plateau en temps réel.
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
                    <p class="text-xs font-black uppercase tracking-widest text-charcoal-400">Agents actifs</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">42</span>
                    <span class="text-[10px] font-bold text-emerald-600">Sur le plateau</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-100 flex items-center justify-center text-amber-600">
                        <i class="pi pi-clock text-lg"></i>
                    </div>
                    <p class="text-xs font-black uppercase tracking-widest text-charcoal-400">À Valider</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">8</span>
                    <span class="text-[10px] font-bold text-amber-500 underline cursor-pointer">Feuilles en attente</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-100 flex items-center justify-center text-emerald-600">
                        <i class="pi pi-check-circle text-lg"></i>
                    </div>
                    <p class="text-xs font-black uppercase tracking-widest text-charcoal-400">Taux de Validation</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">94%</span>
                    <span class="text-[10px] font-bold text-emerald-600">Semaine en cours</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-3xl border border-pearl-200 shadow-premium overflow-hidden">
            <div class="px-8 py-6 border-b border-pearl-100 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest text-charcoal-700">Actions Prioritaires</h2>
                <span class="px-3 py-1 bg-pearl-100 rounded-full text-[10px] font-black text-charcoal-400">3 ALERTES</span>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <Link :href="route('cp.time-tracking.validate-tc')" class="flex items-center gap-5 p-6 rounded-2xl border border-pearl-100 hover:border-gold-300 hover:bg-gold-50/30 transition-premium group shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-gold-gradient flex items-center justify-center flex-shrink-0 shadow-gold-premium group-hover:rotate-6 transition-premium">
                        <i class="pi pi-check-square text-charcoal-900 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-base font-black text-charcoal-700">Valider les Heures TC</p>
                        <p class="text-xs text-charcoal-400 mt-1">Feuilles soumises par les superviseurs</p>
                    </div>
                </Link>
                <Link :href="route('cp.time-tracking.supervisors')" class="flex items-center gap-5 p-6 rounded-2xl border border-pearl-100 hover:border-charcoal-400 hover:bg-pearl-50 transition-premium group shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-pearl-200 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-premium">
                        <i class="pi pi-user-edit text-charcoal-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-base font-black text-charcoal-700">Saisir Heures SUP</p>
                        <p class="text-xs text-charcoal-400 mt-1">Journal de présence des superviseurs</p>
                    </div>
                </Link>
            </div>
        </div>
    </CpLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
</style>

<style scoped>
.bg-gold-gradient { background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%); }
</style>
