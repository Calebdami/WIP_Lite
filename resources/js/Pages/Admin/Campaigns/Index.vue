<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    campaigns: Object,
    filters: Object,
});

const showModal = ref(false);
const editingCampaign = ref(null);

const form = useForm({
    name: '',
    description: '',
    start_date: '',
    end_date: '',
    status: 'inactive',
});

const openCreateModal = () => {
    editingCampaign.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (campaign) => {
    editingCampaign.value = campaign;
    form.name = campaign.name;
    form.description = campaign.description;
    form.start_date = campaign.start_date;
    form.end_date = campaign.end_date;
    form.status = campaign.status;
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    if (editingCampaign.value) {
        form.put(route('admin.campaigns.update', editingCampaign.value.id), {
            onSuccess: () => showModal.value = false,
        });
    } else {
        form.post(route('admin.campaigns.store'), {
            onSuccess: () => showModal.value = false,
        });
    }
};

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const updateFilters = () => {
    router.get(route('admin.campaigns.index'), {
        search: search.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch([search, statusFilter], () => {
    // Debounce here would be good
    setTimeout(updateFilters, 300);
});

const getStatusBadge = (status) => {
    switch (status) {
        case 'active': return 'bg-emerald-100 text-emerald-700';
        case 'inactive': return 'bg-amber-100 text-amber-700';
        case 'finished': return 'bg-charcoal-100 text-charcoal-500';
        default: return 'bg-pearl-200 text-charcoal-400';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'active': return 'Active';
        case 'inactive': return 'Inactive';
        case 'finished': return 'Terminée';
        default: return status;
    }
};
</script>

<template>
    <Head title="Gestion des Campagnes — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Gestion des Campagnes</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Pilotez le cycle de vie de vos opérations commerciales</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="px-4 py-2 bg-gold-gradient text-charcoal-900 rounded-lg text-xs font-bold shadow-gold hover:opacity-90 transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouvelle Campagne
                </button>
            </div>
        </template>

        <!-- Filters -->
        <div class="mb-6 bg-white p-4 rounded-xl border border-pearl-200 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <div class="flex gap-4 flex-1 max-w-2xl">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Rechercher une campagne..." 
                        class="block w-full pl-10 pr-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500"
                    />
                </div>
                <select 
                    v-model="statusFilter"
                    class="border border-pearl-200 rounded-lg text-xs px-3 py-2 focus:ring-gold-500 focus:border-gold-500 text-charcoal-600"
                >
                    <option value="">Tous les statuts</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="finished">Terminée</option>
                </select>
            </div>
        </div>

        <!-- Campaigns Table -->
        <div class="bg-white rounded-xl border border-pearl-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-pearl-50 border-b border-pearl-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Campagne</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Statut</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest">Période</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-charcoal-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pearl-100">
                        <tr v-for="campaign in campaigns.data" :key="campaign.id" class="hover:bg-pearl-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-charcoal-700">{{ campaign.name }}</div>
                                <div class="text-[10px] text-charcoal-400 mt-0.5 line-clamp-1 max-w-xs">{{ campaign.description || 'Pas de description' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="getStatusBadge(campaign.status)" class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-tighter">
                                    {{ getStatusLabel(campaign.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-[10px] text-charcoal-600">
                                    {{ new Date(campaign.start_date).toLocaleDateString('fr-FR') }} ➔ 
                                    {{ campaign.end_date ? new Date(campaign.end_date).toLocaleDateString('fr-FR') : 'Indéfinie' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button 
                                    @click="openEditModal(campaign)"
                                    class="text-charcoal-400 hover:text-gold-600 p-2 transition-colors"
                                    title="Modifier"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button 
                                    class="text-charcoal-400 hover:text-rose-600 p-2 transition-colors ml-1"
                                    title="Supprimer"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="campaigns.data.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-charcoal-400 italic text-sm">Aucune campagne trouvée.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="campaigns.links.length > 3" class="px-6 py-4 bg-pearl-50 border-t border-pearl-200 flex justify-center">
                <div class="flex gap-1">
                    <Link 
                        v-for="link in campaigns.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 shadow-sm': link.active,
                            'bg-white text-charcoal-600 border border-pearl-200 hover:bg-pearl-100': !link.active && link.url,
                            'opacity-50 cursor-not-allowed': !link.url
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-charcoal-900/80 backdrop-blur-sm">
                <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-pearl-200">
                    <div class="px-6 py-4 bg-pearl-50 border-b border-pearl-200 flex justify-between items-center">
                        <h2 class="text-sm font-black text-charcoal-700 uppercase tracking-widest">
                            {{ editingCampaign ? 'Modifier la campagne' : 'Nouvelle campagne' }}
                        </h2>
                        <button @click="showModal = false" class="text-charcoal-400 hover:text-charcoal-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submit" class="p-6 space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Nom de la campagne</label>
                            <input v-model="form.name" type="text" class="w-full border border-pearl-200 rounded-lg p-2.5 text-xs focus:ring-gold-500 focus:border-gold-500" placeholder="Ex: Prospection 2024" required>
                            <div v-if="form.errors.name" class="text-rose-500 text-[10px] mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Description</label>
                            <textarea v-model="form.description" class="w-full border border-pearl-200 rounded-lg p-2.5 text-xs focus:ring-gold-500 focus:border-gold-500" rows="3" placeholder="Objectifs, cibles..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Date de début</label>
                                <input v-model="form.start_date" type="date" class="w-full border border-pearl-200 rounded-lg p-2.5 text-xs focus:ring-gold-500 focus:border-gold-500" required>
                                <div v-if="form.errors.start_date" class="text-rose-500 text-[10px] mt-1">{{ form.errors.start_date }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Date de fin</label>
                                <input v-model="form.end_date" type="date" class="w-full border border-pearl-200 rounded-lg p-2.5 text-xs focus:ring-gold-500 focus:border-gold-500">
                                <div v-if="form.errors.end_date" class="text-rose-500 text-[10px] mt-1">{{ form.errors.end_date }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Statut initial</label>
                            <div class="flex gap-2 p-1 bg-pearl-50 rounded-lg border border-pearl-100">
                                <button 
                                    v-for="status in ['inactive', 'active', 'finished']" 
                                    :key="status"
                                    type="button"
                                    @click="form.status = status"
                                    class="flex-1 py-1.5 rounded-md text-[9px] font-bold uppercase transition-all"
                                    :class="form.status === status ? 'bg-white text-gold-600 shadow-sm border border-pearl-200' : 'text-charcoal-400 hover:text-charcoal-600'"
                                >
                                    {{ getStatusLabel(status) }}
                                </button>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-pearl-100 mt-6">
                            <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-charcoal-400 hover:text-charcoal-600">Annuler</button>
                            <button type="submit" :disabled="form.processing" class="px-8 py-2 bg-gold-gradient text-charcoal-900 rounded-lg text-xs font-bold shadow-gold hover:opacity-90 disabled:opacity-50">
                                {{ form.processing ? 'Enregistrement...' : (editingCampaign ? 'Mettre à jour' : 'Créer la campagne') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
</style>
