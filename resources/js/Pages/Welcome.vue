<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <PortalLayout>
        <Head :title="$t('welcome.title')" />

        <!-- Language Switcher Flottant -->
        <div class="absolute top-4 right-4 z-50 flex gap-2">
            <Link 
                v-if="$page.props.locale === 'en'"
                :href="route('language.update')" method="post" as="button" :data="{ language: 'fr' }"
                class="w-10 h-10 rounded-xl bg-white shadow-soft border border-gray-100 hover:bg-gray-50 flex items-center justify-center text-sm font-bold text-gray-700 transition"
            >FR</Link>
            <Link 
                v-else
                :href="route('language.update')" method="post" as="button" :data="{ language: 'en' }"
                class="w-10 h-10 rounded-xl bg-white shadow-soft border border-gray-100 hover:bg-gray-50 flex items-center justify-center text-sm font-bold text-gray-700 transition"
            >EN</Link>
        </div>

        <!-- Hero Section -->
        <div class="text-center py-20 bg-sparkotto-purple rounded-3xl shadow-card relative overflow-hidden mb-12">
            <!-- Decorative Background SVG -->
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <svg width="100%" height="100%" fill="none" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0,100 C30,80 70,120 100,50 L100,0 L0,0 Z" fill="currentColor"/>
                </svg>
            </div>
            
            <div class="relative z-10 px-6">
                <h1 class="text-5xl font-extrabold text-white mb-6 tracking-tight">
                    {{ $t('welcome.hero_heading') }} <span class="text-sparkotto-yellow">SparkOtto</span>
                </h1>
                <p class="text-xl text-purple-100 max-w-2xl mx-auto mb-10">
                    {{ $t('welcome.hero_desc') }}
                </p>
                
                <div class="flex justify-center gap-4">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="px-8 py-4 bg-sparkotto-yellow hover:bg-yellow-500 text-gray-900 font-bold rounded-xl shadow-soft transition-transform transform hover:-translate-y-1">
                        {{ $t('welcome.cta_dashboard') }}
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="px-8 py-4 bg-white text-sparkotto-purple hover:bg-gray-50 font-bold rounded-xl shadow-soft transition-transform transform hover:-translate-y-1">
                            {{ $t('welcome.cta_login') }}
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="px-8 py-4 bg-transparent border-2 border-purple-300 text-white hover:border-white font-bold rounded-xl transition-colors">
                            {{ $t('welcome.cta_register') }}
                        </Link>
                    </template>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
            <div class="flex flex-col text-center p-8 bg-white rounded-2xl shadow-card border border-gray-100 hover:shadow-soft transition-shadow duration-300 group">
                <div class="w-16 h-16 mx-auto bg-purple-50 group-hover:bg-sparkotto-purple text-sparkotto-purple group-hover:text-white rounded-2xl flex items-center justify-center transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-bold text-gray-900">{{ $t('welcome.feature_carpool_title') }}</h3>
                <p class="mt-3 text-gray-600 leading-relaxed">{{ $t('welcome.feature_carpool_desc') }}</p>
            </div>
            
            <div class="flex flex-col text-center p-8 bg-white rounded-2xl shadow-card border border-gray-100 hover:shadow-soft transition-shadow duration-300 group">
                <div class="w-16 h-16 mx-auto bg-green-50 group-hover:bg-green-600 text-green-600 group-hover:text-white rounded-2xl flex items-center justify-center transition-colors shadow-sm">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="currentColor" class="w-8 h-8">
                        <path d="M28,13h1c0.6,0,1-0.4,1-1s-0.4-1-1-1h-2.8L25,8c-0.8-1.8-2.6-3-4.6-3h-8.7C9.6,5,7.8,6.2,7,8l-1.3,3H3c-0.6,0-1,0.4-1,1 s0.4,1,1,1h1c-1.2,0.9-2,2.4-2,4v4c0,0.9,0.4,1.7,1,2.2V25c0,1.7,1.3,3,3,3h2c1.7,0,3-1.3,3-3v-1h10v1c0,1.7,1.3,3,3,3h2 c1.7,0,3-1.3,3-3v-1.8c0.6-0.5,1-1.3,1-2.2v-4C30,15.4,29.2,13.9,28,13z M27,18c0,0.6-0.4,1-1,1h-3c-0.6,0-1-0.4-1-1s0.4-1,1-1h3 C26.6,17,27,17.4,27,18z M6,17h3c0.6,0,1,0.4,1,1s-0.4,1-1,1H6c-0.6,0-1-0.4-1-1S5.4,17,6,17z M10.4,22l1.2-2.3 c0.5-1,1.5-1.7,2.7-1.7h3.5c1.1,0,2.2,0.6,2.7,1.7l1.2,2.3H10.4z M8.9,8.8C9.4,7.7,10.4,7,11.6,7h8.7c1.2,0,2.3,0.7,2.8,1.8l1.4,3.2 h-17L8.9,8.8z"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-bold text-gray-900">{{ $t('welcome.feature_vehicles_title') }}</h3>
                <p class="mt-3 text-gray-600 leading-relaxed">{{ $t('welcome.feature_vehicles_desc') }}</p>
            </div>
            
            <div class="flex flex-col text-center p-8 bg-white rounded-2xl shadow-card border border-gray-100 hover:shadow-soft transition-shadow duration-300 group">
                <div class="w-16 h-16 mx-auto bg-yellow-50 group-hover:bg-sparkotto-yellow text-yellow-600 group-hover:text-gray-900 rounded-2xl flex items-center justify-center transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-bold text-gray-900">{{ $t('welcome.feature_booking_title') }}</h3>
                <p class="mt-3 text-gray-600 leading-relaxed">{{ $t('welcome.feature_booking_desc') }}</p>
            </div>
        </div>

        <!-- Why SparkOtto Section -->
        <div class="mt-16 bg-white py-16 px-8 rounded-3xl shadow-card border border-gray-100">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-12 text-center">{{ $t('welcome.why_title') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-5 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-sparkotto-purple">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $t('welcome.security_title') }}</h3>
                            <p class="mt-2 text-gray-600">{{ $t('welcome.security_desc') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-5 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-green-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $t('welcome.adaptability_title') }}</h3>
                            <p class="mt-2 text-gray-600">{{ $t('welcome.adaptability_desc') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-5 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-blue-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Support et évolution</h3>
                            <p class="mt-2 text-gray-600">Mises à jour régulières de Laravel et VueJS, support technique dédié pour l'entité et une documentation complète.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-5 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-yellow-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Efficacité</h3>
                            <p class="mt-2 text-gray-600">Gagnez du temps : formulaires ergonomiques, chat intégré pour les passagers — tout est fait pour vous simplifier la vie.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>