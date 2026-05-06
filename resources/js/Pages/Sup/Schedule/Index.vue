<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Planning d'Équipe — Superviseur" />
    <SupLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Planning d'Équipe</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Consultation des horaires de vos agents</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-300 italic h-64 flex items-center justify-center">
            Calendrier d'équipe en cours de développement
        </div>
    </SupLayout>
</template>
