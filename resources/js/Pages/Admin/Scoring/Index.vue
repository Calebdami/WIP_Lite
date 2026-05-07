<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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
</script>

<template>
    <Head title="Scoring & Performance — Admin" />
    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Scoring & Performance</h1>
            <p class="text-xs text-charcoal-400 mt-0.5">Suivi des points de performance basés sur les tâches validées</p>
        </template>

        <!-- Search Bar -->
        <div class="mb-6 bg-white p-4 rounded-xl border border-pearl-200 shadow-sm flex items-center justify-between">
            <div class="relative max-w-md w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Rechercher un employé..." 
                    class="block w-full pl-10 pr-3 py-2 border border-pearl-200 rounded-lg text-xs focus:ring-gold-500 focus:border-gold-500"
                />
            </div>
        </div>

        <!-- Employees List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="emp in employees.data" :key="emp.id" class="bg-white rounded-2xl border border-pearl-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-pearl-100 flex items-center justify-center text-charcoal-700 font-bold border-2 border-white shadow-sm group-hover:border-gold-200 transition-colors">
                                {{ emp.first_name.charAt(0) }}{{ emp.last_name.charAt(0) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-charcoal-800">{{ emp.first_name }} {{ emp.last_name }}</h3>
                                <p class="text-[10px] text-charcoal-400 font-bold uppercase tracking-widest">{{ emp.position?.name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black text-gold-600 leading-none">{{ emp.total_score || 0 }}</div>
                            <div class="text-[9px] text-charcoal-400 font-bold uppercase tracking-tighter">Points</div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="bg-pearl-50 rounded-lg p-3 border border-pearl-100">
                            <div class="flex justify-between text-[10px] mb-1.5">
                                <span class="text-charcoal-400 font-bold uppercase">Progression Objectif</span>
                                <span class="text-gold-600 font-black">{{ Math.min(100, Math.round(((emp.total_score || 0) / 1000) * 100)) }}%</span>
                            </div>
                            <div class="h-1.5 bg-pearl-200 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-gold-gradient transition-all duration-500" 
                                    :style="{ width: Math.min(100, Math.round(((emp.total_score || 0) / 1000) * 100)) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-pearl-100 flex justify-between items-center">
                        <div class="text-[10px] text-charcoal-400 font-bold uppercase tracking-tight">
                            Matricule: <span class="text-charcoal-700">{{ emp.matricule }}</span>
                        </div>
                        <Link 
                            :href="route('admin.scoring.show', emp.id)"
                            class="px-4 py-2 bg-charcoal-900 text-white rounded-lg text-[10px] font-bold hover:bg-gold-600 transition-all uppercase tracking-wider"
                        >
                            Gérer le Scoring
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="employees.data.length === 0" class="mt-12 text-center py-20 bg-white rounded-2xl border border-dashed border-pearl-300">
            <div class="text-charcoal-300 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-charcoal-700">Aucun employé trouvé</h3>
            <p class="text-sm text-charcoal-400">Ajustez votre recherche ou ajoutez des tâches pour voir les scores.</p>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex justify-center">
            <div class="flex gap-2">
                <Link 
                    v-for="link in employees.links" 
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all border shadow-sm"
                    :class="{
                        'bg-gold-gradient text-charcoal-900 border-gold-200': link.active,
                        'bg-white text-charcoal-600 border-pearl-200 hover:bg-pearl-50': !link.active && link.url,
                        'opacity-50 cursor-not-allowed bg-pearl-100': !link.url
                    }"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
</style>
