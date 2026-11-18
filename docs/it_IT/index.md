Plugin creato da **Sagitaz** e **Noodom**

# <u>Ringraziamenti</u>
Il plugin e l'assistenza sono gratuiti, ma se volete offrirmi un caffè o dei pannolini per il bambino, vi ringrazio in anticipo.

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/C1C61AKVV7)

# <u>Assistenza e supporto</u>
- Comunità Jeedom
- Discord JeeMate

Per qualsiasi richiesta di assistenza su Community o Discord, vi preghiamo di fornire il maggior numero possibile di informazioni (hardware, tipo di telecamera, versione di Jeedom, di Frigate, del vostro sistema, ecc...)

Nella pagina di configurazione, il pulsante "Assistenza" consente già di compilare automaticamente alcuni campi.

Assicurati di disporre dell'hardware compatibile con Frigate e che questo funzioni correttamente prima di richiedere assistenza sul plugin. (Consulta la documentazione ufficiale di Frigate per le configurazioni hardware consigliate).

Fornire anche i log in modalità debug (quelli del plugin e del server Frigate).

Non verrà fornita alcuna assistenza tramite mezzi di comunicazione diversi da quelli indicati.

Grazie


# <u>Prerequisiti</u>
- Jeedom 4.4.0 o versioni successive
- Debian 11 (Bullseye) o versione successiva
- Frigate 0.16.0 (versione minima)

Il plugin non installa né configura il server Frigate, pertanto è necessario installarlo e configurarlo autonomamente. Per ulteriori informazioni, consultare la documentazione ufficiale di Frigate.

# <u>Installazione</u>
Come per tutti gli altri plugin, dopo averlo installato è necessario attivarlo.

Il plugin sarà sempre compatibile con l'ultima versione stabile disponibile (il tempo necessario per l'adeguamento). Tuttavia, non verranno effettuati ulteriori sviluppi per garantire la compatibilità con le versioni precedenti. Pertanto, se qualcosa non funziona, provate innanzitutto ad aggiornare il vostro server Frigate prima di richiedere assistenza.

Al 30-09-2026 il plugin funziona con le seguenti versioni di Frigate:
- Frigate 0.18.0 Stabile

Frigate 0.16.0 è la versione minima. Alcune funzioni sono disponibili solo a partire da Frigate 0.18: stato dei flussi delle telecamere, profili e parametro pre_capture degli eventi creati manualmente. Non garantisco il supporto per le versioni precedenti del server Frigate.

# Modalità di connessione al server Frigate
### API
Il recupero delle immagini delle telecamere, degli eventi passati, l'eliminazione degli eventi, ecc...
Molte funzioni del plugin utilizzano la connessione all'API Frigate.
È accessibile tramite la porta 5000 sulla vostra rete locale; è indispensabile che l'abbiate configurata, altrimenti il plugin non potrà funzionare.
Se desiderate utilizzare un'altra porta, potete farlo purché effettuiate il mapping verso la porta 5000 (5054:5000) nella configurazione del server Frigate.
### MQTT
La configurazione MQTT consente di ricevere le informazioni dal server Frigate in tempo reale.
Questo migliora l'esperienza utente del plugin, ma non è necessario per il suo funzionamento.
È necessario un broker sicuro (user:mdp) affinché il plugin mqtt-manager, da cui dipende il plugin frigate, funzioni correttamente.


# <u>Log</u>
Il plugin contiene dei sottolog; affinché siano visibili su Jeedom 4.4.19, è necessario impostare i log globali al livello "info" minimo.

![livello dei log](../images/frigate_Doc_Logs.png)
# <u>Configurazione</u>
- **Stanza predefinita**: le telecamere create verranno automaticamente collocate in questa stanza.
- **Escludi dal backup**: se selezionato, la cartella "data" del plugin (istantanee, clip, miniature e schermate) viene esclusa dai backup Jeedom, che risultano così più leggeri. Dopo il ripristino di un backup di questo tipo, gli eventi non avranno più i relativi file: al loro posto verrà visualizzata un'immagine predefinita.
- **Versione del plugin**: la versione installata, in sola lettura. Da indicare nelle richieste di assistenza.

