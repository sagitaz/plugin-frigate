Plugin erstellt von **Sagitaz** und **Noodom**

# <u>Vielen Dank</u>
Das Plugin und der Support sind kostenlos. Wenn Sie mir dennoch gerne einen Kaffee oder Babywindeln spendieren möchten, bedanke ich mich schon im Voraus.

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/C1C61AKVV7)

# <u>Hilfe und Support</u>
- Jeedom-Community
- Discord JeeMate

Wenn Sie in der Community oder auf Discord um Hilfe bitten, geben Sie bitte so viele Informationen wie möglich an. (Hardware, Kameratyp, Version von Jeedom, Frigate, Ihrem System usw.)

Auf der Konfigurationsseite können bestimmte Felder über die Schaltfläche „Hilfe“ bereits automatisch ausgefüllt werden.

Stellen Sie sicher, dass Sie über kompatible Hardware für Frigate verfügen und dass diese ordnungsgemäß funktioniert, bevor Sie Hilfe zum Plugin anfordern. (Empfohlene Hardwarekonfigurationen finden Sie in der offiziellen Frigate-Dokumentation.)

Bitte geben Sie auch die Logs im Debug-Modus an (die des Plugins und des Frigate-Servers).

Für andere Kommunikationskanäle als die hier genannten wird kein Support angeboten.

Danke


# <u>Voraussetzungen</u>
- Jeedom 4.4.0 oder höher
- Mindestens Debian 11 (Bullseye)
- Frigate 0.16.0 (Mindestversion)

Das Plugin installiert und konfiguriert den Frigate-Server nicht. Sie müssen ihn daher selbst installieren und konfigurieren. Weitere Informationen finden Sie in der offiziellen Frigate-Dokumentation.

# <u>Installation</u>
Wie bei allen anderen Plugins muss es nach der Installation aktiviert werden.

Das Plugin wird stets mit der letzten bekannten stabilen Version kompatibel sein (es dauert eine Weile, bis die Anpassung erfolgt ist). Wir werden jedoch keine weiteren Entwicklungen vornehmen, um die Kompatibilität mit älteren Versionen aufrechtzuerhalten. Sollte also etwas nicht funktionieren, aktualisieren Sie bitte zunächst Ihren Frigate-Server, bevor Sie um Hilfe bitten.

Stand 30.09.2026 funktioniert das Plugin mit den folgenden Versionen von Frigate:
- Frigate 0.18.0 Stable

Frigate 0.16.0 ist die Mindestversion. Bestimmte Funktionen sind erst ab Frigate 0.18 verfügbar: Status der Kamerastreams, Profile und die Einstellung „pre_capture“ für manuell erstellte Ereignisse. Ich übernehme keine Garantie für die Unterstützung älterer Versionen des Frigate-Servers.

# Verbindungsmodus zum Frigate-Server
### API
Das Abrufen von Kameraaufnahmen, alten Ereignissen, das Löschen von Ereignissen usw.
Viele Funktionen des Plugins nutzen die Verbindung zur Frigate-API.
Auf diese kann über Port 5000 in Ihrem lokalen Netzwerk zugegriffen werden. Sie müssen diesen unbedingt konfiguriert haben, da das Plugin sonst nicht funktionieren kann.
Wenn Sie einen anderen Port verwenden möchten, ist dies möglich, sofern Sie in der Konfiguration des Frigate-Servers eine Weiterleitung auf Port 5000 (5054:5000) einrichten.
### MQTT
Die MQTT-Konfiguration ermöglicht den Empfang von Informationen vom Frigate-Server in Echtzeit.
Dies verbessert die Benutzererfahrung mit dem Plugin, ist für dessen Funktionieren jedoch nicht erforderlich.
Ein sicherer Broker (Benutzername: Passwort) ist erforderlich, damit das Plugin „mqtt-manager“, von dem das Plugin „frigate“ abhängt, ordnungsgemäß funktioniert.


# <u>Protokoll</u>
Das Plugin enthält Unterprotokolle. Damit diese in Jeedom 4.4.19 angezeigt werden, müssen die globalen Protokolle auf mindestens die Stufe „Info“ gesetzt werden.

![Protokollierungsstufe](../images/frigate_Doc_Logs.png)
# <u>Konfiguration</u>
- **Standardraum**: Die erstellten Kameras werden automatisch in diesem Raum platziert.
- **Vom Backup ausschließen**: Wenn diese Option aktiviert ist, wird der Datenordner des Plugins (Snapshots, Clips, Miniaturansichten und Screenshots) aus den Jeedom-Backups ausgeschlossen, wodurch diese kleiner werden. Nach der Wiederherstellung eines solchen Backups fehlen die Dateien zu den Ereignissen: An ihrer Stelle wird ein Standardbild angezeigt.
- **Plugin-Version**: Die installierte Version, schreibgeschützt. Bitte geben Sie diese in Ihren Supportanfragen an.

#### Einrichtung von Frigate
- **URL**: Die URL Ihres Frigate-Servers (z. B.: 192.168.1.20)
- **Port**: Der Port des Frigate-Servers (standardmäßig 5000). Sie können einen anderen Port verwenden, solange dieser auf 5000 weitergeleitet wird (z. B. 5054:5000). Ohne diese Weiterleitung funktioniert die API nicht.
- **Externe Adresse**: Um von außen auf die Seite des Frigate-Servers zuzugreifen.
- **MQTT-Topic**: das Topic Ihres Frigate-Servers (standardmäßig „frigate“)
- **Voreinstellung**: Bei PTZ-Kameras legen Sie die Anzahl der Positionen fest, die Sie abrufen möchten.
- **Aktionspause**: Pause, die bei PTZ-Aktionen eingelegt werden soll. Beispielsweise wird nach dem Drücken von „Move up“ automatisch ein Stopp ausgeführt: Sie können die Zeit vor dieser Stopp-Aktion auf einen Wert zwischen 0 und 10 einstellen, was einer Pause von 0 bis 1 Sekunde entspricht (0, 0,1, 0,2 usw.).

