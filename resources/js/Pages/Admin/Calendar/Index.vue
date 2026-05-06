<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Calendrier — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Calendrier</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Vue globale des événements et échéances</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 h-[600px] flex items-center justify-center text-charcoal-300 italic">
            Composant Calendrier (Intégration PrimeVue à venir)
        </div>
    </AdminLayout>
</template>
