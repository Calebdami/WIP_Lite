<script setup>
import SupLayout from '@/Layouts/SupLayout.vue';
import { Head } from '@inertiajs/vue3';

const team = [
    { id: 1, name: 'Alice Durand', position: 'Téléconseiller', status: 'En ligne' },
    { id: 2, name: 'Bob Lemoine', position: 'Téléconseiller', status: 'En pause' },
    { id: 3, name: 'Charlie Vidal', position: 'Téléconseiller', status: 'Hors ligne' },
];
</script>

<template>
    <Head title="Mon Équipe — Superviseur" />
    <SupLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Mon Équipe</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Suivi des agents sous votre supervision</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-pearl-50 border-b border-pearl-200 text-xs font-bold text-charcoal-400 uppercase tracking-widest">
                        <th class="px-6 py-4">Nom</th>
                        <th class="px-6 py-4">Poste</th>
                        <th class="px-6 py-4">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pearl-100 text-sm">
                    <tr v-for="agent in team" :key="agent.id">
                        <td class="px-6 py-4 font-bold text-charcoal-700">{{ agent.name }}</td>
                        <td class="px-6 py-4 text-charcoal-600">{{ agent.position }}</td>
                        <td class="px-6 py-4">
                            <span :class="{
                                'bg-emerald-100 text-emerald-700': agent.status === 'En ligne',
                                'bg-amber-100 text-amber-700': agent.status === 'En pause',
                                'bg-pearl-200 text-charcoal-400': agent.status === 'Hors ligne',
                            }" class="px-2 py-1 rounded-full text-[10px] font-bold uppercase">
                                {{ agent.status }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </SupLayout>
</template>
