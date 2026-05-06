<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Suivi des Affectations — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Suivi des Affectations</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Suivi en temps réel des liaisons TC/SUP</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Interface de suivi des affectations actives
        </div>
    </AdminLayout>
</template>
