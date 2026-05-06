import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './node_modules/primevue/**/*.{vue,js,ts,jsx,tsx}'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Palette Charcoal & Gold
                charcoal: {
                    950: '#0A0A0A',
                    900: '#111111',
                    800: '#1A1A1A',
                    700: '#262626',
                    600: '#374151',
                    500: '#4B5563',
                    400: '#6B7280',
                },
                gold: {
                    DEFAULT: '#D4A017',
                    50:  '#FDF8E7',
                    100: '#FAF0C0',
                    200: '#F5E083',
                    300: '#EFD047',
                    400: '#D4A017',
                    500: '#C9A02A',
                    600: '#A8841F',
                    700: '#8B6914',
                    800: '#6B500E',
                    900: '#4A3808',
                },
                pearl: {
                    DEFAULT: '#F4F4F5',
                    50: '#FAFAFA',
                    100: '#F4F4F5',
                    200: '#E4E4E7',
                    300: '#D4D4D8',
                },
            },
            backgroundImage: {
                // Dégradé primaire (boutons, accents)
                'gold-gradient': 'linear-gradient(135deg, #D4A017 0%, #8B6914 100%)',
                // Dégradé secondaire (éléments structurels)
                'slate-gradient': 'linear-gradient(135deg, #6B7280 0%, #374151 100%)',
            },
            boxShadow: {
                'gold': '0 4px 14px 0 rgba(212, 160, 23, 0.25)',
                'charcoal': '0 4px 14px 0 rgba(17, 17, 17, 0.15)',
            },
        },
    },

    plugins: [forms],
};