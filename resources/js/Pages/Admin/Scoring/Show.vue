<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useConfirm } from "primevue/useconfirm";
import ConfirmDialog from 'primevue/confirmdialog';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Slider from 'primevue/slider';
import Textarea from 'primevue/textarea';
import ConfirmDialogBox from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    employee: Object,
    tasks: Array,
});

const confirm = useConfirm();
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
        }
    });
};

const showConfirmModal = ref(false);
const taskToValidate = ref(null);

const confirmValidation = (task) => {
    taskToValidate.value = task;
    showConfirmModal.value = true;
};

const validateTask = () => {
    if (!taskToValidate.value) return;
    
    router.patch(route('admin.scoring.tasks.validate', taskToValidate.value.id), {}, {
        onSuccess: () => {
            showConfirmModal.value = false;
            taskToValidate.value = null;
        },
        onFinish: () => {
            showConfirmModal.value = false;
        }
    });
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
                <Button 
                    label="Attribuer une tâche" 
                    icon="pi pi-plus" 
                    @click="showModal = true"
                    class="bg-gold-gradient border-none text-charcoal-900 text-xs font-black uppercase tracking-widest shadow-gold-premium px-6 py-2.5"
                />
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm">
                    <h3 class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-4 border-b border-pearl-100 pb-2 italic">Informations Générales</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[9px] text-charcoal-400 uppercase font-black tracking-tighter">Matricule Employé</p>
                            <p class="text-sm font-black text-gold-600">{{ employee.matricule }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] text-charcoal-400 uppercase font-black tracking-tighter">Poste Occupé</p>
                            <p class="text-sm font-bold text-charcoal-700 uppercase tracking-tight">{{ employee.position?.name }}</p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-pearl-100 bg-pearl-50/30 rounded-xl p-4">
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-[9px] text-charcoal-400 uppercase font-black tracking-tighter">Score Total</p>
                                    <p class="text-4xl font-black text-gold-700">{{ tasks.filter(t => t.status === 'validated').reduce((acc, t) => acc + t.points, 0) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] text-charcoal-400 uppercase font-black tracking-tighter">Missions Validées</p>
                                    <p class="text-sm font-black text-charcoal-700">{{ tasks.filter(t => t.status === 'validated').length }} / {{ tasks.length }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-pearl-200 overflow-hidden shadow-premium">
                    <div class="px-6 py-4 border-b border-pearl-100 bg-pearl-50/50 flex justify-between items-center">
                        <h3 class="text-[10px] font-black text-charcoal-700 uppercase tracking-[0.2em]">Journal des Missions</h3>
                        <span class="text-[9px] font-bold text-charcoal-400 uppercase">Audit en temps réel</span>
                    </div>
                    <div class="divide-y divide-pearl-100">
                        <div v-for="task in tasks" :key="task.id" class="p-6 hover:bg-pearl-50/30 transition-all duration-300 group">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1.5">
                                        <h4 class="text-sm font-black text-charcoal-800 uppercase tracking-tight">{{ task.title }}</h4>
                                        <Tag 
                                            :value="task.status === 'validated' ? 'Validé' : 'En attente'" 
                                            :severity="task.status === 'validated' ? 'success' : 'warn'" 
                                            class="text-[8px] font-black uppercase tracking-tighter px-2"
                                        />
                                    </div>
                                    <p class="text-xs text-charcoal-500 line-clamp-2 leading-relaxed mb-4 font-medium">{{ task.description || 'Aucune description détaillée pour cette mission.' }}</p>
                                    <div class="flex items-center gap-5 text-[9px] text-charcoal-400 font-black uppercase tracking-widest">
                                        <div class="flex items-center gap-1.5">
                                            <i class="pi pi-calendar text-[10px] text-gold-500"></i>
                                            {{ formatDate(task.created_at) }}
                                        </div>
                                        <div v-if="task.validated_at" class="flex items-center gap-1.5 text-emerald-600">
                                            <i class="pi pi-check-circle text-[10px]"></i>
                                            Validée le {{ formatDate(task.validated_at) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right flex flex-col items-end gap-4 pl-6">
                                    <div class="text-2xl font-black text-gold-600 leading-none tracking-tighter">+{{ task.points }} <span class="text-[9px] uppercase font-bold opacity-70">pts</span></div>
                                    <Button 
                                        v-if="task.status === 'pending'"
                                        label="Valider" 
                                        icon="pi pi-check" 
                                        size="small"
                                        @click="confirmValidation(task)"
                                        class="p-button-success p-button-outlined text-[10px] font-black uppercase tracking-widest px-4 py-1.5 rounded-lg transition-premium"
                                    />
                                </div>
                            </div>
                        </div>
                        <div v-if="tasks.length === 0" class="p-16 text-center text-charcoal-400 italic text-sm bg-pearl-50/20">
                            <i class="pi pi-inbox text-4xl block mb-4 opacity-20"></i>
                            Aucune tâche attribuée pour le moment.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Task Modal (PrimeVue Dialog) -->
        <Dialog 
            v-model:visible="showModal" 
            :modal="true" 
            :draggable="false"
            class="p-dialog-premium"
            :style="{ width: '500px' }"
            :closable="true"
            dismissableMask
        >
            <template #header>
                <div class="flex flex-col">
                    <span class="text-[10px] font-black text-gold-600 uppercase tracking-[0.3em] mb-1">Scoring Management</span>
                    <h2 class="text-base font-black text-charcoal-800 uppercase tracking-tight">Attribuer une nouvelle mission</h2>
                </div>
            </template>

            <form @submit.prevent="submitTask" class="space-y-6 pt-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest block ml-1">Intitulé de la mission</label>
                    <InputText 
                        v-model="form.title" 
                        placeholder="Ex: Performance mensuelle accrue"
                        class="w-full text-xs border-pearl-200 focus:border-gold-500 rounded-xl p-3 bg-pearl-50/50" 
                        required 
                    />
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between items-center mb-1">
                        <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest block ml-1">Points de récompense</label>
                        <span class="text-xs font-black text-gold-600">{{ form.points }} PTS</span>
                    </div>
                    <div class="px-2">
                        <Slider v-model="form.points" :min="1" :max="100" class="w-full" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-charcoal-400 uppercase tracking-widest block ml-1">Consignes et Objectifs</label>
                    <Textarea 
                        v-model="form.description" 
                        rows="4" 
                        class="w-full text-xs border-pearl-200 focus:border-gold-500 rounded-xl p-3 bg-pearl-50/50 resize-none" 
                        placeholder="Détaillez ici les critères de réussite..."
                    />
                </div>

                <div class="pt-6 flex justify-end gap-3 border-t border-pearl-100">
                    <Button 
                        type="button" 
                        label="Abandonner" 
                        text 
                        class="text-xs font-bold text-charcoal-400 uppercase tracking-widest hover:text-charcoal-600" 
                        @click="showModal = false" 
                    />
                    <Button 
                        type="submit" 
                        label="Confirmer l'attribution" 
                        icon="pi pi-send"
                        :loading="form.processing"
                        class="bg-gold-gradient border-none text-charcoal-900 text-xs font-black uppercase tracking-widest shadow-gold-premium px-6 py-2.5 rounded-xl" 
                    />
                </div>
            </form>
        </Dialog>
        <!-- Confirm Validation Modal -->
        <ConfirmDialogBox
            v-model="showConfirmModal"
            title="Valider la mission"
            confirmLabel="Confirmer la validation"
            cancelLabel="Annuler"
            confirmSeverity="success"
            icon="pi pi-exclamation-triangle"
            iconBgClass="bg-amber-100"
            iconTextClass="text-amber-600"
            width="420px"
            :closable="false"
            className="max-w-sm"
            @confirm="validateTask"
            @cancel="showConfirmModal = false"
        >
            <p class="text-xs text-charcoal-500 leading-relaxed">
                Êtes-vous sûr de vouloir valider la mission <span class="font-bold text-charcoal-700">"{{ taskToValidate?.title }}"</span> ?
                Cette action créditera immédiatement <span class="font-black text-gold-600">{{ taskToValidate?.points }} points</span>.
            </p>
        </ConfirmDialogBox>
    </AdminLayout>
</template>

<style>
/* Global PrimeVue Overrides to match App Style */
.p-dialog-premium {
    border-radius: 24px !important;
    overflow: hidden !important;
    border: 1px solid rgba(255, 255, 255, 0.5) !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
}
.p-dialog-premium .p-dialog-header {
    background: #ffffff !important;
    padding: 2rem 2rem 1.5rem 2rem !important;
}
.p-dialog-premium .p-dialog-content {
    background: #ffffff !important;
    padding: 0 2rem 2rem 2rem !important;
}
.p-dialog-premium .p-dialog-footer {
    padding: 0 2rem 2rem 2rem !important;
}
.p-slider .p-slider-handle {
    background: #ffffff !important;
    border: 3px solid #D4A017 !important;
    width: 1.2rem !important;
    height: 1.2rem !important;
    margin-top: -0.6rem !important;
}
.p-slider .p-slider-range {
    background: linear-gradient(90deg, #D4A017, #8B6914) !important;
}
.p-toast .p-toast-message {
    border-radius: 16px !important;
    backdrop-filter: blur(10px) !important;
}
</style>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
.shadow-gold-premium {
    box-shadow: 0 10px 25px -5px rgba(212, 160, 23, 0.4);
}
.shadow-premium {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
}
.transition-premium {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
