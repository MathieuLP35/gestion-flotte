<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <PortalLayout>
        <Head title="Politique de confidentialité" />

        <div class="bg-white rounded-lg shadow-md py-10 px-6 sm:px-10 max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Politique de confidentialité</h1>
            <p class="text-sm text-gray-500 mb-8">Dernière mise à jour : février 2025</p>

            <div class="prose prose-gray max-w-none space-y-6 text-gray-700">
                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">1. Introduction</h2>
                    <p>
                        La présente politique de confidentialité décrit la manière dont <strong>SparkOtto</strong> (projet de démonstration fictif)
                        traite les données personnelles des utilisateurs de la plateforme. Nous nous engageons à respecter le Règlement général sur la
                        protection des données (RGPD) et la loi « Informatique et Libertés ».
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">2. Responsable du traitement</h2>
                    <p>
                        Le traitement des données est réalisé dans le cadre d'un projet pédagogique. Pour toute question relative à vos données,
                        vous pouvez contacter les responsables du projet via les moyens indiqués sur le site ou dans le cadre de l'enseignement.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">3. Données collectées</h2>
                    <p>Nous sommes susceptibles de collecter et traiter les données suivantes :</p>
                    <ul class="list-disc pl-6 mt-2 space-y-1">
                        <li><strong>Données de compte</strong> : nom, adresse email, mot de passe (stocké de manière sécurisée et hashé).</li>
                        <li><strong>Données d'usage</strong> : réservations, trajets, messages échangés dans le cadre du covoiturage, rattachement à une agence.</li>
                        <li><strong>Données techniques</strong> : adresse IP, type de navigateur, logs d'accès (dans les limites des besoins techniques et de sécurité).</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">4. Finalités et base légale</h2>
                    <p>
                        Les données sont traitées pour : la création et la gestion du compte, la gestion des réservations et du covoiturage,
                        l'envoi d'emails techniques (vérification d'email, réinitialisation de mot de passe), et le bon fonctionnement de l'application.
                    </p>
                    <p>
                        Les bases légales sont l'exécution du contrat (utilisation du service) et, le cas échéant, le respect d'obligations légales.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">5. Durée de conservation</h2>
                    <p>
                        Les données du compte et des réservations sont conservées tant que le compte est actif. En cas de suppression du compte,
                        les données personnelles sont effacées ou anonymisées dans un délai raisonnable, sous réserve des obligations légales
                        (conservation de preuves ou données comptables si applicable).
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">6. Destinataires et transferts</h2>
                    <p>
                        Les données sont accessibles aux personnes habilitées à faire fonctionner l'application (administration, support technique)
                        et, le cas échéant, à l'hébergeur du service. Nous ne vendons pas vos données. En cas d'hébergement ou de sous-traitants
                        hors Union européenne, des garanties appropriées (clauses types, etc.) seront mises en œuvre.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">7. Vos droits</h2>
                    <p>Conformément au RGPD, vous disposez des droits suivants :</p>
                    <ul class="list-disc pl-6 mt-2 space-y-1">
                        <li><strong>Droit d'accès</strong> : obtenir une copie de vos données personnelles.</li>
                        <li><strong>Droit de rectification</strong> : faire corriger des données inexactes ou incomplètes.</li>
                        <li><strong>Droit à l'effacement</strong> : demander la suppression de vos données dans les limites prévues par la loi.</li>
                        <li><strong>Droit à la portabilité</strong> : recevoir vos données dans un format structuré et lisible.</li>
                        <li><strong>Droit d'opposition et de limitation</strong> : vous opposer à certains traitements ou en demander la limitation.</li>
                        <li><strong>Droit de réclamation</strong> : introduire une réclamation auprès de la CNIL (www.cnil.fr).</li>
                    </ul>
                    <p class="mt-2">
                        Pour exercer ces droits, adressez votre demande à l'adresse de contact indiquée sur le site ou dans le cadre du projet.
                        Nous nous engageons à répondre dans un délai d'un mois.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">8. Cookies</h2>
                    <p>
                        L'application utilise des cookies strictement nécessaires au fonctionnement du service (session, sécurité, préférences essentielles).
                        Aucun cookie publicitaire ou de traçage tiers n'est utilisé dans le cadre de ce projet de démonstration.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">9. Sécurité</h2>
                    <p>
                        Nous mettons en œuvre des mesures techniques et organisationnelles appropriées (accès sécurisé, mots de passe hashés,
                        protocole HTTPS, etc.) pour protéger vos données contre l'accès non autorisé, la perte ou l'altération.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">10. Modifications</h2>
                    <p>
                        Cette politique peut être mise à jour pour refléter les évolutions du service ou de la réglementation. La date de dernière
                        mise à jour figure en tête de page. Nous vous invitons à la consulter régulièrement.
                    </p>
                </section>
            </div>
        </div>
    </PortalLayout>
</template>
