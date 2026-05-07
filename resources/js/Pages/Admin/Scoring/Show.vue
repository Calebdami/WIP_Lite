<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    employee: Object,
    tasks: Array,
});

const showModal = ref(false);

const form = useForm({
    employee_id: props.employee.id,
    title: '',
    points: 10,
    description: '',
});

const submitTask = () => {
    form.post(route('admin.scoring.tasks.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset('title', 'points', 'description');
        },
    });
};

const validateTask = (task) => {
    if (confirm('Voulez-vous valider cette tâche et créditer les points ?')) {
        router.patch(route('admin.scoring.tasks.validate', task.id));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>

<template>
    <Head :title="'Scoring — ' + employee.first_name" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.scoring.index')" class="p-2 hover:bg-pearl-100 rounded-lg transition-colors text-charcoal-400 hover:text-gold-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">{{ employee.first_name }} {{ employee.last_name }}</h1>
                        <p class="text-xs text-charcoal-400 mt-0.5">Détail des performances et validation des tâches</p>
                    </div>
                </div>
                <button 
                    @click="showModal = true"
                    class="px-4 py-2 bg-gold-gradient text-charcoal-900 rounded-lg text-xs font-bold shadow-gold hover:opacity-90 transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Attribuer une tâche
                </button>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm">
                    <h3 class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-4 border-b border-pearl-100 pb-2">Informations Générales</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[9px] text-charcoal-400 uppercase font-bold">Matricule</p>
                            <p class="text-sm font-bold text-charcoal-700">{{ employee.matricule }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] text-charcoal-400 uppercase font-bold">Poste</p>
                            <p class="text-sm font-medium text-charcoal-700">{{ employee.position?.name }}</p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-pearl-100">
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-[9px] text-charcoal-400 uppercase font-bold">Score Total Validé</p>
                                    <p class="text-3xl font-black text-gold-600">{{ tasks.filter(t => t.status === 'validated').reduce((acc, t) => acc + t.points, 0) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] text-charcoal-400 uppercase font-bold">Tâches Validées</p>
                                    <p class="text-sm font-bold text-charcoal-700">{{ tasks.filter(t => t.status === 'validated').length }} / {{ tasks.length }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-pearl-200 overflow-hidden shadow-sm">
                    <div class="px-6 py-4 border-b border-pearl-100 bg-pearl-50/50 flex justify-between items-center">
                        <h3 class="text-[10px] font-bold text-charcoal-700 uppercase tracking-widest">Journal des Tâches</h3>
                    </div>
                    <div class="divide-y divide-pearl-100">
                        <div v-for="task in tasks" :key="task.id" class="p-6 hover:bg-pearl-50/50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1">
                                        <h4 class="text-sm font-bold text-charcoal-800">{{ task.title }}</h4>
                                        <span :class="task.status === 'validated' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'" class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter">
                                            {{ task.status === 'validated' ? 'Validé' : 'En attente' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-charcoal-500 line-clamp-2 leading-relaxed mb-3">{{ task.description || 'Aucune description' }}</p>
                                    <div class="flex items-center gap-4 text-[10px] text-charcoal-400 font-medium">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Créée le: {{ formatDate(task.created_at) }}
                                        </div>
                                        <div v-if="task.validated_at" class="flex items-center gap-1 text-emerald-600">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Validée le: {{ formatDate(task.validated_at) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right flex flex-col items-end gap-3 pl-4">
                                    <div class="text-xl font-black text-gold-600 leading-none">+{{ task.points }} <span class="text-[10px] uppercase">pts</span></div>
                                    <button 
                                        v-if="task.status === 'pending'"
                                        @click="validateTask(task)"
                                        class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-[10px] font-bold hover:bg-emerald-700 transition-colors uppercase tracking-widest"
                                    >
                                        Valider
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-if="tasks.length === 0" class="p-12 text-center text-charcoal-400 italic text-sm">
                            Aucune tâche attribuée pour le moment.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Task Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-charcoal-900/80 backdrop-blur-sm">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-pearl-200">
                    <div class="px-6 py-4 bg-pearl-50 border-b border-pearl-200 flex justify-between items-center">
                        <h2 class="text-sm font-black text-charcoal-700 uppercase tracking-widest">Attribuer une tâche</h2>
                        <button @click="showModal = false" class="text-charcoal-400 hover:text-charcoal-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitTask" class="p-6 space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Titre de la tâche</label>
                            <input v-model="form.title" type="text" class="w-full border border-pearl-200 rounded-lg p-2 text-xs focus:ring-gold-500 focus:border-gold-500" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Points attribués</label>
                            <input v-model="form.points" type="number" class="w-full border border-pearl-200 rounded-lg p-2 text-xs focus:ring-gold-500 focus:border-gold-500" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Description (optionnel)</label>
                            <textarea v-model="form.description" class="w-full border border-pearl-200 rounded-lg p-2 text-xs focus:ring-gold-500 focus:border-gold-500" rows="3"></textarea>
                        </div>
                        <div class="pt-4 flex justify-end gap-3">
                            <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-charcoal-400 hover:text-charcoal-600">Annuler</button>
                            <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-gold-gradient text-charcoal-900 rounded-lg text-xs font-bold shadow-gold hover:opacity-90 disabled:opacity-50">Attribuer</button>
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
