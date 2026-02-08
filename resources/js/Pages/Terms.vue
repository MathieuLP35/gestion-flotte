<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <PortalLayout>
        <Head title="Conditions d'utilisation" />

        <div class="bg-white rounded-lg shadow-md py-10 px-6 sm:px-10 max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Conditions d'utilisation</h1>
            <p class="text-sm text-gray-500 mb-8">Dernière mise à jour : février 2025</p>

            <div class="prose prose-gray max-w-none space-y-6 text-gray-700">
                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">1. Objet et acceptation</h2>
                    <p>
                        Les présentes conditions d'utilisation (« CGU ») régissent l'accès et l'utilisation du service <strong>SparkOtto</strong>,
                        plateforme de gestion de flotte, de réservations de véhicules et de covoiturage. Ce projet est une application de démonstration
                        à vocation pédagogique ; l'entité « SparkOtto » est fictive.
                    </p>
                    <p>
                        En accédant au site et en créant un compte, vous acceptez sans réserve les présentes CGU. Si vous n'acceptez pas ces conditions,
                        veuillez ne pas utiliser le service.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">2. Description du service</h2>
                    <p>
                        SparkOtto permet notamment de : réserver des véhicules, organiser des trajets en covoiturage, gérer des agences et un parc
                        automobile, et suivre les réservations. Les fonctionnalités sont fournies « en l'état » dans le cadre d'un projet de démonstration.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">3. Compte utilisateur</h2>
                    <p>
                        L'inscription peut être restreinte (par exemple par domaine email). Vous vous engagez à fournir des informations exactes et à
                        maintenir la confidentialité de votre mot de passe. Vous êtes responsable des actions effectuées depuis votre compte.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">4. Usage acceptable</h2>
                    <p>
                        Vous vous engagez à utiliser le service de manière conforme aux lois en vigueur et à ne pas en faire un usage frauduleux,
                        abusif ou préjudiciable à autrui. Toute utilisation détournée peut entraîner la suspension ou la suppression du compte.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">5. Propriété intellectuelle</h2>
                    <p>
                        L'application, son code, son design et les contenus éditoriaux restent la propriété de leurs auteurs. Aucune licence d'exploitation
                        commerciale n'est accordée au-delà de l'usage du service dans le cadre prévu.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">6. Limitation de responsabilité</h2>
                    <p>
                        SparkOtto étant un projet de démonstration, le service est fourni sans garantie de disponibilité, d'exactitude ou d'adaptation
                        à un usage professionnel. En aucun cas les responsables du projet ne pourront être tenus responsables des dommages directs ou
                        indirects résultant de l'utilisation du service.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">7. Modifications</h2>
                    <p>
                        Les présentes CGU peuvent être modifiées à tout moment. La date de dernière mise à jour sera indiquée en tête de page.
                        La poursuite de l'utilisation du service après modification vaut acceptation des nouvelles conditions.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">8. Contact</h2>
                    <p>
                        Pour toute question relative aux conditions d'utilisation, vous pouvez vous rapprocher des responsables du projet via les
                        canaux indiqués sur le site ou dans le cadre pédagogique du projet.
                    </p>
                </section>
            </div>
        </div>
    </PortalLayout>
</template>
