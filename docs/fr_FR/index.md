Plugin créé par **Sagitaz** et **Noodom**

# <u>Remerciemment</u>
Le plugin et le support sont gratuits, vous souhaitez néanmoins m'offrir un café ou des couches pour bébé, je vous remercie par avance.

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/C1C61AKVV7)

# <u>Aide et Support</u>
- Community Jeedom
- Discord JeeMate

Pour toute demande d'aide sur Community ou Discord, merci de fournir le maximum d'informations possibles.

Sur la page configuration, le bouton assistance permet déjà d'en remplir automatiquement certaines.

Donner aussi des logs en mode debug.
Aucun support ne sera apporté sur d'autres moyens de communication que ceux-ci.
Merci


# <u>Pré-requis</u>
- Jeedom 4.4.0 minimum
- Debian 11 (Bullseye) minimum
- Frigate 0.13.0 minimum

Le plugin n'installe pas et ne configure pas le serveur Frigate, il vous faut donc l'installer et le configurer vous-même. Voir la documentation officielle de Frigate pour plus d'informations.

# <u>Installation</u>
Comme pour tous les autres plugins, après l'avoir installé, il faut l'activer.

Le plugin sera toujours compatible avec la dernière version stable connue (le temps de s'adapter). Par contre, on ne fera pas plusieurs développements pour rester opérationnel avec les anciennes versions. Donc si quelque chose ne fonctionne pas, commencez par mettre à jour votre serveur Frigate avant de demander de l'aide.

Au 08-06-2025 le plugin fonctionne avec les versions suivantes de Frigate :
- Frigate 0.15.1 Stable
- Frigate 0.16.2 Beta

# <u>Log</u>
Le plugin comporte des sous-logs, pour qu'ils soient visibles sur jeedom 4.4.19, il est nécessaire de passer les logs globaux en niveau info minimum.

![niveau de logs](../images/frigate_Doc_Logs.png)
# <u>Configuration</u>
- **Pièce par défaut** : Les caméras créées seront automatiquement placées dans cette pièce.
- **Exclure du backup** : Si coché, les données de la caméra ne seront pas sauvegardées dans le fichier de configuration de Jeedom.
- **Version du plugin** : Si vous avez des problèmes avec la mise à jour, vous pouvez indiquer la version du plugin.

#### Paramétrage Frigate
- **URL** : l'url de votre serveur Frigate (ex: 192.168.1.20)
- **Port** : le port du serveur Frigate (5000 par défaut)
- **Adresse externe** : Pour accéder à la page du serveur Frigate depuis l'extérieur.
- **Topic MQTT** : le topic de votre serveur Frigate (frigate par défaut)
- **Preset** : Pour les caméras avec PTZ, définir le nombre de positions que vous souhaitez récupérer.
- **Pause action** : Pause à effectuer sur les actions PTZ. Par exemple, après avoir appuyé sur move up, un stop est automatiquement effectué : vous pouvez définir le temps avant cette action stop de 0 à 10, correspondant à une pause de 0 à 1 seconde (0, 0.1, 0.2, etc...).

#### Gestion des évènements
- **Récupération des évènements** : Vous pouvez avoir 30 jours d'évènements sur votre serveur Frigate mais vouloir en importer que 7 sur Jeedom. Indiquez ici le nombre de jours souhaités. Si le nombre de jours est 0, alors le processus est arrêté et aucun appel à l'API Frigate n'est effectué.
- **Suppression des évènements** : Les évènements plus anciens que le nombre de jours indiqués seront supprimés de la database Jeedom (pas du serveur Frigate).

Le nombre de jours de suppression ne peut pas être plus petit que le nombre de jours de récupération. Dans le cas contraire, ce sera alors le nombre de jours de récupération qui sera utilisé.

- **Taille des dossiers** : Taille maximum du dossier data.
- **Durée de rafraîchissement** : En secondes, durée de rafraîchissement des snapshots de vos caméras. (5 secondes par défaut)
- **Vidéos en vignette** : Au passage de la souris sur une vignette de la page évènement, la vidéo sera jouée.
- **Confirmation avant suppression** : Affiche une alerte avant la suppression d'un évènement.
- **Pause création fichiers (en secondes)** : Délai d'attente avant de créer le fichier (clip / snapshot) (5 s par défaut). Suivant les serveurs, cela peut être nécessaire pour laisser le temps à Frigate de créer le fichier.
#### Paramétrage par défaut d'un évènement créé manuellement
- **Label** : le nom de l'évènement créé (manuel par défaut).
- **Enregistrer une vidéo** : oui par défaut.
- **Durée de la vidéo** : 40 secondes par défaut.
- **Score** : 0 par défaut.

#### Fonctionnalités
- **Cron** : sélectionner le cron voulu.


# <u>Demon</u>
Le démon démarre automatiquement après avoir sauvegardé la partie configuration et y avoir configuré le topic Frigate.
Pour pouvoir utiliser MQTT, il faut que vous ayez correctement configuré votre serveur Frigate et que vous ayez le plugin mqtt-manager (mqtt2) installé et correctement configuré.
Si vous utilisez MQTT, vous pouvez mettre le cron à Hourly ou Daily.

**Deamon NOK :**
Si vous n'avez pas mqtt-manager, il est normal que le démon reste sur NOK. Aucun problème, le plugin fonctionne quand même, cependant certaines fonctions seront indisponibles ou limitées.

# <u>Utilisation</u>

**Les commandes infos de tous les équipements sont créées automatiquement à la prochaine réception d'événements ou de statistiques. Si vous ne les voyez pas à la première installation du plugin, c'est que vos événements récents ont plus de 3 heures, il faut donc attendre le prochain événement pour voir les commandes.**

**Les commandes actions sont créées seulement quand vous utiliser le bouton "Rechercher / MAJ".**

## <u>Equipement Events</u>
L'équipement est créé de manière automatique en même temps que les caméras.
Celui-ci comporte des commandes infos avec la valeur du dernier évènement reçu.
Il comporte aussi 2 commandes actions : cron start et cron stop, ceci afin de mettre en pause la recherche de nouveaux évènements.

Il est possible de créer des actions communes à toutes les caméras (voir la section dédiée)
Cocher "autoriser les actions" si vous souhaitez sur une détection exécuter les actions présentes dans l'équipement events et dans les équipements caméras.


## <u>Equipement Statistiques</u>
L'équipement est créé de manière automatique en même temps que les caméras.
Celui-ci comporte des commandes infos avec quelques statistiques disponibles.

Il comporte aussi la commande action permettant de redémarrer le serveur Frigate.

## <u>Equipement Caméra</u>
Après installation du plugin et la configuration de l'URL et du port de votre serveur Frigate, il vous suffit de cliquer sur le bouton Rechercher. Les caméras trouvées seront automatiquement créées. Il est nécessaire de patienter car à la première recherche sont également importés les évènements de la dernière journée. Cela peut prendre un peu de temps.

### Equipement

- user : seulement utile si vous créez des commandes HTTP
- mot de passe : seulement utile si vous créez des commandes HTTP
- Panel : cocher pour que la caméra soit visible sur le Panel
- Flux vidéo : Renseigner un flux différent que celui par défaut si ce dernier ne convient pas (rtsp://URL_Frigate:8554/Nom_de_la_caméra)
- preset : si vous souhaitez un nombre différent du réglage global

A droite, les quelques paramètres disponibles pour la visualisation.
Refresh de l'image suivant votre configuration.

- bbox
- timestamp : la date, celle-ci sera présente aussi sur le snapshot réalisé si coché.
- zones
- mask : la zone sera masquée
- motion : la zone est avec un contour rouge
- region : la zone est avec un contour vert

### Commandes infos
##### Toutes les caméras
Les informations sur le dernier évènement de la caméra et sur ses statistiques.

L'info **LABEL** correspond à l'objet qui a déclenché la détection (person, vehicle, cat, dog, etc...)

##### MQTT
- **Détection en cours** : dès que Frigate voit un changement, il passe à 1 (nuages, luminosité, personne, etc...) 
- **Détection xxx** : pour chaque caméra sera ajouté un état qui indique si une détection active est en cours ou non pour chaque objet configuré. Par exemple, si vous avez une caméra avec un personnage, un véhicule, une vache, etc., vous aurez 3 états : personne, vache, véhicule. Si vous cochez "visible", l'icône sera présente sur le widget lorsqu'il y aura une détection. L'icône est à personnaliser dans les paramètres de la commande. Si un objet est considéré statique, alors la détection repasse à 0.
- **Détection all** : Si un objet en déplacement est détecté, alors la commande passe à 1. Lorsque Frigate ne détecte plus de mouvement ou que l'objet est immobile, la commande repasse à 0. Si la commande all est à 0, alors les autres commandes de détection seront forcées à 0.

### Commandes actions
- **Capture** :état, capture
- **Camera** : état, activer, désactiver, toggle (Un redémarrage du serveur est nécessaire pour la prise en compte car le fichier configuration est modifié).

Pour avoir les commandes actions suivantes, il est obligatoire d'utiliser MQTT. Sans cela, les commandes ne seront pas créées. Je vous invite à lire la documentation de Frigate pour la configuration de votre serveur MQTT.

- **Detect** : état, on, off, toggle
- **Snapshot** : état, on, off, toggle
- **Recording** : état, on, off, toggle
- **Motion** : état, on, off, toggle (le OFF n'est possible que si detect est sur OFF aussi)

Les commandes PTZ, preset et audio ne sont créées que si la configuration de votre serveur Frigate possède les informations.
- **PTZ** : left, right, up, down, stop, zoom in, zoom out
- **Audio** : état, on, off, toggle
- **Preset** : l'action permettant de placer votre caméra sur un point précis.

### Action(s) sur évènement
Les actions sur évènements sont disponible pour l'équipement **Events** et pour chaque équipement **caméras**.
Les actions configurées sur l'équipement **Events** seront exécutées par les évènements provenant de toutes les caméras **sauf si elles possèdent des actions configurées et activées.**
Si vous souhaitez regrouper sur l'équipement Events des actions communes et ensuite ajouter des actions pour chaque caméra, pensez à cocher sur l'équipement Events la case "autoriser les actions".

<u>Déroulé de l'action</u> :


![execution d'une action](../images/frigate_Doc_ActionsEvents.png)
#### Conditions générales
Indiquer ici dans quel cas les actions **NE DOIVENT PAS** être exécutées.

Par exemple, vous configurez la condition comme ceci : 
**#[Maison][Mode maison][Mode]# == "présent"** 
Les actions ne seront exécutées que si le mode est tout autre que présent.


#### Actions
Vous pouvez indiquer ici les actions à effectuer à chaque nouvel évènement.

Une checkbox vous permet de désactiver la vérification de la condition génèrale. 

<u>LABEL</u> : 
**Pour rappel, le label est ce qui déclenche la détection (person, vehicle, animal, etc...)**
Dans la case **label**, il vous suffit d'indiquer le(s) label(s) pour lesquels vous souhaitez que l'action soit exécutée.
Si ce champ est **vide** ou que vous mettez **all**, alors l'action sera exécutée pour tous les nouveaux évènements.
Vous pouvez indiquer plusieurs labels en les séparant par des virgules.
Les majuscules et les accents sont ignorés, donc si vous indiquez "Vélo" ou "velo", les deux seront considérées comme identiques.

<u>TYPE</u> : 
**Avec** MQTT, ils peuvent être de type **new**, **update** et **end**.
**Sans** MQTT, il sera toujours de type **end**.
Dans la case **type**, il vous suffit d'indiquer le type pour lequel vous souhaitez que l'action soit exécutée.
Vous pourvez en mettre plusieurs en les séparant par des virgules.
Si aucun type n'est spécifié, l'action sera exécutée seulement pour les évènements de type **end**.
les majuscules et les accents sont ignorés, donc si vous indiquez "update" ou "UPDATE", les deux seront considérées comme identiques.

<u>ZONES</u> :

Dans la case **zone d'entrée**, il vous suffit d'indiquer la ou les zones pour lesquelles vous souhaitez que l'action soit exécutée.
Vous pouvez indiquer plusieurs zones en les séparant par des virgules.

La case **zone de sortie** permet de gérer le sens de la détection. Cela ne fonctionne qu'avec une zone d'entrée définie. Si la zone d'entrée est déclenchée avant la zone de sortie alors l'action sera exécutée.

Les majuscules et les accents sont ignorés, donc si vous indiquez "Allée" ou "allee", les deux seront considérées comme identiques.

<u>CONDITION DE L'ACTION</u> :
Indiquer ici dans quel cas les actions **DOIVENT** être exécutées.

Par exemple, vous configurez la condition comme ceci : 
**#[Maison][Mode maison][Mode]# == "absent"**
Les actions ne seront exécutées que si le mode est configuré comme absent.

Si aucune condition n'est spécifiée, l'action sera réalisée.

<u>Variables disponibles pour les conditions:</u>
- **#camera#** : le nom de la caméra
- **#score#** : le score en pourcentage -> 82 %
- **#top_score#** : le score maximum en pourcentage -> 92 %

<u>Variables disponibles pour les actions:</u>
Une liste de variables est disponible afin de personnaliser les actions, ces variables sont remplacées par leur valeur lors de l'exécution de l'action.
- **#time#** : l'heure actuelle au format 12:00
- **#camera#** : le nom de la caméra
- **#cameraId#** : l'id de la caméra (pour par exemple un deeplink vers la page de la caméra dans l'application JeeMate)
- **#score#** : le score en pourcentage -> 82 %
- **#has_clip#** : texte 0 ou 1
- **#has_snapshot#** : texte 0 ou 1
- **#top_score#** : le score maximum en pourcentage -> 92 %
- **#zones#** : tableau
- **#description#** : la description de l'événement générée par genAI (il faut bien entendu l'avoir activé dans le serveur Frigate)
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
- **#preview#** : lien vers fichier le fichier preview
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#preview_path#** : path vers fichier preview
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#label#** : texte
- **#start#** : heure de début
- **#end#** : heure de fin
- **#duree#** : durée de l'évènement
- **#jeemate#** : voir explications plus bas



### Exemple de notifications :
#### Plugin JeeMate
- **snapshot** : dans le champ titre : **``title=votre titre;;bigPicture=#snapshot#``**
- **preview** : dans le champ titre : **``title=votre titre;;bigPicture=#preview#``**
- **thumbnail** : dans le champ titre : **``title=votre titre;;bigPicture=#thumbnail#``**
- **clip** : dans le champ titre : **``title=votre titre;;bigPicture=#clip#``**

Pour une notification automatique, ajouter frigate=#jeemate#, disponible avec la future v3 de JeeMate

- **snapshot** : dans le champ titre : **``title=votre titre;;bigPicture=#snapshot#;;frigate=#jeemate#``**
- **clip** : dans le champ titre : **``title=votre titre;;bigPicture=#clip#;;frigate=#jeemate#``**

#### Plugin Telegram
Testez les 2 commandes snapshot. Selon les configurations, il se peut qu'une des deux ne fonctionne pas.
- **snapshot** : dans le champ options : **``title=votre titre | snapshot=#snapshot#``**
- **snapshot** : dans le champ options : **``title=votre titre | file=#snapshot_path#``**
- **clip** : dans le champ options : **``title=votre titre | file=#clip_path#``**
- **preview** : dans le champ message : **``#preview#``**

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

**ATTENTION** : Le bouton "**supprimer tous les évènements visibles**" fera exactement ce qu'il annonce, donc appliquez bien les bons filtres avant de supprimer : aucun retour en arrière ne sera possible : une popup de confirmation est présente. La suppression est effectuée en database Jeedom mais aussi sur votre serveur Frigate.

**ATTENTION** : le bouton "**supprimer**" supprime l'évènement en database Jeedom mais aussi sur votre serveur Frigate. En aucun cas, je ne serai responsable de votre mauvaise utilisation de ce bouton. Néanmoins, une popup de confirmation est ici aussi présente.

### Création d'un évènement manuel
Dans la configuration générale du plugin Frigate, vous pouvez indiquer les valeurs par défaut des évènements créés manuellement.
Sur la page **Events**, vous trouverez un bouton permettant de créer un nouvel évènement.
Pour chaque caméra, une commande action vous permettra aussi de créer un évènement.
Cette commande est de type message. Si vous laissez vide alors les paramètres par défaut seront utilisés (depuis le widget ce sera toujours le cas).
title : **``Indiquer le label``**
message : **``score=80 | video=1 | duration=20``**

Pour la durée des clips, il faut penser aussi au fait que Frigate ajoute du temps avant et après la vidéo, 5 sec. par defaut, donc en paramétrant à 20 sec. vous obtiendrez une vidéo de 30 sec.

Attention sur les évènements créés manuellement, si dans votre configuration Frigate pour **``record -> retain -> mode``** vous avez **``motion``** alors les clips ne seront disponibles que s'il y a du mouvement de detecté, mettre à **``all``** si vous voulez tout avoir.

Pour ceux en 0.14 et MQTT, les évènements sont remontés automatiquement lors de la création.

Pour ceux n'utilisant pas MQTT, le snapshot est remonté rapidement, le clip s'il y en a un qu'au cron suivant.

### Création d'une capture instantanée

Dans les actions des caméras se trouvent deux commandes :
- Capturer image (action)
- URL image (info)

L'URL est de la forme **``/plugins/frigate/data/caméra/id_snapshot.jpg``** afin de s'adapter au maximum de plugins de communication.

Par exemple si vous souhaitez une URL complète, vous pouvez mettre ceci dans configuration, calcul et arrondi de la commande info :
**``str_replace('"','',"https://monjeedom.eu.jeedom.link"#value#)``**

Ou bien pour ceux ayant besoin du path :

**``str_replace('"','',"/var/www/html"#value#)``**

# <u>Configuration Frigate</u>

**ATTENTION** : La modification de la configuration du serveur Frigate est à vos risques et périls ! Aucun support ne sera donné !

# <u>Logs Frigate</u>
Visualiser tous les logs de votre serveur Frigate

# <u>Cron</u>
**Si vous n'utilisez pas MQTT** : un cron régulier vous permet de récupérer les derniers events et donc d'exécuter les actions associées.

**Si vous utilisez MQTT** : tous les nouveaux events sont reçus automatiquement, un cron heure ou jour est suffisant : il permet de mettre à jour les infos de l'évènement.

Dans tous les cas, laisser au moins un cron actif car il sera vérifié à chaque fois si les fichiers sauvegardés correspondent bien à un évènement et dans le cas contraire, ils seront supprimés.

Le cronDaily est le seul à vérifier la version de votre serveur frigate : si une maj est disponible, vous aurez un message.

**Mon conseil :**
Sans MQTT : cron ou cron5 (suivant puissance machine) + cronDaily
Avec MQTT : cronDaily

***Dans tous les cas, si un cron est en cours d'exécution, le suivant ne sera pas lancé et en MQTT, les cron (1, 5, 10 et 15 sont désactivès).***

# <u>Widget</u>
Vous y trouverez la visualisation de la caméra et les boutons cochés visibles.

# <u>Flux vidéo</u>
### configuration
Dans le plugin Frigate **il n'y a pas de lecteur pour le flux vidéo**, cette configuration sert pour les plugins compatibles.

L'URL du flux vidéo enregistré dans le plugin est celle de votre serveur Frigate et pas celle de la caméra.

1. **Flux RTSP de Frigate** :
   - **Avantages** : Frigate peut centraliser les flux de plusieurs caméras, ce qui réduit le nombre de connexions directes à chaque caméra. Cela peut améliorer la stabilité et la gestion des ressources réseau.
   - **Inconvénients** : La configuration peut être plus complexe, surtout si vous avez plusieurs caméras avec des paramètres différents.

2. **Flux RTSP de la caméra** :
   - **Avantages** : Utiliser directement le flux RTSP de la caméra peut être plus simple à configurer, surtout si vous avez une seule caméra ou si vous ne souhaitez pas utiliser de logiciel intermédiaire.
   - **Inconvénients** : Chaque appareil se connectera directement à la caméra, ce qui peut augmenter la charge sur le réseau et sur la caméra elle-même.

En résumé, si vous avez plusieurs caméras et que vous souhaitez une gestion centralisée, le flux RTSP de Frigate pourrait être plus avantageux. Si vous préférez une solution plus simple et directe, utiliser le flux RTSP de la caméra pourrait être suffisant.

### Avec JeeMate
Si votre configuration Frigate comporte plusieurs flux par caméra, il vous faudra indiquer dans le champ flux vidéo de votre équipement celui que vous souhaitez utiliser, la même chose si vous préférez utiliser le fux d'origine de la caméra.

Configuration Frigate avec un seul flux, ici je n'ai pas besoin d'indiquer le flux, celui par défaut conviendra.

>**``   frigate1:``**
**``      ffmpeg:``**
**``          inputs:``**
**``         - path: rtsp://127.0.0.1:8554/frigate1``**

Configuration Frigate avec plusieurs flux, indiquer l'url du flux voulu sur la page de votre équipement , celui par defaut ne conviendra pas, remplacer 127.0.0.1 par l'ip du serveur Frigate.

>**``   frigate1:``** 
**``      ffmpeg:``**
**``          inputs:``**
**``         - path: rtsp://127.0.0.1:8554/frigate1_SD``**
**`` - role: detect``**
**``         - path: rtsp://127.0.0.1:8554/frigate1_HD``**
**`` - role: record``**

***Attention, en aucun cas il ne vous est demandé de modifier la configuration sur Frigate***

Après chaque modification de l'URL du flux dans le plugin Frigate, il vous faudra sauvegarder aussi dans le plugin JeeMate puis faire une synchronisation complète dans l'application.

# <u>Panel</u>
N'oubliez pas d'activer la page panel dans la configuration générale, puis pour chaque caméra de cocher la case "Panel".

- visualisation des caméras.
- page évènements
