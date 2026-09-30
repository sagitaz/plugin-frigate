Complemento creado por **Sagitaz** y **Noodom**

# <u>Agradecimientos</u>
El plugin y la asistencia técnica son gratuitos, pero si de todos modos quieres invitarme a un café o regalarme pañales para el bebé, te lo agradezco de antemano.

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/C1C61AKVV7)

# <u>Ayuda y asistencia</u>
- Comunidad Jeedom
- Discord JeeMate

Para cualquier solicitud de ayuda en Community o Discord, por favor, facilita toda la información posible (hardware, tipo de cámara, versión de Jeedom, de Frigate, de tu sistema, etc.).

En la página de configuración, el botón «Asistencia» ya permite rellenar automáticamente algunos campos.

Asegúrate de que dispones del hardware compatible con Frigate y de que este funciona correctamente antes de solicitar ayuda sobre el complemento. (Consulta la documentación oficial de Frigate para conocer las configuraciones de hardware recomendadas).

Proporciona también los registros en modo de depuración (los del complemento y los del servidor Frigate).

No se prestará asistencia a través de otros medios de comunicación distintos de estos.

Gracias


# <u>Requisitos previos</u>
- Jeedom 4.4.0 como mínimo
- Debian 11 (Bullseye) como mínimo
- Frigate 0.16.0 como mínimo

El complemento no instala ni configura el servidor Frigate, por lo que tendrás que instalarlo y configurarlo tú mismo. Consulta la documentación oficial de Frigate para obtener más información.

# <u>Instalación</u>
Al igual que con el resto de plugins, una vez instalado, hay que activarlo.

El complemento siempre será compatible con la última versión estable conocida (mientras se adapta). Sin embargo, no realizaremos más desarrollos para mantener la compatibilidad con versiones anteriores. Por lo tanto, si algo no funciona, empieza por actualizar tu servidor Frigate antes de pedir ayuda.

A fecha de 30 de septiembre de 2026, el complemento funciona con las siguientes versiones de Frigate:
- Frigate 0.18.0 Estable

Frigate 0.16.0 es la versión mínima. Algunas funciones solo están disponibles a partir de Frigate 0.18: estado de las transmisiones de las cámaras, perfiles y parámetro «pre_capture» de los eventos creados manualmente. No garantizo el soporte para versiones anteriores del servidor Frigate.

# Modo de conexión al servidor Frigate
### API
Recuperación de imágenes de las cámaras, de eventos antiguos, eliminación de eventos, etc.
Muchas funciones del complemento utilizan la conexión a la API de Frigate.
Se puede acceder a ella a través del puerto 5000 de tu red local; es imprescindible que lo hayas configurado, ya que, de lo contrario, el complemento no podrá funcionar.
Si deseas utilizar otro puerto, puedes hacerlo siempre y cuando realices un reenvío al puerto 5000 (5054:5000) en la configuración del servidor Frigate.
### MQTT
La configuración MQTT permite recibir la información del servidor Frigate en tiempo real.
Esto mejora la experiencia del usuario con el complemento, pero no es necesario para su funcionamiento.
Es obligatorio disponer de un broker seguro (user:mdp) para que el complemento mqtt-manager, del que depende el complemento frigate, funcione correctamente.


# <u>Registro</u>
El complemento incluye subregistros; para que sean visibles en Jeedom 4.4.19, es necesario configurar los registros globales con un nivel de información mínimo.

![nivel de registros](../images/frigate_Doc_Logs.png)
# <u>Configuración</u>
- **Habitación por defecto**: Las cámaras creadas se colocarán automáticamente en esta habitación.
- **Excluir de la copia de seguridad**: si se marca esta casilla, la carpeta «data» del complemento (instantáneas, clips, miniaturas y capturas) queda excluida de las copias de seguridad de Jeedom, que así resultarán más ligeras. Tras restaurar una copia de seguridad de este tipo, los eventos ya no tendrán sus archivos: en su lugar, se mostrará una imagen predeterminada.
- **Versión del complemento**: la versión instalada, de solo lectura. Indícala en tus solicitudes de ayuda.

