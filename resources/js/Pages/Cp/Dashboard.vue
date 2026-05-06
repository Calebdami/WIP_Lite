<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <Head title="Tableau de bord — Chef de Projet" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Tableau de bord</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Espace Chef de Projet</p>
            </div>
        </template>

        <!-- Welcome Banner -->
        <div class="rounded-2xl p-6 mb-6 text-white shadow-gold relative overflow-hidden"
             style="background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);">
            <div class="relative z-10">
                <p class="text-gold-100 text-sm font-medium mb-1">Bienvenue,</p>
                <h2 class="text-2xl font-black mb-3">{{ user?.name || 'Chef de Projet' }} 👋</h2>
                <p class="text-gold-200 text-sm">Gérez vos campagnes, plannings et affectations depuis cet espace.</p>
            </div>
            <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full opacity-20" style="background: rgba(255,255,255,0.3);"></div>
            <div class="absolute -right-2 -bottom-8 w-24 h-24 rounded-full opacity-10" style="background: rgba(255,255,255,0.3);"></div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-pearl-200 p-5 shadow-sm">
                <p class="text-xs text-charcoal-400 mb-1">Mes campagnes actives</p>
                <p class="text-3xl font-black text-charcoal-700">5</p>
                <p class="text-[11px] text-emerald-600 mt-1">+1 cette semaine</p>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-5 shadow-sm">
                <p class="text-xs text-charcoal-400 mb-1">Affectations en attente</p>
                <p class="text-3xl font-black text-charcoal-700">12</p>
                <p class="text-[11px] text-amber-500 mt-1">À valider</p>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-5 shadow-sm">
                <p class="text-xs text-charcoal-400 mb-1">Taux complétion</p>
                <p class="text-3xl font-black text-charcoal-700">78%</p>
                <p class="text-[11px] text-emerald-600 mt-1">+3% vs semaine dernière</p>
            </div>
        </div>

        <!-- Contenu placeholder -->
        <div class="bg-white rounded-xl border border-pearl-200 p-8 shadow-sm text-center">
            <svg class="w-12 h-12 text-pearl-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <p class="text-charcoal-400 text-sm font-medium">Modules Chef de Projet à venir</p>
            <p class="text-charcoal-300 text-xs mt-1">Plannings, campagnes et suivi des affectations</p>
        </div>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient { background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%); }
</style>
