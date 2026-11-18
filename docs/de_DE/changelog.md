# Änderungsprotokoll des Frigate-Plugins

>**WICHTIG**
>
>Wenn keine Informationen zum Update vorhanden sind, bedeutet dies, dass es sich ausschließlich um eine Aktualisierung der Dokumentation, der Übersetzung oder des Textes handelt.

# 11.05.2026 Beta & Stable 1.5.5
- Korrektur von ERROR-Protokollen

# 06.05.2026 Beta & Stable 1.5.4
- Neue Schaltfläche zum Herunterladen der Ereignisliste (nützlich für die Fehlersuche bei der Entwicklung)

# 19.04.2026 Beta 1.5.3
- Korrektur des Put-Curl-Befehls zum Ein- und Ausschalten der Kameras über die API

# 13.04.2026 Beta 1.5.2
- Korrektur am Dashboard-Widget

# 09.04.2026 Beta 1.5.1
- Verbesserung des Prozesses zur Bereinigung von Ereignissen
- Korrektur der Groß-/Kleinschreibung bei cron über HTTP

# 05.04.2026 Stable 1.5.0
- Nachfolgend finden Sie Informationen zu den Beta-Versionen.

# 21.03.2026 Beta 1.4.97
- Hinzufügen des Befehls zur Aktivierung der Kameras über MQTT
- Deaktivierung des Cron-Jobs für deaktivierte Kameras

# 21.03.2026 Beta 1.4.96
- Bereinigung der Ereignisliste

# 16.03.2026 Beta 1.4.95
- Bereinigung der Befehlsnamen auf die gleiche Weise wie bei Jeedom
- Korrektur der Klassifizierung „sub_label“ – ich hatte ein „s“ (sub_labels) eingefügt

# 15.03.2026 Beta 1.4.94
- Funktion zum Sortieren der Bestellungen (Klick auf den Namen oder die ID)

# 15.03.2026 Beta 1.4.93
- Erstellung von Befehlen für Informationen zu Klassifizierungsstatus
- Hinzufügen von Ein-/Aus-Schaltflächen auf dem Bedienfeld

# 10.03.2026 Beta 1.4.9
- Neue Befehle für die Gesichtserkennung hinzugefügt
- Neue Befehle für die Kennzeichenerkennung hinzugefügt
- Neue genAI-Befehle hinzugefügt
- Behebung eines Fehlers unter Debian 12 und Debian 13

# 09.11.2025 Beta 1.4.7
- Änderung der Spalte „data“ in der Datenbank
- Protokolle hinzufügen
- kleine Fehlerbehebungen

# 01.11.2025 Beta 1.4.6
- Befehle für die Gesichtserkennung hinzugefügt
- Befehle für die Kennzeichenerkennung hinzugefügt

# 30.10.2025 Beta 1.4.5
- Position auf dem Bedienfeld hinzufügen (wird berücksichtigt, wenn eine Frigate-Einstellung vorhanden ist)
- Konfiguration für Bildqualität und Bildhöhe hinzugefügt
- Möglichkeit, die Aufnahmen in das WebP-Format zu konvertieren
- Checkbox zum Ausblenden der Schaltflächen in den Dashboard- und Panel-Vorlagen
- Das Dashboard-Widget (und das Design) lässt sich in Höhe und Breite frei anpassen
- Das Widget-Panel hat feste Abmessungen in Höhe und Breite (435px und 315px)
- Fehlerbehebung (danke @t0urista)

# 22.09.2025 Stable 1.4.2
- PHP-Warnung beheben
- Korrekturprotokolle

# 12.07.2025 Stable 1.4.0
- Hinzufügen der FAQ zur Dokumentation

# 05.07.2025 Beta 1.3.6
- Download per Doppelklick hinzufügen.

# 04.07.2025 Beta 1.3.5
- JS-Fehler im Dashboard behoben.
- Behebung eines Fehlers bei Ereignissen, die sich über mehrere Monate erstrecken.
- Spaltenbreite auf den Veranstaltungsseiten festgelegt. (Danke an @vegeta0911)
- Popup-Fenster „Veranstaltungen“ im Bereich „Schönheit“.
- Es wurde die Möglichkeit hinzugefügt, Screenshots und Videos herunterzuladen.

