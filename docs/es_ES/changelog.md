# Registro de cambios del plugin Frigate

>**IMPORTANTE**
>
>Si no hay información sobre la actualización, es porque esta se refiere únicamente a cambios en la documentación, la traducción o el texto.

# 11/05/2026 Beta y Estable 1.5.5
- Corrección de registros ERROR

# 06/05/2026 Beta y Estable 1.5.4
- nuevo botón para descargar la lista de eventos (útil para la depuración en desarrollo)

# 19/04/2026 Beta 1.5.3
- Corrección del comando «curl» para activar y desactivar las cámaras a través de la API

# 13/04/2026 Beta 1.5.2
- Corrección del widget del panel de control

# 09/04/2026 Beta 1.5.1
- Mejora del proceso de limpieza de eventos
- Corrección de la actualización en cron a través de http

# 05/04/2026 Estable 1.5.0
- A continuación encontrarás información sobre las versiones beta.

# 21/03/2026 Beta 1.4.97
- Se ha añadido el comando para activar las cámaras mediante MQTT
- Desactivación del cron para las cámaras desactivadas

# 21/03/2026 Beta 1.4.96
- Limpieza de la lista de eventos

# 16/03/2026 Beta 1.4.95
- Limpieza de los nombres de los comandos de la misma forma que en Jeedom
- Corrección de la clasificación «sub_label»: había puesto una «s» (sub_labels).

# 15/03/2026 Beta 1.4.94
- Se ha añadido la posibilidad de ordenar los pedidos (haz clic en el nombre o en el ID)

# 15/03/2026 Beta 1.4.93
- Creación de comandos de información sobre estados de clasificación
- Incorporación de botones de encendido/apagado en el panel

# 10/03/2026 Beta 1.4.9
- Se han añadido nuevos comandos para el reconocimiento facial
- Se han añadido nuevos comandos para el reconocimiento de matrículas
- Incorporación de los nuevos comandos de genAI
- Correcciones de un error en Debian 12 y Debian 13

# 09/11/2025 Beta 1.4.7
- Modificación de la columna «data» en la base de datos
- Añadir registros
- pequeñas correcciones de errores

# 01/11/2025 Beta 1.4.6
- Se han añadido comandos para el reconocimiento facial
- Se han añadido comandos para el reconocimiento de matrículas

# 30/10/2025 Beta 1.4.5
- Añadir posición en el panel (se tiene en cuenta si existe el ajuste Frigate)
- Se ha añadido la configuración de la calidad y la altura de las capturas
- Posibilidad de convertir las capturas a formato webp
- Casilla de selección para ocultar los botones en las plantillas del panel de control y del panel
- El widget del panel de control (y el diseño) se puede ajustar libremente en altura y anchura
- El panel de widgets tiene unas dimensiones fijas en altura y anchura (435 píxeles y 315 píxeles).
- Corrección de errores (gracias, @t0urista)

# 22/09/2025 Estable 1.4.2
- Corrección de aviso de PHP
- Registros de correcciones

# 12/07/2025 Estable 1.4.0
- Inclusión de la sección de preguntas frecuentes en la documentación

# 05/07/2025 Beta 1.3.6
- Se ha añadido la función de descarga al hacer doble clic.

# 04/07/2025 Beta 1.3.5
- Corregir el error de JS en el panel de control.
- Se ha corregido el plural en los eventos de varios meses.
- Se ha fijado el ancho de las columnas en las páginas de eventos. (Gracias, @vegeta0911)
- Ventana emergente de eventos de estética.
- Se ha añadido la posibilidad de descargar capturas y vídeos.

# 08/06/2025 Estable 1.3.3
- Comparación de eventos antes de la recuperación
- Gestión de la recuperación en 0 días.

# 04/06/2025 Estable 1.3.2
- Se han añadido las variables #camera#, #score# y #top_score# a las condiciones

# 27/05/2025 Estable 1.3.1
- Corrección en nuevas instalaciones (error de la versión 1.3.0)
- Ocultar en el panel de control no oculta en el panel.

# 23/05/2025 Estable 1.3.0
- Versión mínima de Jeedom: 4.4
- Versión mínima de Debian: 11

# 08/05/2025 Versión estable 1.2.9
- Corrección de un error cuando el servidor no está conectado.

# 09/04/2025 Estable 1.2.5
- Se ha añadido el comando «info uptime»
- Se ha añadido el comando «info uptimeDate»

# 04/04/2025 Estable 1.2.4
- Añadir comando de información y descripción (comprueba de todos modos las acciones del complemento)

