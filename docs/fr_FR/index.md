# Plugin frigate

# <u>Installation</u>
Comme pour tous les autres plugins, après l'avoir installé, il faut l'activer.

# <u>Configuration</u>
- **URL** : l'url de votre serveur frigate (ex: 192.168.1.20)
- **Port** : le port du serveur frigate (5000 par default)
- **Récupération des évènements** : Vous pouvez avoir 30 jours d'évènements sur votre serveur Frigate mais vouloir en importer que 7 sur Jeedom, indiquer ici le nombre de jours souhaité.
- **Suppression des évènement** : Les évènement plus anciens que le nombre de jours indiqué seront supprimés de la database Jeedom (pas du serveur Frigate).
- **Cron** : Le delais entre 2 mises à jours des évènements, de 1 minute à 60 minutes, par default 5 minutes. Les stats sont elles mises à jour toutes les 5 minutes, aucun réglage.

Le nombre de jours de suppression ne peut pas être plus petit que le nombre de jours de récupération. Dans le cas contraire alors ce sera le nombre de jours de récupération qui sera utilisé.

# <u>Utilisation</u>
## Equipement Events
L'équipement est créé de manière automatique à l'installation du plugin.
Celui-ci comporte des commandes infos avec la valeur du dernier event reçu.
J'en ajouterais suivant les demandes et besoin de chacun.


## Equipement Statistiques
L'équipement est créé de manière automatique à l'installation du plugin.
Celui-ci comporte des commandes infos avec quelques statistiques disponibles.
J'en ajouterais suivant les demandes et besoin de chacun.

## Equipement Caméra
Après installation du plugin et la configuration de l'URL et du port de votre serveur Frigate, il vous suffit de cliquer sur le bouton rechercher. Les caméras trouvées seront automatiquement créées.
### Equipement
A gauche les quelques paramètres disponible pour la visualisation présente à droite. Refresh de l'image toute les 2 secondes.
### Commandes
Sont présent ici les informations sur le dernier évènement de la caméra et sur ses statistiques.
### Actions
Vous pouvez ici indiquer les actions à effectuer a chaque nouveau évènement.
Une liste de variable est disponible afin de personnaliser les notifications.
- **#camera#** : texte
- **#score#** : texte numérique
- **#has_clip#** : texte 0 ou 1
- **#has_snapshot#** : texte 0 ou 1
- **#top_score#** : texte numérique
- **#zones#** : tableau
- **#snapshot#** : liens vers fichier image
- **#clip#** : liens vers fichier mp4
- **#label#** : texte
- **#start#** : heure de début
- **#end#** : heure de fin
- **#duree#** : durée de l'évènement

# <u>Page Events</u>
Sont regroupés ici tous les évènements reçus, vous pouvez :
- Visualiser le snapshop (s'il existe)
- Visualiser le clip (s'il existe)
- Supprimer l'évènement

> **ATTENTION : le bouton supprimer supprime l'évènement sur jeedom mais aussi sur votre serveur Frigate, En aucun cas je ne serais responsable de votre mauvaise utilisation de ce bouton.**

# <u>Support</u>
- Community Jeedom
- Discord JeeMate
