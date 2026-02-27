<template>

  <Head :title="title" />

  <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4 sm:p-6">
    <!-- Logo au dessus -->
    <div class="mb-8">
      <Link href="/">
        <ApplicationLogo class="w-[8rem] h-[8rem] fill-current text-gray-600" />
      </Link>
    </div>

    <!-- Contenu principal -->
    <div class="bg-white rounded-xl shadow-lg p-8 w-2xl max-w-2xl text-center">
      
      <!-- Icône d'erreur selon le code -->
      <div class="mb-6 flex justify-center">
        <div class="w-20 h-20 rounded-full flex items-center justify-center bg-gray-100"
             :class="iconBgClass">
          <svg v-if="status === 404" class="w-16 h-10" :class="iconTextClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
          </svg>
          <svg v-else-if="status === 403" class="w-16 h-10" :class="iconTextClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
          </svg>
          <svg v-else-if="status === 503" class="w-16 h-10" :class="iconTextClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <svg v-else class="w-16 h-10" :class="iconTextClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
        </div>
      </div>

      <h1 class="text-4xl font-extrabold text-gray-900 mb-2">
        {{ status }}
      </h1>
      <h2 class="text-lg font-bold text-gray-800 mb-2">
        {{ title }}
      </h2>
      <p class="text-sm text-gray-600 mb-8 max-w-sm mx-auto">
        {{ description }}
      </p>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <button v-if="canGoBack" @click="goBack" class="w-full sm:w-auto inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
          Retour en arrière
        </button>
        <Link href="/" class="w-full sm:w-auto inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
          Aller à l'accueil
        </Link>
      </div>
    </div>

    <!-- Footer -->
    <div class="mt-6 text-center text-sm text-gray-500">
      © {{ new Date().getFullYear() }} SparkOtto. Tous droits réservés.
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

const title = computed(() => {
  return {
    503: 'Service indisponible',
    500: 'Erreur Serveur',
    404: 'Page Non Trouvée',
    403: 'Accès Refusé',
  }[props.status] || 'Une erreur est survenue'
})

const description = computed(() => {
  return {
    503: 'Désolé, nous sommes actuellement en maintenance. Veuillez réessayer plus tard.',
    500: 'Oups ! Quelque chose a mal tourné sur nos serveurs. L\'équipe a été alertée.',
    404: 'Désolé, la page que vous recherchez semble introuvable ou a été déplacée.',
    403: 'Désolé, vous n\'avez pas les permissions nécessaires pour accéder à cette page.',
  }[props.status] || 'Nous ne parvenons pas à compléter votre requête pour le moment.'
})

const iconBgClass = computed(() => {
  return {
    503: 'bg-blue-100',
    500: 'bg-red-100',
    404: 'bg-indigo-100',
    403: 'bg-orange-100',
  }[props.status] || 'bg-gray-100'
})

const iconTextClass = computed(() => {
  return {
    503: 'text-blue-600',
    500: 'text-red-600',
    404: 'text-indigo-600',
    403: 'text-orange-600',
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
