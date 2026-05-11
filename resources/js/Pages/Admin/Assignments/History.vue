<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Components simplifiés

const props = defineProps({
    history: Object,
    stats: Object,
    filters: {
        type: Object,
        default: () => ({ search: '' })
    }
});

const page = usePage();

// Custom debounce function
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn(...args);
        }, delay);
    };
};

// Search & Filtering
const search = ref(props.filters?.search || '');

// Manual search functions
const triggerSearch = () => {
    const currentUrl = window.location.pathname;
    const params = new URLSearchParams(window.location.search);
    
    if (search.value) {
        params.set('search', search.value);
    } else {
        params.delete('search');
    }
    
    const newUrl = currentUrl + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
};

// Handle Enter key press
const handleSearchKeydown = (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        triggerSearch();
    }
};

// Afficher les détails
const showDetails = (historyItem) => {
    // Créer un message détaillé pour l'instant
    const details = `
        ID: ${historyItem.id}
        Employé: ${historyItem.assignment?.employee?.first_name} ${historyItem.assignment?.employee?.last_name}
        Matricule: ${historyItem.assignment?.employee?.matricule}
        Ancien statut: ${historyItem.old_status}
        Nouveau statut: ${historyItem.new_status}
        Raison: ${historyItem.reason || 'Aucune raison'}
        Validé par: ${historyItem.author?.email || 'Système'}
        Date: ${formatDate(historyItem.created_at)} ${formatTime(historyItem.created_at)}
    `;
    
    alert(details.trim());
};

const getStatusSeverity = (status) => {
    if (!status) return 'secondary';
    switch (status.toLowerCase()) {
        case 'en attente': return 'warn';
        case 'validé': return 'success';
        case 'suspendu': return 'danger';
        case 'actif': return 'success';
        case 'clôturé': return 'info';
        default: return 'secondary';
    }
};

const getStatusClass = (status) => {
    if (!status) return 'bg-gray-100 text-gray-600';
    switch (status.toLowerCase()) {
        case 'en attente': return 'bg-yellow-100 text-yellow-700';
        case 'validé': return 'bg-green-100 text-green-700';
        case 'suspendu': return 'bg-red-100 text-red-700';
        case 'actif': return 'bg-green-100 text-green-700';
        case 'clôturé': return 'bg-blue-100 text-blue-700';
        default: return 'bg-gray-100 text-gray-600';
    }
};