#### Configurazione Frigate
- **URL**: l'URL del vostro server Frigate (es.: 192.168.1.20)
- **Porta**: la porta del server Frigate (5000 per impostazione predefinita); è possibile utilizzare un'altra porta purché sia mappata alla 5000 (ad esempio 5054:5000); in caso contrario, l'API non funzionerà.
- **Indirizzo esterno**: Per accedere alla pagina del server Frigate dall'esterno.
- **Topic MQTT**: il topic del vostro server Frigate (frigate per impostazione predefinita)
- **Preset**: Per le telecamere PTZ, impostare il numero di posizioni che si desidera recuperare.
- **Pausa azione**: Pausa da applicare alle azioni PTZ. Ad esempio, dopo aver premuto "move up", viene automaticamente eseguita una pausa: è possibile impostare il tempo che precede questa pausa da 0 a 10, corrispondente a una pausa da 0 a 1 secondo (0, 0,1, 0,2, ecc...).

#### Gestione degli eventi
- **Recupero degli eventi**: è possibile che sul server Frigate siano presenti 30 giorni di eventi, ma che se ne vogliano importare solo 7 su Jeedom. Indicare qui il numero di giorni desiderato (7 per impostazione predefinita). Se il numero di giorni è 0, il processo viene interrotto e non viene effettuata alcuna chiamata all'API Frigate.
- **Eliminazione degli eventi**: Gli eventi più vecchi del numero di giorni indicato (7 per impostazione predefinita) verranno eliminati dal database Jeedom insieme ai relativi file, ma non dal server Frigate.

Il numero di giorni di cancellazione non può essere inferiore al numero di giorni di recupero. In caso contrario, verrà utilizzato il numero di giorni di recupero.

- **Dimensione delle cartelle**: dimensione massima della cartella "data", in MB (500 MB per impostazione predefinita). Se tale limite viene superato, gli eventi più vecchi vengono eliminati fino a quando la dimensione non torna al di sotto del limite.

Gli eventi aggiunti ai preferiti non vengono mai eliminati, né in base alla data di creazione né in base alle dimensioni. Le schermate acquisite manualmente seguono le stesse regole degli eventi: aggiungile ai preferiti per conservarle.

- **Frequenza di aggiornamento**: in secondi, la frequenza di aggiornamento degli snapshot delle telecamere. (5 secondi per impostazione predefinita). Ogni telecamera può avere una propria frequenza; consultare le specifiche tecniche della telecamera.
- **Video in miniatura**: passando il mouse su una miniatura nella pagina dell'evento, il video verrà riprodotto.
- **Conferma prima dell'eliminazione**: Visualizza un avviso prima dell'eliminazione di un evento.
- **Pausa creazione file (in secondi)**: tempo di attesa prima della creazione del file (clip / snapshot) (5 s per impostazione predefinita). A seconda dei server, potrebbe essere necessario per consentire a Frigate di creare il file.
#### Impostazioni predefinite di un evento creato manualmente
- **Etichetta**: il nome dell'evento creato (per impostazione predefinita: "manuale").
- **Registrare un video**: sì, per impostazione predefinita.
- **Durata del video**: 40 secondi per impostazione predefinita.
- **Punteggio**: 0 per impostazione predefinita.

#### Funzionalità
- **Cron**: selezionare il cron desiderato.


# <u>Demon</u>
Il demone si avvia automaticamente dopo aver salvato la sezione di configurazione e aver configurato il topic Frigate.
Per poter utilizzare MQTT, è necessario che il server Frigate sia configurato correttamente e che il plugin mqtt-manager (mqtt2) sia installato e configurato correttamente.
Il broker MQTT deve essere protetto affinché il plugin mqtt-manager funzioni correttamente.
Se utilizzi MQTT, puoi impostare il cron su "Hourly" o "Daily".

**Deamon NOK:**
Se non avete mqtt-manager, è normale che il demone rimanga su NOK. Non c'è alcun problema, il plugin funziona comunque, anche se alcune funzioni saranno indisponibili o limitate.

# <u>Utilizzo</u>

**I comandi informativi relativi a tutti i dispositivi vengono creati automaticamente al successivo ricevimento di eventi o statistiche. Se non li vedete al momento della prima installazione del plugin, significa che i vostri eventi recenti risalgono a più di 3 ore fa; è quindi necessario attendere il prossimo evento per visualizzare i comandi.**

**I comandi di azione vengono creati solo quando si utilizza il pulsante "Cerca / Aggiorna".**

## <u>Equipement Events</u>
L'apparecchiatura viene configurata automaticamente insieme alle telecamere.
Questo contiene comandi informativi con il valore dell'ultimo evento ricevuto.
Include inoltre 2 comandi di azione: cron start e cron stop, che consentono di mettere in pausa la ricerca di nuovi eventi.

È possibile creare azioni comuni a tutte le telecamere (vedere la sezione dedicata)
Selezionare "Consenti azioni" se si desidera che, al rilevamento di un evento, vengano eseguite le azioni presenti nelle schede "Eventi" e "Telecamere".


