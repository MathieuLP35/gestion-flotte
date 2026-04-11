import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'sparkotto-purple': '#68308E',
                'sparkotto-purple-hover': '#522573',
                'sparkotto-yellow': '#FCCC2E',
                'sparkotto-yellow-hover': '#e5b729',
                // Sémantique des statuts
                'status-green': '#10B981',
                'status-blue': '#3B82F6',
                'status-orange': '#F59E0B',
                'status-red': '#EF4444',
                'status-gray': '#6B7280',
            },
            boxShadow: {
                'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                'card': '0 2px 10px rgba(0, 0, 0, 0.03)',
            }
        },
    },

    plugins: [forms],
};
