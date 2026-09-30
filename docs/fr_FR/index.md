Plugin créé par **Sagitaz** et **Noodom**

# <u>Remerciements</u>
Le plugin et le support sont gratuits, vous souhaitez néanmoins m'offrir un café ou des couches pour bébé, je vous remercie par avance.

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/C1C61AKVV7)

# <u>Aide et Support</u>
- Community Jeedom
- Discord JeeMate

Pour toute demande d'aide sur Community ou Discord, merci de fournir le maximum d'informations possibles. (matériel, type de caméra, version de Jeedom, de Frigate, de votre système, etc...)

Sur la page configuration, le bouton assistance permet déjà d'en remplir automatiquement certaines.

Assurez-vous d'avoir un matériel compatible avec Frigate et que ce dernier fonctionne correctement avant de demander de l'aide sur le plugin. (voir la documentation officielle de Frigate pour les configurations matérielles recommandées).

Donnez aussi des logs en mode debug. (ceux du plugin et du serveur Frigate).

Aucun support ne sera apporté sur d'autres moyens de communication que ceux-ci.

Merci


# <u>Pré-requis</u>
- Jeedom 4.4.0 minimum
- Debian 11 (Bullseye) minimum
- Frigate 0.16.0 minimum

Le plugin n'installe pas et ne configure pas le serveur Frigate, il vous faut donc l'installer et le configurer vous-même. Voir la documentation officielle de Frigate pour plus d'informations.

# <u>Installation</u>
Comme pour tous les autres plugins, après l'avoir installé, il faut l'activer.

