# Changelog plugin frigate

>**IMPORTANT**
>
>S'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.
# 10/08/2024 Beta 0.9.0
- Ajout bouton et options create event.
- Corrections erreur cron isFavorite.
- Ajout d'un éditeur pour le fichier de configuration ( toutes les modifications sont à vos risques, lisez bien la documentation officielle de Frigate et faites une sauvegarde de la configuration avant ).
- Récupération des logs du serveur Frigate.
- Modification de la gestion du nettoyage des dossiers et events.
- Pleins d'autres modifications .

# 26/07/2024 Beta 0.8.2
- corrections récupération thumbnail
- Ajout bouton pour accéder aux évènements de la caméra sur le widget
- Petites corrections

# 26/07/2024 Beta 0.8.1
- corrections récupération clips et snapshots
- modification couleur boutons widget
- le dossier data n'est plus pris en compte dans les sauvegardes Jeedom

# 22/07/2024 Beta 0.8.0
- Ajout variable #thumbnail_path# et #thumbnail#
- Ajout MQTT2 dependance
- Ajout Widget dashboard et mobile
- Ajout event favoris
- Ajout commandes redémarer (équipement statistiques)
- Ajout dans les actions d'une condition d'exécution
- Création des commandes detect, snapshot et recording (start, stop, toggle)
- Bouton disponible pour créer les commandes PTZ
- Configuration du délais de refresh
- Configuration de la taille maxi du dossier de sauvegarde des snapshots et clips
- Modification visualisation snapshot
- Ajout bouton debug (fichier config)
- Ajout bouton Discord
- Ajout bouton serveur Frigate
- Multiples petites corrections

# 22/06/2024 Beta 0.7.5
- Ajout variables #time#, #event_id#, #snapshot_path# et #clip_path#
- Ajout bouton de suppression de tous les évènements (voir doc)
- Ajout popup de confirmation avant suppression

# 20/06/2024 Beta 0.7.0
- Correction bug sur création d'équipements
- Correction bugs sur affichage page Events
- Corrections bugs Cron
- Ajout d'options de filtrage page Events
- Ajout d'un lien sur les events pour aller vers la caméra et d'un lien de la caméra pour aller vers les events.
- Ajout pour les actions un champ label (soit vide, soit all, soit nom du label), pour déclencher l'action que pour un label spécifique.

# 17/06/2024 Beta 0.6.0
- Ajout de logs
- Ajout dans l'équipement Events de commandes pour activer le cron
- Modification de la configuration cron, utiliser les checkbox jeedom.
- Ajout d'options page events (merci @noodom)
- Configuration pièce par defaut

# 15/06/2024 Beta 0.5.0
- première version Beta
