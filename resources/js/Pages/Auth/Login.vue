<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        replace: true,
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion — WIP Lite" />

    <!-- Fond gradient sombre Charcoal -->
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden"
         style="background: linear-gradient(145deg, #0A0A0A 0%, #1A1A1A 40%, #262626 100%);">

        <!-- Cercles décoratifs flous -->
        <div class="absolute top-0 left-1/4 w-96 h-96 rounded-full opacity-10 blur-3xl pointer-events-none"
             style="background: radial-gradient(circle, #D4A017, transparent 70%);"></div>
        <div class="absolute bottom-0 right-1/4 w-80 h-80 rounded-full opacity-8 blur-3xl pointer-events-none"
             style="background: radial-gradient(circle, #C9A02A, transparent 70%);"></div>

        <!-- Carte de connexion -->
        <div class="relative z-10 w-full max-w-md mx-4">

            <!-- Logo & titre -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl shadow-gold mb-4"
                     style="background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%);">
                    <span class="text-charcoal-900 font-black text-2xl" style="color: #111;">W</span>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight">WIP Lite</h1>
                <p class="text-charcoal-400 text-sm mt-1">Gestion d'Entreprise &amp; Planification</p>
            </div>

            <!-- Carte formulaire -->
            <div class="rounded-2xl border border-charcoal-700 overflow-hidden shadow-2xl"
                 style="background: rgba(26,26,26,0.85); backdrop-filter: blur(20px);">

                <div class="px-8 pt-8 pb-2">
                    <h2 class="text-lg font-bold text-white mb-1">Connexion</h2>
                    <p class="text-charcoal-400 text-xs mb-6">Entrez vos identifiants pour accéder à votre espace</p>

                    <!-- Status message -->
                    <div v-if="status" class="mb-4 text-xs font-medium text-emerald-400 bg-emerald-900/30 border border-emerald-800 rounded-lg px-3 py-2">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-charcoal-300 mb-1.5 uppercase tracking-wider">
                                Adresse email
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-charcoal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </div>
                                <input
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="admin@example.com"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl text-sm text-white placeholder-charcoal-600 border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:border-gold-500"
                                    style="background: rgba(255,255,255,0.05); border-color: #374151;"
                                />
                            </div>
                            <InputError :message="form.errors.email" class="mt-1.5 text-red-400 text-xs" />
                        </div>

                        <!-- Mot de passe -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-xs font-semibold text-charcoal-300 uppercase tracking-wider">
                                    Mot de passe
                                </label>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-[11px] text-gold-400 hover:text-gold-300 transition-colors"
                                >
                                    Mot de passe oublié ?
                                </Link>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-charcoal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl text-sm text-white placeholder-charcoal-600 border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:border-gold-500"
                                    style="background: rgba(255,255,255,0.05); border-color: #374151;"
                                />
                            </div>
                            <InputError :message="form.errors.password" class="mt-1.5 text-red-400 text-xs" />
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="remember"
                                name="remember"
                                v-model:checked="form.remember"
                                class="rounded border-charcoal-600 bg-charcoal-800 text-gold-500 focus:ring-gold-500"
                            />
                            <label for="remember" class="text-xs text-charcoal-400 cursor-pointer select-none">
                                Se souvenir de moi
                            </label>
                        </div>

                        <!-- Bouton submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3 px-4 rounded-xl font-bold text-sm text-charcoal-900 transition-all duration-200 shadow-gold hover:shadow-lg hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100 flex items-center justify-center gap-2"
                            style="background: linear-gradient(135deg, #D4A017 0%, #8B6914 100%); color: #111;"
                        >
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            {{ form.processing ? 'Connexion...' : 'Se connecter' }}
                        </button>
                    </form>
                </div>

                <!-- Footer -->
                <div class="px-8 py-4 mt-4 border-t border-charcoal-800 text-center">
                    <p class="text-[11px] text-charcoal-600">
                        © {{ new Date().getFullYear() }} WIP Lite · Tous droits réservés
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