# 08.06.2025 Stable 1.3.3
- Vergleich der Ereignisse vor der Wiederherstellung
- Bearbeitungszeit: 0 Tage.

# 04.06.2025 Stable 1.3.2
- Hinzufügen der Variablen #camera#, #score# und #top_score# zu den Bedingungen

# 27.05.2025 Stable 1.3.1
- Korrektur bei Neuinstallation (Fehler in Version 1.3.0)
- Das Ausblenden auf dem Dashboard führt nicht zum Ausblenden auf dem Panel.

# 23.05.2025 Stable 1.3.0
- Jeedom-Version mindestens 4.4
- Debian-Version mindestens 11

# 08.05.2025 Stable 1.2.9
- Fehlerbehebung, wenn keine Verbindung zum Server besteht.

# 09.04.2025 Stable 1.2.5
- Befehl „info uptime“ hinzugefügt
- Befehl „info uptimeDate“ hinzugefügt

# 04.04.2025 Stable 1.2.4
- Befehl „info description“ hinzugefügt (die Funktionen des Plugins dennoch testen)

# 02.04.2025 Stable 1.2.3
- Verwaltung der genAI-Beschreibung
- Maßnahmen bei Erfüllung einer Bedingung festlegen

# 21.03.2025 Beta 1.2.2
- Bedingung für Aktionen hinzufügen

# 20.03.2025 Beta 1.2.1
- Hinzufügen eines Kontrollkästchens „Aktionen zulassen“ für Geräteereignisse

# 18.03.2025 Stable 1.2.0
- Update mit allen bisherigen Korrekturen.

# 18.03.2025 Beta 1.2.0
- Korrektur des Pfad-Snapshots

# 27.02.2025 Beta 1.1.9
- Hinzufügen von CPU- und Speicher-Statistiken

# 23.02.2025 Beta 1.1.8
- Hinzufügen von „frigateActions“- und „frigateMQTT“-Protokollen
- Korrekturen bei „variable snapshot“ bei den Befehlen „update“ und „new“
- Korrektur der Bild-URL (siehe Dokumentation, falls eine Änderung erforderlich ist)

# 19.02.2025 Beta 1.1.7
- Verwaltung des Ausgangsbereichs
- Korrektur bei der Anzeige der Konfigurationsdatei „frigate“ > 0.15

# 10.01.2025 Beta 1.1.6
- Behebung des Fehlers „cronDaily mySQL“

# 11.11.2024 Stable 1.1.5
- URL bereinigen

# 23.10.2024 Beta 1.1.3
- Hinzufügen des Feldes zu den Aktionen
- Korrektur der Ausführung von Aktionen

# 07.10.2024 Stable 1.1.2
- Überprüfung des Status des Frigate-Servers vor der Ausführung der Cron-Jobs

# 07.10.2024 Beta 1.1.1
- Hinzufügen von Binärbefehlen für erkannte Objekte

# 05.10.2024 Stable 1.1.0
- Details zu früheren Updates anzeigen.

# 04.10.2024 Beta 1.0.6
- Korrektur der Änderung des Audiowerts
- Aktualisierung der Statusangaben nur, wenn sie sich von der letzten unterscheiden

# 02.10.2024 Beta 1.0.5
- Behebung eines Fehlers beim Erstellen von Audiobefehlen
- Behebung eines Fehlers beim Erstellen von MQTT-Befehlen (Wert auf 1 zurückgesetzt)

# 01.10.2024 Beta 1.0.4
- Option, ob die Daten aus dem Jeedom-Backup ausgeschlossen werden sollen oder nicht
- PTZ-Pause hinzugefügt
- Hinzufügen von Server-Status- und Server-Verfügbarkeitsanzeige
- Die Bbox automatisch in den Snapshots speichern
- Optimierung des Cron-Jobs
- Behebung des Fehlers „file_get_content“, wenn Dateien nicht vorhanden sind
- Korrektur des Datumsfilters (Firefox)
- Option zur Anzeige der Kameras auf dem Bedienfeld