## <u>Statistiche sulle apparecchiature</u>
L'apparecchiatura viene configurata automaticamente insieme alle telecamere.
Questo sistema include comandi informativi con alcune statistiche disponibili.

Include anche il comando "action" che consente di riavviare il server Frigate.

Con Frigate 0.18 o versioni successive e MQTT, due comandi nascosti per impostazione predefinita consentono di monitorare e modificare il profilo attivo di Frigate:
- **Profilo attivo**: nome del profilo attivo, oppure "nessuno"
- **Cambia profilo**: indicare nel messaggio il nome del profilo, oppure "none" per non attivarne nessuno

## <u>Attrezzatura per telecamere</u>
Dopo aver installato il plugin e configurato l'URL e la porta del vostro server Frigate, è sufficiente cliccare sul pulsante Cerca. Le telecamere rilevate verranno create automaticamente. È necessario attendere un po', poiché durante la prima ricerca vengono importati anche gli eventi dell'ultimo giorno. L'operazione potrebbe richiedere un po' di tempo.

### Apparecchiature

- **Nome utente** e **password**: necessari solo per i comandi HTTP (vedi sotto).
- **Aggiornamento**: durata dell'aggiornamento dell'immagine della telecamera, in secondi: il primo valore si riferisce alla dashboard e al pannello di controllo, il secondo a JeeMate. Se non viene specificato alcun valore, viene utilizzata la durata impostata nella configurazione generale.
- **Visualizza sul pannello**: selezionare questa opzione affinché la telecamera sia visibile sul pannello.
- **Posizione sul pannello**: ordine di visualizzazione della telecamera sul pannello (1, 2, 3…). Le telecamere senza posizione vengono visualizzate dopo le altre. Al momento della creazione della telecamera, la posizione riprende l'ordine definito in Frigate (**``ui -> order``**).
- **Flusso video**: Specificare un flusso diverso da quello predefinito se quest'ultimo non è adeguato (rtsp://URL_Frigate:8554/Nome_della_telecamera)
- **Numero di preset**: numero di preset PTZ da importare, se si desidera un numero diverso dall'impostazione predefinita (massimo 10).
- **Qualità degli snapshot**: qualità di compressione delle immagini scaricate, da 1 a 100 (70 per impostazione predefinita). Più basso è il valore, più leggeri sono i file. Si applica a snapshot, miniature e schermate.
- **Altezza degli snapshot**: altezza massima delle immagini, in pixel. Un'immagine più alta viene ridimensionata mantenendo le proporzioni. Se non viene specificato alcun valore, viene mantenuta la dimensione originale. Non si applica alle miniature.
- **Converti in WEBP**: le immagini vengono salvate in formato WebP, più leggero del JPEG.
- **Modello dashboard** e **Modello pannello**: visualizza solo l'immagine della telecamera, sulla dashboard o sul pannello. I pulsanti, i comandi PTZ e le icone di rilevamento sono nascosti; cliccando sull'immagine si apre sempre la finestra ingrandita con le azioni.

La qualità, l'altezza e il formato si applicano solo alle immagini caricate dopo essere state modificate.

A destra, le poche impostazioni disponibili per la visualizzazione.
Aggiorna l'immagine in base alla tua configurazione.

- bbox
- timestamp: la data, che sarà presente anche nello snapshot creato se questa opzione è selezionata.
- zone
- maschera: l'area verrà mascherata
- movimento: l'area è contornata da una linea rossa
- regione: l'area con il contorno verde

### Comandi e informazioni
##### Tutte le telecamere
Informazioni sull'ultimo evento della telecamera: telecamera, etichetta, punteggio, punteggio massimo, zone, ID, tipo, timestamp, durata, clip disponibile, istantanea disponibile, URL istantanea, URL clip e URL miniatura. E le statistiche della telecamera.

L'informazione **LABEL** corrisponde all'oggetto che ha attivato il rilevamento (persona, veicolo, gatto, cane, ecc...)

- **RTSP**: il link del flusso video della telecamera (vedere la sezione Flusso video).
- **SNAPSHOT LIVE**: il link all'immagine in diretta della telecamera, per i plugin che visualizzano un'immagine della telecamera.

