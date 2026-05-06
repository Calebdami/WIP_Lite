<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Validation Plannings — CP" />
    <CpLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Validation des Plannings</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Validation finale pour visibilité TC/SUP</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Interface de validation des plannings "En attente"
        </div>
    </CpLayout>
</template>
