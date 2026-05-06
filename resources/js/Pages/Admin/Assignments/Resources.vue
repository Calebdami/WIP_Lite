<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Ressources disponibles — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Ressources disponibles</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Visualisation des agents non affectés</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Liste des ressources en attente d'affectation
        </div>
    </AdminLayout>
</template>
