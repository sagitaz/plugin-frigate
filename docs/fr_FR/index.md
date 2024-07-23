# Plugin Frigate

# <u>Installation</u>
Comme pour tous les autres plugins, après l'avoir installé, il faut l'activer.
**Le plugin est compatible avec les versions de Frigate supérieure à 0.13.0**

# <u>Configuration</u>
- **URL** : l'url de votre serveur Frigate (ex: 192.168.1.20)
- **Port** : le port du serveur Frigate (5000 par défaut)
- **MQTT Topic** : le topic de votre serveur Frigate (frigate par défaut)
- **Récupération des évènements** : Vous pouvez avoir 30 jours d'évènements sur votre serveur Frigate mais vouloir en importer que 7 sur Jeedom, indiquer ici le nombre de jours souhaité.
- **Suppression des évènements** : Les évènement plus anciens que le nombre de jours indiqué seront supprimés de la database Jeedom (pas du serveur Frigate).
- **Taille des dossiers** : Taille maximum du dossier datas.
- **Durée de rafraîchissement** : En seconde, duréée de rafraîchissement des snapshots de vos caméras. (5sc par défault)
- **Cron** : Choisir dans la partie fonctionnalités, le cron souhaité.

Le nombre de jours de suppression ne peut pas être plus petit que le nombre de jours de récupération. Dans le cas contraire alors ce sera le nombre de jours de récupération qui sera utilisé.

# <u>Demon</u>
Le demon démarre automatiquement après avoir sauvegarder la partie configuration et y avoir configuré le topic Frigate.
Pour pouvoir utiliser MQTT, il faut que vous ayez le plugin mqtt-manager (mqtt2) installé et correctement configuré.
Si vous utilisez MQTT, vous pouvez mettre le cron à Hourly, voir le désactiver.

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
#### Conditions
Indiquer ici dans quel cas les actions NE DOIVENT PAS être exécutées.
#### Actions
Vous pouvez ici indiquer les actions à effectuer à chaque nouvel évènement.
Une liste de variables est disponible afin de personnaliser les notifications.
- **#time#** : l'heure actuelle au format 12:00
- **#camera#** : le nom de la caméra
- **#score#** : le score en pourcentage -> 82 %
- **#has_clip#** : texte 0 ou 1
- **#has_snapshot#** : texte 0 ou 1
- **#top_score#** : le score maximum en pourcentage -> 92 %
- **#zones#** : tableau
- **#snapshot#** : liens vers fichier image
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#snapshot_path#** : path vers fichier image
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#clip#** : liens vers fichier mp4
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#clip_path#** : path vers fichier mp4
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#thumbnail#** : liens vers fichier image
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#thumbnail_path#** : path vers fichier image
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#label#** : texte
- **#start#** : heure de début
- **#end#** : heure de fin
- **#duree#** : durée de l'évènement

Dans la case **label**, il vous suffit d'indiquer le label pour lequel vous souhaitez que l'action soit exécutée.
Si ce champ est **vide** ou que vous mettez **all**, alors l'action sera exécutée pour tous les nouveaux évènements.


Dans la case **type**, il vous suffit d'indiquer le type pour lequel vous souhaitez que l'action soit exécutée.
En configuration sans MQTT, ce n'est pas utile, tous les évènementssont de type **end**.
En MQTT, ils peuvent être de type **start**, **update** et **end**.

### Exemple de notification :
#### Plugin JeeMate
- **snapshot** : dans le champ titre : **``title=votre titre;;bigPicture=#snapshot#``**
- **clip** : dans le champ titre : **``title=votre titre;;bigPicture=#clip#``**

#### Plugin Telegram
- **snapshot** : dans le champ titre : **``title=votre titre | snapshot=#snapshot#``**
ou
- **snapshot** : dans le champ titre : **``title=votre titre | file=#snapshot_path#``**
- **clip** : dans le champ titre : **``title=votre titre | file=#clip_path#``**

#### Plugin Mobile v2
- **snapshot** : dans le champ message : **``votre message | file=#snapshot_path#``**
- **clip** : aucune idée

#### Plugin JeedomConnect
- **snapshot** : dans le champ titre : **``title=votre titre | files=#snapshot_path#``**
- **clip** : dans le champ titre : **``title=votre titre | files=#clip_path#``**

# <u>Page Events</u>

De nombreux filtre sont disponible pour l'affichage de votre liste d'évènements.

Dans celle-ci seront regroupés tous les évènements visibles, vous pouvez pour chacun d'entre eux :
- Visualiser le snapshot (s'il existe)
- Visualiser le clip (s'il existe)
- Supprimer l'évènement
- Mettre l'évènement en favori
- lien vers la caméra

Tous les évènement favoris ne sont pas supprimés.

> **ATTENTION** : Le bouton "**supprimer tous les évènements visibles**" fera exactement ce qu'il annonce, donc appliquer bien les bons filtres avant de supprimer, aucun retour en arrière ne sera possible, une popup de confirmation est présente. la suppression est effectuée en database Jeedom mais aussi sur votre serveur frigate.

> **ATTENTION** : le bouton "**supprimer**" supprime l'évènement en database Jeedom mais aussi sur votre serveur Frigate. En aucun cas je ne serai responsable de votre mauvaise utilisation de ce bouton. Néanmoins, une popup de confirmation est ici aussi présente.

# <u>Widget</u>
Le widget est en cours de création.
pour le moment vous y trouverez la visualisation de la caméra et les boutons cochés visible.

# <u>Panel</u>
En cours de création.

# <u>Page santé</u>
En cours de création.

# <u>Support</u>
- Community Jeedom
- Discord JeeMate
