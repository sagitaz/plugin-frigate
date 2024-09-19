### Pré-requis
- Un Sonoff NSPanel Pro
- Une connexion Wi-Fi
- Un smartphone avec l’application eWeLink installée
- Un ordinateur sous Windows ou Linux avec adb installé et opérationnel


### Étape 1 : Déballage et Configuration Initiale
Déballer le NSPanel Pro

Sortez le NSPanel Pro de son emballage.
**Effectuez la configuration initiale** : choix de la langue, connexion Wi-Fi, etc.
Connexion au Wi-Fi et Intégration à eWeLink

Ouvrez l’application **eWeLink** sur votre smartphone.
Scannez le QR code affiché sur le NSPanel Pro pour l’ajouter à votre application.
>Remarque : Inutile de faire les mises à jour.

### Étape 2 : Activer le Mode Développeur
Activer le Mode Développeur sur le NSPanel Pro

Sur le NSPanel Pro, allez dans les paramètres (Settings).
Cliquez 7 fois sur DEVICE ID pour activer le mode développeur.
Firmware 3.5.0 pas necessaire, seulement dans Ewelink
**Attention : Cette action annulera définitivement la garantie et il n’y a pas de retour en arrière possible.**
Activer ADB dans eWeLink

Sur l’application eWeLink, allez dans les paramètres du NSPanel Pro.
Allez dans Mode développeur et activez ADB.

### Étape 3 : Télécharger tous les outils nécessaires
<u>Sur le site GitHub de seaky 10:</u>
- nspanel_pro_tools_apk
- nspanel_pro_roottool_apk

<u>Sur le site GitLab de svanrossem 16:</u>
Les 6 scripts (les mettre dans un répertoire spécifique sonoff-nspanelpro-scripts)
La ROM à jour : CoolKit_Sonoff_480P_20240306_3.0.0-ota.zip 11
La renommer en system_update.zip

L’apk Jeedom Connect ou Jeemate
**Pour Jeedom Connect** : JeedomConnect releases 15
Attention à bien prendre la release correspondant à votre installation (beta ou stable)
**Pour JeeMate** : prendre l'apk via l'application app-tester
https://appdistribution.firebase.dev/i/bcb251ebe2b1160d

### Étape 4 : Installation de Jeedom Connect en mode Launcher
Installer Jeedom Connect
adb connect [ip_address]
adb install JC_XXXXXX.apk (en fonction de la release récupérée)
Rebooter le NSPanel Pro
Au redémarrage, il doit vous demander de sélectionner le launcher par défaut. Sélectionnez Jeedom et cochez « Toujours ».
Connectez-vous à Jeedom Connect (voir documentation Jeedom pour la création d’équipement côté plugin).
Configurez Jeedom Connect pour afficher le lanceur d’applications dans la barre du haut (Préférences >> Barre du haut >> Bouton de la barre du haut).
Le minimum est fait côté Jeedom Connect. Passons aux mises à jour et à l’optimisation du Sonoff NSPanel Pro.


### Étape 4  : Installation de JeeMate en mode Launcher
- Installer JeeMate
adb connect [ip_address]
adb install JC_XXXXXX.apk (en fonction de la release récupérée)

- Rebooter le NSPanel Pro
Au redémarrage, il doit vous demander de sélectionner le launcher par défaut. Sélectionnez JeeMate et cochez « Toujours ».


### Étape 5 : Mise à Jour du NSPanel Pro en Version 3.0.0
Installation des Outils nécessaires
**``adb connect [ip_address]``**
**``adb install nspanel-pro-roottools-1.0-release.apk``**
**``adb reboot``**

Téléchargement et Transfert de la Mise à Jour
**``adb push system_update.zip /storage/emulated/0/``**

Mise à Jour via l’Application sur le NSPanel Pro

Ouvrez l’application nspanel_pro_roottool via le lanceur d’application sur le NSPanel Pro et lancez la mise à jour.
Après le redémarrage, l’outil indiquera que vous êtes en version 3.0.

### Étape 6 : Bascule du NSPanel Pro en Mode Routeur Zigbee
Connexion et Accès Root
**``adb connect [ip_address]``**
**``adb root``**
**``adb shell``**

Montage du Système en Écriture
**``mount -o remount,rw /vendor``**
**``exit``**

Clonage du Dépôt et Transfert des Scripts
**``cd sonoff-nspanelpro-scripts``**
**``find *.sh -exec adb push {} /vendor/bin/siliconlabs_host/ \;``**
**``adb shell``**

Configuration des Permissions et Exécution des Scripts
**``chmod +x /vendor/bin/siliconlabs_host/mod-*``**
**``exit``**

### Étape 7 : Exécution des Scripts de Configuration Zigbee
Écoute des Topics MQTT
**``adb shell /vendor/bin/siliconlabs_host/mod-mqtt_listen.sh``**
Sur un autre terminal pour laisser le script précédent tourner :

Configuration du Module Zigbee en Mode Répéteur
**``adb shell /vendor/bin/siliconlabs_host/mod-set_zigbee_repeater_mode.sh``**
Activation du Mode Pairing en Mode Répéteur

Activez le mode appairage sur votre plugin Zigbee préféré (ex : z2m).
Exécutez la commande suivante :
**``adb shell /vendor/bin/siliconlabs_host/mod-set_zigbee_repeater_pairing_mode.sh``**
Vous verrez ainsi votre NSPanel Pro apparaître dans la liste des équipements (sans commandes associées, il ne fera que du routage).
Activation du Mode Turbo Zigbee

Vous pouvez modifier la puissance d’émission Zigbee (impact inconnu) :
**``adb shell /vendor/bin/siliconlabs_host/mod-set_zigbee_turbo_mode.sh 10``**
**``adb shell /vendor/bin/siliconlabs_host/mod-set_zigbee_turbo_mode.sh 20``**
### Étape 8 : Suppression des Applications Inutiles
Gagner en Fluidité sur le NSPanel Pro
**``adb shell /vendor/bin/siliconlabs_host/mod-debloat_nspanelpro.sh``**
### Étape 9 : Installer les Outils de Paramétrage du NSPanel Pro
Installer nspanel_pro_tools
**``adb install nspanel_pro_tools_apk``**
**``adb reboot``**
Lancer nspanel_pro_tools
Via le lanceur d’application sur Jeedom Connect, lancez nspanel_pro_tools et découvrez les paramétrages possibles.

### Étape 10 : Utilisation de scrcpy
Installer et Configurer scrcpy
Téléchargez et installez scrcpy depuis GitHub 1.
Configurez un fichier de lancement de scrcpy (scrcpy-console.bat) avec le contenu suivant :
>@echo off
adb connect [ip_address]
scrcpy -e --video-codec=h264 --video-encoder='OMX.google.h264.encoder' --pause-on-exit=if-error %*

Lancer scrcpy
Lancez scrcpy via le fichier scrcpy-console.bat créé pour travailler graphiquement sur le NSPanel Pro depuis votre ordinateur.