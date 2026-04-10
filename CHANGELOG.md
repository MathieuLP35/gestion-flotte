# Changelog

Toutes les modifications notables de ce projet sont documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Versionnage Sémantique](https://semver.org/lang/fr/).

---

## [0.2.0-alpha] – 2026-04-10

### Ajouté
- **Module de maintenance prédictif** : remplace l'ancien système de seuils simples par un suivi basé sur l'historique réel des interventions (kilométrage + date)
- **Historique d'entretien par véhicule** : nouvelle page `Show.vue` listant toutes les interventions (révision, pneus, freins, CT, etc.) avec coût et notes
- **Formulaire d'intervention** : création et édition enrichies avec type d'intervention, kilométrage, coût, notes
- **Statut predictif** (`ok`, `warning`, `overdue`) calculé dynamiquement à partir de la dernière révision générale
- **Navigation intelligente** : depuis la fiche véhicule (`Edit.vue`), le bouton "Aller au module" transmet `from_vehicle=1` pour que le retour affiche "Retour à la fiche véhicule" au lieu de "Retour au parc"
- **Colonne droite équilibrée** dans la fiche véhicule (`items-stretch`, colonnes égales)
- **Migration BDD** : nouveaux champs `kilometrage`, `last_service_km`, `service_interval_km`, `service_interval_months` sur `vehicles` ; refactorisation de `maintenances`

### Modifié
- **Sidebar admin** (`AdminLayout.vue`) : refonte complète depuis une navbar horizontale vers une sidebar latérale sombre et moderne
- **Commande `check:maintenance`** : utilise désormais le statut prédictif (`maintenance_status`) plutôt que le seuil kilométrique brut
- **Mail d'alerte `MaintenanceAlert`** : affiche le kilométrage actuel, l'intervalle et la distance restante avant révision ; lien vers l'historique plutôt que la fiche véhicule
- **Boutons d'action** : harmonisés sur le style "solide" propre à gestion-flotte (fond plein jaune/rouge/indigo) dans toutes les pages admin du module maintenance
- **Tests PHP** (`MaintenanceControllerTest`, `CheckVehicleMaintenanceTest`, `MaintenanceAlertTest`) : mis à jour pour couvrir la nouvelle architecture
- **Tests Vue** (`AdminLayout.spec.js`, `Maintenances/Index.spec.js`, `Create.spec.js`, `Edit.spec.js`, `Vehicles/Edit.spec.js`) : synchronisés avec les nouveaux composants

### Supprimé
- Ancienne logique de seuil (`km_alert_threshold`, `date_dernier_entretien`) remplacée par le suivi par intervention

---

## [0.1.0-alpha] – 2026-03-24

### Ajouté
- Première pré-release publique
- Système de réservation de véhicules (covoiturage + flotte)
- Administration des véhicules, agences, utilisateurs, rôles et domaines
- Authentification (email + OIDC)
- Rapport RSE / mobilité avec export PDF
- Suggestion de véhicule par IA (énergie, distance)
- Système de maintenance basique (seuils kilométriques)
- Tests unitaires et fonctionnels (Pest + Vitest)