#### Ereignisverwaltung
- **Ereignisabruf**: Möglicherweise befinden sich 30 Tage an Ereignissen auf Ihrem Frigate-Server, Sie möchten jedoch nur 7 davon in Jeedom importieren. Geben Sie hier die gewünschte Anzahl an Tagen an (Standardwert: 7). Wenn die Anzahl der Tage 0 beträgt, wird der Vorgang abgebrochen und es erfolgt kein Aufruf der Frigate-API.
- **Löschen von Ereignissen**: Ereignisse, die älter sind als die angegebene Anzahl von Tagen (standardmäßig 7), werden zusammen mit ihren Dateien aus der Jeedom-Datenbank gelöscht, jedoch nicht vom Frigate-Server.

Die Anzahl der Löschtage darf nicht geringer sein als die Anzahl der Wiederherstellungstage. Andernfalls wird die Anzahl der Wiederherstellungstage zugrunde gelegt.

- **Ordnergröße**: Maximale Größe des Ordners „data“ in MB (standardmäßig 500 MB). Wird dieser Wert überschritten, werden die ältesten Ereignisse gelöscht, bis die Grenze wieder unterschritten wird.

Als Favoriten markierte Ereignisse werden niemals gelöscht, weder aufgrund ihres Alters noch aufgrund ihrer Größe. Manuelle Erfassungen unterliegen denselben Regeln wie Ereignisse: Markieren Sie sie als Favoriten, um sie zu behalten.

- **Aktualisierungsintervall**: In Sekunden, das Aktualisierungsintervall für die Schnappschüsse Ihrer Kameras. (Standardmäßig 5 Sekunden). Jede Kamera kann ein eigenes Intervall haben, siehe Kameraausstattung.
- **Videos als Miniaturansichten**: Wenn Sie mit der Maus über eine Miniaturansicht auf der Veranstaltungsseite fahren, wird das Video abgespielt.
- **Bestätigung vor dem Löschen**: Zeigt vor dem Löschen eines Ereignisses eine Warnmeldung an.
- **Pause beim Erstellen von Dateien (in Sekunden)**: Wartezeit vor dem Erstellen der Datei (Clip/Snapshot) (standardmäßig 5 s). Je nach Server kann dies erforderlich sein, um Frigate genügend Zeit zum Erstellen der Datei zu geben.
#### Standardeinstellungen für ein manuell erstelltes Ereignis
- **Bezeichnung**: Der Name des erstellten Ereignisses (standardmäßig „manuell“).
- **Video aufnehmen**: Ja (standardmäßig).
- **Videolänge**: standardmäßig 40 Sekunden.
- **Punktzahl**: Standardmäßig 0.

#### Funktionen
- **Cron**: Wählen Sie den gewünschten Cron-Eintrag aus.


# <u>Demon</u>
Der Daemon startet automatisch, nachdem der Konfigurationsabschnitt gespeichert und darin das Thema „Frigate“ konfiguriert wurde.
Um MQTT nutzen zu können, müssen Sie Ihren Frigate-Server korrekt konfiguriert haben und das Plugin „mqtt-manager“ (mqtt2) installiert und korrekt konfiguriert haben.
Ihr MQTT-Broker muss gesichert sein, damit das Plugin „mqtt-manager“ funktioniert.
Wenn Sie MQTT verwenden, können Sie den Cron-Job auf „Stündlich“ oder „Täglich“ einstellen.

**Daemon NOK:**
Wenn Sie mqtt-manager nicht installiert haben, ist es normal, dass der Daemon den Status „NOK“ anzeigt. Das ist kein Problem, das Plugin funktioniert trotzdem, allerdings sind einige Funktionen nicht verfügbar oder eingeschränkt.

# <u>Anwendung</u>

**Die Info-Befehle für alle Geräte werden automatisch erstellt, sobald die nächsten Ereignisse oder Statistiken eingehen. Wenn Sie diese bei der ersten Installation des Plugins nicht sehen, liegt das daran, dass Ihre letzten Ereignisse bereits länger als 3 Stunden zurückliegen. Sie müssen daher auf das nächste Ereignis warten, um die Befehle zu sehen.**

**Aktionsbefehle werden nur erstellt, wenn Sie die Schaltfläche „Suchen / Aktualisieren“ verwenden.**

## <u>Equipement Events</u>
Die Geräte werden automatisch zusammen mit den Kameras angelegt.
Dieser enthält Info-Befehle mit dem Wert des zuletzt empfangenen Ereignisses.
Es enthält außerdem zwei Aktionsbefehle: „cron start“ und „cron stop“, um die Suche nach neuen Ereignissen zu unterbrechen.

Es ist möglich, Aktionen zu erstellen, die für alle Kameras gelten (siehe den entsprechenden Abschnitt)
Aktivieren Sie das Kontrollkästchen „Aktionen zulassen“, wenn Sie bei einer Erkennung die in den Gerätereignissen und in den Kamerageräten vorhandenen Aktionen ausführen möchten.


## <u>Ausstattung – Statistiken</u>
Die Geräte werden automatisch zusammen mit den Kameras angelegt.
Dieses Modul enthält Informationsbefehle mit einigen verfügbaren Statistiken.

Es enthält außerdem den Befehl „action“, mit dem der Frigate-Server neu gestartet werden kann.

Mit Frigate 0.18 oder höher und MQTT ermöglichen zwei standardmäßig ausgeblendete Befehle, das aktive Profil von Frigate zu überwachen und zu ändern:
- **Aktives Profil**: Name des aktiven Profils oder „none“
- **Profil wechseln**: Geben Sie in der Nachricht den Namen des Profils an oder „none“, um kein Profil zu aktivieren.

## <u>Kameraausrüstung</u>
Nachdem Sie das Plugin installiert und die URL sowie den Port Ihres Frigate-Servers konfiguriert haben, klicken Sie einfach auf die Schaltfläche „Suchen“. Die gefundenen Kameras werden automatisch angelegt. Bitte haben Sie etwas Geduld, da bei der ersten Suche auch die Ereignisse des letzten Tages importiert werden. Dies kann etwas Zeit in Anspruch nehmen.

