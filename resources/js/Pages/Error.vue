<template>
  <Head>
    <title>{{ title }}</title>
  </Head>

  <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4 sm:p-6">
    <!-- Logo au dessus -->
    <div class="mb-10">
      <Link href="/">
        <ApplicationLogo class="w-[8rem] h-[8rem] fill-current text-sparkotto-purple" />
      </Link>
    </div>

    <!-- Contenu principal -->
    <div class="bg-white rounded-3xl shadow-card border border-gray-100 p-8 md:p-12 w-full max-w-2xl text-center transform transition-all">
      
      <!-- Icône d'erreur selon le code -->
      <div class="mb-8 flex justify-center">
        <div class="w-24 h-24 rounded-full flex items-center justify-center shadow-inner"
             :class="iconBgClass">
          <svg v-if="status === 404" class="w-12 h-12" :class="iconTextClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
          </svg>
          <svg v-else-if="status === 403" class="w-12 h-12" :class="iconTextClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
          </svg>
          <svg v-else-if="status === 503" class="w-12 h-12" :class="iconTextClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <svg v-else class="w-12 h-12" :class="iconTextClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
        </div>
      </div>

      <h1 class="text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">
        Erreur {{ status }}
      </h1>
      <h2 class="text-2xl font-bold text-gray-800 mb-4">
        {{ $t(titleKey) }}
      </h2>
      <p class="text-base text-gray-500 mb-10 max-w-md mx-auto leading-relaxed">
        {{ $t(descKey) }}
      </p>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
        <button v-if="canGoBack" @click="goBack" class="w-full sm:w-auto inline-flex justify-center px-6 py-3.5 border border-gray-200 shadow-sm text-sm font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sparkotto-purple transition-all duration-200">
          {{ $t('error.go_back') }}
        </button>
        <Link href="/" class="w-full sm:w-auto inline-flex justify-center px-6 py-3.5 border border-transparent shadow-soft text-sm font-bold rounded-xl text-white bg-sparkotto-purple hover:bg-sparkotto-purple-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sparkotto-purple transition-transform duration-200 hover:-translate-y-0.5">
          {{ $t('error.go_home') }}
        </Link>
      </div>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-sm font-medium text-gray-400">
      © {{ new Date().getFullYear() }} SparkOtto. {{ $t('nav.copyright') }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'

const props = defineProps({
  status: {
    type: Number,
    required: true
  }
})

const titles = computed(() => ({
    503: 'error.503_title',
    500: 'error.500_title',
    404: 'error.404_title',
    403: 'error.403_title',
}))

const descriptions = computed(() => ({
    503: 'error.503_desc',
    500: 'error.500_desc',
    404: 'error.404_desc',
    403: 'error.403_desc',
}))

const titleKey = computed(() => titles.value[props.status] || 'error.generic_title')
const descKey = computed(() => descriptions.value[props.status] || 'error.generic_desc')

const iconBgClass = computed(() => {
  return {
    503: 'bg-blue-50',
    500: 'bg-red-50',
    404: 'bg-purple-50',
    403: 'bg-yellow-50',
  }[props.status] || 'bg-gray-50'
})

const iconTextClass = computed(() => {
  return {
    503: 'text-blue-600',
    500: 'text-red-500',
    404: 'text-sparkotto-purple',
    403: 'text-yellow-600',
  }[props.status] || 'text-gray-600'
})

const canGoBack = computed(() => {
  return typeof window !== 'undefined' && window.history.length > 1
})

const goBack = () => {
  if (typeof window !== 'undefined') {
    window.history.back()
  }
}
</script>
