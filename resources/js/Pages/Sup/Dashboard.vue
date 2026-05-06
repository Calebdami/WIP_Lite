<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <Head title="Tableau de bord — Superviseur" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Tableau de bord</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Espace Superviseur</p>
            </div>
        </template>

        <!-- Welcome Banner -->
        <div class="rounded-2xl p-6 mb-6 text-white shadow-charcoal relative overflow-hidden"
             style="background: linear-gradient(135deg, #374151 0%, #1F2937 100%);">
            <div class="relative z-10">
                <p class="text-gray-400 text-sm font-medium mb-1">Bienvenue,</p>
                <h2 class="text-2xl font-black mb-3">{{ user?.name || 'Superviseur' }} 👋</h2>
                <p class="text-gray-400 text-sm">Supervisez vos équipes, validez les timesheets et suivez les présences.</p>
            </div>
            <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full" style="background: rgba(212,160,23,0.1);"></div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-pearl-200 p-5 shadow-sm">
                <p class="text-xs text-charcoal-400 mb-1">Mon équipe</p>
                <p class="text-3xl font-black text-charcoal-700">24</p>
                <p class="text-[11px] text-charcoal-400 mt-1">Techniciens actifs</p>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-5 shadow-sm">
                <p class="text-xs text-charcoal-400 mb-1">Timesheets à valider</p>
                <p class="text-3xl font-black text-charcoal-700">7</p>
                <p class="text-[11px] text-amber-500 mt-1">En attente de validation</p>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-5 shadow-sm">
                <p class="text-xs text-charcoal-400 mb-1">Présence aujourd'hui</p>
                <p class="text-3xl font-black text-charcoal-700">91%</p>
                <p class="text-[11px] text-emerald-600 mt-1">22/24 présents</p>
            </div>
        </div>

        <!-- Placeholder -->
        <div class="bg-white rounded-xl border border-pearl-200 p-8 shadow-sm text-center">
            <svg class="w-12 h-12 text-pearl-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-charcoal-400 text-sm font-medium">Modules Superviseur à venir</p>
            <p class="text-charcoal-300 text-xs mt-1">Suivi d'équipe, validation et rapports de présence</p>
        </div>
    </AdminLayout>
</template>
