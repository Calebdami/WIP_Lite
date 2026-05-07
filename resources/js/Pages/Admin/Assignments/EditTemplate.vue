<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    model: Object
});

const form = useForm({
    name: props.model.name,
    description: props.model.description || '',
    hours_summary: props.model.hours_summary || '',
    monday_hours: props.model.monday_hours,
    tuesday_hours: props.model.tuesday_hours,
    wednesday_hours: props.model.wednesday_hours,
    thursday_hours: props.model.thursday_hours,
    friday_hours: props.model.friday_hours,
    saturday_hours: props.model.saturday_hours,
    sunday_hours: props.model.sunday_hours
});

const totalHours = computed(() => {
    return [
        form.monday_hours, form.tuesday_hours, form.wednesday_hours,
        form.thursday_hours, form.friday_hours, form.saturday_hours, form.sunday_hours
    ].reduce((acc, curr) => acc + (parseFloat(curr) || 0), 0);
});

const workingDaysCount = computed(() => {
    return [
        form.monday_hours, form.tuesday_hours, form.wednesday_hours,
        form.thursday_hours, form.friday_hours, form.saturday_hours, form.sunday_hours
    ].filter(h => parseFloat(h) > 0).length;
});

const averageHoursPerDay = computed(() => {
    return workingDaysCount.value > 0 ? (totalHours.value / workingDaysCount.value).toFixed(1) : 0;
});

const submit = () => {
    form.patch(route('admin.assignments.schedules.update', props.model.id));
};

const days = [
    { key: 'monday_hours', label: 'Lundi' },
    { key: 'tuesday_hours', label: 'Mardi' },
    { key: 'wednesday_hours', label: 'Mercredi' },
    { key: 'thursday_hours', label: 'Jeudi' },
    { key: 'friday_hours', label: 'Vendredi' },
    { key: 'saturday_hours', label: 'Samedi' },
    { key: 'sunday_hours', label: 'Dimanche' }
];
</script>

<template>
    <Head :title="'Modifier ' + props.model.name + ' — Admin'" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Modifier le modèle</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Édition de : <span class="text-gold-600 font-bold">{{ props.model.name }}</span></p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.assignments.schedules')" class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-bold text-charcoal-600 hover:bg-pearl-50 transition-all">
                        Annuler
                    </Link>
                    <button 
                        @click="submit"
                        :disabled="form.processing || !form.name"
                        class="px-4 py-2 bg-gold-gradient rounded-lg text-xs font-bold text-white hover:opacity-90 transition-all shadow-gold disabled:opacity-50"
                    >
                        Enregistrer les modifications
                    </button>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulaire principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Infos générales -->
                <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm">
                    <h2 class="text-sm font-bold text-charcoal-700 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-pearl-100 text-charcoal-400 flex items-center justify-center text-[10px]">01</span>
                        Informations générales
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold text-charcoal-400 uppercase tracking-widest mb-1.5">Nom du modèle *</label>
                            <input 
                                v-model="form.name"
                                type="text" 
                                placeholder="ex: 35h Standard - Bureau"
                                class="w-full bg-pearl-50 border border-pearl-200 rounded-lg px-4 py-2.5 text-sm text-charcoal-700 focus:ring-2 focus:ring-gold-400/20 focus:border-gold-400 outline-none transition-all"
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-[10px] mt-1">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-charcoal-400 uppercase tracking-widest mb-1.5">Plage horaire (affichage)</label>
                            <input 
                                v-model="form.hours_summary"
                                type="text" 
                                placeholder="ex: 9h00-17h00"
                                class="w-full bg-pearl-50 border border-pearl-200 rounded-lg px-4 py-2.5 text-sm text-charcoal-700 focus:ring-2 focus:ring-gold-400/20 focus:border-gold-400 outline-none transition-all"
                            />
                            <div v-if="form.errors.hours_summary" class="text-red-500 text-[10px] mt-1">{{ form.errors.hours_summary }}</div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-charcoal-400 uppercase tracking-widest mb-1.5">Description</label>
                            <textarea 
                                v-model="form.description"
                                rows="2"
                                placeholder="Détails sur l'utilisation de ce planning..."
                                class="w-full bg-pearl-50 border border-pearl-200 rounded-lg px-4 py-2.5 text-sm text-charcoal-700 focus:ring-2 focus:ring-gold-400/20 focus:border-gold-400 outline-none transition-all resize-none"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Grille horaire -->
                <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm">
                    <h2 class="text-sm font-bold text-charcoal-700 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-pearl-100 text-charcoal-400 flex items-center justify-center text-[10px]">02</span>
                        Heures de travail par jour
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                        <div v-for="day in days" :key="day.key" class="space-y-2">
                            <label class="block text-center text-[10px] font-bold text-charcoal-400 uppercase tracking-tighter">{{ day.label }}</label>
                            <input 
                                v-model="form[day.key]"
                                type="number" 
                                step="0.5"
                                min="0"
                                max="24"
                                class="w-full bg-pearl-50 border border-pearl-200 rounded-lg px-2 py-3 text-center text-sm font-bold text-charcoal-700 focus:ring-2 focus:ring-gold-400/20 focus:border-gold-400 outline-none transition-all"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Statistiques & Règles -->
            <div class="space-y-6">
                <!-- Récapitulatif -->
                <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gold-400/5 rounded-full -mr-8 -mt-8"></div>
                    <h2 class="text-sm font-bold text-charcoal-700 mb-6 flex items-center gap-2">
                        Visualisation
                    </h2>
                    
                    <div class="space-y-6">
                        <div class="flex items-end justify-between border-b border-pearl-100 pb-4">
                            <div>
                                <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Total Hebdo</div>
                                <div class="text-3xl font-black text-charcoal-700">{{ totalHours }}h</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] font-bold text-charcoal-400 uppercase tracking-widest mb-1">Moyenne / jour</div>
                                <div class="text-lg font-bold text-gold-600">{{ averageHoursPerDay }}h</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-charcoal-500 font-medium">Jours travaillés</span>
                            <span class="px-2 py-1 bg-pearl-100 text-charcoal-700 rounded-lg text-xs font-bold">{{ workingDaysCount }} / 7</span>
                        </div>
                        
                        <div class="pt-4">
                            <div class="h-2 w-full bg-pearl-100 rounded-full overflow-hidden flex">
                                <div 
                                    v-for="day in days" 
                                    :key="day.key"
                                    :style="{ width: (totalHours > 0 ? (parseFloat(form[day.key]) / totalHours * 100) : 0) + '%' }"
                                    class="h-full border-r border-white last:border-0 transition-all duration-500"
                                    :class="parseFloat(form[day.key]) > 0 ? 'bg-gold-400' : 'bg-pearl-200'"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Avertissement -->
                <div v-if="props.model.assignments_count > 0" class="bg-orange-50 rounded-xl border border-orange-200 p-6">
                    <div class="flex items-start gap-3 text-orange-600">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-widest mb-1">Attention</h3>
                            <p class="text-xs leading-relaxed opacity-90">Ce modèle est utilisé par <strong>{{ props.model.assignments_count }} employés</strong>. Les modifications impacteront leurs plannings futurs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
.shadow-gold {
    box-shadow: 0 4px 15px -3px rgba(212, 160, 23, 0.4);
}
</style>