const formatStatus = (status) => {
    if (!status) return 'Initial';
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatTime = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Historique des Plannings — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Historique des Plannings</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Suivi chronologique des changements d'états et affectations</p>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                        <i class="pi pi-search text-xs"></i>
                    </span>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Rechercher (Nom, matricule, raison...)" 
                        @keydown="handleSearchKeydown"
                        class="block w-full pl-10 pr-20 py-3 bg-pearl-50 border-pearl-200 rounded-xl text-sm transition-all duration-200 focus:border-gold-500 focus:ring-4 focus:ring-gold-500/15 focus:bg-white outline-none"
                    />
                    <button @click="triggerSearch"
                        class="absolute inset-y-0 right-0 px-4 bg-gold-gradient text-white rounded-r-xl font-black text-xs uppercase tracking-widest shadow-gold-premium hover:opacity-90 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div v-for="stat in [
                { label: 'Total Événements', value: stats?.total || 0, icon: 'pi-history', color: 'charcoal' },
                { label: 'Créations', value: stats?.creations || 0, icon: 'pi-plus-circle', color: 'blue' },
                { label: 'Validations', value: stats?.validations || 0, icon: 'pi-check-circle', color: 'emerald' },
                { label: 'Suspensions', value: stats?.suspensions || 0, icon: 'pi-exclamation-circle', color: 'red' }
            ]" :key="stat.label" 
            class="bg-white rounded-3xl border border-pearl-100 p-6 shadow-premium flex items-center gap-4 hover:shadow-gold-premium transition-all duration-300">
                <div :class="`w-12 h-12 rounded-2xl bg-${stat.color}-50 text-${stat.color}-500 flex items-center justify-center`">
                    <i :class="`pi ${stat.icon} text-lg`"></i>
                </div>
                <div>
                    <div class="text-2xl font-black text-charcoal-700">{{ stat.value }}</div>
                    <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">{{ stat.label }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-pearl-100 shadow-premium overflow-hidden">
            <div v-if="history.data && history.data.length > 0" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-pearl-50 border-b border-pearl-200">
                        <tr>
                            <th class="py-4 px-6 text-left font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Employé</th>
                            <th class="py-4 px-6 text-left font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Planning</th>
                            <th class="py-4 px-6 text-left font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Transition Statut</th>
                            <th class="py-4 px-6 text-left font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Validé par</th>
                            <th class="py-4 px-6 text-left font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Raison</th>
                            <th class="py-4 px-6 text-right font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Date & Heure</th>
                            <th class="py-4 px-6 text-center font-black text-charcoal-700 uppercase text-[10px] tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pearl-50">
                        <tr v-for="item in history.data" :key="item.id" class="hover:bg-pearl-50 transition-all duration-200">
                            <td class="py-4 px-6">
                                <div v-if="item.assignment?.employee" class="flex flex-col">
                                    <span class="text-sm font-bold text-charcoal-700 uppercase">
                                        {{ item.assignment.employee.first_name }} {{ item.assignment.employee.last_name }}
                                    </span>
                                    <span class="text-[10px] font-black text-gold-500 tracking-tighter">
                                        {{ item.assignment.employee.matricule }}
                                    </span>
                                </div>
                                <span v-else class="text-charcoal-400 italic text-sm">Inconnu</span>
                            </td>
                            <td class="py-4 px-6">
                                <div v-if="item.assignment?.planning_model" class="text-sm font-bold text-charcoal-600">
                                    {{ item.assignment.planning_model.name }}
                                </div>
                                <span v-else class="text-charcoal-400">—</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 text-[10px] font-bold rounded-xl" :class="getStatusClass(item.old_status)">
                                        {{ formatStatus(item.old_status) }}
                                    </span>
                                    <i class="pi pi-arrow-right text-xs text-charcoal-400"></i>
                                    <span class="px-3 py-1 text-[10px] font-bold rounded-xl" :class="getStatusClass(item.new_status)">
                                        {{ formatStatus(item.new_status) }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div v-if="item.author" class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-pearl-50 flex items-center justify-center text-xs font-black text-gold-500 border border-pearl-200 uppercase">
                                        {{ item.author.email.charAt(0) }}
                                    </div>
                                    <div class="text-sm font-bold text-charcoal-600 truncate max-w-[120px]">
                                        {{ item.author.email.split('@')[0] }}
                                    </div>
                                </div>
                                <span v-else class="text-charcoal-400 italic text-sm">Système</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-xs text-charcoal-500 italic max-w-xs truncate" :title="item.reason">
                                    {{ item.reason || 'Aucune raison spécifiée' }}
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="text-sm font-bold text-charcoal-700">{{ formatDate(item.created_at) }}</div>
                                <div class="text-xs text-charcoal-400 font-medium">{{ formatTime(item.created_at) }}</div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <button 
                                    @click="showDetails(item)"
                                    class="px-4 py-2 bg-pearl-50 text-charcoal-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gold-gradient hover:text-white transition-all duration-200 border border-pearl-200"
                                >
                                    <i class="pi pi-eye mr-2"></i>
                                    Détail
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-else class="text-center py-16 text-charcoal-400">
                <i class="pi pi-inbox text-6xl mb-6 opacity-20"></i>
                <p class="text-lg italic">Aucun historique trouvé.</p>
            </div>

            <!-- Pagination -->
            <div v-if="history.links.length > 3" class="py-6 border-t border-pearl-100 flex justify-center">
                <div class="flex gap-3">
                    <Link 
                        v-for="(link, k) in history.links" 
                        :key="k"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 border"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 border-transparent shadow-gold-premium': link.active,
                            'bg-white text-charcoal-500 border-pearl-200 hover:border-gold-500 hover:text-charcoal-900': !link.active && link.url,
                            'opacity-40 cursor-not-allowed border-pearl-100 text-charcoal-300': !link.url
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Explanatory Note -->
        <div class="mt-8 bg-pearl-50 border border-pearl-100 rounded-3xl p-6 flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-gold-500 shadow-premium flex-shrink-0">
                <i class="pi pi-info-circle text-xl"></i>
            </div>
            <div class="text-sm leading-relaxed text-charcoal-500 italic">
                <span class="font-bold text-charcoal-700 not-italic block mb-2 uppercase tracking-widest text-xs">Traçabilité des plannings :</span>
                Chaque action effectuée sur une affectation de planning (création, validation, suspension, réactivation) est archivée dans cette table. Cela permet de garantir une traçabilité totale des changements d'état et des interventions des administrateurs ou chefs de plateau sur les ressources de l'entreprise.
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}

.shadow-gold-sm {
    box-shadow: 0 4px 10px -5px rgba(212, 160, 23, 0.5);
}

.shadow-premium {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
}

/* Colors for stats - manually adding classes since dynamic tailwind classes might be purged */
.bg-charcoal-50 { background-color: #f8fafc; }
.text-charcoal-500 { color: #64748b; }
.bg-blue-50 { background-color: #eff6ff; }
.text-blue-500 { color: #3b82f6; }
.bg-emerald-50 { background-color: #ecfdf5; }
.text-emerald-500 { color: #10b981; }
.bg-red-50 { background-color: #fef2f2; }
.text-red-500 { color: #ef4444; }
</style>
