<script setup>
import CpLayout from '@/Layouts/CpLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    recentActivities: Array
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const getStatusBadge = (status) => {
    switch (status) {
        case 'validé': return 'bg-emerald-100 text-emerald-700';
        case 'en attente': return 'bg-amber-100 text-amber-700';
        case 'suspendu': return 'bg-red-100 text-red-700';
        default: return 'bg-pearl-100 text-charcoal-600';
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Tableau de bord — Chef de Plateau" />
    <CpLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Tableau de bord</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Console de pilotage du plateau</p>
                </div>
                <div class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">
                    {{ new Date().toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' }) }}
                </div>
            </div>
        </template>

        <!-- Welcome Banner -->
        <div class="rounded-3xl p-8 mb-8 relative overflow-hidden shadow-premium"
             style="background: linear-gradient(135deg, #111827 0%, #374151 100%);">
            <div class="relative z-10">
                <p class="text-gold-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 opacity-80">Chef de Plateau</p>
                <h2 class="text-3xl font-black text-white mb-3">Hello, {{ user?.name || 'CP' }}</h2>
                <p class="text-charcoal-400 text-sm max-w-md leading-relaxed">
                    Validez les heures de vos agents, gérez vos superviseurs et suivez la performance du plateau en temps réel.
                </p>
            </div>
            <!-- Decorative elements -->
            <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-gold-500/10 blur-3xl"></div>
            <div class="absolute right-20 top-0 w-32 h-32 rounded-full bg-white/5 blur-2xl"></div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-50 flex items-center justify-center text-gold-600 shadow-inner">
                        <i class="pi pi-users text-lg"></i>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Agents inscrits</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">{{ stats?.employees || 0 }}</span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-tighter">Effectif Total</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-50 flex items-center justify-center text-amber-600 shadow-inner">
                        <i class="pi pi-briefcase text-lg"></i>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Campagnes</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">{{ stats?.campaigns || 0 }}</span>
                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-tighter">En activité</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-pearl-200 p-6 shadow-sm hover:shadow-md transition-premium">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pearl-50 flex items-center justify-center text-emerald-600 shadow-inner">
                        <i class="pi pi-check-circle text-lg"></i>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-charcoal-400">Alertes</p>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-charcoal-700">{{ stats?.alerts || 0 }}</span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-tighter">Pointages critiques</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- Quick Actions -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl border border-pearl-200 shadow-premium overflow-hidden h-full">
                    <div class="px-8 py-6 border-b border-pearl-100 flex items-center justify-between bg-pearl-50/20">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-charcoal-700">Actions Prioritaires</h2>
                        <div class="flex gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-gold-400 animate-pulse"></span>
                            <span class="text-[9px] font-black text-gold-600 uppercase tracking-widest">Temps réel</span>
                        </div>
                    </div>
                    <div class="p-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <Link :href="route('cp.time-tracking.validate-tc')" class="flex items-center gap-5 p-6 rounded-2xl border border-pearl-100 hover:border-gold-300 hover:bg-gold-50/30 transition-premium group shadow-sm hover:shadow-gold-premium">
                            <div class="w-14 h-14 rounded-2xl bg-gold-gradient flex items-center justify-center flex-shrink-0 shadow-gold-premium group-hover:rotate-6 transition-premium">
                                <i class="pi pi-check-square text-charcoal-900 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-base font-black text-charcoal-700">Valider les Heures TC</p>
                                <p class="text-xs text-charcoal-400 mt-1">Feuilles soumises par les superviseurs</p>
                            </div>
                        </Link>
                        <Link :href="route('cp.time-tracking.supervisors')" class="flex items-center gap-5 p-6 rounded-2xl border border-pearl-100 hover:border-charcoal-400 hover:bg-pearl-50 transition-premium group shadow-sm">
                            <div class="w-14 h-14 rounded-2xl bg-pearl-200 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-premium">
                                <i class="pi pi-user-edit text-charcoal-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-base font-black text-charcoal-700">Saisir Heures SUP</p>
                                <p class="text-xs text-charcoal-400 mt-1">Journal de présence des superviseurs</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl border border-pearl-200 shadow-premium overflow-hidden h-full flex flex-col">
                    <div class="px-8 py-6 border-b border-pearl-100 flex items-center justify-between bg-pearl-50/20">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-charcoal-700">Activités Récentes</h2>
                        <Link :href="route('cp.schedules.history')" class="text-[10px] font-bold text-gold-600 hover:underline">Voir tout</Link>
                    </div>
                    <div class="p-6 flex-1">
                        <div v-if="recentActivities?.length" class="space-y-6">
                            <div v-for="activity in recentActivities" :key="activity.id" class="flex gap-4 relative">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-pearl-50 flex items-center justify-center text-charcoal-400 border border-pearl-100">
                                    <i :class="activity.new_status === 'validé' ? 'pi pi-check text-[10px] text-emerald-500' : 'pi pi-sync text-[10px]'"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-xs font-black text-charcoal-700">
                                            {{ activity.assignment?.employee?.first_name }} {{ activity.assignment?.employee?.last_name }}
                                        </p>
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full" :class="getStatusBadge(activity.new_status)">
                                            {{ activity.new_status }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-charcoal-400 leading-tight">
                                        {{ activity.reason }} <br/>
                                        <span class="text-charcoal-300 italic">Par {{ activity.author?.name }} — {{ formatDate(activity.created_at) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="h-full flex flex-col items-center justify-center py-12 text-center opacity-40">
                            <i class="pi pi-inbox text-3xl mb-2"></i>
                            <p class="text-xs font-bold uppercase tracking-widest">Aucune activité</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CpLayout>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
.shadow-premium {
    box-shadow: 0 15px 50px -15px rgba(0,0,0,0.1);
}
.shadow-gold-premium {
    box-shadow: 0 10px 25px -5px rgba(212,160,23,0.3);
}
.transition-premium {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
