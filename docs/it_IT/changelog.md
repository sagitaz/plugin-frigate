# Registro delle modifiche del plugin Frigate

>**IMPORTANTE**
>
>Se non sono presenti informazioni sull'aggiornamento, significa che si tratta esclusivamente di un aggiornamento della documentazione, della traduzione o del testo.

# 11/05/2026 Beta e Stabile 1.5.5
- Correzione dei log ERROR

# 06/05/2026 Beta e Stabile 1.5.4
- nuovo pulsante per scaricare l'elenco degli eventi (utile per il debug in fase di sviluppo)

# 19/04/2026 Beta 1.5.3
- Correzione del comando curl per l'attivazione e la disattivazione delle telecamere tramite API

# 13/04/2026 Beta 1.5.2
- Correzione widget dashboard

# 09/04/2026 Beta 1.5.1
- Miglioramento del processo di pulizia degli eventi
- Correzione della versione su cron tramite http

# 05/04/2026 Stabile 1.5.0
- Di seguito sono riportate le informazioni sulle versioni beta.

# 21/03/2026 Beta 1.4.97
- Aggiunta della funzione di attivazione delle telecamere tramite MQTT
- Disattivazione del cron per le telecamere disattivate

# 21/03/2026 Beta 1.4.96
- Pulizia dell'elenco degli eventi

# 16/03/2026 Beta 1.4.95
- Pulizia dei nomi dei comandi allo stesso modo di Jeedom
- Correzione della classificazione "sub_label": avevo inserito una "s" (sub_labels)