##### MQTT
- **Rilevamento in corso**: non appena Frigate rileva un cambiamento, passa a 1 (nuvole, luminosità, persona, ecc...)
- **Rilevamento xxx**: per ogni telecamera verrà aggiunto uno stato che indica se è in corso o meno un rilevamento attivo per ciascun oggetto configurato. Ad esempio, se si dispone di una telecamera che rileva una persona, un veicolo, una mucca, ecc., si avranno 3 stati: persona, mucca, veicolo. Se si seleziona "visibile", l'icona sarà presente sul widget quando è in corso un rilevamento. L’icona è personalizzabile nelle impostazioni del comando. Se un oggetto viene considerato statico, il rilevamento torna a 0.
- **Rilevamento all**: Se viene rilevato un oggetto in movimento, il comando passa a 1. Quando Frigate non rileva più alcun movimento o l'oggetto è fermo, il comando torna a 0. Se il comando all è a 0, gli altri comandi di rilevamento vengono forzati a 0.
- **Stato dei flussi di rilevamento / registrazione / audio** (Frigate 0.18 o versioni successive, nascosti per impostazione predefinita): online, offline o disabilitato per ciascun flusso della telecamera. Frigate riavvia un flusso offline, pertanto il valore può alternarsi tra offline e online: attendere che rimanga stabile prima di agire, ad esempio con una condizione di durata nello scenario.

##### Riconoscimento
Se in Frigate sono attivati il riconoscimento facciale, la lettura delle targhe, i modelli di classificazione o l'IA generativa, la telecamera riceve tramite MQTT il risultato dell'ultimo riconoscimento. I comandi vengono generati al primo risultato.
- **Riconoscimento - Tipo**: volto, LPR (targa), classificazione o descrizione
- **Riconoscimento - Nome** e **Riconoscimento - Punteggio**: la persona o la targa riconosciuta, oppure il modello di classificazione, con il punteggio espresso in %
- **Riconoscimento - Targa**: la targa è stata letta
- **Riconoscimento - Etichetta** e **Riconoscimento - Attributi**: il risultato di un modello di classificazione degli oggetti
- **Riconoscimento - Descrizione**: la descrizione generata dall'IA
- **Riconoscimento - Stato xxx**: lo stato attuale di un modello di classificazione degli stati configurato per la telecamera

### Comandi e azioni
- **Creare un evento**: consultare la pagina Eventi.
- **Screenshot**: stato, screenshot (vedere Creazione di uno screenshot).
- **(Config) Telecamera**: stato, attiva, disattiva, alterna. Questi comandi modificano il file di configurazione di Frigate: per rendere effettive le modifiche è necessario riavviare il server.

Per poter disporre dei seguenti comandi di azione, è obbligatorio utilizzare MQTT. In caso contrario, i comandi non verranno creati. Vi invito a consultare la documentazione di Frigate per la configurazione del vostro server MQTT. Ciascuno di essi ha uno stato e i comandi on, off e toggle.

- **Detect**: rilevamento di oggetti
- **Snapshot**: istantanee degli eventi
- **Registrazione**: registrazione
- **Motion**: rilevamento del movimento (l'impostazione OFF è possibile solo se anche l'opzione "detect" è su OFF)
- **abilitato**: attiva o disattiva immediatamente la telecamera, senza modificare il file di configurazione. A partire dalla versione 0.18 di Frigate, lo stato viene mantenuto al riavvio di Frigate; in precedenza, la telecamera tornava alla configurazione precedente.
- **review_alerts** e **review_detections**: avvisi e rilevamenti relativi alle attività della telecamera, fino al riavvio di Frigate. Il plugin riceve i nuovi eventi in tempo reale tramite le attività: in assenza di avvisi o rilevamenti, non ne riceve più.
- **review_descriptions** e **object_descriptions**: descrizioni generate dall'IA delle attività e degli oggetti monitorati, fino al riavvio di Frigate
- **notifiche**: notifiche di Frigate relative alla telecamera (non quelle di Jeedom)
- **improve_contrast**: miglioramento del contrasto per il rilevamento del movimento

I comandi PTZ, preset e audio vengono creati solo se la configurazione del server Frigate contiene le informazioni necessarie.
- **PTZ**: sinistra, destra, su, giù, stop, zoom avanti, zoom indietro
- **Audio**: stato, acceso, spento, alternanza
- **Preset**: l'azione che consente di posizionare la telecamera su un punto preciso.

### Comandi HTTP
Nella scheda **PTZ & HTTP** di una telecamera, il pulsante **Aggiungi un comando HTTP** crea un comando di azione che richiama l'URL specificato, ad esempio per controllare una funzione della telecamera non disponibile in Frigate.

Nell'URL, **``#user#``** e **``#password#``** vengono sostituiti dall'ID e dalla password del dispositivo, ad esempio:
**``http://192.168.1.50/cgi-bin/api.cgi?cmd=Snap&user=#user#&password=#password#``**

