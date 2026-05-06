<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Modèles de Planning — CP" />
    <CpLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Modèles de Planning</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Création et gestion des semaines types</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Interface de gestion des modèles (35h, 40h, etc.)
        </div>
    </CpLayout>
</template>
