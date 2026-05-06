<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Diffusion — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Diffusion</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Envoi massif de notifications</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Interface d'envoi de notifications groupées
        </div>
    </AdminLayout>
</template>
