<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const users = [
    { id: 1, email: 'admin@wiplite.com', role: 'Administrateur', lastLogin: 'Il y a 2h' },
    { id: 2, email: 'cp@wiplite.com', role: 'Chef de Plateau', lastLogin: 'Hier' },
    { id: 3, email: 'sup@wiplite.com', role: 'Superviseur', lastLogin: 'Il y a 5 min' },
    { id: 4, email: 'tc@wiplite.com', role: 'Téléconseiller', lastLogin: 'Jamais' },
];
</script>

<template>
    <Head title="Comptes & Rôles — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Comptes & Rôles</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Gestion des accès et authentification</p>
                </div>
            </div>
        </template>

        <div class="bg-white rounded-xl border border-pearl-200 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-pearl-50 border-b border-pearl-200">
                        <th class="px-6 py-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Rôle</th>
                        <th class="px-6 py-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Dernière connexion</th>
                        <th class="px-6 py-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pearl-100">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-pearl-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-charcoal-700">{{ user.email }}</td>
                        <td class="px-6 py-4 text-sm text-charcoal-600">{{ user.role }}</td>
                        <td class="px-6 py-4 text-sm text-charcoal-400 italic">{{ user.lastLogin }}</td>
                        <td class="px-6 py-4 text-sm">
                            <button class="text-charcoal-900 font-bold hover:underline mr-4">Modifier</button>
                            <button class="text-red-500 font-bold hover:underline">Désactiver</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
