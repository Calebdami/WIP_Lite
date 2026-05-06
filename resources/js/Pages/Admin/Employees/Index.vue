<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const employees = [
    { id: 1, name: 'Jean Dupont', role: 'Téléconseiller', team: 'Campagne Alpha', status: 'Actif' },
    { id: 2, name: 'Marie Martin', role: 'Superviseur', team: 'Campagne Beta', status: 'Actif' },
    { id: 3, name: 'Luc Leroy', role: 'Téléconseiller', team: 'Campagne Alpha', status: 'Inactif' },
    { id: 4, name: 'Sophie Petit', role: 'Chef de Plateau', team: 'Multi-plateau', status: 'Actif' },
];
</script>

<template>
    <Head title="Gestion des Employés — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Gestion des Employés</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Liste complète et gestion du personnel</p>
                </div>
                <button class="px-4 py-2 bg-gold-gradient text-charcoal-900 rounded-lg text-xs font-bold shadow-gold">
                    + Ajouter un employé
                </button>
            </div>
        </template>

        <div class="bg-white rounded-xl border border-pearl-200 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-pearl-50 border-b border-pearl-200">
                        <th class="px-6 py-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Rôle</th>
                        <th class="px-6 py-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Équipe/Campagne</th>
                        <th class="px-6 py-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pearl-100">
                    <tr v-for="emp in employees" :key="emp.id" class="hover:bg-pearl-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-charcoal-700">{{ emp.name }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-charcoal-600">{{ emp.role }}</td>
                        <td class="px-6 py-4 text-sm text-charcoal-600">{{ emp.team }}</td>
                        <td class="px-6 py-4">
                            <span :class="emp.status === 'Actif' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 rounded-full text-[10px] font-bold uppercase">
                                {{ emp.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <button class="text-gold-600 font-bold hover:underline">Modifier</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
</style>