# 02/04/2025 Estable 1.2.3
- Gestión de la descripción de genAI
- Acciones fijas en función de una condición

# 21/03/2025 Beta 1.2.2
- Se ha añadido una condición para las acciones

# 20/03/2025 Beta 1.2.1
- Se ha añadido la casilla de selección «Permitir acciones» para los eventos de los dispositivos

# 18/03/2025 Estable 1.2.0
- Actualización con todas las correcciones anteriores.

# 18/03/2025 Beta 1.2.0
- Instantánea de la ruta de corrección

# 27/02/2025 Beta 1.1.9
- Se han añadido las estadísticas de CPU y almacenamiento

# 23/02/2025 Beta 1.1.8
- Incorporación de registros de frigateActions y frigateMQTT
- Correcciones en la instantánea de variables en los tipos «update» y «new»
- Corrección de la URL de la imagen (consulta la documentación si necesitas modificarla)

# 19/02/2025 Beta 1.1.7
- Gestión de zonas de salida
- Corrección en la visualización del archivo de configuración de Frigate > 0.15

# 10/01/2025 Beta 1.1.6
- Corrección del error cronDaily de MySQL

# 11/11/2024 Estable 1.1.5
- Limpia la URL

# 23/10/2024 Beta 1.1.3
- Añadir el campo en las acciones
- Corrección de la ejecución de las acciones

# 07/10/2024 Estable 1.1.2
- Comprobación del estado del servidor Frigate antes de ejecutar las tareas cron

# 07/10/2024 Beta 1.1.1
- Incorporación de comandos binarios para los objetos detectados

# 05/10/2024 Estable 1.1.0
- Ver los detalles de las actualizaciones anteriores.

# 04/10/2024 Beta 1.0.6
- Corrección del cambio en el valor del audio
- Actualización de los estados solo si difieren de los últimos

# 02/10/2024 Beta 1.0.5
- Corrección de un error en la creación de comandos de audio
- Corrección de un error en la creación de comandos MQTT (valor restablecido a 1)

# 01/10/2024 Beta 1.0.4
- Opción para excluir o no los datos de la copia de seguridad de Jeedom
- Incorporación de la función de pausa PTZ
- Se ha añadido el comando de estado del servidor y la disponibilidad del servidor
- Guardar automáticamente el bbox en las instantáneas
- Optimización de cron
- Corrección del error de `file_get_content` si los archivos no existen
- Corrección del filtro de fechas (Firefox)
- Opción para mostrar las cámaras en el panel

# 21/09/2024 Beta 1.0.3
- Opción para flujos RTSP (véase la documentación)
- Fija el tipo genérico de la URL del instantáneo (realiza una búsqueda o guarda cada equipo)
- Corrección de la página de eventos de la miniatura (clip, vista previa, nada)

# 17/09/2024 Beta 1.0.2
- Incorporación de la variable #preview# en las notificaciones
- En la página de eventos, aparecerá la vista previa al pasar el cursor por encima, además del vídeo (que ocupa menos espacio).
- Los filtros se guardan para que se apliquen la próxima vez que se abra la página de eventos.

# 16/09/2024 Beta 1.0.1
- Corrección del selector de preajustes en el widget
- Correcciones de varios errores de JS
- Se ha añadido la configuración de un enlace externo para acceder a Frigate
- Si se utiliza MQTT, los cron de menos de 30 minutos no se ejecutarán
- No se marcará ninguna opción de «información de comandos» para el registro en las nuevas instalaciones (en el resto de casos, recuerda desmarcarlas).
- Se ha añadido una espera antes de recuperar las instantáneas (¡échale un vistazo!)
- Es posible editar los nombres de los comandos predefinidos y los de HTTP
- Se ha corregido la casilla de selección de excepción de condición, que solo se aplicaba a la primera acción.

# 14/09/2024 Versión estable 1.0.0
- Todo lo que hay en las versiones beta anteriores.

# 14/09/2024 Beta 0.9.7
- Correcciones de errores HTTP_ERROR y JS
- Botón para editar la URL de la solicitud HTTP
- Mejora del panel
- Variables #user# y #password#, si son necesarias, en los comandos HTTP
- Reorganización de los menús de información y acciones
- Configuración para la integración automática en JeeMate v3
- casilla de selección para ignorar la condición al activar acciones

# 13/09/2024 Beta 0.9.6
- Correcciones en los comandos PTZ
- Incorporación de los botones PTZ al widget
- Se ha añadido un botón para crear comandos HTTP (hay que introducir el nombre de usuario y la contraseña en la página de la cámara)

