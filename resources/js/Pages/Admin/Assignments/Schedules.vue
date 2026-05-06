<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Plannings — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Gestion des Plannings</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Vue d'ensemble des plannings de production</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Calendrier global de planification (Données en dur prochainement)
        </div>
    </AdminLayout>
</template>
