<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4 sm:p-6">
        <!-- Language switcher -->
        <div class="absolute top-4 right-4 flex items-center gap-1 bg-white border border-gray-200 rounded-lg p-1 shadow-sm">
            <Link
                :href="route('language.update')" method="post" as="button" :data="{ language: 'fr' }"
                class="px-2.5 py-1 rounded-md text-xs font-bold transition"
                :class="$page.props.locale === 'fr' ? 'bg-sparkotto-purple text-white' : 'text-gray-500 hover:text-gray-700'"
            >FR</Link>
            <Link
                :href="route('language.update')" method="post" as="button" :data="{ language: 'en' }"
                class="px-2.5 py-1 rounded-md text-xs font-bold transition"
                :class="$page.props.locale === 'en' ? 'bg-sparkotto-purple text-white' : 'text-gray-500 hover:text-gray-700'"
            >EN</Link>
        </div>

        <!-- Logo -->
        <div class="mb-8">
            <Link href="/">
                <ApplicationLogo class="w-[8rem] h-[8rem] fill-current text-gray-600" />
            </Link>
        </div>

        <!-- Contenu principal -->
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
            <slot />
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-500">
            © {{ new Date().getFullYear() }} SparkOtto. {{ $t('nav.copyright') }}
        </div>
    </div>
</template>