# 11/09/2024 Beta 0.9.5
- Modificación en la creación de pedidos.
- Comandos de audio (estado, encendido, apagado y alternar) disponibles si están presentes en tu configuración.
- Comprobación de la versión de Frigate una vez al día (si cronDaily está activado).
- Corrección si el nombre ya existe en otro lugar (oculto o con mayúsculas)
- Modificación del widget del panel de control y de la versión móvil
- Creación de comandos PTZ preestablecidos (configuración pendiente)

# 06/09/2024 Beta 0.9.4
- Se ha añadido el comando «crear captura» (véase la documentación)
- Añadir panel

# 05/09/2024 Beta 0.9.3
- Se ha añadido la máscara a la visualización de las cámaras.
- Actualización de las instantáneas durante las recepciones.
- Se han realizado diversas modificaciones y mejoras en la página «Eventos».
- La recuperación del evento en createEvent es más rápida si no está instalado MQTT.
- Traducciones

# 19/08/2024 Beta 0.9.2
- Corrección de las acciones del tipo «palabras clave».
- Corrección del filtro «tipo» en la ejecución de las acciones.
- Corrección de los acentos en la creación de eventos.

# 17/08/2024 Beta 0.9.1
- Traducción al inglés, alemán, español, italiano y portugués. Gracias, @mips
- Corrección de la ejecución de las acciones.
- Nueva gestión para la recepción de eventos MQTT (Frigate 0.14).
- Corrección para la creación de un evento manual.
- Mejora de la página de eventos.

# 10/08/2024 Beta 0.9.0
- Se ha añadido el botón y las opciones para crear un evento.
- Correcciones del error «cron isFavorite».
- Se ha añadido un editor para el archivo de configuración (cualquier modificación se realiza bajo tu propia responsabilidad; lee atentamente la documentación oficial de Frigate y haz una copia de seguridad de la configuración antes de continuar).
- Recuperación de los registros del servidor Frigate.
- Modificación de la gestión de la limpieza de carpetas y eventos.
- Muchas otras modificaciones.

# 26/07/2024 Beta 0.8.2
- correcciones y recuperación de miniaturas
- Se ha añadido un botón para acceder a los eventos de la cámara en el widget
- Pequeñas correcciones

# 26/07/2024 Beta 0.8.1
- correcciones, recuperación de clips e instantáneas
- cambio de color de los botones del widget
- La carpeta «data» ya no se incluye en las copias de seguridad de Jeedom

# 22/07/2024 Beta 0.8.0
- Se añaden las variables #thumbnail_path# y #thumbnail#
- Incorporación de la dependencia MQTT2
- Incorporación del widget para el panel de control y dispositivos móviles
- Añadir evento a favoritos
- Se han añadido comandos para reiniciar (equipos de estadísticas)
- Incorporación de una condición de ejecución en las acciones
- Creación de los comandos «detect», «snapshot» y «recording» (iniciar, detener, alternar)
- Botón disponible para crear comandos PTZ
- Configuración del intervalo de actualización
- Configuración del tamaño máximo de la carpeta de copia de seguridad de instantáneas y clips
- Modificación de la visualización de instantáneas
- Se ha añadido un botón de depuración (archivo de configuración)
- Añadir botón de Discord
- Se ha añadido el botón del servidor Frigate
- Varias correcciones menores

# 22/06/2024 Beta 0.7.5
- Se han añadido las variables #time#, #event_id#, #snapshot_path# y #clip_path#
- Se ha añadido un botón para eliminar todos los eventos (véase la documentación)
- Se añade una ventana emergente de confirmación antes de eliminar

# 20/06/2024 Beta 0.7.0
- Corrección de un error en la creación de dispositivos
- Corrección de errores en la visualización de la página «Eventos»
- Correcciones de errores de Cron
- Se han añadido opciones de filtrado a la página «Eventos»
- Se ha añadido un enlace en los eventos para acceder a la cámara y un enlace desde la cámara para acceder a los eventos.
- Se añade un campo «etiqueta» para las acciones (que puede estar vacío, contener «todos» o el nombre de la etiqueta), para que la acción solo se active con una etiqueta específica.

# 17/06/2024 Beta 0.6.0
- Añadir registros
- Se han añadido comandos en el equipo «Events» para activar el cron
- Modificación de la configuración de cron: utiliza las casillas de selección de Jeedom.
- Se han añadido opciones a la página de eventos (gracias, @noodom)
- Configuración predeterminada de la habitación

# 15/06/2024 Beta 0.5.0
- primera versión beta
