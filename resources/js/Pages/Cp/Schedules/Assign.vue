<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Affectation de Plannings — CP" />
    <CpLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Affectation de Plannings</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Attribuer des plannings aux superviseurs et téléconseillers</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Interface d'attribution massive de plannings
        </div>
    </CpLayout>
</template>