# 21.09.2024 Beta 1.0.3
- Option für RTSP-Streams (siehe Dokumentation)
- Erzwingt den generischen Typ des URL-Snapshots (führen Sie eine Suche durch oder speichern Sie jedes Gerät)
- Korrektur der Ereignisse auf der Miniaturansicht-Seite (Clip, Vorschau, nichts)

# 17.09.2024 Beta 1.0.2
- Hinzufügen der Variablen #preview# zu den Benachrichtigungen
- Auf der Seite „Events“ gibt es eine Vorschau beim Überfliegen sowie den Clip (der weniger Speicherplatz beansprucht).
- Die Filter werden gespeichert, damit sie beim nächsten Aufruf der Seite „Events“ angewendet werden.

# 16.09.2024 Beta 1.0.1
- Korrektur des Preset-Wahlschalters im Widget
- Behebung verschiedener JS-Fehler
- Konfiguration eines externen Links für den Zugriff auf Frigate hinzugefügt
- Bei Verwendung von MQTT werden Cron-Jobs mit einer Intervallzeit von weniger als 30 Minuten nicht ausgeführt.
- Bei neuen Installationen wird das Kontrollkästchen „Infos protokollieren“ nicht aktiviert (bei anderen Installationen denken Sie bitte daran, es zu deaktivieren).
- Einfügen einer Wartezeit vor dem Abrufen der Snapshots (unbedingt anschauen!)
- Bearbeitung der Namen von Preset- und HTTP-Befehlen möglich
- Korrektur des Kontrollkästchens für die Bedingungsausnahme, das bisher nur auf die erste Aktion angewendet wurde

# 14.09.2024 Stable 1.0.0
- Alles, was in den vorherigen Beta-Versionen enthalten war.

# 14.09.2024 Beta 0.9.7
- Korrekturen bei HTTP_ERROR und JS
- Schaltfläche zum Bearbeiten der URL des HTTP-Befehls
- Verbesserung des Panels
- Variablen #user# und #password#, falls in den HTTP-Befehlen erforderlich
- Neuanordnung der Befehle „Infos“ und „Aktionen“
- Einrichtung für die automatische Integration in JeeMate v3
- Kontrollkästchen, um die Bedingung für das Auslösen von Aktionen zu ignorieren

# 13.09.2024 Beta 0.9.6
- Korrekturen bei PTZ-Steuerbefehlen
- Ergänzung zum PTZ-Schaltflächen-Widget
- Hinzufügen einer Schaltfläche zum Erstellen von HTTP-Befehlen (Benutzername und Passwort müssen auf der Kameraseite eingegeben werden)

# 11.09.2024 Beta 0.9.5
- Änderung bei der Erstellung von Bestellungen.
- Audio-Befehle (Status, Ein, Aus und Umschalten) sind verfügbar, sofern sie in Ihrer Konfiguration vorhanden sind.
- Einmal tägliche Überprüfung der Frigate-Version (sofern cronDaily aktiviert ist).
- Korrektur, falls der Name an anderer Stelle bereits vorhanden ist (ausgeblendet oder mit Großbuchstaben)
- Anpassung des Widgets für das Dashboard und mobile Geräte
- Erstellung von PTZ-Voreinstellungsbefehlen (Konfiguration erforderlich)

# 06.09.2024 Beta 0.9.4
- Befehl „Screenshot erstellen“ hinzugefügt (siehe Dokumentation)
- Panel hinzufügen

# 05.09.2024 Beta 0.9.3
- Hinzufügen der Maske zur Kameraansicht.
- Aktualisierung der Snapshots bei dem Empfang von Daten.
- Verschiedene Änderungen und Verbesserungen auf der Seite „Events“.
- Schnellere Abfrage des Events bei „createEvent“, wenn MQTT nicht installiert ist.
- Übersetzungen

# 19.08.2024 Beta 0.9.2
- Korrektur der Aktionen vom Typ „Schlüsselwörter“.
- Korrektur des Filters „Typ“ bei der Ausführung von Aktionen.
- Korrektur der Akzente bei der Erstellung von Ereignissen.

