<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Analyse des Écarts — CP" />
    <CpLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Analyse des Écarts</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Différences entre plannings et heures réelles</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Rapports de productivité et d'assiduité
        </div>
    </CpLayout>
</template>