La richiesta utilizza anche l'autenticazione Digest con questo nome utente e questa password. La risposta della telecamera viene registrata nel comando info **Stato HTTP del comando**. La password viene mascherata nei log.

I comandi HTTP vengono creati in modo nascosto. Una volta resi visibili, compaiono nell'elenco a discesa delle azioni del widget, insieme alle impostazioni predefinite. Il pulsante a forma di matita del comando consente di modificarne l'URL.

### Azioni in caso di evento
Le azioni in base agli eventi sono disponibili per i dispositivi **Eventi** e per ogni dispositivo **telecamera**.
Le azioni configurate sul dispositivo **Events** verranno eseguite dagli eventi provenienti da tutte le telecamere **a meno che queste non abbiano azioni configurate e attivate.**
Se desiderate raggruppare azioni comuni sul dispositivo Events e successivamente aggiungere azioni per ciascuna telecamera, ricordatevi di spuntare la casella "Consenti azioni" sul dispositivo Events.

<u>Svolgimento dell'azione</u>:


![esecuzione di un'azione](../images/frigate_Doc_ActionsEvents.png)
#### Condizioni generali
Indicare qui in quali casi le azioni **NON DEVONO** essere eseguite.

Ad esempio, si configura la condizione in questo modo:
**#[Casa][Stile di vita domestico][Moda]# == "presente"**
Le azioni verranno eseguite solo se la modalità è diversa da quella attuale.


#### Azioni
Qui è possibile specificare le azioni da eseguire ogni volta che si verifica un nuovo evento.

Una casella di selezione consente di disattivare la verifica della condizione generale.

<u>LABEL</u> :
**Ricordiamo che è il sensore a innescare il rilevamento (persona, veicolo, animale, ecc...)**
Nel campo **etichetta**, è sufficiente indicare la o le etichette per le quali si desidera che l'azione venga eseguita.
Se questo campo è **vuoto** o se si inserisce **all**, l'azione verrà eseguita per tutti i nuovi eventi.
È possibile specificare più etichette separandole con delle virgole.
Le maiuscole e gli accenti vengono ignorati, quindi se inserisci "Vélo" o "velo", entrambi saranno considerati identici.

<u>TIPO</u>:
**Con** MQTT, possono essere di tipo **new**, **update** e **end**.
**Senza** MQTT, sarà sempre di tipo **end**.
Nel campo **tipo**, è sufficiente indicare il tipo per il quale si desidera che l'azione venga eseguita.
È possibile inserirne più di uno separandoli con delle virgole.
Se non viene specificato alcun tipo, l'azione verrà eseguita solo per gli eventi di tipo **end**.
le maiuscole e gli accenti vengono ignorati, quindi se si digita "update" o "UPDATE", entrambi saranno considerati identici.

<u>ZONE</u>:

Nel campo **zona di ingresso**, è sufficiente indicare la o le zone per le quali si desidera che l'azione venga eseguita.
È possibile specificare più zone separandole con delle virgole.

Il campo **zona di uscita** consente di gestire la direzione del rilevamento. Funziona solo se è stata definita una zona di ingresso. Se la zona di ingresso viene attivata prima della zona di uscita, l'azione verrà eseguita.

Le maiuscole e gli accenti vengono ignorati, quindi se inserisci "Allée" o "allee", entrambe le varianti saranno considerate identiche.

<u>CONDIZIONI DELL'OFFERTA</u>:
Indicare qui in quali casi le azioni **DEVONO** essere eseguite.

Ad esempio, si configura la condizione in questo modo:
**#[Casa][Stile di vita domestico][Moda]# == "assente"**
Le azioni verranno eseguite solo se la modalità è impostata su "Assente".

Se non viene specificata alcuna condizione, l'azione verrà eseguita.

<u>BUONO A SAPERSI</u> :
- Un'azione che utilizza **#clip#** o **#clip_path#** viene eseguita solo se il clip è disponibile. Allo stesso modo, un'azione che utilizza **#snapshot#** o **#snapshot_path#** viene eseguita solo se lo snapshot è disponibile.
- Un evento iniziato più di 3 ore fa non attiva alcuna azione, ad esempio durante il recupero di eventi precedenti.

<u>Variabili disponibili per le condizioni:</u>
- **#camera#**: il nome della telecamera
- **#score#**: punteggio in percentuale -> 82%
- **#top_score#**: punteggio massimo in percentuale -> 92%

<u>Variabili disponibili per le azioni:</u>
È disponibile un elenco di variabili per personalizzare le azioni; tali variabili vengono sostituite con il loro valore al momento dell'esecuzione dell'azione.
- **#time#**: l'ora attuale nel formato 12:00
- **#event_id#**: l'identificativo Frigate dell'evento
- **#type#**: il tipo di evento: new, update o end
- **#camera#**: il nome della telecamera
- **#cameraId#**: l'ID della telecamera (ad esempio, un deeplink alla pagina della telecamera nell'app JeeMate)
- **#score#**: punteggio in percentuale -> 82%
- **#has_clip#**: testo 0 o 1
- **#has_snapshot#**: testo 0 o 1
- **#top_score#**: punteggio massimo in percentuale -> 92%
- **#zone#**: testo, le zone separate da virgole
- **#description#**: la descrizione dell'evento generata da genAI (ovviamente è necessario averlo attivato nel server Frigate)
- **#sublabel#**: l'etichetta assegnata da un modello di classificazione degli oggetti di Frigate
- **#attributes#**: gli attributi assegnati da un modello di classificazione degli oggetti di Frigate
- **#snapshot#**: link al file immagine
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#snapshot_path#**: percorso del file immagine
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#clip#**: link al file mp4
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#clip_path#**: percorso del file mp4
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#thumbnail#**: link all'immagine
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#thumbnail_path#**: percorso del file immagine
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#preview#**: link al file di anteprima
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#preview_path#**: percorso del file di anteprima
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#label#**: testo
- **#start#**: ora di inizio
- **#end#**: ora di fine
- **#durata#**: durata dell'evento
- **#jeemate#**: vedi spiegazioni più avanti



### Esempi di notifiche:
#### Plugin JeeMate
- **snapshot**: nel campo titolo: **``title=il tuo titolo;;bigPicture=#snapshot#``**
- **anteprima**: nel campo titolo: **``title=il tuo titolo;;bigPicture=#preview#``**
- **thumbnail**: nel campo titolo: **``title=il tuo titolo;;bigPicture=#thumbnail#``**
- **clip**: nel campo titolo: **``title=il tuo titolo;;bigPicture=#clip#``**

Per ricevere una notifica automatica, aggiungi frigate=#jeemate#, disponibile con la futura versione 3 di JeeMate

- **snapshot**: nel campo titolo: **``title=il tuo titolo;;bigPicture=#snapshot#;;frigate=#jeemate#``**
- **clip**: nel campo titolo: **``title=il tuo titolo;;bigPicture=#clip#;;frigate=#jeemate#``**

#### Plugin Telegram
Provate i due comandi snapshot. A seconda delle configurazioni, è possibile che uno dei due non funzioni.
- **snapshot**: nel campo delle opzioni: **``title=il tuo titolo | snapshot=#snapshot#``**
- **snapshot**: nel campo "opzioni": **``title=il tuo titolo | file=#snapshot_path#``**
- **clip**: nel campo delle opzioni: **``title=il tuo titolo | file=#clip_path#``**
- **anteprima**: nel campo del messaggio: **``#preview#``**

#### Plugin Mobile v2
- **snapshot**: nel campo "Messaggio": **``il tuo messaggio | file=#snapshot_path#``**
- **clip**: non ne ho idea

#### Plugin JeedomConnect
- **snapshot**: nel campo titolo: **``title=il tuo titolo | files=#snapshot_path#``**
- **clip**: nel campo titolo: **``title=il tuo titolo | files=#clip_path#``**

#### Plugin NTFY
- **snapshot**: nel campo "Opzioni": **``Title:il tuo titolo;Attach:#snapshot#``**
- **clip**: nel campo delle opzioni: **``Title:il tuo titolo;Attach:#clip#``**

# <u>Pagina Eventi</u>

### Evento:
![quadro dell'evento](../images/frigate_Doc_Evenement.png)
1. - Aggiungi l'evento ai preferiti
2. - link alla telecamera
3. - Visualizza lo snapshot (basta cliccare sull'icona)
   - Scarica lo snapshot (fai doppio clic sull'icona)
4. - Guarda il video (basta cliccare sull'icona)
   - Scarica il video (fai doppio clic sull'icona)
5. - Elimina l'evento
6. - Descrizione dell'evento (se genAI è attivato)

**ATTENZIONE**: il pulsante "**elimina**" elimina l'evento dal database Jeedom ma anche dal vostro server Frigate. In nessun caso sarò responsabile di un uso improprio di questo pulsante da parte vostra. Tuttavia, è presente una finestra di conferma.

Gli eventi aggiunti ai preferiti non vengono eliminati.


![pagina degli eventi](../images/frigate_Doc_Evenements.png)

1. - **elimina tutti gli eventi visibili**
**ATTENZIONE**: Il pulsante "**elimina tutti gli eventi visibili**" farà esattamente ciò che indica, quindi assicurati di applicare i filtri corretti prima di procedere all'eliminazione: non sarà possibile tornare indietro; verrà visualizzata una finestra di conferma. L'eliminazione viene effettuata nel database Jeedom ma anche sul tuo server Frigate.

2. - **Creazione di un evento manuale**
Nelle impostazioni generali del plugin Frigate è possibile specificare i valori predefiniti degli eventi creati manualmente.
Nella pagina **Eventi** troverete un pulsante che consente di creare un nuovo evento.
Per ogni telecamera, un comando di azione ti consentirà inoltre di creare un evento.
Questo comando è di tipo messaggio. Se lo si lascia vuoto, verranno utilizzati i parametri predefiniti (dal widget sarà sempre così).
titolo: **``Indicare l'etichetta``**
messaggio: **``score=80 | video=1 | duration=20 | pre_capture=5``**
**pre_capture** (Frigate 0.18 o versioni successive): numero di secondi registrati prima della creazione dell'evento. In assenza di questo parametro, Frigate applica la preregistrazione della telecamera.
Per quanto riguarda la durata dei clip, occorre tenere presente che Frigate aggiunge del tempo prima e dopo il video, 5 secondi per impostazione predefinita; pertanto, impostando 20 secondi, otterrete un video di 30 secondi.
Attenzione agli eventi creati manualmente: se nella configurazione di Frigate, alla voce **``record -> retain -> mode``**, è selezionato **``motion``**, i clip saranno disponibili solo se viene rilevato un movimento; impostare **``all``** se si desidera registrare tutto.

3. - **Filtra gli eventi**
Visualizza solo gli eventi di una o più telecamere, solo di un'etichetta o di un tipo di evento, solo gli eventi della settimana o dell'anno in corso, ecc...

# Creazione di un'istantanea

Non volete creare un evento manualmente, ma desiderate ottenere un'istantanea dalla telecamera? Potete creare un'azione sulla telecamera che acquisirà l'immagine dalla telecamera.

Nei pulsanti delle telecamere sono presenti due comandi:
- Acquisizione di un'immagine (azione)
- URL immagine (informazioni)

Ogni acquisizione viene inoltre registrata come evento con l'etichetta **acquisizione**, visibile nella pagina Eventi. Le acquisizioni vengono eliminate secondo le stesse regole degli eventi (anzianità e dimensione della cartella dati): aggiungete ai preferiti quelle che desiderate conservare.

L'URL ha il formato **``/plugins/frigate/data/snapshots/id_snapshot.jpg``** per adattarsi al maggior numero possibile di plugin di comunicazione.

Ad esempio, se desiderate un URL completo, potete inserire quanto segue nella sezione "Configurazione, calcolo e arrotondamento" del comando info:
**``str_replace('"','',"https://monjeedom.eu.jeedom.link"#value#)``**

Oppure, per chi ha bisogno del percorso:

**``str_replace('"','',"/var/www/html"#value#)``**

# <u>Configurazione Frigate</u>

**ATTENZIONE**: La modifica della configurazione del server Frigate è a vostro rischio e pericolo! Non verrà fornita alcuna assistenza!

# <u>Logs Frigate</u>
Visualizza tutti i log del tuo server Frigate

# <u>Cron</u>
**Se non si utilizza MQTT**: un cron regolare consente di recuperare gli ultimi eventi e quindi di eseguire le azioni associate.

**Se si utilizza MQTT**: tutti i nuovi eventi vengono ricevuti automaticamente, è sufficiente un cron orario o giornaliero: questo permette di aggiornare le informazioni relative all'evento.

In ogni caso, lasciare almeno un cron attivo poiché verrà verificato ogni volta se i file salvati corrispondono effettivamente a un evento e, in caso contrario, verranno eliminati.

cronDaily è l'unico a verificare la versione del vostro server Frigate: se è disponibile un aggiornamento, riceverete un messaggio.

**Il mio consiglio:**
Senza MQTT: cron o cron5 (a seconda della potenza del computer) + cronDaily
Con MQTT: cronDaily

***In ogni caso, se un cron è in esecuzione, quello successivo non verrà avviato e, in MQTT, i cron (1, 5, 10 e 15) sono disattivati.***

# <u>Widget</u>
Qui troverete la visualizzazione della telecamera e i pulsanti contrassegnati visibili:
- cliccando sull'immagine si apre una finestra ingrandita con le azioni, i comandi PTZ e i preset;
- i pulsanti di registrazione, istantanee, rilevamento, audio e movimento, creazione di eventi e acquisizione;
- l'elenco a discesa delle azioni raggruppa i preset PTZ e i comandi HTTP visibili;
- l'icona della chiave inglese apre il pannello IA: attivazione della telecamera, avvisi e rilevamento delle attività, descrizioni tramite IA generativa;
- un'icona mostra gli eventi della telecamera nella pagina Events;
- le icone degli oggetti rilevati in questo momento, per i comandi **Rilevamento xxx** visibili.

Un pulsante viene visualizzato solo se i relativi comandi sono visibili.

# <u>Flusso video</u>
### configurazione
Nel plugin Frigate **non è presente un lettore per il flusso video**; questa configurazione è destinata ai plugin compatibili.

L'URL del flusso video registrato nel plugin è quello del tuo server Frigate e non quello della telecamera.

1. **Flusso RTSP di Frigate**:
   - **Vantaggi**: Frigate è in grado di centralizzare i flussi provenienti da più telecamere, riducendo così il numero di connessioni dirette a ciascuna telecamera. Ciò può migliorare la stabilità e la gestione delle risorse di rete.
   - **Svantaggi**: La configurazione può risultare più complessa, soprattutto se si dispone di più telecamere con impostazioni diverse.

2. **Flusso RTSP della telecamera**:
   - **Vantaggi**: Utilizzare direttamente il flusso RTSP della telecamera può essere più semplice da configurare, soprattutto se si dispone di una sola telecamera o se non si desidera utilizzare un software intermedio.
   - **Svantaggi**: ogni dispositivo si collegherà direttamente alla telecamera, il che potrebbe aumentare il carico sulla rete e sulla telecamera stessa.

In sintesi, se disponete di più telecamere e desiderate una gestione centralizzata, il flusso RTSP di Frigate potrebbe essere la soluzione più vantaggiosa. Se preferite una soluzione più semplice e diretta, potrebbe essere sufficiente utilizzare il flusso RTSP della telecamera.

### Con JeeMate
Se la vostra configurazione Frigate prevede più flussi per ciascuna telecamera, dovrete indicare nel campo "flusso video" del vostro dispositivo quello che desiderate utilizzare; lo stesso vale se preferite utilizzare il flusso originale della telecamera.

Configurazione di Frigate con un unico flusso; in questo caso non è necessario specificare il flusso, poiché quello predefinito andrà bene.

```yaml
frigate1:
  ffmpeg:
    inputs:
      - path: rtsp://127.0.0.1:8554/frigate1
```

Configurazione di Frigate con più feed: indicare l'URL del feed desiderato nella pagina del proprio dispositivo; quello predefinito non è adatto; sostituire 127.0.0.1 con l'IP del server Frigate.

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

***Attenzione: in nessun caso vi verrà chiesto di modificare la configurazione su Frigate***

Dopo ogni modifica dell'URL del feed nel plugin Frigate, sarà necessario salvare le modifiche anche nel plugin JeeMate e quindi eseguire una sincronizzazione completa nell'applicazione.

# <u>Pannello</u>
Non dimenticate di attivare la pagina "Panel" nelle impostazioni generali, quindi di selezionare la casella "Panel" per ogni telecamera.

- visualizzazione delle telecamere.
- pagina degli eventi

# <u> Domande frequenti </u>

### Il plugin è configurato correttamente per MQTT, ma non viene eseguita alcuna azione
L'argomento "frigate/reviews" corrisponde agli elementi di revisione (periodi di attività rilevata) generati dopo il rilevamento e la registrazione degli oggetti. Questo sistema di revisione si basa fortemente sulla funzione di registrazione (recording) per funzionare:

Frigate organizza gli elementi di revisione come intervalli di tempo che raggruppano diversi rilevamenti

Se la registrazione è disattivata (record.enabled: false), non viene memorizzato alcun segmento video e, di conseguenza, la piattaforma non crea elementi di revisione → nulla viene pubblicato in frigate/reviews.

Per funzionare, il plugin richiede quindi:

```yaml
record:
  enabled: true
```

### Esempio di file di configurazione
Si prega di notare che si tratta del mio file e delle mie impostazioni, che funzionano nella mia situazione specifica; spetta a voi adattarlo o confrontarlo con il vostro nel caso in cui alcune funzioni del plugin non fossero operative nel vostro caso.

Non potrò essere ritenuto responsabile per eventuali malfunzionamenti causati da questa configurazione; è quindi necessario adattare la configurazione al proprio server e alle proprie esigenze.

Ho aggiunto dei commenti per aiutarvi.

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


