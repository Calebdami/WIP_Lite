<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Saisie des Heures — Superviseur" />
    <SupLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Saisie des Heures</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Enregistrement des temps de travail de vos agents</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Interface de saisie quotidienne/hebdomadaire pour l'équipe
        </div>
    </SupLayout>
</template>
