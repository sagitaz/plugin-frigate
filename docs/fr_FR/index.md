# Plugin frigate

# Installation
Comme pour tous les autres plugins, après l'avoir installé, il faut l'activer.

# Configuration
- **URL** : l'url de votre serveur frigate (ex: 192.168.1.20)
- **Port** : le port du serveur frigate (5000 par default)
- **Récupération des évènements** : Vous pouvez avoir 30 jours d'évènements sur votre serveur Frigate mais vouloir en importer que 7 sur Jeedom, indiquer ici le nombre de jours souhaité.
- **Suppression des évènement** : Les évènement plus anciens que le nombre de jours indiqué seront supprimés de la database Jeedom (pas du serveur Frigate).
- **Cron** : Le delais entre 2 mises à jours des évènements, de 1 minute à 60 minutes, par default 5 minutes. Les stats sont elles mises à jour toutes les 5 minutes, aucun réglage.

Le nombre de jours de suppression ne peut pas être plus petit que le nombre de jours de récupération. Dans le cas contraire alors ce sera le nombre de jours de récupération qui sera utilisé.

# Utilisation
## Equipement Events
L'équipement est créé de manière automatique à l'installation du plugin.
Celui-ci comporte des commandes infos avec la valeur du dernier event reçu.
J'en ajouterais suivant les demandes et besoin de chacun.


## Equipement Statistiques
L'équipement est créé de manière automatique à l'installation du plugin.
Celui-ci comporte des commandes infos avec quelques statistiques disponible.
J'en ajouterais suivant les demandes et besoin de chacun.

## Equipement Caméra
Après installation du plugin et la configuration de l'URL et du port de votre serveur Frigate, il vous suffit de cliquer sur le bouton rechercher. Les caméras trouvées seront automatiquement créées.
### page principale

# Support
- Community Jeedom
- Discord JeeMate
