<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Saisie Heures SUP — CP" />
    <CpLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Saisie des Heures SUP</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Enregistrement du temps de travail de vos superviseurs</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Formulaire de saisie des heures pour les superviseurs
        </div>
    </CpLayout>
</template>
