<p align="center">
  <img src="public/images/logo.png" width="300" alt="SparkOtto Logo">
</p>

<p align="center">
    <a href="https://github.com/votre-nom/sparkotto/actions"><img src="https://img.shields.io/badge/CI%2FCD-Passing-brightgreen?style=flat-square&logo=github-actions" alt="Build Status"></a>
    <a href="#"><img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel" alt="Laravel 12"></a>
    <a href="#"><img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vuedotjs" alt="Vue 3"></a>
    <a href="#"><img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php" alt="PHP 8.4"></a>
    <a href="#"><img src="https://img.shields.io/badge/PostgreSQL-16-336791?style=flat-square&logo=postgresql" alt="PostgreSQL"></a>
    <a href="#"><img src="https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square" alt="License"></a>
</p>

---

## 🚗 À propos de SparkOtto

**SparkOtto** est une application web innovante dédiée à la **gestion de flotte automobile et au covoiturage d'entreprise**. 
Conçue pour simplifier la logistique de mobilité interne, elle permet aux collaborateurs de réserver des véhicules, de proposer des trajets partagés et de communiquer en temps réel, le tout au sein d'une interface fluide et réactive.

### ✨ Fonctionnalités Principales

- 📅 **Réservation simplifiée** : Planification et gestion des emprunts de véhicules de la flotte.
- 🤝 **Covoiturage collaboratif** : Système d'intégration de passagers (demande, acceptation, refus) sur des trajets existants.
- 💬 **Messagerie Temps Réel** : Chat intégré pour chaque trajet via **Laravel Reverb** (WebSockets).
- 🔑 **Gestion des clés** : Suivi précis des remises et retours de clés.
- 🔋 **Suivi technique** : Monitoring du kilométrage, de l'état des véhicules et du type d'énergie (Électrique, Hybride, Thermique).
- 🗺️ **Cartographie** : Visualisation des itinéraires avec calcul des distances de départ et de destination.

---

## 🛠 Stack Technique

SparkOtto repose sur une architecture **Monolithique (Monorepo)** ultra-moderne, couplant la robustesse de Laravel à la réactivité de Vue.js grâce à Inertia.js.

- **Backend** : [Laravel 12](https://laravel.com) (PHP 8.4)
- **Frontend** : [Vue.js 3](https://vuejs.org) (Composition API) & [Tailwind CSS](https://tailwindcss.com)
- **Connecteur** : [Inertia.js](https://inertiajs.com) (Single Page Application sans API REST complexe)
- **WebSockets** : [Laravel Reverb](https://reverb.laravel.com) (Temps réel natif)
- **Base de données** : [PostgreSQL](https://www.postgresql.org) (Haute performance et intégrité relationnelle)

---

## 🚀 Installation & Lancement en Local

Pour faire tourner SparkOtto sur votre machine de développement, suivez ces étapes précisément :

### Étape 1 : Pré-requis
Assurez-vous d'avoir installé sur votre machine :
- **PHP 8.4+** et **Composer**
- **Node.js** (v18+) et **NPM**
- **PostgreSQL** (Service actif en local ou via Docker)

### Étape 2 : Récupération du projet
Clonez le dépôt depuis GitHub et placez-vous dans le dossier :
```bash
git clone [https://github.com/votre-nom/sparkotto.git](https://github.com/votre-nom/sparkotto.git)
cd sparkotto
```

### Étape 3 : Installation des dépendances
Installez les bibliothèques Backend (PHP) et Frontend (JS) :
```bash
composer install
npm install
```

### Étape 4 : Configuration de l'environnement
Copiez le fichier d'exemple et générez la clé de sécurité unique de votre application :
```bash
cp .env.example .env
php artisan key:generate
```

### Étape 5 : Configuration de la Base de Données
Ouvrez votre fichier .env et configurez vos accès PostgreSQL (modifiez les valeurs selon votre configuration locale) :
```bash
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sparkotto
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
```

### Étape 6 : Configuration de la Base de Données
Lancez la création des tables dans la base de données et injectez les données de test (Seeders) :
```bash
php artisan migrate --seed
```

### Étape 7 : Démarrage de l'interface (Frontend / Vite)
Dans un premier terminal, lancez le compilateur à chaud pour Vue.js et Tailwind :
```bash
npm run dev
```

### Étape 8 : Démarrage du serveur web (Backend / Laravel)
Dans un deuxième terminal, lancez le serveur PHP pour rendre l'application accessible :
```bash
php artisan serve
```

### Étape 9 : Démarrage de la messagerie en direct (Reverb)
Dans un troisième terminal, lancez le serveur WebSocket pour activer le temps réel (indispensable pour le chat) :
```bash
php artisan reverb:start
```

### Étape 10 : Démarrage des files d'attente (Queues)
Dans un quatrième terminal, lancez le "Worker" qui traitera l'envoi des messages en arrière-plan sans bloquer l'application :
```bash
php artisan queue:work
```
---

## 🤝 Contribution
Toute contribution est la bienvenue ! N'hésitez pas à ouvrir une Issue pour signaler un bug ou proposer une fonctionnalité, ou à soumettre une Pull Request. Assurez-vous que votre code passe les tests locaux (php artisan test et npm run test) avant toute soumission.

## 📄 Licence
Ce projet est sous licence open-source MIT.