# 15/03/2026 Beta 1.4.94
- Aggiunta la funzione per ordinare gli ordini (clicca sul nome o sull'ID)

# 15/03/2026 Beta 1.4.93
- Creazione di comandi per informazioni sugli stati di classificazione
- Aggiunta dei pulsanti on/off sul pannello

# 10/03/2026 Beta 1.4.9
- Aggiunta di nuovi comandi per il riconoscimento facciale
- Aggiunta di nuovi comandi per il riconoscimento della targa
- Aggiunta dei nuovi comandi genAI
- Correzioni di un bug su Debian 12 e Debian 13

# 09/11/2025 Beta 1.4.7
- Modifica della colonna "data" nel database
- Aggiunta di registri
- piccole correzioni di bug

# 01/11/2025 Beta 1.4.6
- Aggiunta dei comandi per il riconoscimento facciale
- Aggiunta dei comandi per il riconoscimento della targa

# 30/10/2025 Beta 1.4.5
- Aggiunta posizione sul pannello (viene presa in considerazione se esiste l'impostazione Frigate)
- Aggiunta della configurazione relativa alla qualità e all'altezza delle acquisizioni
- Possibilità di convertire le schermate in formato webp
- Casella di controllo per nascondere i pulsanti nei modelli di dashboard e pannello
- Il widget della dashboard (e il design) è regolabile liberamente in altezza e larghezza
- Il pannello dei widget ha dimensioni fisse in altezza e larghezza (435px e 315px)
- Correzione di bug (grazie @t0urista)

# 22/09/2025 Versione stabile 1.4.2
- Correzione avviso PHP
- Registri di correzione

# 12/07/2025 Versione stabile 1.4.0
- Aggiunta delle FAQ alla documentazione

# 05/07/2025 Beta 1.3.6
- Aggiunta la funzione di download con doppio clic.

# 04/07/2025 Beta 1.3.5
- Correzione dell'errore JS nella dashboard.
- Correzione del plurale relativo agli eventi che si protraggono per diversi mesi.
- Fissata la larghezza delle colonne nelle pagine degli eventi. (grazie @vegeta0911)
- popup eventi di bellezza.
- Aggiunta la possibilità di scaricare schermate e video.

# 08/06/2025 Stabile 1.3.3
- Confronto degli eventi prima del ripristino
- Gestione del recupero: 0 giorni.

# 04/06/2025 Stabile 1.3.2
- Aggiunta delle variabili #camera#, #score# e #top_score# nelle condizioni

# 27/05/2025 Versione stabile 1.3.1
- Correzione su una nuova installazione (bug della versione 1.3.0)
- Nascondere nella dashboard non nasconde nel pannello.

# 23/05/2025 Stabile 1.3.0
- Versione Jeedom 4.4 o superiore
- Versione Debian almeno 11

# 08/05/2025 Stabile 1.2.9
- Correzione dell'errore in caso di server non connesso.

# 09/04/2025 Versione stabile 1.2.5
- Aggiunta comando "info uptime"
- Aggiunta del comando info uptimeDate

# 04/04/2025 Stabile 1.2.4
- Aggiunta comando info descrizione (provare comunque le azioni del plugin)

# 02/04/2025 Stabile 1.2.3
- Gestione della descrizione genAI
- Imposta azioni in base a condizioni

# 21/03/2025 Beta 1.2.2
- Aggiunta di una condizione per le azioni

# 20/03/2025 Beta 1.2.1
- Aggiunta casella di controllo "Consenti azioni" per gli eventi relativi alle apparecchiature

# 18/03/2025 Versione stabile 1.2.0
- Aggiornamento con tutte le correzioni precedenti.

# 18/03/2025 Beta 1.2.0
- Correzione dello snapshot del percorso

# 27/02/2025 Beta 1.1.9
- Aggiunte le statistiche relative alla CPU e allo spazio di archiviazione

# 23/02/2025 Beta 1.1.8
- Aggiunta dei log frigateActions e frigateMQTT
- Correzioni relative allo snapshot delle variabili nei tipi update e new
- Correzione URL immagine (consultare la documentazione se occorre modificarlo)

# 19/02/2025 Beta 1.1.7
- Gestione delle zone di uscita
- Correzione della visualizzazione del file di configurazione di Frigate > 0.15

# 10/01/2025 Beta 1.1.6
- Correzione errore cronDaily mySQL

# 11/11/2024 Versione stabile 1.1.5
- Pulisci l'URL

# 23/10/2024 Beta 1.1.3
- Aggiunta della zona nelle azioni
- Correzione dell'esecuzione delle azioni

# 07/10/2024 Stabile 1.1.2
- Verifica dello stato del server Frigate prima di eseguire i cron

# 07/10/2024 Beta 1.1.1
- Aggiunta dei comandi binari per gli oggetti rilevati

# 05/10/2024 Versione stabile 1.1.0
- Visualizza i dettagli degli aggiornamenti precedenti.

# 04/10/2024 Beta 1.0.6
- Correzione della modifica del valore audio
- Aggiornamento dello stato solo se diverso dall'ultimo

# 02/10/2024 Beta 1.0.5
- Correzione dell'errore nella creazione dei comandi audio
- Correzione dell'errore nella creazione dei comandi MQTT (valore reimpostato a 1)

# 01/10/2024 Beta 1.0.4
- Opzione per escludere o meno i dati dal backup Jeedom
- Aggiunta della pausa PTZ
- Aggiunta della funzione di controllo dello stato e della disponibilità del server
- Salva automaticamente il bbox negli snapshot
- Ottimizzazione del cron
- Correzione dell'errore file_get_content se i file non esistono
- Correzione filtro data (Firefox)
- Opzione per visualizzare le telecamere sul pannello

# 21/09/2024 Beta 1.0.3
- Opzione per flussi RTSP (vedere la documentazione)
- Imposta il tipo generico dell'URL dello snapshot (effettua una ricerca o salva ogni dispositivo)
- Correzione della pagina degli eventi delle miniature (clip, anteprima, nulla)

# 17/09/2024 Beta 1.0.2
- Aggiunta della variabile #preview# nelle notifiche
- Nella pagina degli eventi, ci sarà l'anteprima al passaggio del mouse e, in più, il video (più leggero).
- I filtri vengono salvati per essere applicati alla prossima apertura della pagina degli eventi.

# 16/09/2024 Beta 1.0.1
- Correzione del selettore dei preset sul widget
- Correzioni di vari errori JS
- Aggiunta della configurazione di un link esterno per accedere a Frigate
- Se si utilizza MQTT, i cron con intervallo inferiore a 30 minuti non verranno eseguiti
- Nessuna opzione relativa alle informazioni sugli ordini verrà selezionata per l'archiviazione nelle nuove installazioni (per le altre, ricordarsi di deselezionarle)
- Aggiunta di un'attesa prima del recupero degli snapshot (da verificare!)
- È possibile modificare i nomi dei comandi preimpostati e HTTP
- Correzione della casella di controllo relativa all'eccezione di condizione che veniva applicata solo alla prima azione

# 14/09/2024 Versione stabile 1.0.0
- Tutto ciò che era presente nelle versioni beta precedenti.

# 14/09/2024 Beta 0.9.7
- Correzioni degli errori HTTP_ERROR e JS
- Pulsante per modificare l'URL del comando HTTP
- Miglioramento del pannello
- Variabili #user# e #password#, se necessarie, nei comandi HTTP
- Riorganizzazione dei comandi "Informazioni" e "Azioni"
- Configurazione per l'integrazione automatica in JeeMate v3
- casella di controllo per ignorare la condizione relativa all'attivazione delle azioni

# 13/09/2024 Beta 0.9.6
- Correzioni ai comandi PTZ
- Aggiunta dei pulsanti PTZ al widget
- Aggiunta di un pulsante per creare comandi HTTP (nome utente e password da inserire nella pagina della telecamera)

# 11/09/2024 Beta 0.9.5
- Modifica relativa alla creazione degli ordini.
- Comandi audio (stato, accensione, spegnimento e commutazione) disponibili se presenti nella vostra configurazione.
- Verifica della versione di Frigate una sola volta al giorno (se cronDaily è attivato).
- Correzione se il nome esiste altrove (nascosto o con maiuscola)
- Modifica del widget nella dashboard e nella versione mobile
- Creazione dei comandi PTZ preimpostati (configurazione da effettuare)

# 06/09/2024 Beta 0.9.4
- Aggiunta del comando "crea screenshot" (vedi documentazione)
- Aggiunta pannello

# 05/09/2024 Beta 0.9.3
- Aggiunta della maschera alla visualizzazione delle telecamere.
- Aggiornamento degli snapshot durante le ricezioni end.
- Varie modifiche e miglioramenti alla pagina Eventi.
- Recupero dell'evento su createEvent più veloce se MQTT non è installato.
- Traduzioni

# 19/08/2024 Beta 0.9.2
- Correzione delle azioni di tipo "parole chiave".
- Correzione del filtro "tipo" nell'esecuzione delle azioni.
- Correzione degli accenti nella creazione di eventi.

# 17/08/2024 Beta 0.9.1
- Traduzione in inglese, tedesco, spagnolo, italiano, portoghese. Grazie @mips
- Correzione dell'esecuzione delle azioni.
- Nuova gestione per la ricezione degli eventi MQTT (Frigate 0.14).
- Correzione relativa alla creazione di un evento manuale.
- Miglioramento della pagina degli eventi.

# 10/08/2024 Beta 0.9.0
- Aggiunta del pulsante e delle opzioni "Crea evento".
- Correzioni dell'errore cron isFavorite.
- Aggiunta di un editor per il file di configurazione (tutte le modifiche sono a vostro rischio e pericolo; leggete attentamente la documentazione ufficiale di Frigate ed effettuate un backup della configurazione prima di procedere).
- Recupero dei log dal server Frigate.
- Modifica della gestione della pulizia delle cartelle e degli eventi.
- Tante altre modifiche.

# 26/07/2024 Beta 0.8.2
- correzioni e recupero delle miniature
- Aggiunta di un pulsante per accedere agli eventi della telecamera sul widget
- Piccole correzioni

# 26/07/2024 Beta 0.8.1
- correzioni, recupero clip e istantanee
- modifica del colore dei pulsanti del widget
- la cartella "data" non viene più inclusa nei backup di Jeedom

# 22/07/2024 Beta 0.8.0
- Aggiunta delle variabili #thumbnail_path# e #thumbnail#
- Aggiunta della dipendenza MQTT2
- Aggiunta del widget alla dashboard e alla versione mobile
- Aggiungi evento ai preferiti
- Aggiunta dei comandi di riavvio (apparecchiature statistiche)
- Aggiunta di una condizione di esecuzione nelle azioni
- Creazione dei comandi detect, snapshot e recording (avvio, arresto, attivazione/disattivazione)
- Pulsante disponibile per creare i comandi PTZ
- Configurazione dell'intervallo di aggiornamento
- Configurazione della dimensione massima della cartella di salvataggio degli snapshot e dei clip
- Modifica della visualizzazione dello snapshot
- Aggiunta del pulsante di debug (file di configurazione)
- Aggiunta del pulsante Discord
- Aggiunta del pulsante "Server Frigate"
- Numerose piccole correzioni

# 22/06/2024 Beta 0.7.5
- Aggiunta delle variabili #time#, #event_id#, #snapshot_path# e #clip_path#
- Aggiunta del pulsante per l'eliminazione di tutti gli eventi (vedi documentazione)
- Aggiunta di una finestra pop-up di conferma prima dell'eliminazione

# 20/06/2024 Beta 0.7.0
- Correzione di un bug relativo alla creazione di dispositivi
- Correzione di bug nella visualizzazione della pagina Eventi
- Correzioni dei bug di Cron
- Aggiunta di opzioni di filtro alla pagina Eventi
- Aggiunta di un link dagli eventi alla telecamera e di un link dalla telecamera agli eventi.
- Aggiunta per le azioni di un campo "etichetta" (vuoto, "tutto" o nome dell'etichetta), per attivare l'azione solo per un'etichetta specifica.

# 17/06/2024 Beta 0.6.0
- Aggiunta di registri
- Aggiunta nell'apparecchiatura Events di comandi per attivare il cron
- Modifica della configurazione cron, utilizzare le caselle di controllo di Jeedom.
- Aggiunta di opzioni alla pagina degli eventi (grazie @noodom)
- Configurazione predefinita per stanza

# 15/06/2024 Beta 0.5.0
- prima versione beta
