<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import ProgressBar from 'primevue/progressbar';

const props = defineProps({
    employees: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

// Custom debounce
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn(...args);
        }, delay);
    };
};

const updateFilters = debounce(() => {
    router.get(route('admin.scoring.index'), {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(search, updateFilters);

const getProgressValue = (score) => {
    return Math.min(100, Math.round(((score || 0) / 1000) * 100));
};
</script>

<template>
    <Head title="Scoring & Performance — Admin" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Scoring & Performance</h1>
                <p class="text-xs text-charcoal-400 mt-0.5">Suivi des points de performance basés sur les tâches validées</p>
            </div>
        </template>

        <!-- Search Bar -->
        <div class="mb-6 bg-white p-4 rounded-xl border border-pearl-200 shadow-sm">
            <div class="relative max-w-md w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                    <i class="pi pi-search text-xs"></i>
                </span>
                <InputText 
                    v-model="search"
                    placeholder="Rechercher un employé..." 
                    class="block w-full pl-10 text-xs border-pearl-200 focus:border-gold outline-none"
                />
            </div>
        </div>

        <!-- Scoring Table (PrimeVue DataTable) -->
        <div class="bg-white rounded-2xl border border-pearl-200 shadow-premium p-6">
            <DataTable 
                :value="employees.data" 
                responsiveLayout="scroll" 
                class="p-datatable-sm"
                stripedRows
            >
                <template #empty>
                    <div class="text-center p-8 text-charcoal-400 flex flex-col items-center gap-3">
                        <i class="pi pi-chart-bar text-4xl opacity-20"></i>
                        <p>Aucun score trouvé.</p>
                    </div>
                </template>
                <Column header="Employé" sortable field="last_name">
                    <template #body="{ data }">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-pearl-100 flex items-center justify-center text-charcoal-700 font-bold text-[10px] border border-pearl-200">
                                {{ data.first_name.charAt(0) }}{{ data.last_name.charAt(0) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold text-gold-600 leading-none mb-0.5 uppercase tracking-tighter">{{ data.matricule }}</span>
                                <span class="font-bold text-charcoal-700 uppercase">{{ data.first_name }} {{ data.last_name }}</span>
                                <span class="text-[9px] text-charcoal-400 font-bold uppercase tracking-widest">{{ data.position?.name }}</span>
                            </div>
                        </div>
                    </template>
                </Column>
                <Column field="total_score" header="Points" sortable class="text-center">
                    <template #body="{ data }">
                        <div class="text-lg font-black text-gold-600">{{ data.total_score || 0 }}</div>
                    </template>
                </Column>
                <Column header="Objectif (1000 pts)" class="w-1/3">
                    <template #body="{ data }">
                        <div class="flex flex-col gap-1.5">
                            <div class="flex justify-between text-[9px] font-bold uppercase tracking-tighter text-charcoal-500">
                                <span>Progression</span>
                                <span>{{ getProgressValue(data.total_score) }}%</span>
                            </div>
                            <div class="h-2 bg-pearl-100 rounded-full overflow-hidden border border-pearl-200">
                                <div 
                                    class="h-full bg-gold-gradient transition-all duration-500" 
                                    :style="{ width: getProgressValue(data.total_score) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </template>
                </Column>
                <Column header="Actions" class="text-right">
                    <template #body="{ data }">
                        <Link 
                            :href="route('admin.scoring.show', data.id)"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-charcoal-900 text-white rounded-lg text-[9px] font-bold hover:bg-gold-600 transition-all uppercase tracking-widest"
                        >
                            <i class="pi pi-cog text-[10px]"></i>
                            Gérer
                        </Link>
                    </template>
                </Column>
            </DataTable>

            <!-- Pagination -->
            <div v-if="employees.links.length > 3" class="mt-6 flex items-center justify-between border-t border-pearl-100 pt-6">
                <div class="text-xs text-charcoal-400 font-medium uppercase tracking-widest">
                    Page <span class="text-charcoal-700 font-bold">{{ employees.current_page }}</span> sur <span class="text-charcoal-700 font-bold">{{ employees.last_page }}</span>
                </div>
                <div class="flex gap-2">
                    <Link 
                        v-for="link in employees.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3.5 py-1.5 rounded-xl text-[10px] font-black uppercase transition-all border"
                        :class="{
                            'bg-gold-gradient text-charcoal-900 border-transparent shadow-gold-sm': link.active,
                            'bg-white text-charcoal-500 border-pearl-200 hover:border-gold-300 hover:text-gold-600': !link.active && link.url,
                            'opacity-40 cursor-not-allowed border-pearl-100 text-charcoal-300': !link.url
                        }"
                    />
                </div>
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
</style>
