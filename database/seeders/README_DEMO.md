# Comptes et données de démonstration

Après avoir exécuté `php artisan migrate --seed` (ou `php artisan db:seed` si la base existe déjà), les comptes suivants sont disponibles.

**Mot de passe commun pour tous les comptes : `password`**

## Recevoir les mails sur ta vraie boîte (comptes démo uniquement)

`DEMO_MAIL_RECIPIENT` est utilisé **uniquement par les seeders** pour définir l’adresse des 7 comptes démo. Aucun impact sur l’inscription ni sur les comptes créés normalement : chaque nouvel utilisateur garde l’email qu’il a saisi et reçoit les mails sur cette adresse.

Pour que les mails des **comptes démo** (réinitialisation, vérification, etc.) arrivent chez toi :

1. Dans ton fichier **`.env`**, ajoute une ligne avec **ton adresse email** :
   ```env
   DEMO_MAIL_RECIPIENT=ton-email@gmail.com
   ```
2. **Réensemencer** pour recréer les comptes démo avec ces emails :
   ```bash
   php artisan migrate:fresh --seed
   ```
3. Les comptes démo auront alors des emails du type **`ton-email+admin@gmail.com`**, **`ton-email+thomas@gmail.com`**, etc. Gmail et la plupart des fournisseurs ignorent la partie après le `+`, donc les mails envoyés **à ces comptes démo** arrivent dans ta boîte.

Sans `DEMO_MAIL_RECIPIENT`, les emails des comptes démo restent des adresses fictives (admin@sparkotto.fr, etc.) et tu ne pourras pas recevoir les mails pour ces comptes-là.

---

## Comptes administrateurs

| Compte | Rôle | Email (sans variable) |
|--------|------|----------------------|
| Admin Principal | Super Admin | admin@sparkotto.fr |
| Marie Martin | Administrateur | admin2@sparkotto.fr |

Avec `DEMO_MAIL_RECIPIENT=toi@gmail.com` : **toi+admin@gmail.com**, **toi+admin2@gmail.com**.

## Comptes conducteurs / passagers

| Nom | Agence | Email (avec DEMO_MAIL_RECIPIENT = toi@gmail.com) |
|-----|--------|--------------------------------------------------|
| Thomas Dubois | Paris Centre | toi+thomas@gmail.com |
| Léa Petit | Rennes | toi+lea@gmail.com |
| Julien Moreau | Rennes | toi+julien@gmail.com |
| Camille Bernard | Lyon | toi+camille@gmail.com |
| Lucas Simon | Lyon | toi+lucas@gmail.com |

Ces comptes ont des réservations (trajets) et des covoiturages déjà créés pour illustrer l’application.

## Données créées par les seeders

- **Agences** : Paris Centre, Rennes, Lyon, Marseille, Bordeaux, Toulouse, Nantes, Strasbourg
- **Véhicules** : Plusieurs véhicules par agence (thermiques, hybrides, électriques)
- **Réservations** : Statuts variés (en attente, validé, en cours, à retourner, terminé), avec et sans covoiturage
- **Passagers** : Demandes de covoiturage confirmées sur certains trajets

## Lancer le seed

```bash
# Réinitialiser la base et tout réensemencer
php artisan migrate:fresh --seed

# Ou seulement exécuter les seeders (sans toucher aux migrations)
php artisan db:seed
```
