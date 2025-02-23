# Changelog plugin frigate

>**IMPORTANT**
>
>S'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

# 23/02/2025 Beta 1.1.8
- Ajout de logs frigateActions et frigateMQTT
- Corrections variable snapshot sur type update et new
- Correction URL image (voir doc si besoin de le modifier)

# 19/02/202 Beta 1.1.7
- Gestion zone de sortie
- Correction affichage fichier de configuration frigate > 0.15

# 10/01/2025 Beta 1.1.6
- Correction erreur cronDaily mySQL

# 11/11/2024 Stable 1.1.5
- Nettoie l'URL

# 23/10/2024 Beta 1.1.3
- Ajout de la zone dans les actions
- Correction de l'exécution des actions

# 07/10/2024 Stable 1.1.2
- Verification de l'etat du serveur Frigate avant d'executer les cron

# 07/10/2024 Beta 1.1.1
- Ajout des commandes binaire pour les object detectés

# 05/10/2024 Stable 1.1.0
- Voir le détails des mises à jour précèdentes.

# 04/10/2024 Beta 1.0.6
- Correction changement valeur audio
- Mise a jour des statuts seulement si différent de la dernière

# 02/10/2024 Beta 1.0.5
- Correction erreur création des commandes audio
- Correction erreur création des commandes mqtt (valeur remise à 1)

# 01/10/2024 Beta 1.0.4
- Option pour exclure ou non les datas du backup Jeedom
- Ajout de la pause PTZ
- Ajout commande statut serveur et disponibilité serveur
- Save automatiquement le bbox sur les snapshots
- Optimisation du cron
- Correction erreur file_get_content si fichiers n'existe pas
- Correction filtre date (Firefox)
- Option pour afficher les cameras sur le panel

# 21/09/2024 Beta 1.0.3
- Option pour flux RTSP (voir documentation)
- Force le type génèrique de l'url snapshot (faites une recherche ou un save de chaque equipement)
- Correction page events vignette (clip, preview, rien)

# 17/09/2024 Beta 1.0.2
- Ajout de la variable #preview# dans les notifications
- Sur la page events, ce sera la preview au survol et plus le clip (moins lourd).
- Les filtres sont sauvegardés pour etre appliqués à la prochaine ouverture de la page events.

# 16/09/2024 Beta 1.0.1
- Correction selecteur preset sur widget
- Corrections de diverses erreur JS
- Ajout de la configuration d'un lien externe pour accèder à Frigate
- Si utilisation de MQTT les cron < 30 minutes ne seront pas lancés
- Aucune commandes infos ne sera coché historiser sur les nouvelles installations (pour les autres penser à les décocher)
- Ajout d'un wait avant la récupération des snapshots (a voir !)
- Edition du nom des commandes preset et http possible
- Correction de la checkbox d'execption de condition qui était appliqué que sur la première action

# 14/09/2024 Stable 1.0.0
- Tout ce qui est dans les betas précèdente.

# 14/09/2024 Beta 0.9.7
- Corrections d'erreur HTTP_ERROR et JS
- Bouton pour editer l'url de la commande HTTP
- Amélioration du panel
- Variable #user# et #password# si necessaire dans les commandes HTTP
- Réorganisation des commandes infos et actions
- Mise en place pour intégration automatique dans JeeMate v3
- checkbox afin d'ignorer la condition sur le declenchement d'actions

# 13/09/2024 Beta 0.9.6
- Corrections des commandes PTZ
- Ajout sur le widget des bouton PTZ
- Ajout d'un bouton pour créer des commandes HTTP (user et mot de passe à renseigner sur page caméra)

# 11/09/2024 Beta 0.9.5
- Modification sur la création des commandes.
- Commandes audio (etat, on, off et toggle) disponibles si présent dans votre configuration.
- Verification de la version Frigate une seule fois par jour (si cronDaily activé).
- Correction si nom existant ailleurs (caché ou avec maj)
- Modification Widget dashboard et mobile
- Création des commandes PTZ preset (config a faire)

# 06/09/2024 Beta 0.9.4
- Ajout commande "créer capture" (voir doc)
- Ajout Panel

# 05/09/2024 Beta 0.9.3
- Ajout du mask sur la visualisation des caméras.
- Mise à jour des snapshots lors des receptions end.
- Diverses modifications et améliorations sur la page Events.
- Récuperation de l'event sur createEvent plus rapide si mqtt pas installé.
- Traductions

# 19/08/2024 Beta 0.9.2
- Correction des actions du type "mots clés".
- Correction du filtre "type" sur l'exécution des actions.
- Correction des accents dans la création d'évènement.

# 17/08/2024 Beta 0.9.1
- Traduction Anglais, Allemand, Espagnol, Italien, Portugais. merci @mips
- Correction de l'exécution des actions.
- Nouvelle gestion pour réception des évènements MQTT (Frigate 0.14).
- Correction pour la création d'un évènement manuel.
- Amélioration de la page des évènements.

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
