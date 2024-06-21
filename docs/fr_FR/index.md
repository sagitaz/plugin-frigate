# Plugin Frigate

# <u>Installation</u>
Comme pour tous les autres plugins, après l'avoir installé, il faut l'activer.
**Le plugin est compatible avec les versions de Frigate supérieure à 0.13.0**

# <u>Configuration</u>
- **URL** : l'url de votre serveur Frigate (ex: 192.168.1.20)
- **Port** : le port du serveur Frigate (5000 par défaut)
- **Récupération des évènements** : Vous pouvez avoir 30 jours d'évènements sur votre serveur Frigate mais vouloir en importer que 7 sur Jeedom, indiquer ici le nombre de jours souhaité.
- **Suppression des évènements** : Les évènement plus anciens que le nombre de jours indiqué seront supprimés de la database Jeedom (pas du serveur Frigate).
- **Cron** : Choisir dans la partie fonctionnalités, le cron souhaité.

Le nombre de jours de suppression ne peut pas être plus petit que le nombre de jours de récupération. Dans le cas contraire alors ce sera le nombre de jours de récupération qui sera utilisé.

# <u>Utilisation</u>
## Equipement Events
L'équipement est créé de manière automatique à l'installation du plugin.
Celui-ci comporte des commandes infos avec la valeur du dernier event reçu.
Il comporte aussi 2 commandes actions : cron start et cron stop, ceci afin de mettre en pause la recherche de nouveau évènement.
J'en ajouterai suivant les demandes et besoin de chacun.


## Equipement Statistiques
L'équipement est créé de manière automatique à l'installation du plugin.
Celui-ci comporte des commandes infos avec quelques statistiques disponibles.
J'en ajouterai suivant les demandes et besoin de chacun.

## Equipement Caméra
Après installation du plugin et la configuration de l'URL et du port de votre serveur Frigate, il vous suffit de cliquer sur le bouton rechercher. Les caméras trouvées seront automatiquement créées.
### Equipement
A gauche, les quelques paramètres disponibles pour la visualisation présente à droite. Refresh de l'image toute les 2 secondes.
### Commandes
Sont présents ici les informations sur le dernier évènement de la caméra et sur ses statistiques.
### Actions
Vous pouvez ici indiquer les actions à effectuer à chaque nouvel évènement.
Une liste de variables est disponible afin de personnaliser les notifications.
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

Dans la case **label**, il vous suffit d'indiquer le label pour lequel vous souhaitez que l'action soit exécutée.
Si ce champ est **vide** ou que vous mettez **all**, alors l'action sera exécutée pour tous les nouveaux évènements.

# <u>Page Events</u>
Sont regroupés ici tous les évènements reçus, vous pouvez :
- Visualiser le snapshot (s'il existe)
- Visualiser le clip (s'il existe)
- Supprimer l'évènement

> **ATTENTION : le bouton supprimer supprime l'évènement sur Jeedom mais aussi sur votre serveur Frigate. En aucun cas je ne serai responsable de votre mauvaise utilisation de ce bouton.**

# <u>Support</u>
- Community Jeedom
- Discord JeeMate
