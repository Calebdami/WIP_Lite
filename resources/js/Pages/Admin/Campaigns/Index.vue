<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const campaigns = [
    { id: 1, name: 'Soutien Client Orange', client: 'Orange FR', manager: 'Sophie Petit', progress: 85 },
    { id: 2, name: 'Prospection EDF', client: 'EDF', manager: 'Marc Durand', progress: 40 },
    { id: 3, name: 'SAV Bouygues', client: 'Bouygues Telecom', manager: 'Lucie Bernard', progress: 95 },
];
</script>

<template>
    <Head title="Gestion des Campagnes — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Gestion des Campagnes</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Pilotage et suivi des opérations</p>
                </div>
                <button class="px-4 py-2 bg-charcoal-900 text-white rounded-lg text-xs font-bold hover:bg-charcoal-800 transition-colors">
                    + Nouvelle Campagne
                </button>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="c in campaigns" :key="c.id" class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-charcoal-400">{{ c.client }}</span>
                    <div class="w-8 h-8 rounded-lg bg-pearl-100 flex items-center justify-center text-charcoal-700 font-bold">
                        {{ c.id }}
                    </div>
                </div>
                <h3 class="text-lg font-black text-charcoal-700 mb-2">{{ c.name }}</h3>
                <p class="text-xs text-charcoal-400 mb-6">Manager: {{ c.manager }}</p>
                
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-tighter">
                        <span class="text-charcoal-400">Progression</span>
                        <span class="text-gold-600">{{ c.progress }}%</span>
                    </div>
                    <div class="w-full h-2 bg-pearl-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gold-gradient" :style="{ width: c.progress + '%' }"></div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
</style>
