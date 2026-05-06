<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Bienvenue — WIP Lite" />

    <div class="min-h-screen bg-charcoal-950 text-white selection:bg-gold selection:text-charcoal-900 overflow-hidden">
        
        <!-- Navbar -->
        <nav class="fixed top-0 w-full z-50 bg-charcoal-950/80 backdrop-blur-md border-b border-charcoal-800">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gold-gradient flex items-center justify-center shadow-gold">
                        <span class="text-charcoal-900 font-black text-xl">W</span>
                    </div>
                    <span class="text-xl font-black tracking-tight">WIP Lite</span>
                </div>
                
                <div v-if="canLogin" class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="text-sm font-semibold text-gold hover:text-gold-300 transition-colors"
                    >
                        Tableau de bord
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold bg-gold-gradient text-charcoal-900 shadow-gold hover:shadow-lg hover:scale-[1.02] transition-all"
                        >
                            Se connecter
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative pt-32 pb-20 px-6">
            <!-- Background Elements -->
            <div class="absolute top-1/4 -left-20 w-96 h-96 bg-gold opacity-5 blur-[120px] rounded-full pointer-events-none"></div>
            <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-gold opacity-5 blur-[120px] rounded-full pointer-events-none"></div>

            <div class="max-w-7xl mx-auto text-center relative z-10">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-charcoal-900 border border-charcoal-700 mb-8 animate-fade-in">
                    <span class="w-2 h-2 rounded-full bg-gold animate-pulse"></span>
                    <span class="text-xs font-bold text-charcoal-400 uppercase tracking-widest">Solutions de Gestion d'Entreprise</span>
                </div>

                <h1 class="text-5xl md:text-7xl font-black tracking-tight mb-8 leading-[1.1]">
                    L'excellence dans la <br />
                    <span class="text-transparent bg-clip-text bg-gold-gradient">planification</span>
                </h1>

                <p class="max-w-2xl mx-auto text-charcoal-400 text-lg md:text-xl leading-relaxed mb-12">
                    WIP Lite offre une plateforme robuste et intuitive pour la gestion de vos ressources, 
                    la planification de campagnes et le suivi en temps réel de votre activité.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        :href="route('login')"
                        class="w-full sm:w-auto px-10 py-4 rounded-2xl bg-gold-gradient text-charcoal-900 font-black text-lg shadow-gold hover:shadow-2xl hover:-translate-y-1 transition-all flex items-center justify-center gap-3"
                    >
                        Accéder à l'espace
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- Dashboard Preview / Info cards -->
            <div class="max-w-7xl mx-auto mt-32 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 rounded-3xl bg-charcoal-900/50 border border-charcoal-800 backdrop-blur-sm">
                    <div class="w-12 h-12 rounded-2xl bg-gold/10 flex items-center justify-center text-gold mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Analyse de Performance</h3>
                    <p class="text-charcoal-400 text-sm leading-relaxed">Suivez vos indicateurs clés en temps réel avec nos tableaux de bord dynamiques et précis.</p>
                </div>

                <div class="p-8 rounded-3xl bg-charcoal-900/50 border border-charcoal-800 backdrop-blur-sm translate-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-gold/10 flex items-center justify-center text-gold mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Gestion du Temps</h3>
                    <p class="text-charcoal-400 text-sm leading-relaxed">Simplifiez la saisie et la validation des feuilles de temps pour une gestion optimale des ressources.</p>
                </div>

                <div class="p-8 rounded-3xl bg-charcoal-900/50 border border-charcoal-800 backdrop-blur-sm translate-y-8">
                    <div class="w-12 h-12 rounded-2xl bg-gold/10 flex items-center justify-center text-gold mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Collaboration d'Équipe</h3>
                    <p class="text-charcoal-400 text-sm leading-relaxed">Connectez vos administrateurs, superviseurs et techniciens sur une plateforme unique.</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-40 border-t border-charcoal-800 py-12 px-6">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3 opacity-50">
                    <div class="w-6 h-6 rounded bg-gold-gradient flex items-center justify-center">
                        <span class="text-charcoal-900 font-black text-[10px]">W</span>
                    </div>
                    <span class="text-sm font-bold">WIP Lite</span>
                </div>
                <p class="text-charcoal-600 text-xs">
                    © {{ new Date().getFullYear() }} WIP Lite · Tous droits réservés
                </p>
                <div class="flex gap-6">
                    <a href="#" class="text-charcoal-500 hover:text-gold text-xs transition-colors">Confidentialité</a>
                    <a href="#" class="text-charcoal-500 hover:text-gold text-xs transition-colors">Conditions</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.8s ease-out forwards;
}
</style>
