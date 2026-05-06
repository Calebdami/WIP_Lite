<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Temps de travail — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Temps de travail</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Suivi et clôture des feuilles de temps</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Interface de gestion des temps de travail
        </div>
    </AdminLayout>
</template>
