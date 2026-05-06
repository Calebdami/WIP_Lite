<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Rôles & Permissions — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Rôles & Permissions</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Matrice des droits d'accès</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-center text-charcoal-400">
            Configuration fine des permissions par rôle
        </div>
    </AdminLayout>
</template>
