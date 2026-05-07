<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="404 - Page Non Trouvée" />

    <div class="min-h-screen bg-charcoal-900 flex flex-col items-center justify-center relative overflow-hidden font-sans">
        <!-- Background Elements -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-gold-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-gold-800/10 rounded-full blur-[120px]"></div>
        
        <!-- Main Content -->
        <div class="relative z-10 text-center px-6">
            <div class="mb-8 relative inline-block">
                <h1 class="text-[180px] md:text-[240px] font-black text-transparent bg-clip-text bg-gold-gradient opacity-20 select-none leading-none">
                    404
                </h1>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-white font-black text-4xl md:text-6xl tracking-tighter uppercase">
                        Oups !
                    </span>
                </div>
            </div>

            <div class="max-w-md mx-auto">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">La page est introuvable</h2>
                <p class="text-charcoal-400 text-sm md:text-base mb-10 leading-relaxed font-medium">
                    Il semble que le chemin que vous essayez d'emprunter n'existe pas ou a été déplacé.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link 
                        href="/dashboard" 
                        class="px-8 py-4 bg-gold-gradient text-charcoal-900 rounded-2xl font-black text-xs uppercase tracking-widest shadow-gold-premium hover:scale-105 active:scale-95 transition-premium"
                    >
                        Retour au Tableau de Bord
                    </Link>
                    <button 
                        @click="window.history.back()"
                        class="px-8 py-4 bg-charcoal-800 text-white rounded-2xl font-black text-xs uppercase tracking-widest border border-charcoal-700 hover:bg-charcoal-700 transition-premium"
                    >
                        Page Précédente
                    </button>
                </div>
            </div>
        </div>

        <!-- Decorative lines -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-px h-full bg-gradient-to-b from-transparent via-charcoal-700/50 to-transparent opacity-20"></div>
        <div class="absolute top-1/2 left-0 -translate-y-1/2 h-px w-full bg-gradient-to-r from-transparent via-charcoal-700/50 to-transparent opacity-20"></div>
    </div>
</template>

<style scoped>
.bg-gold-gradient {
    background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);
}

.shadow-gold-premium {
    box-shadow: 0 10px 30px -10px rgba(212, 160, 23, 0.4);
}

.transition-premium {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.bg-charcoal-900 {
    background-color: #0F1115;
}

.bg-charcoal-800 {
    background-color: #1A1D23;
}

.text-charcoal-400 {
    color: #64748B;
}

.border-charcoal-700 {
    border-color: #2D333D;
}
</style>
