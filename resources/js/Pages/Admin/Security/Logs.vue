<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const logs = [
    { date: '2024-05-06 14:20', user: 'admin@wiplite.com', action: 'Modification rôle', target: 'user_12' },
    { date: '2024-05-06 14:15', user: 'admin@wiplite.com', action: 'Connexion', target: '-' },
    { date: '2024-05-06 13:45', user: 'cp@wiplite.com', action: 'Validation planning', target: 'planning_45' },
];
</script>

<template>
    <Head title="Audit Logs — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Audit Logs</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Historique des actions critiques</p>
        </template>
        <div class="bg-white rounded-xl border border-pearl-200 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-pearl-50 border-b border-pearl-200 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">
                        <th class="px-6 py-4">Date & Heure</th>
                        <th class="px-6 py-4">Utilisateur</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Cible</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pearl-100 text-xs">
                    <tr v-for="log in logs" :key="log.date" class="hover:bg-pearl-50 transition-colors">
                        <td class="px-6 py-4 text-charcoal-400">{{ log.date }}</td>
                        <td class="px-6 py-4 font-medium text-charcoal-700">{{ log.user }}</td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 bg-pearl-100 rounded text-charcoal-600">{{ log.action }}</span></td>
                        <td class="px-6 py-4 text-charcoal-400">{{ log.target }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
