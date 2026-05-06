<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Configuration Entreprise — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Configuration Entreprise</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Paramètres généraux de la structure</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8 text-charcoal-600">
            <div class="space-y-4 max-w-lg">
                <div>
                    <label class="block text-xs font-bold text-charcoal-400 uppercase mb-1">Nom de l'entreprise</label>
                    <input type="text" value="WIP Lite Corp" class="w-full p-2 border border-pearl-200 rounded-lg text-sm" readonly />
                </div>
                <div>
                    <label class="block text-xs font-bold text-charcoal-400 uppercase mb-1">Fuseau horaire</label>
                    <input type="text" value="Europe/Paris (UTC+1)" class="w-full p-2 border border-pearl-200 rounded-lg text-sm" readonly />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