### Ausstattung

- **Benutzername** und **Passwort**: nur für HTTP-Befehle erforderlich (siehe unten).
- **Aktualisierung**: Aktualisierungsintervall des Kamerabildes in Sekunden: der erste Wert gilt für das Dashboard und das Panel, der zweite für JeeMate. Wird kein Wert angegeben, wird das in den allgemeinen Einstellungen festgelegte Intervall übernommen.
- **Auf dem Panel anzeigen**: Aktivieren Sie dieses Kontrollkästchen, damit die Kamera auf dem Panel sichtbar ist.
- **Position auf dem Panel**: Reihenfolge, in der die Kamera auf dem Panel angezeigt wird (1, 2, 3…). Kameras ohne Position werden nach den anderen angezeigt. Bei der Erstellung der Kamera übernimmt die Position die in Frigate festgelegte Reihenfolge (**``ui -> order``**).
- **Videostream**: Geben Sie einen anderen Stream als den Standard ein, falls dieser nicht geeignet ist (rtsp://URL_Frigate:8554/Kameraname)
- **Anzahl der Voreinstellungen**: Anzahl der zu importierenden PTZ-Voreinstellungen, falls Sie eine andere Anzahl als die globale Einstellung wünschen (maximal 10).
- **Qualität der Snapshots**: Komprimierungsqualität der hochgeladenen Bilder, von 1 bis 100 (Standardwert: 70). Je niedriger der Wert, desto kleiner sind die Dateien. Gilt für Snapshots, Miniaturansichten und Screenshots.
- **Höhe der Snapshots**: Maximale Höhe der Bilder in Pixeln. Ein höheres Bild wird unter Beibehaltung des Seitenverhältnisses verkleinert. Ohne Angabe wird die Originalgröße beibehalten. Gilt nicht für Miniaturansichten.
- **In WEBP konvertieren**: Die Bilder werden im WebP-Format gespeichert, das weniger Speicherplatz beansprucht als JPEG.
- **Dashboard-Vorlage** und **Panel-Vorlage**: Zeigt auf dem Dashboard oder im Panel nur das Kamerabild an. Schaltflächen, PTZ-Steuerelemente und Erkennungssymbole werden ausgeblendet; ein Klick auf das Bild öffnet immer das vergrößerte Fenster mit den Aktionen.

Qualität, Höhe und Format gelten nur für Bilder, die nach ihrer Bearbeitung hochgeladen wurden.

Rechts sind die wenigen verfügbaren Parameter für die Anzeige zu sehen.
Das Bild wird entsprechend Ihrer Konfiguration aktualisiert.

- bbox
- Zeitstempel: Das Datum; dieses wird auch auf dem erstellten Snapshot angezeigt, sofern das entsprechende Kästchen angekreuzt ist.
- Zonen
- mask: Der Bereich wird ausgeblendet
- Bewegung: Der Bereich ist rot umrandet
- Region: der Bereich mit dem grünen Umriss

### Bedienung und Informationen
##### Alle Kameras
Informationen zum letzten Ereignis der Kamera: Kamera, Bezeichnung, Punktzahl, Höchstpunktzahl, Bereiche, ID, Typ, Zeitstempel, Dauer, Clip verfügbar, Schnappschuss verfügbar, URL des Schnappschusses, URL des Clips und URL des Miniaturbilds. Sowie die Statistiken der Kamera.

Die Angabe **LABEL** bezieht sich auf das Objekt, das die Erkennung ausgelöst hat (Person, Fahrzeug, Katze, Hund usw.).

- **RTSP**: Der Link zum Videostream der Kamera (siehe Abschnitt „Videostream“).
- **SNAPSHOT LIVE**: Der Link zum Live-Bild der Kamera für Plugins, die ein Kamerabild anzeigen.

##### MQTT
- **Erkennung läuft**: Sobald Frigate eine Veränderung feststellt, wechselt der Wert auf 1 (Wolken, Helligkeit, Person usw.)
- **Erkennung xxx**: Für jede Kamera wird ein Status hinzugefügt, der angibt, ob für jedes konfigurierte Objekt gerade eine aktive Erkennung stattfindet oder nicht. Wenn Sie beispielsweise eine Kamera haben, die eine Person, ein Fahrzeug, eine Kuh usw. erfasst, gibt es drei Status: Person, Kuh, Fahrzeug. Wenn Sie „Sichtbar“ aktivieren, wird das Symbol im Widget angezeigt, sobald eine Erkennung stattfindet. Das Symbol kann in den Einstellungen des Befehls angepasst werden. Wird ein Objekt als statisch eingestuft, wird die Erkennung auf 0 zurückgesetzt.
- **Erkennung „all“**: Wird ein sich bewegendes Objekt erkannt, wird der Befehl auf 1 gesetzt. Wenn Frigate keine Bewegung mehr erkennt oder das Objekt stillsteht, wird der Befehl wieder auf 0 gesetzt. Liegt der Befehl „all“ auf 0, werden die anderen Erkennungsbefehle zwangsweise auf 0 gesetzt.
- **Status der Erkennungs-/Aufzeichnungs-/Audio-Streams** (Frigate 0.18 oder höher, standardmäßig ausgeblendet): online, offline oder deaktiviert für jeden Kamerastream. Frigate startet einen Offline-Stream neu, daher kann der Wert zwischen „offline“ und „online“ wechseln: Warten Sie, bis der Wert stabil bleibt, bevor Sie Maßnahmen ergreifen, beispielsweise mit einer Zeitbedingung im Szenario.

##### Anerkennung
Sind in Frigate die Gesichtserkennung, die Kennzeichenerkennung, Klassifizierungsmodelle oder generative KI aktiviert, erhält die Kamera über MQTT das Ergebnis der letzten Erkennung. Die Befehle werden beim ersten Ergebnis erstellt.
- **Erkennung – Typ**: Gesicht, Kfz-Kennzeichen (LPR), Klassifizierung oder Beschreibung
- **Erkennung – Name** und **Erkennung – Wert**: die erkannte Person oder das erkannte Kennzeichen bzw. das Klassifizierungsmodell mit dem Wert in %
- **Erkennung – Kfz-Kennzeichen**: Das gelesene Kennzeichen
- **Erkennung – Label** und **Erkennung – Attribute**: das Ergebnis eines Objektklassifikationsmodells
- **Erkennung – Beschreibung**: Die von der KI generierte Beschreibung
- **Erkennung – Status xxx**: Der aktuelle Status eines für die Kamera konfigurierten Statusklassifizierungsmodells

### Befehle und Aktionen
- **Eine Veranstaltung erstellen**: siehe Seite „Events“.
- **Screenshot**: Status, Screenshot (siehe „Erstellen eines Screenshots“).
- **(Konfiguration) Kamera**: Status, aktivieren, deaktivieren, umschalten. Diese Befehle ändern die Konfigurationsdatei von Frigate: Damit die Änderungen wirksam werden, muss der Server neu gestartet werden.

Um die folgenden Aktionsbefehle nutzen zu können, muss MQTT verwendet werden. Andernfalls werden die Befehle nicht erstellt. Bitte lesen Sie die Frigate-Dokumentation zur Konfiguration Ihres MQTT-Servers. Jede Aktion verfügt über einen Status sowie die Befehle „on“, „off“ und „toggle“.

- **Detect**: Objekterkennung
- **Snapshot**: Ereignissnapshots
- **Aufzeichnung**: Aufzeichnung
- **Motion**: Bewegungserkennung (die Einstellung „OFF“ ist nur möglich, wenn „detect“ ebenfalls auf „OFF“ steht)
- **enabled**: Schaltet die Kamera sofort ein oder aus, ohne die Konfigurationsdatei zu ändern. Ab Frigate 0.18 bleibt der Status beim Neustart von Frigate erhalten; zuvor kehrt die Kamera zu ihrer ursprünglichen Konfiguration zurück.
- **review_alerts** und **review_detections**: Warnmeldungen und Erkennungen von Kameraaktivitäten bis zum Neustart von Frigate. Das Plugin empfängt neue Ereignisse in Echtzeit über die Aktivitäten: Wenn keine Warnmeldungen oder Erkennungen mehr vorliegen, erhält es keine weiteren Meldungen mehr.
- **review_descriptions** und **object_descriptions**: Durch generative KI erstellte Beschreibungen der Aktivitäten und der überwachten Objekte bis zum Neustart von Frigate
- **Benachrichtigungen**: Benachrichtigungen von Frigate für die Kamera (nicht die von Jeedom)
- **improve_contrast**: Kontrastverbesserung für die Bewegungserkennung

PTZ-, Preset- und Audio-Befehle werden nur erstellt, wenn die Konfiguration Ihres Frigate-Servers die entsprechenden Informationen enthält.
- **PTZ**: links, rechts, oben, unten, Stopp, Vergrößern, Verkleinern
- **Audio**: Status, Ein, Aus, Umschalten
- **Voreinstellung**: Die Funktion, mit der Sie Ihre Kamera auf einen bestimmten Punkt ausrichten können.

### HTTP-Befehle
Auf der Registerkarte **PTZ & HTTP** einer Kamera erstellt die Schaltfläche **HTTP-Befehl hinzufügen** einen Aktionsbefehl, der die angegebene URL aufruft, beispielsweise um eine Kamerafunktion zu steuern, die Frigate nicht bietet.

In der URL werden **``#user#``** und **``#password#``** durch die Benutzer-ID und das Passwort des Geräts ersetzt, zum Beispiel:
**``http://192.168.1.50/cgi-bin/api.cgi?cmd=Snap&user=#user#&password=#password#``**

Der Aufruf nutzt zudem die Digest-Authentifizierung mit dieser Kennung und diesem Passwort. Die Antwort der Kamera wird im Befehl „info **HTTP-Status**“ protokolliert. Das Passwort wird in den Protokollen maskiert.

HTTP-Befehle werden zunächst ausgeblendet erstellt. Sobald sie sichtbar gemacht werden, erscheinen sie zusammen mit den Voreinstellungen in der Dropdown-Liste der Aktionen des Widgets. Über die Stiftschaltfläche des Befehls kann dessen URL geändert werden.

### Ereignisgesteuerte Aktion(en)
Ereignisgesteuerte Aktionen sind für die Geräte unter **Ereignisse** sowie für jedes einzelne Gerät unter **Kameras** verfügbar.
Die auf dem Gerät **Events** konfigurierten Aktionen werden durch Ereignisse von allen Kameras ausgeführt, **es sei denn, für diese sind bereits Aktionen konfiguriert und aktiviert.**
Wenn Sie gemeinsame Aktionen unter „Events“ des Geräts zusammenfassen und anschließend Aktionen für jede Kamera hinzufügen möchten, denken Sie daran, unter „Events“ des Geräts das Kontrollkästchen „Aktionen zulassen“ zu aktivieren.

<u>Ablauf der Aktion</u>:


![Ausführung einer Aktion](../images/frigate_Doc_ActionsEvents.png)
#### Allgemeine Geschäftsbedingungen
Geben Sie hier an, in welchen Fällen die Aktionen **NICHT** ausgeführt werden sollen.

Beispielsweise konfigurieren Sie die Bedingung wie folgt:
**#[Haus][Wohnstil][Mode]# == „aktuell“**
Die Aktionen werden nur ausgeführt, wenn der Modus ein anderer als der aktuelle ist.


#### Maßnahmen
Hier können Sie festlegen, welche Aktionen bei jedem neuen Ereignis ausgeführt werden sollen.

Über ein Kontrollkästchen können Sie die Überprüfung der allgemeinen Bedingung deaktivieren.

<u>LABEL</u>:
**Zur Erinnerung: Das Label löst die Erkennung aus (Person, Fahrzeug, Tier usw.)**
Im Feld **Label** müssen Sie lediglich das oder die Labels angeben, für die die Aktion ausgeführt werden soll.
Wenn dieses Feld **leer** ist oder Sie **all** eingeben, wird die Aktion bei allen neuen Ereignissen ausgeführt.
Sie können mehrere Bezeichnungen angeben, indem Sie diese durch Kommas trennen.
Groß- und Kleinbuchstaben sowie Akzente werden ignoriert. Wenn Sie also „Vélo“ oder „velo“ eingeben, werden beide als identisch angesehen.

<u>TYP</u>:
**Mit** MQTT können sie vom Typ **new**, **update** und **end** sein.
**Ohne** MQTT ist der Typ immer **end**.
Im Feld **Typ** geben Sie einfach den Typ an, für den die Aktion ausgeführt werden soll.
Sie können mehrere Angaben machen, indem Sie diese durch Kommas trennen.
Wenn kein Typ angegeben ist, wird die Aktion nur für Ereignisse vom Typ **end** ausgeführt.
Groß- und Kleinbuchstaben sowie Akzente werden ignoriert. Wenn Sie also „update“ oder „UPDATE“ eingeben, werden beide als identisch angesehen.

<u>ZONEN</u>:

Im Feld **Eingangsbereich** müssen Sie lediglich den oder die Bereiche angeben, für die die Aktion ausgeführt werden soll.
Sie können mehrere Bereiche angeben, indem Sie diese durch Kommas trennen.

Über das Feld **Ausgangsbereich** lässt sich die Erkennungsrichtung festlegen. Dies funktioniert nur, wenn ein Eingangsbereich definiert ist. Wird der Eingangsbereich vor dem Ausgangsbereich ausgelöst, wird die Aktion ausgeführt.

Groß- und Kleinschreibung sowie Akzente werden ignoriert. Wenn Sie also „Allée“ oder „allee“ eingeben, werden beide als identisch angesehen.

<u>AKTIONSBEDINGUNGEN</u>:
Geben Sie hier an, in welchen Fällen die Aktionen **ausgeführt werden MÜSSEN**.

Beispielsweise konfigurieren Sie die Bedingung wie folgt:
**#[Haus][Wohnstil][Mode]# == „nicht vorhanden“**
Die Aktionen werden nur ausgeführt, wenn der Modus auf „Abwesend“ eingestellt ist.

Wenn keine Bedingung angegeben ist, wird die Aktion ausgeführt.

<u>GUT ZU WISSEN</u>:
- Eine Aktion, die **#clip#** oder **#clip_path#** verwendet, wird nur ausgeführt, wenn der Clip verfügbar ist. Ebenso wird eine Aktion, die **#snapshot#** oder **#snapshot_path#** verwendet, nur ausgeführt, wenn der Snapshot verfügbar ist.
- Ein Ereignis, das vor mehr als 3 Stunden begonnen hat, löst keine Aktion aus, beispielsweise beim Abrufen älterer Ereignisse.

<u>Für Bedingungen verfügbare Variablen:</u>
- **#camera#**: Name der Kamera
- **#score#**: Punktzahl in Prozent -> 82 %
- **#top_score#**: die maximale Punktzahl in Prozent -> 92 %

<u>Für Aktionen verfügbare Variablen:</u>
Es steht eine Liste von Variablen zur Verfügung, mit denen sich Aktionen individuell anpassen lassen. Diese Variablen werden bei der Ausführung der Aktion durch ihren Wert ersetzt.
- **#time#**: die aktuelle Uhrzeit im 12:00-Format
- **#event_id#**: die Frigate-ID des Ereignisses
- **#type#**: Der Typ des Ereignisses: new, update oder end
- **#camera#**: Name der Kamera
- **#cameraId#**: die ID der Kamera (z. B. für einen Deeplink zur Kameraseite in der JeeMate-App)
- **#score#**: Punktzahl in Prozent -> 82 %
- **#has_clip#**: Text 0 oder 1
- **#has_snapshot#**: Text 0 oder 1
- **#top_score#**: die maximale Punktzahl in Prozent -> 92 %
- **#Zonen#**: Text, durch Kommas getrennte Zonen
- **#description#**: Die von genAI generierte Beschreibung des Ereignisses (natürlich muss genAI auf dem Frigate-Server aktiviert sein)
- **#sublabel#**: Das von einem Objektklassifizierungsmodell von Frigate zugewiesene Label
- **#attributes#**: Die von einem Objektklassifizierungsmodell von Frigate zugewiesenen Attribute
- **#snapshot#**: Link zur Bilddatei
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#snapshot_path#**: Pfad zur Bilddatei
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#clip#**: Link zur MP4-Datei
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#clip_path#**: Pfad zur MP4-Datei
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#thumbnail#**: Link zur Bilddatei
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#thumbnail_path#**: Pfad zur Bilddatei
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#preview#**: Link zur Vorschau-Datei
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#preview_path#**: Pfad zur Vorschau-Datei
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#label#**: Text
- **#start#**: Startzeit
- **#end#**: Endzeit
- **#Dauer#**: Dauer des Ereignisses
- **#jeemate#**: siehe Erläuterungen weiter unten



### Beispiele für Benachrichtigungen:
#### JeeMate-Plugin
- **Snapshot**: im Feld „Titel“: **``title=Ihr Titel;;bigPicture=#snapshot#``**
- **Vorschau**: im Feld „Titel“: **``title=Ihr Titel;;bigPicture=#preview#``**
- **thumbnail**: im Feld „Titel“: **``title=Ihr Titel;;bigPicture=#thumbnail#``**
- **Clip**: Im Feld „Titel“: **``title=Ihr Titel;;bigPicture=#clip#``**

Für eine automatische Benachrichtigung fügen Sie „frigate=#jeemate#“ hinzu, verfügbar mit der kommenden Version 3 von JeeMate

- **Snapshot**: Im Feld „Titel“: **``title=Ihr Titel;;bigPicture=#snapshot#;;frigate=#jeemate#``**
- **Clip**: Im Feld „Titel“: **``title=Ihr Titel;;bigPicture=#clip#;;frigate=#jeemate#``**

#### Telegram-Plugin
Testen Sie die beiden Snapshot-Befehle. Je nach Konfiguration kann es vorkommen, dass einer der beiden nicht funktioniert.
- **Snapshot**: Im Feld „Optionen“: **``title=Ihr Titel | snapshot=#snapshot#``**
- **Snapshot**: Im Feld „Optionen“: **``title=Ihr Titel | file=#snapshot_path#``**
- **Clip**: Im Feld „Optionen“: **``title=Ihr Titel | file=#clip_path#``**
- **Vorschau**: im Feld „Nachricht“: **``#preview#``**

#### Plugin Mobile v2
- **Snapshot**: Im Feld „Nachricht“: **``Ihre Nachricht | file=#snapshot_path#``**
- **Clip**: Keine Ahnung

#### JeedomConnect-Plugin
- **Snapshot**: Im Feld „Titel“: **``title=Ihr Titel | files=#snapshot_path#``**
- **Clip**: Im Feld „Titel“: **``title=Ihr Titel | files=#clip_path#``**

#### NTFY-Plugin
- **Snapshot**: Im Feld „Optionen“: **``Title:Ihr Titel;Attach:#snapshot#``**
- **Clip**: im Feld „Optionen“: **``Title:Ihr Titel;Attach:#clip#``**

# <u>Veranstaltungen</u>

### Veranstaltung:
![Veranstaltungsrahmen](../images/frigate_Doc_Evenement.png)
1. - Veranstaltung zu den Favoriten hinzufügen
2. - Link zur Kamera
3. - Snapshot anzeigen (einfach auf das Symbol klicken)
   - Snapshot herunterladen (Doppelklick auf das Symbol)
4. - Video ansehen (einfach auf das Symbol klicken)
   - Clip herunterladen (Doppelklick auf das Symbol)
5. - Ereignis löschen
6. - Beschreibung des Ereignisses (sofern genAI aktiviert ist)

**ACHTUNG**: Die Schaltfläche „**Löschen**“ löscht das Ereignis sowohl in der Jeedom-Datenbank als auch auf Ihrem Frigate-Server. Ich übernehme keinerlei Haftung für eine unsachgemäße Verwendung dieser Schaltfläche. Es erscheint jedoch ein Bestätigungs-Popup.

Als Favoriten markierte Ereignisse werden nicht gelöscht.


![Veranstaltungsseite](../images/frigate_Doc_Evenements.png)

1. - **Alle sichtbaren Ereignisse löschen**
**ACHTUNG**: Die Schaltfläche „**Alle sichtbaren Ereignisse löschen**“ tut genau das, was sie verspricht. Wenden Sie daher vor dem Löschen unbedingt die richtigen Filter an: Ein Rückgängigmachen ist nicht möglich. Es erscheint ein Bestätigungs-Popup. Das Löschen erfolgt sowohl in der Jeedom-Datenbank als auch auf Ihrem Frigate-Server.

2. - **Manuelles Erstellen eines Ereignisses**
In den allgemeinen Einstellungen des Frigate-Plugins können Sie die Standardwerte für manuell erstellte Ereignisse festlegen.
Auf der Seite **Events** finden Sie eine Schaltfläche, über die Sie ein neues Ereignis erstellen können.
Für jede Kamera können Sie mithilfe eines Aktionsbefehls auch ein Ereignis erstellen.
Dieser Befehl ist vom Typ „Nachricht“. Wenn Sie das Feld leer lassen, werden die Standardparameter verwendet (im Widget ist dies immer der Fall).
Titel: **``Bezeichnung angeben``**
Meldung: **``score=80 | video=1 | duration=20 | pre_capture=5``**
**pre_capture** (Frigate 0.18 oder höher): Anzahl der Sekunden, die vor der Erzeugung des Ereignisses aufgezeichnet werden. Ohne diesen Parameter wendet Frigate die Voraufzeichnung der Kamera an.
Was die Länge der Clips angeht, muss man auch berücksichtigen, dass Frigate vor und nach dem Video Zeit hinzufügt – standardmäßig 5 Sekunden. Wenn Sie also 20 Sekunden einstellen, erhalten Sie ein 30-Sekunden-Video.
Achtung bei manuell erstellten Ereignissen: Wenn in Ihrer Frigate-Konfiguration unter **``record -> retain -> mode``** die Option **``motion``** eingestellt ist, sind die Clips nur verfügbar, wenn eine Bewegung erkannt wird. Stellen Sie die Option auf **``all``**, wenn Sie alle Clips erhalten möchten.

3. - **Ereignisse filtern**
Nur Ereignisse einer oder mehrerer Kameras anzeigen, nur eines bestimmten Labels oder einer bestimmten Ereignisart, nur Ereignisse der laufenden Woche oder des laufenden Jahres usw.

# Erstellen eines Screenshots

Sie möchten kein Ereignis manuell erstellen, sondern eine Momentaufnahme der Kamera erhalten? Sie können eine Aktion für die Kamera erstellen, die das Bild der Kamera aufnimmt.

In den Kameraaktionen befinden sich zwei Befehle:
- Ein Bild aufnehmen (Aktion)
- Bild-URL (Info)

Jeder Screenshot wird außerdem als Ereignis mit dem Label **Screenshot** gespeichert und ist auf der Seite „Ereignisse“ einsehbar. Screenshots werden nach denselben Regeln wie Ereignisse gelöscht (Alter und Größe des Ordners „data“): Setzen Sie diejenigen, die Sie behalten möchten, als Lesezeichen.

Die URL hat das Format **``/plugins/frigate/data/snapshots/id_snapshot.jpg``**, um mit möglichst vielen Kommunikations-Plugins kompatibel zu sein.

Wenn Sie beispielsweise eine vollständige URL wünschen, können Sie Folgendes in die Konfiguration, Berechnung und Rundung des Befehls „info“ einfügen:
**``str_replace('"','',"https://monjeedom.eu.jeedom.link"#value#)``**

Oder für diejenigen, die den Pfad benötigen:

**``str_replace('"','',"/var/www/html"#value#)``**

# <u>Frigate-Konfiguration</u>

**ACHTUNG**: Die Änderung der Konfiguration des Frigate-Servers erfolgt auf eigene Gefahr! Es wird kein Support geleistet!

# <u>Logs Frigate</u>
Alle Protokolle Ihres Frigate-Servers anzeigen

# <u>Cron</u>
**Wenn Sie MQTT nicht verwenden**: Mit einem regelmäßigen Cron-Job können Sie die neuesten Ereignisse abrufen und somit die zugehörigen Aktionen ausführen.

**Wenn Sie MQTT verwenden**: Alle neuen Ereignisse werden automatisch empfangen, ein stündlicher oder täglicher Cron-Job reicht aus: Damit können die Informationen zum Ereignis aktualisiert werden.

Lassen Sie in jedem Fall mindestens einen aktiven Cron-Job aktiv, da jedes Mal überprüft wird, ob die gesicherten Dateien tatsächlich einem Ereignis entsprechen; ist dies nicht der Fall, werden sie gelöscht.

Nur cronDaily überprüft die Version Ihres Frigate-Servers: Wenn ein Update verfügbar ist, erhalten Sie eine Benachrichtigung.

**Mein Tipp:**
Ohne MQTT: cron oder cron5 (je nach Rechnerleistung) + cronDaily
Mit MQTT: cronDaily

***In jedem Fall wird, wenn gerade ein Cron-Job ausgeführt wird, der nächste nicht gestartet, und bei MQTT sind die Cron-Jobs (1, 5, 10 und 15) deaktiviert.***

# <u>Widget</u>
Dort finden Sie das Kamerabild und die markierten Schaltflächen:
- Ein Klick auf das Bild öffnet ein vergrößertes Fenster mit den Aktionen, den PTZ-Befehlen und den Voreinstellungen;
- die Schaltflächen „Aufzeichnung“, „Snapshots“, „Erkennung“, „Audio“ und „Bewegung“, „Ereignis erstellen“ und „Aufnahme“;
- Die Dropdown-Liste der Aktionen enthält die sichtbaren PTZ-Voreinstellungen und HTTP-Befehle;
- Das Schraubenschlüssel-Symbol öffnet das KI-Fenster: Kamera aktivieren, Warnmeldungen und Aktivitätserkennung, Beschreibungen durch generative KI;
- Ein Symbol zeigt die Ereignisse der Kamera auf der Seite „Events“ an;
- die Symbole der derzeit erkannten Objekte, für die Befehle **Erkennung xxx** sichtbar sind.

Eine Schaltfläche wird nur angezeigt, wenn ihre Befehle sichtbar sind.

# <u>Videostream</u>
### Konfiguration
Im Frigate-Plugin **gibt es keinen Player für den Videostream**; diese Konfiguration dient für kompatible Plugins.

Die im Plugin gespeicherte URL des Videostreams ist die Ihres Frigate-Servers und nicht die der Kamera.

1. **RTSP-Stream von Frigate**:
   - **Vorteile**: Frigate kann die Streams mehrerer Kameras zentralisieren, wodurch sich die Anzahl der direkten Verbindungen zu den einzelnen Kameras verringert. Dies kann die Stabilität verbessern und die Verwaltung der Netzwerkressourcen optimieren.
   - **Nachteile**: Die Einrichtung kann etwas aufwendiger sein, insbesondere wenn Sie mehrere Kameras mit unterschiedlichen Einstellungen haben.

2. **RTSP-Stream der Kamera**:
   - **Vorteile**: Die direkte Nutzung des RTSP-Streams der Kamera kann einfacher zu konfigurieren sein, insbesondere wenn Sie nur eine Kamera haben oder keine zwischengeschaltete Software verwenden möchten.
   - **Nachteile**: Jedes Gerät stellt eine direkte Verbindung zur Kamera her, was die Belastung des Netzwerks und der Kamera selbst erhöhen kann.

Zusammenfassend lässt sich sagen: Wenn Sie mehrere Kameras haben und eine zentrale Verwaltung wünschen, könnte der RTSP-Stream von Frigate vorteilhafter sein. Wenn Sie eine einfachere und direktere Lösung bevorzugen, könnte die Nutzung des RTSP-Streams der Kamera ausreichend sein.

### Mit JeeMate
Wenn Ihre Frigate-Konfiguration mehrere Videostreams pro Kamera umfasst, müssen Sie im Feld „Videostream“ Ihres Geräts angeben, welchen Sie verwenden möchten. Das Gleiche gilt, wenn Sie den Original-Videostream der Kamera verwenden möchten.

Frigate-Konfiguration mit einem einzigen Feed; hier muss ich den Feed nicht angeben, der Standard-Feed ist ausreichend.

```yaml
frigate1:
  ffmpeg:
    inputs:
      - path: rtsp://127.0.0.1:8554/frigate1
```

Frigate-Konfiguration mit mehreren Feeds: Geben Sie die URL des gewünschten Feeds auf der Seite Ihres Geräts an. Der Standard-Feed ist nicht geeignet. Ersetzen Sie 127.0.0.1 durch die IP-Adresse des Frigate-Servers.

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

***Achtung: Sie werden unter keinen Umständen aufgefordert, die Konfiguration auf Frigate zu ändern***

Nach jeder Änderung der Feed-URL im Frigate-Plugin müssen Sie die Änderungen auch im JeeMate-Plugin speichern und anschließend eine vollständige Synchronisierung in der App durchführen.

# <u>Panel</u>
Vergessen Sie nicht, die Seite „Panel“ in den allgemeinen Einstellungen zu aktivieren und anschließend für jede Kamera das Kontrollkästchen „Panel“ anzukreuzen.

- Kameraansicht.
- Veranstaltungsseite

# <u> FAQ </u>

### Das Plugin ist zwar korrekt für MQTT konfiguriert, es wird jedoch keine Aktion ausgeführt
Das Thema „frigate/reviews“ bezieht sich auf die Review-Elemente (erkannte Aktivitätszeiträume), die nach der Erkennung und Aufzeichnung der Objekte generiert werden. Dieses Überprüfungssystem stützt sich in hohem Maße auf die Aufzeichnungsfunktion (Recording), um zu funktionieren:

Frigate organisiert die zu überprüfenden Elemente als Zeitfenster, in denen mehrere Erkennungen zusammengefasst sind

Wenn die Aufzeichnung deaktiviert ist (record.enabled: false), werden keine Videosequenzen gespeichert, und daher erstellt die Plattform keine Review-Elemente → es wird nichts in frigate/reviews veröffentlicht.

Damit das Plugin funktioniert, benötigt es daher:

```yaml
record:
  enabled: true
```

### Beispiel für eine Konfigurationsdatei
Bitte beachten Sie, dass es sich hierbei um meine Datei und meine Einstellungen handelt, die für meine Situation funktionieren. Es liegt an Ihnen, diese anzupassen oder mit Ihren eigenen zu vergleichen, falls nicht alle Funktionen des Plugins bei Ihnen funktionieren sollten.

Ich übernehme keine Haftung für etwaige Fehlfunktionen, die durch diese Konfiguration verursacht werden. Sie müssen die Konfiguration daher an Ihren eigenen Server und Ihre Anforderungen anpassen.

Ich habe Erläuterungen hinzugefügt, um Ihnen zu helfen.

 ```yaml
 # Rappel, le plugin mqtt-manager nécéssite un broker mqtt sécurisé.
 mqtt:
  host: 192.168.2.22        # Adresse IP de votre serveur MQTT
  port: 1883                # Port du broker MQTT (1883 = standard non sécurisé)
  user: ***                 # Nom d'utilisateur (masqué ici)
  password: ***             # Mot de passe (masqué ici)
  stats_interval: 300       # Fréquence (en secondes) des messages de statistiques MQTT

detectors:
  coral:
    type: edgetpu           # Utilise un accélérateur Coral (Edge TPU) pour la détection
    device: usb             # Type de connexion : USB

ffmpeg:
  hwaccel_args: preset-intel-qsv-h264  # Accélération matérielle Intel Quick Sync pour le décodage vidéo

timestamp_style:
  position: tr              # Position du timestamp sur l’image (tr = top-right = coin supérieur droit)
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
  enabled: true             # Active globalement la détection d'objets pour toutes les caméras (ajouté automatiquement par frigate 0.16)

snapshots:
  enabled: true             # Active les captures d’image (snapshots) lors des événements
  clean_copy: true          # Génère une version sans annotation (utile pour archivage ou IA)
  timestamp: false          # Ne superpose pas la date/heure sur les images
  bounding_box: false       # Ne dessine pas de boîte de détection sur les images
  crop: false               # Ne recadre pas automatiquement l’objet détecté
  retain:                   # Durée de conservation des images
    default: 3              # Par défaut, conserve les snapshots 3 jours
    objects:
      personne: 7           # Conserve ceux contenant une "personne" pendant 7 jours
      vehicule: 3           # Conserve ceux contenant un "vehicule" pendant 3 jours

record:
  enabled: true             # Active l’enregistrement vidéo, obligatoire pour que le plugin reçoive les événements.
  retain:
    days: 1                 # Conserve les enregistrements pendant 1 jour
    mode: all               # Enregistre tout, même sans détection
  alerts:
    retain:
      days: 7               # Conserve les clips d’alerte pendant 7 jours
      mode: active_objects  # Seulement si un objet actif a été détecté
    pre_capture: 5          # Enregistre 5 secondes avant le début de l’événement
    post_capture: 5         # Enregistre 5 secondes après la fin de l’événement
  detections:
    retain:
      days: 7               # Conserve les clips avec détection pendant 7 jours
      mode: active_objects
    pre_capture: 3
    post_capture: 5

semantic_search:
  enabled: true             # Active l’analyse sémantique des événements (IA)
  reindex: false            # Ne re-analyse pas les anciens événements au démarrage

genai:
  enabled: true             # Active l’intégration IA (Google Gemini ici)
  provider: gemini          # Fournisseur de l’IA
  api_key: ***              # Clé API Gemini (masquée ici)
  model: gemini-1.5-flash   # Modèle utilisé pour l’analyse comportementale
  object_prompts:          # Prompts personnalisés pour chaque type d’objet, a vous de l'adapter si besoin.
    personne: >
      Commence IMMÉDIATEMENT et DIRECTEMENT la description de l'action...
    vehicule: >
      Décris IMMÉDIATEMENT et DIRECTEMENT le comportement du véhicule...
    animale: >
      Analyse IMMÉDIATEMENT et DIRECTEMENT le comportement de l'animal...

cameras:
  frigate1:                 # Nom de la caméra
    detect:
      fps: 5                # Taux d’analyse des images pour la détection
      enabled: true         # Active la détection pour cette caméra
      width: 640            # Largeur du flux vidéo analysé
      height: 360           # Hauteur du flux vidéo analysé
      stationary:
        interval: 50        # Vérifie les objets immobiles toutes les 50 frames
        threshold: 30       # Seuil de mouvement à partir duquel un objet est considéré comme "mobile"
    ffmpeg:
      inputs:
        - path: rtsp://127.0.0.1:8554/frigate1_high  # Flux principal haute résolution
          input_args: preset-rtsp-restream
          roles:
            - record        # Utilisé pour l’enregistrement
        - path: rtsp://127.0.0.1:8554/frigate1_low   # Flux secondaire basse résolution
          input_args: preset-rtsp-restream
          roles:
            - detect        # Utilisé pour la détection
    objects:
      track:                # Liste des objets à détecter
        - personne
        - vehicule
        - animale
      filters:              # Filtres pour chaque type d’objet
        personne:
          min_score: 0.65   # Score minimum pour commencer à suivre
          threshold: 0.7    # Score minimum pour déclencher un événement
        vehicule:
          min_score: 0.7
          threshold: 0.8
        animale:
          min_score: 0.7
          threshold: 0.8

go2rtc:
  streams:                  # Flux vidéo déclarés pour usage interne (re-streaming)
    frigate1_low: rtsp://***:***@192.168.2.36:554/2   # Flux basse qualité
    frigate1_high: rtsp://***:***@192.168.2.36:554/1  # Flux haute qualité

version: 0.16-0             # Version utilisée de Frigate
```


