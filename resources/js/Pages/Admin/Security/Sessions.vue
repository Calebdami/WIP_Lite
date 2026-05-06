<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Sessions actives — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Sessions actives</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Surveillance des connexions en temps réel</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Liste des utilisateurs actuellement connectés
        </div>
    </AdminLayout>
</template>
