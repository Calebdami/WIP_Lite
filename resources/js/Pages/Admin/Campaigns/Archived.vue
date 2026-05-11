<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';

const props = defineProps({
    campaigns: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

const triggerSearch = () => {
    router.get(route('admin.campaigns.archived'), {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleSearchKeydown = (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        triggerSearch();
    }
};

const formatDate = (dateString, includeTime = false) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: includeTime ? '2-digit' : undefined,
        minute: includeTime ? '2-digit' : undefined
    });
};
</script>

<template>
    <Head title="Archives des Campagnes — Admin" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.campaigns.index')" class="w-8 h-8 rounded-full bg-pearl-100 flex items-center justify-center text-charcoal-600 hover:bg-gold-50 hover:text-gold-600 transition-all">
                        <i class="pi pi-arrow-left text-xs"></i>
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Archives des Campagnes</h1>
                        <p class="text-xs text-charcoal-400 mt-0.5">Historique des opérations clôturées et archivées</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-100 rounded-lg text-amber-700">
                    <i class="pi pi-archive text-xs"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">Espace Archivage</span>
                </div>
            </div>
        </template>

        <!-- Search Bar -->
        <div class="mb-6 bg-white p-4 rounded-xl border border-pearl-200 shadow-sm">
            <div class="relative max-w-md w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                    <i class="pi pi-search text-xs"></i>
                </span>
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Rechercher dans les archives..." 
                    @keydown="handleSearchKeydown"
                    class="block w-full pl-10 pr-20 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-amber-500 focus:border-amber-500"
                />
                <button @click="triggerSearch"
                    class="absolute inset-y-0 right-0 px-4 bg-amber-500 text-white rounded-r-lg hover:bg-amber-600 transition-all">
                    <i class="pi pi-search text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Archived Campaigns Table -->
        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable 
                :value="campaigns.data" 
                responsiveLayout="scroll" 
                class="p-datatable-sm"
                stripedRows
            >
                <template #empty>
                    <div class="text-center p-12 text-charcoal-400 flex flex-col items-center gap-3">
                        <i class="pi pi-box text-5xl opacity-10"></i>
                        <p class="italic">Aucune campagne dans les archives.</p>
                    </div>
                </template>
                
                <Column header="Campagne" field="name">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-charcoal-700 uppercase">{{ data.name }}</span>
                            <span class="text-[10px] text-charcoal-400 mt-0.5 line-clamp-1 max-w-xs italic">{{ data.description || 'Pas de description' }}</span>
                        </div>
                    </template>
                </Column>

                <Column header="Période Active">
                    <template #body="{ data }">
                        <div class="text-[10px] text-charcoal-500">
                            {{ formatDate(data.start_date) }} ➔ {{ data.end_date ? formatDate(data.end_date) : 'Archivage' }}
                        </div>
                    </template>
                </Column>

                <Column header="Date d'Archivage" sortable field="archived_at">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-amber-700">{{ formatDate(data.archived_at) }}</span>
                            <span class="text-[9px] text-charcoal-400">{{ formatDate(data.archived_at, true).split(' ')[1] }}</span>
                        </div>
                    </template>
                </Column>

                <Column header="Statut Final" class="text-center">
                    <template #body>
                        <Tag value="ARCHIVÉ" severity="warning" class="text-[8px] font-black px-3" />
                    </template>
                </Column>
            </DataTable>

            <!-- Pagination -->
            <div v-if="campaigns.links.length > 3" class="mt-6 flex items-center justify-between border-t border-pearl-100 pt-6">
                <div class="text-xs text-charcoal-400 font-medium">
                    Total archives : <span class="text-charcoal-700 font-bold">{{ campaigns.total || 0 }}</span> campagnes
                </div>
                <div class="flex gap-2">
                    <Link 
                        v-for="link in campaigns.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all border"
                        :class="{
                            'bg-amber-500 text-white border-transparent shadow-sm': link.active,
                            'bg-white text-charcoal-600 border-pearl-200 hover:border-amber-300 hover:text-amber-600': !link.active && link.url,
                            'opacity-40 cursor-not-allowed border-pearl-100 text-charcoal-300': !link.url
                        }"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.shadow-premium {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
}
</style>
