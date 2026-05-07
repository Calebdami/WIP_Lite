<script setup>
import TcLayout from '@/Layouts/TcLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
</script>

<template>
    <Head title="Tableau de bord — Téléconseiller" />
    <TcLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Tableau de bord</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Espace Téléconseiller</p>
            </div>
        </template>

        <!-- Welcome Banner -->
        <div class="rounded-3xl p-8 mb-8 relative overflow-hidden shadow-premium"
             style="background: linear-gradient(135deg, #111827 0%, #374151 100%);">
            <div class="relative z-10">
                <p class="text-gold-400 text-xs font-black uppercase tracking-[0.2em] mb-2 opacity-80">Espace Personnel</p>
                <h2 class="text-3xl font-black text-white mb-3">Hello, {{ user?.name || 'Téléconseiller' }} 👋</h2>
                <p class="text-charcoal-400 text-sm max-w-md leading-relaxed">
                    Consultez vos plannings et suivez vos heures de travail validées en toute simplicité.
                </p>
            </div>
            <!-- Decorative circles -->
            <div class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full bg-gold-500/10 blur-2xl"></div>
            <div class="absolute right-20 top-0 w-20 h-20 rounded-full bg-white/5 blur-xl"></div>
        </div>

        <!-- Planning du jour -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-pearl-200 p-5 shadow-sm">
                <p class="text-xs text-charcoal-400 mb-1">Ma campagne</p>
                <p class="text-sm font-bold text-charcoal-700 leading-tight">Campagne Printemps 2026</p>
                <p class="text-[11px] text-emerald-600 mt-2">Active · Semaine 18</p>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-5 shadow-sm">
                <p class="text-xs text-charcoal-400 mb-1">Heures cette semaine</p>
                <p class="text-3xl font-black text-charcoal-700">28h</p>
                <p class="text-[11px] text-charcoal-400 mt-1">sur 35h prévues</p>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-5 shadow-sm">
                <p class="text-xs text-charcoal-400 mb-1">Mon superviseur</p>
                <p class="text-sm font-bold text-charcoal-700">Marie Lefebvre</p>
                <p class="text-[11px] text-charcoal-400 mt-2">Superviseur SUP</p>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium overflow-hidden mb-4">
            <div class="px-6 py-5 border-b border-pearl-100 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest text-charcoal-700">Actions rapides</h2>
                <i class="pi pi-bolt text-gold-500"></i>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link :href="route('tc.hours')" class="flex items-center gap-4 p-5 rounded-2xl border border-pearl-100 hover:border-gold-300 hover:bg-gold-50/30 transition-premium text-left group shadow-sm hover:shadow-gold-premium">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-premium"
                         style="background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);">
                        <i class="pi pi-verified text-charcoal-900 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-charcoal-700">Mes heures validées</p>
                        <p class="text-xs text-charcoal-400 mt-0.5">Historique des temps confirmés</p>
                    </div>
                </Link>
                <button class="flex items-center gap-4 p-5 rounded-2xl border border-pearl-100 hover:border-charcoal-400 hover:bg-pearl-50 transition-premium text-left shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-pearl-200 flex items-center justify-center flex-shrink-0">
                        <i class="pi pi-calendar text-charcoal-500 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-charcoal-700">Voir mon planning</p>
                        <p class="text-xs text-charcoal-400 mt-0.5">Planning hebdomadaire</p>
                    </div>
                </button>
            </div>
        </div>
    </TcLayout>
</template>
