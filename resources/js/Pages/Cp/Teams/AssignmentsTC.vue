<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Affectation Téléconseillers — CP" />
    <CpLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Affectation des Téléconseillers</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Liaison des téléconseillers aux superviseurs</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Interface d'affectation des TC aux équipes
        </div>
    </CpLayout>
</template>