Le plugin sera toujours compatible avec la dernière version stable connue (le temps de s'adapter). Par contre, on ne fera pas plusieurs développements pour rester opérationnel avec les anciennes versions. Donc si quelque chose ne fonctionne pas, commencez par mettre à jour votre serveur Frigate avant de demander de l'aide.

Au 30-09-2026 le plugin fonctionne avec les versions suivantes de Frigate :
- Frigate 0.18.0 Stable

Frigate 0.16.0 est la version minimale. Certaines fonctions ne sont disponibles qu'à partir de Frigate 0.18 : statut des flux des caméras, profils et paramètre pre_capture des évènements créés manuellement. Je ne garantis pas un suivi des anciennes versions du serveur Frigate.

# Mode de connexion au serveur Frigate
### API
La récupération des caméras, des anciens évènements, la suppression d'évènements, etc...
De nombreuses fonctions du plugin utilisent la connexion à l'API Frigate.
Celle-ci est accessible par le port 5000 sur votre réseau local, il est impératif que vous l'ayez configuré, sans quoi le plugin ne pourra fonctionner.
Si vous souhaitez utiliser un autre port, vous pouvez le faire du moment que vous mappez vers le port 5000 (5054:5000) dans la configuration du serveur Frigate.
### MQTT
La configuration MQTT permet de recevoir les informations du serveur Frigate en temps réel.
Cela améliore l'expérience utilisateur du plugin, mais n'est pas nécessaire pour son fonctionnement.
Un broker sécurisé (user:mdp) est obligatoire pour que le plugin mqtt-manager dont dépend le plugin frigate fonctionne correctement.


# <u>Log</u>
Le plugin comporte des sous-logs, pour qu'ils soient visibles sur Jeedom 4.4.19, il est nécessaire de passer les logs globaux en niveau info minimum.

![niveau de logs](../images/frigate_Doc_Logs.png)
# <u>Configuration</u>
- **Pièce par défaut** : Les caméras créées seront automatiquement placées dans cette pièce.
- **Exclure du backup** : Si coché, le dossier data du plugin (snapshots, clips, miniatures et captures) est exclu des sauvegardes Jeedom, qui sont alors plus légères. Après la restauration d'une telle sauvegarde, les évènements n'ont plus leurs fichiers : une image par défaut s'affiche à leur place.
- **Version du plugin** : la version installée, en lecture seule. À indiquer dans vos demandes d'aide.

#### Paramétrage Frigate
- **URL** : l'url de votre serveur Frigate (ex: 192.168.1.20)
- **Port** : le port du serveur Frigate (5000 par défaut), vous pouvez utiliser un autre port du moment qu'il map vers 5000 (5054:5000 par exemple), l'API ne fonctionnera pas sans cela.
- **Adresse externe** : Pour accéder à la page du serveur Frigate depuis l'extérieur.
- **Topic MQTT** : le topic de votre serveur Frigate (frigate par défaut)
- **Preset** : Pour les caméras avec PTZ, définir le nombre de positions que vous souhaitez récupérer.
- **Pause action** : Pause à effectuer sur les actions PTZ. Par exemple, après avoir appuyé sur move up, un stop est automatiquement effectué : vous pouvez définir le temps avant cette action stop de 0 à 10, correspondant à une pause de 0 à 1 seconde (0, 0.1, 0.2, etc...).

#### Gestion des évènements
- **Récupération des évènements** : Vous pouvez avoir 30 jours d'évènements sur votre serveur Frigate mais ne vouloir en importer que 7 sur Jeedom. Indiquez ici le nombre de jours souhaités (7 par défaut). Si le nombre de jours est 0, alors le processus est arrêté et aucun appel à l'API Frigate n'est effectué.
- **Suppression des évènements** : Les évènements plus anciens que le nombre de jours indiqués (7 par défaut) seront supprimés de la database Jeedom avec leurs fichiers, mais pas du serveur Frigate.

Le nombre de jours de suppression ne peut pas être plus petit que le nombre de jours de récupération. Dans le cas contraire, ce sera alors le nombre de jours de récupération qui sera utilisé.

- **Taille des dossiers** : Taille maximum du dossier data, en Mo (500 Mo par défaut). Au-delà, les évènements les plus anciens sont supprimés jusqu'à repasser sous la limite.

Les évènements mis en favori ne sont jamais supprimés, ni par l'ancienneté ni par la taille. Les captures manuelles suivent les mêmes règles que les évènements : mettez-les en favori pour les garder.

- **Durée de rafraîchissement** : En secondes, durée de rafraîchissement des snapshots de vos caméras. (5 secondes par défaut). Chaque caméra peut avoir sa propre durée, voir l'équipement caméra.
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


# <u>Démon</u>
Le démon démarre automatiquement après avoir sauvegardé la partie configuration et y avoir configuré le topic Frigate.
Pour pouvoir utiliser MQTT, il faut que vous ayez correctement configuré votre serveur Frigate et que vous ayez le plugin mqtt-manager (mqtt2) installé et correctement configuré.
Votre broker MQTT doit être sécurisé pour que le plugin mqtt-manager fonctionne.
Si vous utilisez MQTT, vous pouvez mettre le cron à Hourly ou Daily.

**Démon NOK :**
Si vous n'avez pas mqtt-manager, il est normal que le démon reste sur NOK. Aucun problème, le plugin fonctionne quand même, cependant certaines fonctions seront indisponibles ou limitées.

# <u>Utilisation</u>

**Les commandes infos de tous les équipements sont créées automatiquement à la prochaine réception d'événements ou de statistiques. Si vous ne les voyez pas à la première installation du plugin, c'est que vos événements récents ont plus de 3 heures, il faut donc attendre le prochain événement pour voir les commandes.**

**Les commandes actions sont créées seulement quand vous utilisez le bouton "Rechercher / MAJ".**

## <u>Page du plugin</u>
Les boutons de gestion :
- **Rechercher / MAJ** : crée les nouvelles caméras de Frigate et met à jour les commandes de toutes les caméras.
- **Events** : ouvre la page des évènements.
- **Configuration** : ouvre la configuration générale du plugin.
- **Redémarrer Frigate** : redémarre le serveur Frigate.
- **Serveur Frigate** : ouvre l'interface de Frigate. Depuis votre réseau local (adresse en 192.168.x.x), c'est l'URL du serveur qui est utilisée ; ailleurs, c'est l'adresse externe de la configuration. Sans adresse externe, le bouton n'est affiché que sur le réseau local.
- **aide Discord** : ouvre le salon d'aide du plugin sur Discord.
- **Configuration Frigate** : éditeur du fichier de configuration du serveur Frigate (voir plus bas).
- **Logs Frigate** : logs du serveur Frigate (voir plus bas).
- **Json** : télécharge la liste de tous les évènements, utile pour le développeur en cas de demande d'aide.

Sous les boutons, la version de votre serveur Frigate s'affiche en orange quand une mise à jour est disponible.

## <u>Equipement Events</u>
L'équipement est créé de manière automatique en même temps que les caméras.
Celui-ci comporte des commandes infos avec la valeur du dernier évènement reçu.
Il comporte aussi 2 commandes actions : Cron on et Cron off, ceci afin de mettre en pause la recherche de nouveaux évènements.

Il est possible de créer des actions communes à toutes les caméras (voir la section dédiée)
Cocher "autoriser les actions" si vous souhaitez sur une détection exécuter les actions présentes dans l'équipement events et dans les équipements caméras.


## <u>Equipement Statistiques</u>
L'équipement est créé de manière automatique en même temps que les caméras.
Celui-ci comporte des commandes infos avec quelques statistiques disponibles.

Il comporte aussi la commande action permettant de redémarrer le serveur Frigate.

Avec Frigate 0.18 ou plus et MQTT, deux commandes masquées par défaut permettent de suivre et de changer le profil actif de Frigate :
- **Profil actif** : nom du profil actif, ou none
- **Changer de profil** : indiquer dans le message le nom du profil, ou none pour n'en activer aucun

## <u>Equipement Caméra</u>
Après installation du plugin et la configuration de l'URL et du port de votre serveur Frigate, il vous suffit de cliquer sur le bouton Rechercher. Les caméras trouvées seront automatiquement créées. Il est nécessaire de patienter car à la première recherche sont également importés les évènements de la dernière journée. Cela peut prendre un peu de temps.

### Equipement

- **Identifiant** et **Mot de passe** : seulement utiles pour les commandes HTTP (voir plus bas).
- **Rafraîchissement** : durée de rafraîchissement de l'image de la caméra, en secondes : la première pour le dashboard et le panel, la seconde pour JeeMate. Sans valeur, la durée de la configuration générale est reprise.
- **Afficher sur le panel** : cocher pour que la caméra soit visible sur le Panel.
- **Position sur le panel** : ordre d'affichage de la caméra sur le panel (1, 2, 3…). Les caméras sans position s'affichent après les autres, par ordre alphabétique. Tant qu'elle n'est pas renseignée, la position reprend l'ordre défini dans Frigate (**``ui -> order``**) à chaque « Rechercher / MAJ » ; une position saisie n'est jamais modifiée.
- **Flux vidéo** : Renseigner un flux différent que celui par défaut si ce dernier ne convient pas (rtsp://URL_Frigate:8554/Nom_de_la_caméra)
- **Nombre de preset** : nombre de presets PTZ à importer, si vous souhaitez un nombre différent du réglage global (10 au maximum).
- **Qualité des snapshots** : qualité de compression des images téléchargées, de 1 à 100 (70 par défaut). Plus la valeur est basse, plus les fichiers sont légers. S'applique aux snapshots, aux miniatures et aux captures.
- **Hauteur des snapshots** : hauteur maximale des images, en pixels. Une image plus haute est réduite en gardant ses proportions. Sans valeur, la taille d'origine est conservée. Ne s'applique pas aux miniatures.
- **Convertir en WEBP** : les images sont enregistrées au format WebP, plus léger que le JPEG.
- **Template dashboard** et **Template panel** : n'afficher que l'image de la caméra, sur le dashboard ou sur le panel. Les boutons, les commandes PTZ et les icônes de détection sont masqués ; un clic sur l'image ouvre toujours la fenêtre agrandie avec les actions.

La qualité, la hauteur et le format ne s'appliquent qu'aux images téléchargées après leur modification.

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
Les informations sur le dernier évènement de la caméra : caméra, label, score, top score, zones, id, type, timestamp, durée, clip disponible, snapshot disponible, URL snapshot, URL clip et URL thumbnail. Et les statistiques de la caméra.

L'info **LABEL** correspond à l'objet qui a déclenché la détection (person, vehicle, cat, dog, etc...)

- **RTSP** : le lien du flux vidéo de la caméra (voir la section Flux vidéo).
- **SNAPSHOT LIVE** : le lien vers l'image en direct de la caméra, pour les plugins qui affichent une image de caméra.

##### MQTT
- **Détection en cours** : dès que Frigate voit un changement, il passe à 1 (nuages, luminosité, personne, etc...)
- **Détection xxx** : pour chaque caméra sera ajouté un état qui indique si une détection active est en cours ou non pour chaque objet configuré. Par exemple, si vous avez une caméra avec un personnage, un véhicule, une vache, etc., vous aurez 3 états : personne, vache, véhicule. Si vous cochez "visible", l'icône sera présente sur le widget lorsqu'il y aura une détection. L'icône est à personnaliser dans les paramètres de la commande. Si un objet est considéré statique, alors la détection repasse à 0.
- **Détection tout** : Si un objet en déplacement est détecté, alors la commande passe à 1. Lorsque Frigate ne détecte plus de mouvement ou que l'objet est immobile, la commande repasse à 0. Si la commande Détection tout est à 0, alors les autres commandes de détection seront forcées à 0.
- **Statut flux détection / enregistrement / audio** (Frigate 0.18 ou plus, masquées par défaut) : online, offline ou disabled pour chaque flux de la caméra. Frigate relance un flux hors ligne, la valeur peut donc alterner entre offline et online : attendre qu'elle reste stable avant d'agir, par exemple avec une condition de durée dans le scénario.

##### Reconnaissance
Si la reconnaissance faciale, la lecture de plaques, des modèles de classification ou l'IA générative sont activés dans Frigate, la caméra reçoit par MQTT le résultat de la dernière reconnaissance. Les commandes sont créées au premier résultat.
- **Reconnaissance - Type** : face (visage), lpr (plaque), classification ou description
- **Reconnaissance - Nom** et **Reconnaissance - Score** : la personne ou la plaque reconnue, ou le modèle de classification, avec le score en %
- **Reconnaissance - Plaque d'immatriculation** : la plaque lue
- **Reconnaissance - Label** et **Reconnaissance - Attributs** : le résultat d'un modèle de classification d'objets
- **Reconnaissance - Description** : la description générée par l'IA
- **Reconnaissance - Etat xxx** : l'état courant d'un modèle de classification d'état configuré pour la caméra

### Commandes actions
- **Créer un évènement** : voir la page Events.
- **Capture** : état, capture (voir Création d'une capture instantanée).
- **(Config) Camera** : état, activer, désactiver, toggle. Ces commandes modifient le fichier de configuration de Frigate : un redémarrage du serveur est nécessaire pour la prise en compte.

Pour avoir les commandes actions suivantes, il est obligatoire d'utiliser MQTT. Sans cela, les commandes ne seront pas créées. Je vous invite à lire la documentation de Frigate pour la configuration de votre serveur MQTT. Chacune a un état et des commandes on, off et toggle.

- **Detect** : détection d'objets
- **Snapshot** : snapshots des évènements
- **Recording** : enregistrement
- **Motion** : détection de mouvement (le OFF n'est possible que si detect est sur OFF aussi)
- **enabled** : active ou désactive la caméra tout de suite, sans modifier le fichier de configuration. À partir de Frigate 0.18, l'état est conservé au redémarrage de Frigate ; avant, la caméra revient à sa configuration.
- **review_alerts** et **review_detections** : alertes et détections des activités de la caméra, jusqu'au redémarrage de Frigate. Le plugin reçoit les nouveaux évènements en temps réel par les activités : sans alertes ni détections, il n'en reçoit plus.
- **review_descriptions** et **object_descriptions** : descriptions par IA générative des activités et des objets suivis, jusqu'au redémarrage de Frigate
- **notifications** : notifications de Frigate pour la caméra (pas celles de Jeedom)
- **improve_contrast** : amélioration du contraste pour la détection de mouvement

Les commandes PTZ, preset et audio ne sont créées que si la configuration de votre serveur Frigate possède les informations.
- **PTZ** : left, right, up, down, stop, zoom in, zoom out
- **Audio** : état, on, off, toggle
- **Preset** : l'action permettant de placer votre caméra sur un point précis.

### Commandes HTTP
Dans l'onglet **PTZ & HTTP** d'une caméra, le bouton **Ajouter une commande HTTP** crée une commande action qui appelle l'URL indiquée, par exemple pour piloter une fonction de la caméra que Frigate ne propose pas.

Dans l'URL, **``#user#``** et **``#password#``** sont remplacés par l'identifiant et le mot de passe de l'équipement, par exemple :
**``http://192.168.1.50/cgi-bin/api.cgi?cmd=Snap&user=#user#&password=#password#``**

L'appel utilise aussi l'authentification Digest avec cet identifiant et ce mot de passe. La réponse de la caméra est enregistrée dans la commande info **Etat HTTP command**. Le mot de passe est masqué dans les logs.

Les commandes HTTP sont créées masquées. Une fois rendues visibles, elles apparaissent dans la liste déroulante des actions du widget, avec les presets. Le bouton crayon de la commande permet de modifier son URL.

### Action(s) sur évènement
Les actions sur évènements sont disponibles pour l'équipement **Events** et pour chaque équipement **caméras**.
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

Une checkbox vous permet de désactiver la vérification de la condition générale.

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
Vous pouvez en mettre plusieurs en les séparant par des virgules.
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

<u>BON À SAVOIR</u> :
- Une action qui utilise **#clip#** ou **#clip_path#** n'est exécutée que si le clip est disponible. De même, une action qui utilise **#snapshot#** ou **#snapshot_path#** n'est exécutée que si le snapshot est disponible.
- Un évènement qui a commencé il y a plus de 3 heures ne déclenche aucune action, par exemple lors de la récupération d'anciens évènements.

<u>Variables disponibles pour les conditions:</u>
- **#camera#** : le nom de la caméra
- **#score#** : le score en pourcentage -> 82 %
- **#top_score#** : le score maximum en pourcentage -> 92 %

<u>Variables disponibles pour les actions:</u>
Une liste de variables est disponible afin de personnaliser les actions, ces variables sont remplacées par leur valeur lors de l'exécution de l'action.
- **#time#** : l'heure actuelle au format 12:00
- **#event_id#** : l'identifiant Frigate de l'évènement
- **#type#** : le type de l'évènement : new, update ou end
- **#camera#** : le nom de la caméra
- **#cameraId#** : l'id de la caméra (pour par exemple un deeplink vers la page de la caméra dans l'application JeeMate)
- **#score#** : le score en pourcentage -> 82 %
- **#has_clip#** : texte 0 ou 1
- **#has_snapshot#** : texte 0 ou 1
- **#top_score#** : le score maximum en pourcentage -> 92 %
- **#zones#** : texte, les zones séparées par des virgules
- **#description#** : la description de l'événement générée par genAI (il faut bien entendu l'avoir activé dans le serveur Frigate)
- **#sublabel#** : le label attribué par un modèle de classification d'objets de Frigate
- **#attributes#** : les attributs attribués par un modèle de classification d'objets de Frigate
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
- **#preview#** : lien vers le fichier preview
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

Pour une notification automatique, ajouter frigate=#jeemate# (JeeMate v3)

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

### Evènement :
![cadre évènement](../images/frigate_Doc_Evenement.png)
1. - Mettre l'évènement en favori
2. - lien vers la caméra
3. - Visualiser le snapshot (simple clic sur l'icône)
   - Télécharger le snapshot (double clic sur l'icône)
4. - Visualiser le clip (simple clic sur l'icône)
   - Télécharger le clip (double clic sur l'icône)
5. - Supprimer l'évènement
6. - Description de l'évènement (si genAI activé)

**ATTENTION** : le bouton "**supprimer**" supprime l'évènement en database Jeedom mais aussi sur votre serveur Frigate. En aucun cas, je ne serai responsable de votre mauvaise utilisation de ce bouton. Néanmoins, une popup de confirmation est présente.

Les évènements mis en favoris ne sont pas supprimés.


![page évènements](../images/frigate_Doc_Evenements.png)

1. - **supprimer tous les évènements visibles**
**ATTENTION** : Le bouton "**supprimer tous les évènements visibles**" fera exactement ce qu'il annonce, donc appliquez bien les bons filtres avant de supprimer : aucun retour en arrière ne sera possible : une popup de confirmation est présente. La suppression est effectuée en database Jeedom mais aussi sur votre serveur Frigate.

2. - **Création d'un évènement manuel**
Dans la configuration générale du plugin Frigate, vous pouvez indiquer les valeurs par défaut des évènements créés manuellement.
Sur la page **Events**, vous trouverez un bouton permettant de créer un nouvel évènement.
Pour chaque caméra, une commande action vous permettra aussi de créer un évènement.
Cette commande est de type message. Si vous laissez vide alors les paramètres par défaut seront utilisés (depuis le widget ce sera toujours le cas).
title : **``Indiquer le label``**
message : **``score=80 | video=1 | duration=20 | pre_capture=5``**
**pre_capture** (Frigate 0.18 ou plus) : nombre de secondes enregistrées avant la création de l'évènement. Sans ce paramètre, Frigate applique le pré-enregistrement de la caméra.
Pour la durée des clips, il faut penser aussi au fait que Frigate ajoute du temps avant et après la vidéo, 5 sec. par défaut, donc en paramétrant à 20 sec. vous obtiendrez une vidéo de 30 sec.
Attention sur les évènements créés manuellement, si dans votre configuration Frigate pour **``record -> retain -> mode``** vous avez **``motion``** alors les clips ne seront disponibles que s'il y a du mouvement détecté, mettre à **``all``** si vous voulez tout avoir.

3. - **Filtrer les évènements**
Afficher seulement les événements d'une ou plusieurs caméras, seulement d'un label ou d'un type d'événement, seulement les événements de la semaine ou de l'année en cours, etc...

# Création d'une capture instantanée

Vous ne souhaitez pas créer un évènement manuellement, mais vous souhaitez avoir une capture instantanée de la caméra ? Vous pouvez créer une action sur la caméra qui va capturer l'image de la caméra.

Dans les actions des caméras se trouvent deux commandes :
- Capturer une image (action)
- URL image (info)

Chaque capture est aussi enregistrée comme un évènement avec le label **capture**, visible sur la page Events. Les captures sont supprimées selon les mêmes règles que les évènements (ancienneté et taille du dossier data) : mettez en favori celles que vous voulez garder.

L'URL est de la forme **``/plugins/frigate/data/snapshots/id_snapshot.jpg``** afin de s'adapter au maximum de plugins de communication.

Par exemple si vous souhaitez une URL complète, vous pouvez mettre ceci dans configuration, calcul et arrondi de la commande info :
**``str_replace('"','',"https://monjeedom.eu.jeedom.link"#value#)``**

Ou bien pour ceux ayant besoin du path :

**``str_replace('"','',"/var/www/html"#value#)``**

# <u>Configuration Frigate</u>

**ATTENTION** : La modification de la configuration du serveur Frigate est à vos risques et périls ! Aucun support ne sera donné !

L'éditeur affiche le fichier de configuration du serveur Frigate et vérifie la syntaxe YAML pendant la saisie.
- **Récupérer la configuration** : recharge le fichier depuis le serveur Frigate, en abandonnant vos modifications.
- **Télécharger la configuration** : enregistre le fichier sur votre ordinateur. Pensez à le faire avant toute modification.
- **Envoyer la configuration** : remplace le fichier sur le serveur Frigate. Certaines modifications ne sont prises en compte qu'après un redémarrage de Frigate.
- **Envoyer la configuration et redémarrer Frigate** : remplace le fichier puis redémarre Frigate.

# <u>Logs Frigate</u>
Visualiser les logs de votre serveur Frigate : Frigate, go2rtc et nginx. Le bouton de téléchargement enregistre les logs affichés.

# <u>Cron</u>
**Si vous n'utilisez pas MQTT** : un cron régulier vous permet de récupérer les derniers events et donc d'exécuter les actions associées.

**Si vous utilisez MQTT** : tous les nouveaux events sont reçus automatiquement, un cron heure ou jour est suffisant : il permet de mettre à jour les infos de l'évènement.

Dans tous les cas, laisser au moins un cron actif car il sera vérifié à chaque fois si les fichiers sauvegardés correspondent bien à un évènement et dans le cas contraire, ils seront supprimés.

Le cronDaily est le seul à vérifier la version de votre serveur frigate : si une maj est disponible, vous aurez un message.

**Mon conseil :**
Sans MQTT : cron ou cron5 (suivant puissance machine) + cronDaily
Avec MQTT : cronDaily

***Dans tous les cas, si un cron est en cours d'exécution, le suivant ne sera pas lancé et en MQTT, les crons 1, 5, 10 et 15 sont désactivés.***

# <u>Widget</u>
Vous y trouverez la visualisation de la caméra et les boutons cochés visibles :
- un clic sur l'image ouvre une fenêtre agrandie avec les actions, les commandes PTZ et les presets ;
- les boutons enregistrement, snapshots, détection, audio et mouvement, création d'évènement et capture ;
- la liste déroulante des actions regroupe les presets PTZ et les commandes HTTP visibles ;
- l'icône clé à molette ouvre le panneau IA : activation de la caméra, alertes et détections des activités, descriptions par IA générative ;
- une icône affiche les évènements de la caméra sur la page Events ;
- les icônes des objets détectés en ce moment, pour les commandes **Détection xxx** visibles.

Un bouton n'apparaît que si ses commandes sont visibles.

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
Si votre configuration Frigate comporte plusieurs flux par caméra, il vous faudra indiquer dans le champ flux vidéo de votre équipement celui que vous souhaitez utiliser, la même chose si vous préférez utiliser le flux d'origine de la caméra.

Configuration Frigate avec un seul flux, ici je n'ai pas besoin d'indiquer le flux, celui par défaut conviendra.

```yaml
frigate1:
  ffmpeg:
    inputs:
      - path: rtsp://127.0.0.1:8554/frigate1
```

Configuration Frigate avec plusieurs flux, indiquer l'url du flux voulu sur la page de votre équipement, celui par défaut ne conviendra pas, remplacer 127.0.0.1 par l'ip du serveur Frigate.

```yaml
    ffmpeg:
      inputs:
        - path: rtsp://127.0.0.1:8554/frigate1_high  # Flux principal haute résolution
          input_args: preset-rtsp-restream
          roles:
            - record        # Utilisé pour l’enregistrement
        - path: rtsp://127.0.0.1:8554/frigate1_low   # Flux secondaire basse résolution
          input_args: preset-rtsp-restream
          roles:
            - detect
```

***Attention, en aucun cas il ne vous est demandé de modifier la configuration sur Frigate***

Après chaque modification de l'URL du flux dans le plugin Frigate, il vous faudra sauvegarder aussi dans le plugin JeeMate puis faire une synchronisation complète dans l'application.

# <u>Panel</u>
N'oubliez pas d'activer la page panel dans la configuration générale, puis pour chaque caméra de cocher la case "Panel".

- visualisation des caméras.
- page évènements

# <u> FAQ </u>

### Le plugin est bien configuré en MQTT mais aucune action n'est effectuée
Le topic frigate/reviews correspond aux review items (périodes d’activité détectée) qui sont générés après la détection et l’enregistrement des objets. Ce système de revue s'appuie fortement sur la fonction d’enregistrement (recording) pour fonctionner :

Frigate organise les review items comme des plages temporelles regroupant plusieurs détections

Si l’enregistrement est désactivé (record.enabled: false), aucun segment vidéo n’est stocké, et donc la plateforme ne construit pas de review items → rien n’est publié dans frigate/reviews.

Pour fonctionner le plugin a donc besoin de :

```yaml
record:
  enabled: true
```

### Exemple de fichier de configuration
 Veuillez noter que c'est mon fichier, mes réglages et qu'il fonctionne pour ma situation, à vous de l'adapter ou de comparer avec le vôtre si jamais toutes les fonctions du plugin n'étaient pas fonctionnelles chez vous.

 Je ne pourrai être tenu responsable de tout dysfonctionnement causé par cette configuration, vous devez donc adapter la configuration à votre propre serveur et à vos besoins.

 J'ai mis des commentaires afin de vous aider.

 Ce fichier correspond à Frigate 0.18. Il est raccourci : une seule caméra, prompts abrégés.

```yaml
# Rappel, le plugin mqtt-manager nécessite un broker mqtt sécurisé.
mqtt:
  host: 192.168.2.22        # Adresse IP de votre serveur MQTT
  port: 1883                # Port du broker MQTT
  user: '***'               # Nom d'utilisateur (masqué ici)
  password: '***'           # Mot de passe (masqué ici)
  stats_interval: 300       # Fréquence (en secondes) des messages de statistiques MQTT

detectors:
  coral:
    type: edgetpu           # Utilise un accélérateur Coral (Edge TPU) pour la détection
    device: usb             # Type de connexion : USB

timestamp_style:
  position: tr              # Position du timestamp sur l'image (tr = coin supérieur droit)
  format: '%d/%m/%Y %H:%M:%S'  # Format du timestamp affiché (jour/mois/année heure:min:sec)

birdseye:
  enabled: false            # Désactive le mode Birdseye (vue multi-caméras combinée)

model:
  labelmap:                 # Remappage des classes de détection vers des noms personnalisés
    0: personne
    1: vehicule
    2: vehicule
    3: vehicule
    5: vehicule
    7: vehicule
    16: animale
    17: animale
    18: animale
    19: animale
    20: animale

detect:
  enabled: true             # Active la détection d'objets pour toutes les caméras

snapshots:
  enabled: true             # Active les captures d'image (snapshots) des évènements
  timestamp: false          # Ne superpose pas la date/heure sur les images
  bounding_box: false       # Ne dessine pas de cadre autour des objets détectés
  crop: false               # Ne recadre pas sur l'objet détecté
  retain:                   # Durée de conservation des snapshots
    default: 3              # Par défaut, 3 jours
    objects:
      personne: 7           # 7 jours pour une "personne"
      vehicule: 3           # 3 jours pour un "vehicule"

record:
  enabled: true             # Active l'enregistrement vidéo, obligatoire pour que le plugin reçoive les évènements
  alerts:
    retain:
      days: 7               # Conserve les clips d'alerte pendant 7 jours
      mode: active_objects  # Seulement si un objet actif a été détecté
    pre_capture: 5          # Enregistre 5 secondes avant le début de l'évènement
    post_capture: 5         # Enregistre 5 secondes après la fin de l'évènement
  detections:
    retain:
      days: 7               # Conserve les clips de détection pendant 7 jours
      mode: active_objects
    pre_capture: 3
    post_capture: 5

semantic_search:
  enabled: true             # Active la recherche sémantique des évènements

face_recognition:
  enabled: true             # Reconnaissance faciale (commandes Reconnaissance du plugin)

lpr:
  enabled: true             # Lecture des plaques d'immatriculation

classification:
  custom:
    Porte entrée:           # Modèle d'état : commande "Reconnaissance - Etat Porte entrée"
      enabled: true
      name: Porte entrée
      threshold: 0.8
      state_config:
        cameras:
          Porte:
            crop: [0.0, 0.39, 0.34, 0.99]   # Partie de l'image analysée
        motion: true
    Parking:                # Modèle d'objet : variable #sublabel# des actions
      enabled: true
      name: Parking
      threshold: 0.8
      object_config:
        objects:
          - vehicule
        classification_type: sub_label

genai:
  default:                  # Fournisseur d'IA générative (plusieurs possibles depuis Frigate 0.18)
    provider: gemini
    api_key: '***'          # Clé API (masquée ici)
    model: gemini-2.5-flash
    roles:                  # Tâches confiées à ce fournisseur
      - descriptions
      - chat

review:
  genai:                    # Descriptions des activités par l'IA
    enabled: true
    alerts: true
    detections: true

cameras:
  Porte:                    # Nom de la caméra
    detect:
      fps: 2                # Images analysées par seconde
      width: 1280           # Résolution du flux analysé
      height: 720
      stationary:
        interval: 50        # Vérifie les objets immobiles toutes les 50 images
        threshold: 30       # Nombre d'images sans déplacement pour qu'un objet soit considéré immobile
    ffmpeg:
      inputs:
        - path: rtsp://127.0.0.1:8554/Porte_1  # Flux principal haute résolution
          input_args: preset-rtsp-restream
          roles:
            - record        # Utilisé pour l'enregistrement
        - path: rtsp://127.0.0.1:8554/Porte_2  # Flux secondaire basse résolution
          input_args: preset-rtsp-restream
          roles:
            - detect        # Utilisé pour la détection
    objects:
      track:                # Liste des objets à détecter
        - personne
        - vehicule
        - animale
      filters:
        personne:
          min_score: 0.73   # Score minimum pour commencer à suivre
          threshold: 0.8    # Score minimum pour déclencher un évènement
      genai:                # Descriptions des objets par l'IA
        enabled: true
        use_snapshot: true
        prompt: Analyse le {label} dans ces images provenant de la caméra de sécurité {camera}...
        object_prompts:     # Prompts personnalisés pour chaque type d'objet, à vous de les adapter
          personne: Commence IMMÉDIATEMENT et DIRECTEMENT la description de l'action...
          vehicule: Décris IMMÉDIATEMENT et DIRECTEMENT le comportement du véhicule...
    zones:
      entree:               # Zone utilisable dans les actions du plugin
        coordinates: 0.379,0.307,0.985,0.533,0.99,0.986,0.003,0.994
        objects:
          - personne
          - vehicule

go2rtc:
  streams:                  # Flux des caméras, relayés par Frigate
    Porte_1: rtsp://***:***@192.168.2.36:554/1  # Flux haute qualité
    Porte_2: rtsp://***:***@192.168.2.36:554/2  # Flux basse qualité

version: 0.18-0             # Version du format de configuration de Frigate
```


