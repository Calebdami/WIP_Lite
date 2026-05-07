<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { router } from '@inertiajs/vue3';

const confirm = useConfirm();
const toast = useToast();

const form = useForm({
    name: '',
    description: '',
    hours_summary: '',
    monday_hours: 0,
    tuesday_hours: 0,
    wednesday_hours: 0,
    thursday_hours: 0,
    friday_hours: 0,
    saturday_hours: 0,
    sunday_hours: 0
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
    confirm.require({
        message: 'Voulez-vous vraiment enregistrer ce nouveau modèle ?',
        header: 'Confirmation de création',
        icon: 'pi pi-plus-circle',
        rejectLabel: 'Annuler',
        acceptLabel: 'Enregistrer',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-primary',
        accept: () => {
            form.post(route('cp.schedules.templates.store'));
        }
    });
};

const cancel = () => {
    if (form.isDirty) {
        confirm.require({
            message: 'Vous avez des modifications non enregistrées. Voulez-vous vraiment quitter ?',
            header: 'Confirmation d\'annulation',
            icon: 'pi pi-exclamation-triangle',
            rejectLabel: 'Rester',
            acceptLabel: 'Quitter',
            rejectClass: 'p-button-secondary p-button-outlined',
            acceptClass: 'p-button-danger',
            accept: () => {
                toast.add({ severity: 'info', summary: 'Annulé', detail: 'Création annulée', life: 3000 });
                router.get(route('cp.schedules.templates'));
            }
        });
    } else {
        toast.add({ severity: 'info', summary: 'Annulé', detail: 'Création annulée', life: 3000 });
        router.get(route('cp.schedules.templates'));
    }
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
    <Head title="Nouveau Modèle de Planning — CP" />
    <CpLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Créer un nouveau modèle</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Définition d'une semaine type de travail</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="cancel" class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-bold text-charcoal-600 hover:bg-pearl-50 transition-all">
                        Annuler
                    </button>
                    <button 
                        @click="submit"
                        :disabled="form.processing || !form.name"
                        class="px-4 py-2 bg-gold-gradient rounded-lg text-xs font-bold text-white hover:opacity-90 transition-all shadow-gold disabled:opacity-50"
                    >
                        Enregistrer le modèle
                    </button>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
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

                <div class="bg-pearl-50 rounded-xl border border-dashed border-pearl-300 p-6">
                    <h3 class="text-xs font-bold text-charcoal-400 mb-4 uppercase tracking-widest">Modèles types (cliquer pour appliquer)</h3>
                    <div class="flex flex-wrap gap-3">
                        <button 
                            @click="form.monday_hours = 7; form.tuesday_hours = 7; form.wednesday_hours = 7; form.thursday_hours = 7; form.friday_hours = 7; form.saturday_hours = 0; form.sunday_hours = 0"
                            class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-medium text-charcoal-600 hover:border-gold-400 hover:text-gold-600 transition-all"
                        >
                            35h Standard (5x7h)
                        </button>
                        <button 
                            @click="form.monday_hours = 8; form.tuesday_hours = 8; form.wednesday_hours = 8; form.thursday_hours = 8; form.friday_hours = 7; form.saturday_hours = 0; form.sunday_hours = 0"
                            class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-medium text-charcoal-600 hover:border-gold-400 hover:text-gold-600 transition-all"
                        >
                            39h Complet
                        </button>
                        <button 
                            @click="form.monday_hours = 0; form.tuesday_hours = 0; form.wednesday_hours = 0; form.thursday_hours = 0; form.friday_hours = 0; form.saturday_hours = 8; form.sunday_hours = 8"
                            class="px-4 py-2 bg-white border border-pearl-200 rounded-lg text-xs font-medium text-charcoal-600 hover:border-gold-400 hover:text-gold-600 transition-all"
                        >
                            Weekend 16h
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
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
                                    :style="{ width: (parseFloat(form[day.key]) / (totalHours || 1) * 100) + '%' }"
                                    class="h-full border-r border-white last:border-0 transition-all duration-500"
                                    :class="parseFloat(form[day.key]) > 0 ? 'bg-gold-400' : 'bg-pearl-200'"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-pearl-200 p-6 shadow-sm">
                    <h2 class="text-sm font-bold text-charcoal-700 mb-4 flex items-center gap-2">
                        Règles métier
                    </h2>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-gold-400 mt-1.5 flex-shrink-0"></div>
                            <p class="text-xs text-charcoal-500 leading-relaxed">Un modèle représente une <strong>semaine type</strong> réutilisable.</p>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-gold-400 mt-1.5 flex-shrink-0"></div>
                            <p class="text-xs text-charcoal-500 leading-relaxed">Les heures sont <strong>numériques</strong> (ex: 7.5 pour 7h30).</p>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-gold-400 mt-1.5 flex-shrink-0"></div>
                            <p class="text-xs text-charcoal-500 leading-relaxed">Le total est <strong>calculé automatiquement</strong> en temps réel.</p>
                        </li>
                    </ul>
                </div>
            </div>
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
