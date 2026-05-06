<script setup>
import TcLayout from '@/Layouts/TcLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <Head title="Tableau de bord — Technicien" />
    <TcLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Tableau de bord</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Espace Technicien</p>
            </div>
        </template>

        <!-- Welcome Banner -->
        <div class="rounded-2xl p-6 mb-6 relative overflow-hidden"
             style="background: linear-gradient(135deg, #6B7280 0%, #374151 100%);">
            <div class="relative z-10">
                <p class="text-gray-400 text-sm font-medium mb-1">Bienvenue,</p>
                <h2 class="text-2xl font-black text-white mb-3">{{ user?.name || 'Technicien' }} 👋</h2>
                <p class="text-gray-400 text-sm">Consultez vos plannings, saisissez vos feuilles de temps et suivez vos affectations.</p>
            </div>
            <div class="absolute -right-4 -bottom-4 w-28 h-28 rounded-full" style="background: rgba(212,160,23,0.08);"></div>
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
        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden mb-4">
            <div class="px-5 py-4 border-b border-pearl-200">
                <h2 class="text-sm font-bold text-charcoal-700">Actions rapides</h2>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button class="flex items-center gap-3 p-4 rounded-xl border border-pearl-200 hover:border-gold-300 hover:bg-pearl-50 transition-all duration-200 text-left group">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:shadow-gold transition-all"
                         style="background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);">
                        <svg class="w-4 h-4 text-charcoal-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#111;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-charcoal-700">Saisir mes heures</p>
                        <p class="text-[11px] text-charcoal-400">Feuille de temps du jour</p>
                    </div>
                </button>
                <button class="flex items-center gap-3 p-4 rounded-xl border border-pearl-200 hover:border-charcoal-400 hover:bg-pearl-50 transition-all duration-200 text-left">
                    <div class="w-9 h-9 rounded-lg bg-pearl-200 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-charcoal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-charcoal-700">Voir mon planning</p>
                        <p class="text-[11px] text-charcoal-400">Planning de la semaine</p>
                    </div>
                </button>
            </div>
        </div>
    </TcLayout>
</template>