# 17.08.2024 Beta 0.9.1
- Übersetzung ins Englische, Deutsche, Spanische, Italienische und Portugiesische. Danke @mips
- Korrektur der Ausführung von Aktionen.
- Neue Verwaltung für den Empfang von MQTT-Ereignissen (Frigate 0.14).
- Korrektur beim Erstellen eines manuellen Ereignisses.
- Verbesserung der Veranstaltungsseite.

# 10.08.2024 Beta 0.9.0
- Schaltfläche und Optionen zum Erstellen von Ereignissen hinzugefügt.
- Korrekturen des Fehlers „cron isFavorite“.
- Hinzufügen eines Editors für die Konfigurationsdatei (alle Änderungen erfolgen auf eigene Gefahr; lesen Sie bitte die offizielle Dokumentation zu Frigate sorgfältig durch und erstellen Sie zuvor eine Sicherungskopie der Konfiguration).
- Abruf der Protokolle vom Frigate-Server.
- Änderung der Verwaltung der Ordnerbereinigung und der Ereignisse.
- Viele weitere Änderungen.

# 26.07.2024 Beta 0.8.2
- Korrekturen bei der Wiederherstellung von Miniaturansichten
- Hinzufügen einer Schaltfläche zum Aufrufen der Kameraereignisse im Widget
- Kleine Korrekturen

# 26.07.2024 Beta 0.8.1
- Korrekturen, Wiederherstellung von Clips und Snapshots
- Farbe der Widget-Schaltflächen ändern
- Der Ordner „data“ wird bei Jeedom-Backups nicht mehr berücksichtigt

# 22.07.2024 Beta 0.8.0
- Hinzufügen der Variablen #thumbnail_path# und #thumbnail#
- Hinzufügen der MQTT2-Abhängigkeit
- Hinzufügen von Widgets für das Dashboard und mobile Geräte
- Veranstaltung zu den Favoriten hinzufügen
- Befehle zum Neustart hinzugefügt (Statistikgeräte)
- Hinzufügen einer Ausführungsbedingung zu den Aktionen
- Erstellung der Befehle „detect“, „snapshot“ und „recording“ (start, stop, toggle)
- Schaltfläche zum Erstellen von PTZ-Befehlen verfügbar
- Einstellung der Aktualisierungsintervalle
- Konfiguration der maximalen Größe des Speicherordners für Snapshots und Clips
- Änderung der Snapshot-Ansicht
- Debug-Schaltfläche hinzugefügt (Konfigurationsdatei)
- Discord-Schaltfläche hinzufügen
- Hinzufügen der Schaltfläche „Frigate-Server“
- Zahlreiche kleinere Korrekturen

# 22.06.2024 Beta 0.7.5
- Hinzufügen der Variablen #time#, #event_id#, #snapshot_path# und #clip_path#
- Schaltfläche zum Löschen aller Ereignisse hinzugefügt (siehe Dokumentation)
- Bestätigungs-Popup vor dem Löschen hinzufügen

# 20.06.2024 Beta 0.7.0
- Behebung eines Fehlers beim Anlegen von Geräten
- Behebung von Fehlern bei der Anzeige der Seite „Events“
- Behebung von Cron-Fehlern
- Hinzufügen von Filteroptionen auf der Seite „Events“
- Hinzufügen eines Links bei den Ereignissen, der zur Kamera führt, und eines Links bei der Kamera, der zu den Ereignissen führt.
- Hinzufügen eines Feldes „Bezeichnung“ für Aktionen (entweder leer, „alle“ oder Name der Bezeichnung), um die Aktion nur für eine bestimmte Bezeichnung auszulösen.

# 17.06.2024 Beta 0.6.0
- Protokolle hinzufügen
- Hinzufügen von Befehls-Events zur Auslösung des Cron-Jobs
- Änderung der Cron-Konfiguration: Verwenden Sie die Jeedom-Kontrollkästchen.
- Optionen zur Seite „Events“ hinzugefügt (danke @noodom)
- Standard-Raumkonfiguration

# 15.06.2024 Beta 0.5.0
- erste Beta-Version