#### Configuración de Frigate
- **URL**: la URL de tu servidor Frigate (p. ej., 192.168.1.20)
- **Puerto**: el puerto del servidor Frigate (5000 por defecto); puedes utilizar otro puerto siempre que esté mapeado al 5000 (por ejemplo, 5054:5000); sin ello, la API no funcionará.
- **Dirección externa**: Para acceder a la página del servidor Frigate desde fuera.
- **Tema MQTT**: el tema de tu servidor Frigate (frigate por defecto)
- **Preajuste**: Para las cámaras con PTZ, define el número de posiciones que deseas recuperar.
- **Pausa de acción**: Pausa que se debe aplicar a las acciones PTZ. Por ejemplo, tras pulsar «mover hacia arriba», se realiza automáticamente una parada: puedes definir el tiempo previo a esta acción de parada entre 0 y 10, lo que corresponde a una pausa de 0 a 1 segundo (0, 0,1, 0,2, etc.).

#### Gestión de eventos
- **Recuperación de eventos**: Es posible que tengas 30 días de eventos en tu servidor Frigate, pero que solo quieras importar 7 a Jeedom. Indica aquí el número de días que desees (7 por defecto). Si el número de días es 0, el proceso se detiene y no se realiza ninguna llamada a la API de Frigate.
- **Eliminación de eventos**: Los eventos más antiguos que el número de días indicado (7 por defecto) se eliminarán de la base de datos de Jeedom junto con sus archivos, pero no del servidor Frigate.

El número de días de supresión no puede ser inferior al número de días de recuperación. De lo contrario, se utilizará el número de días de recuperación.

- **Tamaño de los archivos**: Tamaño máximo del archivo «data», en MB (500 MB por defecto). Si se supera este límite, se eliminan los eventos más antiguos hasta volver a estar por debajo del límite.

Los eventos marcados como favoritos nunca se eliminan, ni por antigüedad ni por tamaño. Las capturas manuales siguen las mismas reglas que los eventos: márcalas como favoritas para conservarlas.

- **Tiempo de actualización**: En segundos, tiempo de actualización de las instantáneas de tus cámaras. (5 segundos por defecto). Cada cámara puede tener su propio tiempo de actualización; consulta la configuración de la cámara.
- **Vídeos en miniaturas**: Al pasar el ratón por encima de una miniatura de la página del evento, se reproducirá el vídeo.
- **Confirmación antes de eliminar**: Muestra una alerta antes de eliminar un evento.
- **Pausa en la creación de archivos (en segundos)**: Tiempo de espera antes de crear el archivo (clip / instantánea) (5 s por defecto). Dependiendo de los servidores, esto puede ser necesario para dar tiempo a Frigate a crear el archivo.
#### Configuración predeterminada de un evento creado manualmente
- **Etiqueta**: el nombre del evento creado (por defecto, «manual»).
- **Grabar un vídeo**: sí, por defecto.
- **Duración del vídeo**: 40 segundos por defecto.
- **Puntuación**: 0 por defecto.

#### Características
- **Cron**: selecciona el cron que desees.


# <u>Demon</u>
El demonio se inicia automáticamente tras guardar la parte de configuración y haber configurado en ella el tema Frigate.
Para poder utilizar MQTT, es necesario que hayas configurado correctamente tu servidor Frigate y que tengas instalado y configurado correctamente el complemento mqtt-manager (mqtt2).
Tu broker MQTT debe ser seguro para que el plugin mqtt-manager funcione.
Si utilizas MQTT, puedes configurar el cron en «Hourly» o «Daily».

**Deamon NOK:**
Si no tienes mqtt-manager, es normal que el demonio se quede en NOK. No hay problema, el complemento funciona de todos modos, aunque algunas funciones no estarán disponibles o tendrán limitaciones.

# <u>Uso</u>

**Los comandos de información de todos los dispositivos se crean automáticamente la próxima vez que se reciban eventos o estadísticas. Si no los ves al instalar el complemento por primera vez, es porque tus eventos recientes tienen más de tres horas de antigüedad, por lo que debes esperar al próximo evento para ver los comandos.**

**Los comandos de acción solo se crean cuando se utiliza el botón «Buscar / Actualizar».**

## <u>Equipement Events</u>
El equipo se crea automáticamente al mismo tiempo que las cámaras.
Este incluye comandos informativos con el valor del último evento recibido.
También incluye dos comandos de acción: «cron start» y «cron stop», que sirven para pausar la búsqueda de nuevos eventos.

Es posible crear acciones comunes para todas las cámaras (véase la sección dedicada a ello)
Marca la casilla «Permitir acciones» si deseas que, al detectarse un evento, se ejecuten las acciones definidas en los eventos del equipo y en las cámaras.


