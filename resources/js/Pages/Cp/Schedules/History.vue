<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    history: Object,
    stats: Object,
    filters: Object
});

const search = ref((props.filters || {}).search || '');

// Debounce pour la recherche
let timeout;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('cp.schedules.history'), { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const getStatusClass = (status) => {
    if (!status) return 'bg-charcoal-100 text-charcoal-600 border-charcoal-200';
    switch (status) {
        case 'en attente': return 'bg-yellow-100 text-yellow-700 border-yellow-200';
        case 'validé': return 'bg-green-100 text-green-700 border-green-200';
        case 'suspendu': return 'bg-red-100 text-red-700 border-red-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

const formatStatus = (status) => {
    if (!status) return 'Création';
    return status.charAt(0).toUpperCase() + status.slice(1);
};
</script>

<template>
    <Head title="Historique des Plannings — CP" />
    <CpLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Historique des Plannings</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Suivi chronologique des changements d'états et affectations</p>
                </div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-charcoal-50 text-charcoal-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ (stats || {}).total || 0 }}</div>
                    <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Total Événements</div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ (stats || {}).creations || 0 }}</div>
                    <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Créations</div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-50 text-green-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ (stats || {}).validations || 0 }}</div>
                    <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Validations</div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ (stats || {}).suspensions || 0 }}</div>
                    <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Suspensions</div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-pearl-100 bg-white">
                <div class="relative max-w-md">
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Rechercher un employé ou une raison..."
                        class="w-full bg-pearl-50 border border-pearl-200 rounded-lg pl-10 pr-4 py-2 text-sm text-charcoal-700 focus:border-gold-400 outline-none transition-all"
                    />
                    <svg class="w-4 h-4 text-charcoal-300 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-pearl-50/50 text-[10px] font-black text-charcoal-400 uppercase tracking-widest border-b border-pearl-100">
                            <th class="px-6 py-4">Employé</th>
                            <th class="px-6 py-4">Planning</th>
                            <th class="px-6 py-4">Transition Statut</th>
                            <th class="px-6 py-4">Par</th>
                            <th class="px-6 py-4 text-right">Date & Heure</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pearl-50">
                        <tr v-for="h in history.data" :key="h.id" class="hover:bg-pearl-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <template v-if="h.assignment?.employee">
                                    <div class="font-bold text-sm text-charcoal-700">{{ h.assignment.employee.first_name }} {{ h.assignment.employee.last_name }}</div>
                                    <div class="text-[10px] text-charcoal-400 uppercase tracking-tighter">{{ h.assignment.employee.matricule }}</div>
                                </template>
                                <template v-else>
                                    <span class="text-charcoal-300 italic text-xs">Inconnu</span>
                                </template>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="h.assignment?.planning_model" class="text-xs font-bold text-charcoal-600">
                                    {{ h.assignment.planning_model.name }}
                                </div>
                                <div v-else class="text-charcoal-300">—</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span 
                                        class="px-2 py-0.5 rounded text-[9px] font-bold border"
                                        :class="getStatusClass(h.old_status)"
                                    >
                                        {{ formatStatus(h.old_status) }}
                                    </span>
                                    <svg class="w-3 h-3 text-charcoal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    <span 
                                        class="px-2 py-0.5 rounded text-[9px] font-bold border"
                                        :class="getStatusClass(h.new_status)"
                                    >
                                        {{ formatStatus(h.new_status) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="h.author" class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-pearl-100 flex items-center justify-center text-[10px] font-black text-gold-700 border border-pearl-200">
                                        {{ h.author.email.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="text-[11px] font-bold text-charcoal-600 truncate max-w-[100px]">
                                        {{ h.author.email.split('@')[0] }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-xs font-bold text-charcoal-700">{{ new Date(h.created_at).toLocaleDateString() }}</div>
                                <div class="text-[10px] text-charcoal-400">{{ new Date(h.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</div>
                            </td>
                        </tr>
                        <tr v-if="history.data.length === 0">
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="text-charcoal-300 italic text-sm">Aucun historique trouvé.</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="history.links.length > 3" class="mt-8 flex items-center justify-center gap-1">
            <template v-for="(link, k) in history.links" :key="k">
                <div v-if="link.url === null" 
                     class="px-4 py-2 text-xs font-bold text-charcoal-300 cursor-not-allowed"
                     v-html="link.label" />
                <Link v-else
                      :href="link.url"
                      class="px-4 py-2 text-xs font-bold rounded-lg transition-all"
                      :class="link.active 
                        ? 'bg-gold-gradient text-white shadow-gold' 
                        : 'bg-white border border-pearl-200 text-charcoal-500 hover:bg-pearl-50'"
                      v-html="link.label" />
            </template>
        </div>

    </CpLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
.shadow-gold {
    box-shadow: 0 4px 15px -3px rgba(212, 160, 23, 0.4);
}
</style>
