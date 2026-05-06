<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const pendingSchedules = [
    { id: 1, team: 'Équipe Alpha', supervisor: 'Jean Dupont', status: 'En attente', period: 'Semaine 19 - 2024' },
    { id: 2, team: 'Équipe Beta', supervisor: 'Marie Martin', status: 'En attente', period: 'Semaine 19 - 2024' },
];
</script>

<template>
    <Head title="Validation Plannings — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Validation Plannings</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Validation finale des plannings de production</p>
        </template>

        <div class="bg-white rounded-xl border border-pearl-200 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-pearl-50 border-b border-pearl-200 text-xs font-bold text-charcoal-400 uppercase tracking-widest">
                        <th class="px-6 py-4">Période</th>
                        <th class="px-6 py-4">Équipe</th>
                        <th class="px-6 py-4">Superviseur</th>
                        <th class="px-6 py-4">Statut</th>
                        <th class="px-6 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pearl-100 text-sm">
                    <tr v-for="item in pendingSchedules" :key="item.id" class="hover:bg-pearl-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-charcoal-700">{{ item.period }}</td>
                        <td class="px-6 py-4 text-charcoal-600">{{ item.team }}</td>
                        <td class="px-6 py-4 text-charcoal-600">{{ item.supervisor }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-[10px] font-bold uppercase">
                                {{ item.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 space-x-3">
                            <button class="text-emerald-600 font-bold hover:underline">Valider</button>
                            <button class="text-red-500 font-bold hover:underline">Rejeter</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
