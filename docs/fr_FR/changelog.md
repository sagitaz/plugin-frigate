# Changelog plugin frigate

>**IMPORTANT**
>
>S'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

# 26/07/2024 Beta 0.8.1
- corrections récupération clips et snapshots
- modification couleur boutons widget
- le dossier data n'est plus pris en compte dans les sauvegardes jeedom

# 22/07/2024 Beta 0.8.0
- Ajout variable #thumbnail_path# et #thumbnail#
- Ajout MQTT2 dependance
- Ajout Widget dashboard et mobile
- Ajout event favoris
- Ajout commandes redemarer (équipement statistiques)
- Ajout dans les actions d'une condition d'èxécution
- Creation des commandes detect, snapshot et recording (start, stop, toggle)
- Bouton disponible pour créer les commandes PTZ
- Configuration du délais de refresh
- Configuration de la taille maxi du dossier de sauvegarde des snapshots et clips
- Modification visualisation snapshot
- Ajout bouton debug (fichier config)
- Ajout bouton discord
- Ajout bouton serveur Frigate
- Multiples petites corrections

# 22/06/2024 Beta 0.7.5
- Ajout variable #time#, #event_id#, #snapshot_path# et #clip_path#
- Ajout bouton de suppression de tous les évènements (voir doc)
- Ajout popup de confirmation avant suppression

# 20/06/2024 Beta 0.7.0
- Correction bug sur création d'équipements
- Correction bugs sur affichage page Events
- Corrections bugs Cron
- Ajout d'options de filtrage page Events
- Ajout d'un lien sur les events pour aller vers la caméra et d'un lien de la caméra pour aller vers les events.
- Ajout pour les actions un champ label (soit vide, soit all, soit nom du label), pour declencher l'action que pour un label spécifique.

# 17/06/2024 Beta 0.6.0
- Ajout de logs
- Ajout dans l'équipement Events de commandes pour activer le cron
- Modification de la configuration cron, utiliser les checkbox jeedom.
- Ajout d'options page events (merci @noodom)
- Configuration pièce par default

# 15/06/2024 Beta 0.5.0
- première version Beta