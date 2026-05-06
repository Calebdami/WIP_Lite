<script setup>
import TcLayout from '@/Layouts/TcLayout.vue';
import { Head } from '@inertiajs/vue3';

const history = [
    { week: 'Semaine 18', hours: '35:00', status: 'Validé' },
    { week: 'Semaine 17', hours: '37:30', status: 'Validé' },
];
</script>

<template>
    <Head title="Mes Heures Validées — Téléconseiller" />
    <TcLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Mes Heures Validées</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Historique de vos temps de travail confirmés</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-pearl-50 border-b border-pearl-200 text-xs font-bold text-charcoal-400 uppercase tracking-widest">
                        <th class="px-6 py-4">Période</th>
                        <th class="px-6 py-4">Total Heures</th>
                        <th class="px-6 py-4">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pearl-100 text-sm">
                    <tr v-for="item in history" :key="item.week">
                        <td class="px-6 py-4 font-medium text-charcoal-700">{{ item.week }}</td>
                        <td class="px-6 py-4 text-charcoal-600">{{ item.hours }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-bold uppercase">
                                {{ item.status }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </TcLayout>
</template>
