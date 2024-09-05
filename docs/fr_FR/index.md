# Plugin Frigate

# <u>Installation</u>
Comme pour tous les autres plugins, après l'avoir installé, il faut l'activer.
**Le plugin est compatible avec les versions de Frigate supérieures à 0.13.0**

La version 0.14 du serveur Frigate apporte son lot de nouveautés et de breaking changes, le plugin sera toujours compatible avec la dernière version stable connue (le temps de s'adapter). Par contre, on ne fera pas plusieurs développements pour rester opérationnel avec les anciennes versions. Donc si quelque chose ne fonctionne pas, commencez par mettre à jour votre serveur Frigate avant de demander de l'aide.

# <u>Configuration</u>
- **URL** : l'url de votre serveur Frigate (ex: 192.168.1.20)
- **Port** : le port du serveur Frigate (5000 par défaut)
- **Topic MQTT** : le topic de votre serveur Frigate (frigate par défaut)
- **Récupération des évènements** : Vous pouvez avoir 30 jours d'évènements sur votre serveur Frigate mais vouloir en importer que 7 sur Jeedom, indiquez ici le nombre de jours souhaités.
- **Suppression des évènements** : Les évènements plus anciens que le nombre de jours indiqués seront supprimés de la database Jeedom (pas du serveur Frigate).
- **Taille des dossiers** : Taille maximum du dossier data.
- **Durée de rafraîchissement** : En secondes, durée de rafraîchissement des snapshots de vos caméras. (5 secondes par défaut)
- **Cron** : Choisir dans la partie fonctionnalités, le cron souhaité.

Le nombre de jours de suppression ne peut pas être plus petit que le nombre de jours de récupération. Dans le cas contraire, ce sera alors le nombre de jours de récupération qui sera utilisé.

# <u>Demon</u>
Le démon démarre automatiquement après avoir sauvegardé la partie configuration et y avoir configuré le topic Frigate.
Pour pouvoir utiliser MQTT, il faut que vous ayez correctement configuré votre serveur Frigate et que vous ayez le plugin mqtt-manager (mqtt2) installé et correctement configuré.
Si vous utilisez MQTT, vous pouvez mettre le cron à Hourly ou Daily.

**Deamon NOK :**
Si vous n'avez pas mqtt-manager, il est normal que le démon reste sur NOK. Aucun problème, le plugin fonctionne quand même, cependant certaines fonctions seront indisponibles ou limitées.

# <u>Utilisation</u>
## Equipement Events
L'équipement est créé de manière automatique à l'installation du plugin.
Celui-ci comporte des commandes infos avec la valeur du dernier évènement reçu.
Il comporte aussi 2 commandes actions : cron start et cron stop, ceci afin de mettre en pause la recherche de nouveaux évènements.
J'en ajouterai suivant les demandes et besoins de chacun.


## Equipement Statistiques
L'équipement est créé de manière automatique à l'installation du plugin.
Celui-ci comporte des commandes infos avec quelques statistiques disponibles.
Il comporte aussi la commande action permettant de redémarrer le serveur Frigate.
J'en ajouterai suivant les demandes et besoins de chacun.

## Equipement Caméra
Après installation du plugin et la configuration de l'URL et du port de votre serveur Frigate, il vous suffit de cliquer sur le bouton rechercher. Les caméras trouvées seront automatiquement créées.
### Equipement
A gauche, les quelques paramètres disponibles pour la visualisation présente à droite. Refresh de l'image toutes les 2 secondes.
### Commandes
#### infos
##### toutes les cameras
Les informations sur le dernier évènement de la caméra et sur ses statistiques.
##### MQTT
L'information sur détection en cours
#### actions
Pour avoir les commandes actions, il est obligatoire d'utiliser MQTT. Sans cela, les commandes ne seront pas créées.
Je vous invite à lire la documentation de Frigate pour la configuration de votre serveur MQTT.
- **Detect** : état, on, off, toggle
- **Snapshot** : état, on, off, toggle
- **Recording** : état, on, off, toggle
- **Motion** : état, on, off, toggle (le OFF n'est possible que si detect est sur OFF aussi)
- **Camera** : Etat, activer, désactiver, toggle (Un redemarrage du serveur est necessaire pour la prise en compte car le fichier configuration est modifié).
Depuis la page des commandes, un bouton vous permet d'ajouter les commandes PTZ. Il faut bien entendu que votre configuration Frigate soit faite pour pouvoir les utiliser.
N'ayant pas de caméra PTZ, merci de me fournir un fichier de configuration afin que je mette la création de ces boutons en automatique.
- **PTZ** : left, right, up, down, stop, zoom in, zoom out
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
- **#snapshot#** : lien vers fichier image
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#snapshot_path#** : path vers fichier image
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#clip#** : lien vers fichier mp4
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#clip_path#** : path vers fichier mp4
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#thumbnail#** : lien vers fichier image
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

En configuration sans MQTT, ce n'est pas utile, tous les évènements sont de type **end**.
En MQTT, ils peuvent être de type **new**, **update** et **end**.


### Exemple de notifications :
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

#### Plugin NTFY
- **snapshot** : dans le champ options : **``Title:votre titre;Attach:#snapshot#``**
- **clip** : dans le champ options : **``Title:votre titre;Attach:#clip#``**

# <u>Page Events</u>

De nombreux filtres sont disponibles pour l'affichage de votre liste d'évènements.

Dans celle-ci seront regroupés tous les évènements visibles, vous pouvez pour chacun d'entre eux :
- Visualiser le snapshot (s'il existe)
- Visualiser le clip (s'il existe)
- Supprimer l'évènement
- Mettre l'évènement en favori
- lien vers la caméra

Tous les évènements favoris ne sont pas supprimés.

> **ATTENTION** : Le bouton "**supprimer tous les évènements visibles**" fera exactement ce qu'il annonce, donc appliquez bien les bons filtres avant de supprimer : aucun retour en arrière ne sera possible, une popup de confirmation est présente. La suppression est effectuée en database Jeedom mais aussi sur votre serveur Frigate.

> **ATTENTION** : le bouton "**supprimer**" supprime l'évènement en database Jeedom mais aussi sur votre serveur Frigate. En aucun cas, je ne serai responsable de votre mauvaise utilisation de ce bouton. Néanmoins, une popup de confirmation est ici aussi présente.

### Création d'un évènement manuel
Dans la configuration générale du plugin Frigate, vous pouvez indiquer les valeurs par défaut des évènements créés manuellement.
Sur la page **Events**, vous trouverez un bouton permettant de créer un nouvel évènement.
Pour chaque caméra, une commande action vous permettra aussi de créer un évènement.
Cette commande est de type message, si vous laissez vide alors les paramètres par défaut seront utilisés (depuis le widget ce sera toujours le cas).
title : ``Indiquer le label``
message : ``score=80 | video=1 | duration=20``

Pour la durée des clips, il faut penser aussi au fait que Frigate ajoute du temps avant et après la vidéo, 5 sec. par defaut, donc en paramétrant à 20 sec. vous obtiendrez une vidéo de 30 sec.

Attention sur les évènements créés manuellement, si dans votre configuration Frigate pour ``record -> retain -> mode`` vous avez **motion** alors les clips ne seront disponibles que s'il y a du mouvement de detecté, mettre à **all** si vous voulez tout avoir.

Pour ceux en 0.14 et MQTT, les évènements sont remontés automatiquement lors de la création.

Pour ceux n'utilisant pas MQTT le snapshot est remonté rapidement, le clip s'il y en a un qu'au cron suivant.

# <u>Configuration Frigate</u>
> **ATTENTION** : La modification de la configuration du serveur Frigate est à vos risques et périls ! Aucun support ne sera donné !

# <u>Logs Frigate</u>
Visualiser tous les logs de votre serveur Frigate

# <u>Cron</u>
**Si vous n'utilisez pas MQTT** : un cron régulier vous permet de récupérer les derniers events et donc d'exécuter les actions associées.

**Si vous utilisez MQTT** : tous les nouveaux events sont reçus automatiquement, un cron horaire est suffisant, il permet de mettre à jour les infos de l'évènement.

Dans tous les cas, laisser au moins un cron actif car il sera vérifié à chaque fois si les fichiers sauvegardés correspondent bien à un évènement et dans le cas contraire, ils seront supprimés.

# <u>Widget</u>
Le widget est en cours de création.
Pour le moment, vous y trouverez la visualisation de la caméra et les boutons cochés visibles.

# <u>Panel</u>
En cours de création.

# <u>Page santé</u>
En cours de création.

# <u>Support</u>
- Community Jeedom
- Discord JeeMate