## <u>Equipos y estadísticas</u>
El equipo se crea automáticamente al mismo tiempo que las cámaras.
Este incluye comandos de información con algunas estadísticas disponibles.

También incluye el comando «action», que permite reiniciar el servidor Frigate.

Con Frigate 0.18 o superior y MQTT, hay dos comandos ocultos por defecto que permiten supervisar y cambiar el perfil activo de Frigate:
- **Perfil activo**: nombre del perfil activo, o «none»
- **Cambiar de perfil**: indica en el mensaje el nombre del perfil, o «none» si no quieres activar ninguno

## <u>Equipos de videovigilancia</u>
Una vez instalado el complemento y configurada la URL y el puerto de tu servidor Frigate, solo tienes que hacer clic en el botón «Buscar». Las cámaras encontradas se crearán automáticamente. Es necesario esperar un poco, ya que en la primera búsqueda también se importan los eventos del último día. Esto puede tardar un poco.

### Equipamiento

- **Nombre de usuario** y **contraseña**: solo son necesarios para las solicitudes HTTP (véase más abajo).
- **Actualización**: tiempo de actualización de la imagen de la cámara, en segundos: el primero para el panel de control y el panel de gestión, el segundo para JeeMate. Si no se especifica ningún valor, se aplica el tiempo de la configuración general.
- **Mostrar en el panel**: marca esta casilla para que la cámara sea visible en el panel.
- **Posición en el panel**: orden de visualización de la cámara en el panel (1, 2, 3…). Las cámaras sin posición se muestran después de las demás. Al crear la cámara, la posición adopta el orden definido en Frigate (**``ui -> order``**).
- **Flujo de vídeo**: Introduce un flujo diferente al predeterminado si este no te conviene (rtsp://URL_Frigate:8554/Nombre_de_la_cámara)
- **Número de preajustes**: número de preajustes PTZ que se van a importar, si desea un número distinto al de la configuración general (10 como máximo).
- **Calidad de las instantáneas**: calidad de compresión de las imágenes descargadas, de 1 a 100 (70 por defecto). Cuanto menor sea el valor, más ligeros serán los archivos. Se aplica a las instantáneas, las miniaturas y las capturas.
- **Altura de las instantáneas**: altura máxima de las imágenes, en píxeles. Las imágenes más altas se reducen manteniendo sus proporciones. Si no se especifica ningún valor, se conserva el tamaño original. No se aplica a las miniaturas.
- **Convertir a WEBP**: las imágenes se guardan en formato WebP, más ligero que el JPEG.
- **Plantilla de panel de control** y **Plantilla de panel**: mostrar únicamente la imagen de la cámara, tanto en el panel de control como en el panel. Los botones, los controles PTZ y los iconos de detección quedan ocultos; al hacer clic en la imagen, siempre se abre la ventana ampliada con las acciones.

La calidad, la altura y el formato solo se aplican a las imágenes subidas tras haber sido modificadas.

A la derecha, los pocos parámetros disponibles para la visualización.
Actualiza la imagen según tu configuración.

- bbox
- marca de tiempo: la fecha, que también aparecerá en la captura de pantalla realizada si se marca esta casilla.
- zonas
- máscara: se ocultará la zona
- detección de movimiento: la zona está marcada con un contorno rojo
- región: la zona del este, delimitada por un contorno verde

### Información sobre pedidos
##### Todas las cámaras
Información sobre el último evento de la cámara: cámara, etiqueta, puntuación, puntuación máxima, zonas, ID, tipo, marca de tiempo, duración, clip disponible, instantánea disponible, URL de la instantánea, URL del clip y URL de la miniatura. Y las estadísticas de la cámara.

La información **LABEL** corresponde al objeto que ha activado la detección (persona, vehículo, gato, perro, etc.)

- **RTSP**: el enlace de la transmisión de vídeo de la cámara (véase la sección «Transmisión de vídeo»).
- **SNAPSHOT LIVE**: el enlace a la imagen en directo de la cámara, para los complementos que muestran una imagen de la cámara.

##### MQTT
- **Detección en curso**: en cuanto Frigate detecta un cambio, pasa a 1 (nubes, luminosidad, persona, etc.)
- **Detección xxx**: para cada cámara se añadirá un estado que indica si hay una detección activa en curso o no para cada objeto configurado. Por ejemplo, si tienes una cámara con una persona, un vehículo, una vaca, etc., tendrás tres estados: persona, vaca, vehículo. Si marcas «visible», el icono aparecerá en el widget cuando haya una detección. El icono se puede personalizar en los ajustes del comando. Si un objeto se considera estático, la detección vuelve a 0.
- **Detección «all»**: Si se detecta un objeto en movimiento, el comando pasa a 1. Cuando Frigate deja de detectar movimiento o el objeto está inmóvil, el comando vuelve a 0. Si el comando «all» está en 0, los demás comandos de detección se establecerán en 0.
- **Estado de las transmisiones de detección / grabación / audio** (Frigate 0.18 o superior, ocultas por defecto): «online», «offline» o «desactivado» para cada transmisión de la cámara. Frigate reinicia una transmisión que está «offline», por lo que el valor puede alternar entre «offline» y «online»: espera a que se mantenga estable antes de actuar, por ejemplo, con una condición de duración en el escenario.

##### Reconocimiento
Si el reconocimiento facial, la lectura de matrículas, los modelos de clasificación o la IA generativa están activados en Frigate, la cámara recibe a través de MQTT el resultado del último reconocimiento. Los comandos se crean en cuanto se obtiene el primer resultado.
- **Reconocimiento - Tipo**: rostro, LPR (matrícula), clasificación o descripción
- **Reconocimiento - Nombre** y **Reconocimiento - Puntuación**: la persona o la matrícula reconocida, o el modelo de clasificación, con la puntuación en %
- **Reconocimiento de matrículas**: la matrícula leída
- **Reconocimiento - Etiqueta** y **Reconocimiento - Atributos**: el resultado de un modelo de clasificación de objetos
- **Reconocimiento - Descripción**: la descripción generada por la IA
- **Reconocimiento - Estado xxx**: el estado actual de un modelo de clasificación de estados configurado para la cámara

### Comandos y acciones
- **Crear un evento**: consulta la página «Eventos».
- **Captura**: estado, captura (véase «Creación de una captura instantánea»).
- **(Configuración) Cámara**: estado, activar, desactivar, alternar. Estos comandos modifican el archivo de configuración de Frigate: es necesario reiniciar el servidor para que los cambios surtan efecto.

Para disponer de los siguientes comandos de acción, es obligatorio utilizar MQTT. De lo contrario, los comandos no se crearán. Te recomiendo que consultes la documentación de Frigate para configurar tu servidor MQTT. Cada uno tiene un estado y los comandos «on», «off» y «toggle».

- **Detect**: detección de objetos
- **Snapshot**: instantáneas de eventos
- **Grabación**: grabación
- **Movimiento**: detección de movimiento (la opción «OFF» solo es posible si la opción «detect» también está en «OFF»)
- **habilitado**: activa o desactiva la cámara al instante, sin modificar el archivo de configuración. A partir de Frigate 0.18, el estado se mantiene al reiniciar Frigate; antes, la cámara volvía a su configuración original.
- **review_alerts** y **review_detections**: alertas y detecciones de la actividad de la cámara, hasta que se reinicie Frigate. El complemento recibe los nuevos eventos en tiempo real a través de las actividades: si no hay alertas ni detecciones, deja de recibirlas.
- **review_descriptions** y **object_descriptions**: descripciones generadas por IA de las actividades y los objetos supervisados, hasta el reinicio de Frigate
- **notificaciones**: notificaciones de Frigate para la cámara (no las de Jeedom)
- **improve_contrast**: mejora del contraste para la detección de movimiento

Los comandos PTZ, de preajuste y de audio solo se crean si la configuración de tu servidor Frigate contiene dicha información.
- **PTZ**: izquierda, derecha, arriba, abajo, parar, acercar, alejar
- **Audio**: estado, encendido, apagado, alternar
- **Preajuste**: la acción que permite colocar la cámara en un punto concreto.

### Comandos HTTP
En la pestaña **PTZ y HTTP** de una cámara, el botón **Añadir un comando HTTP** crea un comando de acción que llama a la URL indicada, por ejemplo, para controlar una función de la cámara que Frigate no ofrece.

En la URL, **``#user#``** y **``#password#``** se sustituyen por el nombre de usuario y la contraseña del dispositivo, por ejemplo:
**``http://192.168.1.50/cgi-bin/api.cgi?cmd=Snap&user=#user#&password=#password#``**

La llamada también utiliza la autenticación Digest con este nombre de usuario y esta contraseña. La respuesta de la cámara se registra en el comando info **Estado HTTP del comando**. La contraseña aparece ocultada en los registros.

Los comandos HTTP se crean de forma oculta. Una vez que se hacen visibles, aparecen en la lista desplegable de acciones del widget, junto con los ajustes predefinidos. El botón con el icono del lápiz del comando permite modificar su URL.

### Acción(es) ante un evento
Las acciones basadas en eventos están disponibles para el equipo **Eventos** y para cada equipo de **cámaras**.
Las acciones configuradas en el dispositivo **Events** se ejecutarán cuando se produzcan eventos procedentes de todas las cámaras, **salvo que estas tengan acciones configuradas y activadas**.
Si quieres agrupar acciones comunes en el equipo «Events» y, a continuación, añadir acciones para cada cámara, recuerda marcar la casilla «Permitir acciones» en el equipo «Events».

<u>Desarrollo de la acción</u>:


![ejecución de una acción](../images/frigate_Doc_ActionsEvents.png)
#### Condiciones generales
Indica aquí en qué casos **NO DEBEN** ejecutarse las acciones.

Por ejemplo, se configura la condición de la siguiente manera:
**#[Hogar][Estilo de vida en el hogar][Estilo de vida]# == «presente»**
Las acciones solo se ejecutarán si el modo es distinto del actual.


#### Acciones
Aquí puedes indicar las acciones que se deben realizar cada vez que se produzca un nuevo evento.

Una casilla de selección te permite desactivar la comprobación de la condición general.

<u>Etiqueta</u>:
**A modo de recordatorio, la etiqueta es lo que activa la detección (persona, vehículo, animal, etc.)**
En el campo **etiqueta**, solo tienes que indicar la(s) etiqueta(s) para la(s) que deseas que se ejecute la acción.
Si este campo está **vacío** o si introduces **all**, la acción se ejecutará para todos los nuevos eventos.
Puedes indicar varias etiquetas separándolas con comas.
No se tienen en cuenta las mayúsculas ni los acentos, por lo que si escribes «Bicicleta» o «bicicleta», ambas se considerarán idénticas.

<u>TIPO</u>:
**Con** MQTT, pueden ser de tipo **new**, **update** y **end**.
**Sin** MQTT, siempre será de tipo **end**.
En el campo **tipo**, solo tienes que indicar el tipo para el que deseas que se ejecute la acción.
Puedes introducir varios, separándolos con comas.
Si no se especifica ningún tipo, la acción solo se ejecutará para los eventos de tipo **end**.
No se tienen en cuenta las mayúsculas ni los acentos, por lo que si escribes «update» o «UPDATE», ambos se considerarán idénticos.

<u>ZONAS</u>:

En el campo **zona de entrada**, solo tienes que indicar la zona o zonas en las que deseas que se ejecute la acción.
Puede indicar varias zonas separándolas con comas.

La casilla **zona de salida** permite gestionar el sentido de la detección. Esto solo funciona si se ha definido una zona de entrada. Si se activa la zona de entrada antes que la zona de salida, se ejecutará la acción.

No se tienen en cuenta las mayúsculas ni los acentos, por lo que si escribes «Allée» o «allee», ambas se considerarán idénticas.

<u>CONDICIONES DE LA PROMOCIÓN</u>:
Indica aquí en qué casos **DEBEN** ejecutarse las acciones.

Por ejemplo, se configura la condición de la siguiente manera:
**#[Hogar][Moda para el hogar][Moda]# == «ausente»**
Las acciones solo se ejecutarán si el modo está configurado como «ausente».

Si no se especifica ninguna condición, se llevará a cabo la acción.

<u>INFORMACIÓN ÚTIL</u>:
- Una acción que utilice **#clip#** o **#clip_path#** solo se ejecutará si el clip está disponible. Del mismo modo, una acción que utilice **#snapshot#** o **#snapshot_path#** solo se ejecutará si la instantánea está disponible.
- Un evento que comenzó hace más de tres horas no activa ninguna acción, por ejemplo, al recuperar eventos anteriores.

<u>Variables disponibles para las condiciones:</u>
- **#cámara#**: el nombre de la cámara
- **#puntuación#**: puntuación en porcentaje -> 82 %
- **#top_score#**: la puntuación máxima en porcentaje -> 92 %

<u>Variables disponibles para las acciones:</u>
Hay disponible una lista de variables para personalizar las acciones; estas variables se sustituyen por su valor al ejecutar la acción.
- **#time#**: la hora actual en formato 12:00
- **#event_id#**: el identificador de Frigate del evento
- **#type#**: el tipo de evento: new, update o end
- **#cámara#**: el nombre de la cámara
- **#cameraId#**: el identificador de la cámara (por ejemplo, un enlace directo a la página de la cámara en la aplicación JeeMate)
- **#puntuación#**: puntuación en porcentaje -> 82 %
- **#has_clip#**: texto 0 o 1
- **#has_snapshot#**: texto 0 o 1
- **#top_score#**: la puntuación máxima en porcentaje -> 92 %
- **#zonas#**: texto, las zonas separadas por comas
- **#description#**: la descripción del evento generada por genAI (por supuesto, hay que haberlo activado en el servidor Frigate)
- **#sublabel#**: la etiqueta asignada por un modelo de clasificación de objetos de Frigate
- **#atributos#**: los atributos asignados por un modelo de clasificación de objetos de Frigate
- **#snapshot#**: enlace a un archivo de imagen
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#snapshot_path#**: ruta al archivo de imagen
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#clip#**: enlace al archivo mp4
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#clip_path#**: ruta al archivo mp4
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#thumbnail#**: enlace a un archivo de imagen
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#thumbnail_path#**: ruta al archivo de imagen
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#preview#**: enlace al archivo de vista previa
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#preview_path#**: ruta al archivo de vista previa
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#label#**: texto
- **#start#**: hora de inicio
- **#end#**: hora de finalización
- **#duración#**: duración del evento
- **#jeemate#**: ver explicaciones más abajo



### Ejemplos de notificaciones:
#### Complemento JeeMate
- **instantánea**: en el campo «título»: **``title=tu título;;bigPicture=#snapshot#``**
- **vista previa**: en el campo «título»: **``title=tu título;;bigPicture=#preview#``**
- **miniatura**: en el campo «título»: **``title=tu título;;bigPicture=#thumbnail#``**
- **clip**: en el campo «título»: **``title=tu título;;bigPicture=#clip#``**

Para recibir una notificación automática, añade frigate=#jeemate#, disponible en la futura versión 3 de JeeMate

- **instantánea**: en el campo «título»: **``title=tu título;;bigPicture=#snapshot#;;frigate=#jeemate#``**
- **clip**: en el campo «título»: **``title=tu título;;bigPicture=#clip#;;frigate=#jeemate#``**

#### Complemento de Telegram
Prueba los dos comandos «snapshot». Dependiendo de la configuración, es posible que uno de los dos no funcione.
- **instantánea**: en el campo «opciones»: **``title=tu título | snapshot=#snapshot#``**
- **instantánea**: en el campo «opciones»: **``title=tu título | file=#snapshot_path#``**
- **clip**: en el campo «Opciones»: **``title=tu título | file=#clip_path#``**
- **vista previa**: en el campo «mensaje»: **``#preview#``**

#### Plugin Mobile v2
- **instantánea**: en el campo «mensaje»: **``tu mensaje | file=#snapshot_path#``**
- **clip**: ni idea

#### Complemento JeedomConnect
- **instantánea**: en el campo «título»: **``title=tu título | files=#snapshot_path#``**
- **clip**: en el campo «título»: **``title=tu título | files=#clip_path#``**

#### Complemento NTFY
- **instantánea**: en el campo «Opciones»: **``Título:tu título;Adjunto:#instantánea#``**
- **clip**: en el campo «Opciones»: **``Título:tu título;Adjunto:#clip#``**

# <u>Página de eventos</u>

### Evento:
![marco del evento](../images/frigate_Doc_Evenement.png)
1. - Añadir el evento a favoritos
2. - enlace a la cámara
3. - Ver la instantánea (basta con hacer clic en el icono)
   - Descargar la instantánea (haz doble clic en el icono)
4. - Ver el vídeo (basta con hacer clic en el icono)
   - Descargar el vídeo (haz doble clic en el icono)
5. - Eliminar el evento
6. - Descripción del evento (si genAI está activado)

**ATENCIÓN**: el botón «**eliminar**» elimina el evento tanto de la base de datos de Jeedom como de tu servidor Frigate. En ningún caso me haré responsable del uso indebido que hagas de este botón. No obstante, aparece una ventana emergente de confirmación.

Los eventos marcados como favoritos no se eliminan.


![página de eventos](../images/frigate_Doc_Evenements.png)

1. - **Eliminar todos los eventos visibles**
**ATENCIÓN**: El botón «**eliminar todos los eventos visibles**» hará exactamente lo que indica, así que asegúrate de aplicar los filtros correctos antes de eliminar: no habrá marcha atrás; aparecerá una ventana emergente de confirmación. La eliminación se lleva a cabo en la base de datos de Jeedom, pero también en tu servidor Frigate.

2. - **Creación de un evento manual**
En la configuración general del complemento Frigate, puedes indicar los valores por defecto de los eventos creados manualmente.
En la página **Eventos**, encontrarás un botón que te permite crear un nuevo evento.
Para cada cámara, un comando de acción también te permitirá crear un evento.
Este comando es de tipo mensaje. Si lo dejas en blanco, se utilizarán los parámetros por defecto (desde el widget siempre será así).
título: **``Indicar la etiqueta``**
mensaje: **``score=80 | video=1 | duration=20 | pre_capture=5``**
**pre_capture** (Frigate 0.18 o superior): número de segundos grabados antes de la creación del evento. Sin este parámetro, Frigate aplica la pregrabación de la cámara.
En cuanto a la duración de los clips, hay que tener en cuenta que Frigate añade tiempo antes y después del vídeo, 5 segundos por defecto, por lo que si lo configuras en 20 segundos, obtendrás un vídeo de 30 segundos.
Ten cuidado con los eventos creados manualmente: si en tu configuración de Frigate, en **``record -> retain -> mode``**, tienes seleccionado **``motion``**, los clips solo estarán disponibles si se detecta movimiento; selecciona **``all``** si quieres guardarlo todo.

3. - **Filtrar eventos**
Mostrar solo los eventos de una o varias cámaras, solo de una etiqueta o de un tipo de evento, solo los eventos de la semana o del año en curso, etc...

# Creación de una captura instantánea

¿No quieres crear un evento manualmente, pero sí quieres obtener una captura instantánea de la cámara? Puedes crear una acción en la cámara que capture la imagen de la misma.

En las acciones de las cámaras hay dos comandos:
- Capturar una imagen (acción)
- URL de la imagen (información)

Cada captura también se registra como un evento con la etiqueta **captura**, visible en la página «Eventos». Las capturas se eliminan siguiendo las mismas reglas que los eventos (antigüedad y tamaño de la carpeta «data»): añade a favoritos aquellas que quieras conservar.

La URL tiene el formato **``/plugins/frigate/data/snapshots/id_snapshot.jpg``** para adaptarse al mayor número posible de complementos de comunicación.

Por ejemplo, si quieres una URL completa, puedes introducir esto en la configuración, el cálculo y el redondeo del comando «info»:
**``str_replace('"', '', "https://monjeedom.eu.jeedom.link"#value#)``**

O bien, para aquellos que necesiten la ruta:

**``str_replace('"','',"/var/www/html"#value#)``**

# <u>Configuración de Frigate</u>

**ATENCIÓN**: ¡La modificación de la configuración del servidor Frigate es bajo tu propia responsabilidad! ¡No se prestará ningún tipo de asistencia técnica!

# <u>Logs Frigate</u>
Ver todos los registros de tu servidor Frigate

# <u>Cron</u>
**Si no utilizas MQTT**: una tarea cron periódica te permite recuperar los últimos eventos y, por lo tanto, ejecutar las acciones asociadas.

**Si utilizas MQTT**: todos los eventos nuevos se reciben automáticamente, basta con una tarea cron horaria o diaria: permite actualizar la información del evento.

En cualquier caso, deja al menos una tarea programada activa, ya que se comprobará cada vez si los archivos guardados corresponden efectivamente a un evento y, en caso contrario, se eliminarán.

cronDaily es el único que comprueba la versión de tu servidor Frigate: si hay una actualización disponible, recibirás un mensaje.

**Mi consejo:**
Sin MQTT: cron o cron5 (dependiendo de la potencia del equipo) + cronDaily
Con MQTT: cronDaily

***En cualquier caso, si se está ejecutando una tarea programada, la siguiente no se iniciará y, en MQTT, las tareas programadas (1, 5, 10 y 15) están desactivadas).***

# <u>Widget</u>
Allí encontrarás la imagen de la cámara y los botones marcados visibles:
- Al hacer clic en la imagen se abre una ventana ampliada con las acciones, los controles PTZ y los ajustes preestablecidos;
- los botones de grabación, instantáneas, detección, audio y movimiento, creación de eventos y captura;
- la lista desplegable de acciones agrupa los preajustes PTZ y los comandos HTTP visibles;
- El icono de la llave inglesa abre el panel de IA: activación de la cámara, alertas y detección de actividades, descripciones generadas por IA generativa;
- un icono muestra los eventos de la cámara en la página «Eventos»;
- los iconos de los objetos detectados en este momento, para que se vean los controles **Detección xxx**.

Un botón solo aparece si sus comandos están visibles.

# <u>Transmisión de vídeo</u>
### configuración
En el complemento Frigate **no hay reproductor para el flujo de vídeo**; esta configuración sirve para los complementos compatibles.

La URL de la transmisión de vídeo guardada en el complemento es la de tu servidor Frigate y no la de la cámara.

1. **Transmisión RTSP de Frigate**:
   - **Ventajas**: Frigate puede centralizar las transmisiones de varias cámaras, lo que reduce el número de conexiones directas a cada cámara. Esto puede mejorar la estabilidad y la gestión de los recursos de red.
   - **Inconvenientes**: La configuración puede resultar más compleja, sobre todo si tienes varias cámaras con ajustes diferentes.

2. **Transmisión RTSP de la cámara**:
   - **Ventajas**: Utilizar directamente la transmisión RTSP de la cámara puede resultar más sencillo de configurar, sobre todo si solo tienes una cámara o si no quieres utilizar ningún software intermediario.
   - **Inconvenientes**: Cada dispositivo se conectará directamente a la cámara, lo que puede aumentar la carga en la red y en la propia cámara.

En resumen, si tienes varias cámaras y deseas una gestión centralizada, el flujo RTSP de Frigate podría resultarte más ventajoso. Si prefieres una solución más sencilla y directa, utilizar el flujo RTSP de la cámara podría ser suficiente.

### Con JeeMate
Si tu configuración de Frigate incluye varias transmisiones por cámara, tendrás que indicar en el campo «transmisión de vídeo» de tu equipo cuál deseas utilizar; lo mismo ocurre si prefieres utilizar la transmisión original de la cámara.

Configuración de Frigate con un único flujo; en este caso, no es necesario indicar el flujo, ya que el predeterminado será el adecuado.

```yaml
frigate1:
  ffmpeg:
    inputs:
      - path: rtsp://127.0.0.1:8554/frigate1
```

Configuración de Frigate con varios flujos: indica la URL del flujo deseado en la página de tu equipo; la predeterminada no servirá, así que sustituye 127.0.0.1 por la IP del servidor Frigate.

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

***Atención: en ningún caso se te pedirá que modifiques la configuración en Frigate***

Cada vez que modifiques la URL del flujo en el plugin Frigate, tendrás que guardar los cambios también en el plugin JeeMate y, a continuación, realizar una sincronización completa en la aplicación.

# <u>Panel</u>
No olvides activar la página «Panel» en la configuración general y, a continuación, marcar la casilla «Panel» para cada cámara.

- visualización de las cámaras.
- página de eventos

# <u> Preguntas frecuentes </u>

### El complemento está bien configurado en MQTT, pero no se lleva a cabo ninguna acción
El tema «frigate/reviews» corresponde a los elementos de revisión (períodos de actividad detectada) que se generan tras la detección y el registro de los objetos. Este sistema de revisión depende en gran medida de la función de grabación (recording) para funcionar:

Frigate organiza los elementos de revisión como intervalos de tiempo que agrupan varias detecciones

Si la grabación está desactivada (record.enabled: false), no se almacena ningún segmento de vídeo y, por lo tanto, la plataforma no crea elementos de revisión → no se publica nada en frigate/reviews.

Para que funcione, el complemento necesita:

```yaml
record:
  enabled: true
```

### Ejemplo de archivo de configuración
Ten en cuenta que se trata de mi archivo y de mi configuración, y que funciona en mi caso; te toca a ti adaptarlo o compararlo con el tuyo si alguna vez no te funcionaran todas las funciones del complemento.

No me hago responsable de ningún fallo de funcionamiento causado por esta configuración, por lo que debes adaptarla a tu propio servidor y a tus necesidades.

He añadido algunos comentarios para ayudarte.

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


