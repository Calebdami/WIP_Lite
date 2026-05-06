<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head } from '@inertiajs/vue3';

const supervisors = [
    { id: 1, name: 'Marie Martin', team: 'Équipe Alpha', tcCount: 12 },
    { id: 2, name: 'Jean Bernard', team: 'Équipe Beta', tcCount: 10 },
];
</script>

<template>
    <Head title="Gestion des Superviseurs — CP" />
    <CpLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Gestion des Superviseurs</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Liste des superviseurs sous votre responsabilité</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-pearl-50 border-b border-pearl-200 text-xs font-bold text-charcoal-400 uppercase tracking-widest">
                        <th class="px-6 py-4">Nom</th>
                        <th class="px-6 py-4">Équipe</th>
                        <th class="px-6 py-4">Nombre de TC</th>
                        <th class="px-6 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pearl-100 text-sm">
                    <tr v-for="sup in supervisors" :key="sup.id">
                        <td class="px-6 py-4 font-bold text-charcoal-700">{{ sup.name }}</td>
                        <td class="px-6 py-4 text-charcoal-600">{{ sup.team }}</td>
                        <td class="px-6 py-4 text-charcoal-600">{{ sup.tcCount }} TC</td>
                        <td class="px-6 py-4"><button class="text-gold-600 font-bold hover:underline">Gérer</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </CpLayout>
</template>
