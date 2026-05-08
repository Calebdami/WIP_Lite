<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import ConfirmDialogBox from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    campaigns: Object,
    filters: Object,
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const editingCampaign = ref(null);
const campaignToDelete = ref(null);

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

const confirmDelete = (campaign) => {
    campaignToDelete.value = campaign;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!campaignToDelete.value) return;
    router.delete(route('admin.campaigns.destroy', campaignToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            campaignToDelete.value = null;
        }
    });
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

// Manual search functions
const triggerSearch = () => {
    router.get(route('admin.campaigns.index'), {
        search: search.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Handle Enter key press
const handleSearchKeydown = (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        triggerSearch();
    }
};

// Custom debounce (for status filter changes only)
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn(...args);
        }, delay);
    };
};

// Only debounce status filter changes, not search
const updateStatusFilter = debounce(() => {
    router.get(route('admin.campaigns.index'), {
        search: search.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(statusFilter, updateStatusFilter);

const getStatusSeverity = (status) => {
    switch (status) {
        case 'active': return 'success';
        case 'inactive': return 'warn';
        case 'finished': return 'info';
        default: return 'info';
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
                <Button label="Nouvelle Campagne" icon="pi pi-plus" 
                    severity="primary" 
                    @click="openCreateModal"
                    class="px-4 py-2 text-xs font-bold shadow-gold-premium" />
            </div>
        </template>

        <!-- Filters -->
        <div class="mb-6 bg-white p-4 rounded-xl border border-pearl-200 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <div class="flex gap-4 flex-1 max-w-2xl">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                        <i class="pi pi-search text-xs"></i>
                    </span>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Rechercher une campagne..." 
                        @keydown="handleSearchKeydown"
                        class="block w-full pl-10 pr-20 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500"
                    />
                    <button @click="triggerSearch"
                        class="absolute inset-y-0 right-0 px-4 bg-gold-gradient text-white rounded-r-lg hover:bg-gold-700 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
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

        <!-- Campaigns Table (PrimeVue DataTable) -->
        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable 
                :value="campaigns.data" 
                responsiveLayout="scroll" 
                class="p-datatable-sm"
                stripedRows
            >
                <template #empty>
                    <div class="text-center p-8 text-charcoal-400 flex flex-col items-center gap-3">
                        <i class="pi pi-briefcase text-4xl opacity-20"></i>
                        <p>Aucune campagne trouvée.</p>
                    </div>
                </template>
                <Column header="Campagne" sortable field="name">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-charcoal-700 uppercase">{{ data.name }}</span>
                            <span class="text-[10px] text-charcoal-400 mt-0.5 line-clamp-1 max-w-xs">{{ data.description || 'Pas de description' }}</span>
                        </div>
                    </template>
                </Column>
                <Column field="status" header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag :value="getStatusLabel(data.status).toUpperCase()" :severity="getStatusSeverity(data.status)" class="text-[9px]" />
                    </template>
                </Column>
                <Column header="Période" sortable field="start_date">
                    <template #body="{ data }">
                        <div class="text-[10px] text-charcoal-600 font-medium">
                            {{ new Date(data.start_date).toLocaleDateString('fr-FR') }} ➔ 
                            {{ data.end_date ? new Date(data.end_date).toLocaleDateString('fr-FR') : 'Indéfinie' }}
                        </div>
                    </template>
                </Column>
                <Column header="Actions" class="text-right">
                    <template #body="{ data }">
                        <div class="flex justify-end gap-1">
                            <Button icon="pi pi-pencil" text severity="secondary" rounded @click="openEditModal(data)" title="Modifier" />
                            <Button icon="pi pi-trash" text severity="danger" rounded @click="confirmDelete(data)" title="Supprimer" />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Pagination -->
            <div v-if="campaigns.links.length > 3" class="mt-6 flex items-center justify-between border-t border-pearl-100 pt-6">
                <div class="text-xs text-charcoal-400 font-medium">
                    Affichage de <span class="text-charcoal-700 font-bold">{{ campaigns.from || 0 }}</span> à <span class="text-charcoal-700 font-bold">{{ campaigns.to || 0 }}</span> sur <span class="text-charcoal-700 font-bold">{{ campaigns.total || 0 }}</span> campagnes
                </div>
                <div class="flex gap-2">
                    <Link 
                        v-for="link in campaigns.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all border"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 border-transparent shadow-gold-sm': link.active,
                            'bg-white text-charcoal-600 border-pearl-200 hover:border-gold-300 hover:text-gold-600': !link.active && link.url,
                            'opacity-40 cursor-not-allowed border-pearl-100 text-charcoal-300': !link.url
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:visible="showModal" :header="editingCampaign ? 'Modifier la campagne' : 'Nouvelle campagne'" :modal="true" :draggable="false" class="w-full max-w-lg mx-4">
            <form @submit.prevent="submit" class="flex flex-col gap-4 mt-2">
                <div>
                    <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Nom de la campagne</label>
                    <InputText v-model="form.name" class="w-full text-xs" placeholder="Ex: Prospection 2024" required />
                    <div v-if="form.errors.name" class="text-rose-500 text-[10px] mt-1">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Description</label>
                    <textarea v-model="form.description" class="w-full border border-pearl-200 rounded-lg p-2.5 text-xs focus:ring-gold-500 focus:border-gold-500 outline-none" rows="3" placeholder="Objectifs, cibles..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Date de début</label>
                        <InputText v-model="form.start_date" type="date" class="w-full text-xs" required />
                        <div v-if="form.errors.start_date" class="text-rose-500 text-[10px] mt-1">{{ form.errors.start_date }}</div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Date de fin</label>
                        <InputText v-model="form.end_date" type="date" class="w-full text-xs" />
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
                    <Button label="Annuler" text severity="secondary" @click="showModal = false" class="text-xs" />
                    <Button :label="editingCampaign ? 'Mettre à jour' : 'Créer la campagne'" 
                        severity="primary" 
                        type="submit" 
                        :loading="form.processing"
                        class="px-8 py-2 shadow-gold-premium text-xs" />
                </div>
            </form>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <ConfirmDialogBox
            v-model="showDeleteModal"
            title="Confirmation de suppression"
            confirmLabel="Confirmer la suppression"
            cancelLabel="Annuler"
            confirmSeverity="danger"
            icon="pi pi-exclamation-triangle"
            iconBgClass="bg-rose-50"
            iconTextClass="text-rose-500"
            width="450px"
            :closable="false"
            className="max-w-md"
            @confirm="handleDelete"
            @cancel="showDeleteModal = false"
        >
            <p class="text-xs text-charcoal-500 mt-2 leading-relaxed px-4">
                Attention : cette action est irréversible. La campagne <span class="font-bold text-rose-600">"{{ campaignToDelete?.name }}"</span> sera supprimée et <span class="font-bold">tous les employés affectés seront automatiquement libérés</span>.
            </p>
        </ConfirmDialogBox>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}

.shadow-gold-premium {
    box-shadow: 0 10px 20px -10px rgba(212, 160, 23, 0.4);
}

.shadow-gold-sm {
    box-shadow: 0 4px 10px -5px rgba(212, 160, 23, 0.5);
}

.shadow-premium {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
}
</style>
