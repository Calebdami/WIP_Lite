<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Maintenance — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Maintenance</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Outils système et optimisation</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-pearl-50 border border-pearl-200 rounded-xl">
                    <h3 class="text-sm font-bold text-charcoal-700 mb-2">Vider le cache</h3>
                    <p class="text-xs text-charcoal-400 mb-4">Optimise les performances en nettoyant les fichiers temporaires.</p>
                    <button class="px-4 py-2 bg-charcoal-900 text-white text-[10px] font-bold rounded-lg">Exécuter</button>
                </div>
                <div class="p-4 bg-pearl-50 border border-pearl-200 rounded-xl">
                    <h3 class="text-sm font-bold text-charcoal-700 mb-2">Sauvegarde Base de Données</h3>
                    <p class="text-xs text-charcoal-400 mb-4">Génère un export complet des données actuelles.</p>
                    <button class="px-4 py-2 bg-gold-gradient text-charcoal-900 text-[10px] font-bold rounded-lg shadow-gold">Sauvegarder</button>
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
