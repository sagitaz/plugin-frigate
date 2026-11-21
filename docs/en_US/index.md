Plugin created by **Sagitaz** and **Noodom**

# <u>Thank you</u>
The plugin and support are free, but if you'd like to buy me a coffee or some diapers, I thank you in advance.

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/C1C61AKVV7)

# <u>Help and Support</u>
- Jeedom Community
- Discord JeeMate

For any support requests on Community or Discord, please provide as much information as possible (hardware, camera type, version of Jeedom, Frigate, your system, etc.).

On the configuration page, the "Help" button already allows you to fill in some of them automatically.

Make sure you have hardware that is compatible with Frigate and that it works properly before asking for help with the plugin. (See the official Frigate documentation for recommended hardware configurations.)

Please also provide logs in debug mode (those from the plugin and the Frigate server).

No support will be provided for any means of communication other than these.

Thank you


# <u>Prerequisites</u>
- Jeedom 4.4.0 or later
- Debian 11 (Bullseye) or later
- Frigate 0.16.0 or higher

The plugin does not install or configure the Frigate server, so you will need to install and configure it yourself. See the official Frigate documentation for more information.

# <u>Installation</u>
As with all other plugins, after installing it, you must activate it.

The plugin will always be compatible with the latest known stable version (giving it time to adapt). However, we will not release multiple updates to ensure compatibility with older versions. So if something isn't working, start by updating your Frigate server before asking for help.

As of September 30, 2026, the plugin works with the following versions of Frigate:
- Frigate 0.18.0 Stable

Frigate 0.16.0 is the minimum required version. Some features are only available starting with Frigate 0.18: camera stream status, profiles, and the `pre_capture` parameter for manually created events. I cannot guarantee support for older versions of the Frigate server.

# Mode of connection to the Frigate server
### API
Retrieving camera footage, viewing past events, deleting events, etc...
Many of the plugin's features rely on a connection to the Frigate API.
This is accessible via port 5000 on your local network; you must have it configured, otherwise the plugin will not work.
If you want to use a different port, you can do so as long as you map it to port 5000 (5054:5000) in the Frigate server configuration.
### MQTT
The MQTT configuration allows you to receive information from the Frigate server in real time.
This improves the plugin's user experience, but is not required for it to function.
A secure broker (user:mdp) is required for the mqtt-manager plugin—on which the frigate plugin depends—to function properly.


# <u>Log</u>
The plugin includes sub-logs; to make them visible in Jeedom 4.4.19, you must set the global logs to at least the "info" level.

![log level](../images/frigate_Doc_Logs.png)
# <u>Configuration</u>
- **Default Room**: Cameras you create will automatically be placed in this room.
- **Exclude from backup**: If checked, the plugin's "data" folder (snapshots, clips, thumbnails, and screenshots) is excluded from Jeedom backups, which are then smaller. After restoring such a backup, events will no longer have their files; a default image will be displayed in their place.
- **Plugin version**: the installed version, read-only. Please include this in your support requests.

#### Frigate Setup
- **URL**: The URL of your Frigate server (e.g., 192.168.1.20)
- **Port**: The Frigate server port (5000 by default). You can use a different port as long as it is mapped to 5000 (5054:5000, for example); the API will not work without this.
- **External Address**: To access the Frigate server page from outside the network.
- **MQTT Topic**: the topic for your Frigate server (frigate by default)
- **Preset**: For PTZ cameras, set the number of positions you want to retrieve.
- **Action Pause**: A pause to be applied to PTZ actions. For example, after pressing “move up,” a stop is automatically triggered: you can set the delay before this stop action from 0 to 10, corresponding to a pause of 0 to 1 second (0, 0.1, 0.2, etc.).

#### Event Management
- **Event Retrieval**: You may have 30 days’ worth of events on your Frigate server but only want to import 7 of them into Jeedom. Specify the desired number of days here (default is 7). If the number of days is 0, the process is stopped and no calls are made to the Frigate API.
- **Deleting events**: Events older than the specified number of days (7 by default) will be deleted from the Jeedom database along with their files, but not from the Frigate server.

The number of days to be removed cannot be less than the number of days to be recovered. Otherwise, the number of days to be recovered will be used.

- **Folder Size**: Maximum size of the data folder, in MB (500 MB by default). If the size exceeds this limit, the oldest events are deleted until the size falls below the limit.

Bookmarked events are never deleted, regardless of age or size. Manual captures follow the same rules as events: bookmark them to keep them.

- **Refresh Interval**: The refresh interval for your cameras' snapshots, in seconds. (Default: 5 seconds.) Each camera can have its own interval; see the camera settings.
- **Video Thumbnails**: When you hover your mouse over a thumbnail on the event page, the video will play.
- **Confirmation before deletion**: Displays an alert before an event is deleted.
- **File creation delay (in seconds)**: The wait time before creating the file (clip/snapshot) (default: 5 s). Depending on the server, this may be necessary to allow Frigate time to create the file.
#### Default settings for a manually created event
- **Label**: the name of the event created (default: "manual").
- **Record a video**: Yes by default.
- **Video length**: 40 seconds by default.
- **Score**: 0 by default.

#### Features
- **Cron**: Select the desired cron job.


# <u>Demon</u>
The daemon starts automatically after saving the configuration section and configuring the "Frigate" topic there.
To use MQTT, you must have your Frigate server properly configured and the mqtt-manager (mqtt2) plugin installed and properly configured.
Your MQTT broker must be secure for the mqtt-manager plugin to work.
If you're using MQTT, you can set the cron job to run hourly or daily.

**Deamon NOK:**
If you don't have mqtt-manager, it's normal for the daemon to remain in the NOK state. No problem—the plugin will still work, though some features will be unavailable or limited.

# <u>Usage</u>

**Info commands for all devices are created automatically the next time events or statistics are received. If you don't see them when you first install the plugin, it means your most recent events are more than 3 hours old, so you'll need to wait for the next event to see the commands.**

**Action commands are created only when you use the "Search / Refresh" button.**

## <u>Equipment Events</u>
The equipment is automatically created at the same time as the cameras.
This one includes status commands with the value of the last event received.
It also includes two action commands: cron start and cron stop, which are used to pause the search for new events.

You can create actions that apply to all cameras (see the dedicated section)
Check "Allow actions" if you want to execute the actions listed in the "Equipment Events" and "Cameras" sections when a trigger is detected.


## <u>Equipment Statistics</u>
The equipment is automatically created at the same time as the cameras.
This one includes information commands with some statistics available.

It also includes the "action" command, which allows you to restart the Frigate server.

With Frigate 0.18 or later and MQTT, two commands that are hidden by default allow you to monitor and change Frigate's active profile:
- **Active Profile**: name of the active profile, or none
- **Change profile**: Include the profile name in the message, or "none" to enable none

## <u>Camera Equipment</u>
After installing the plugin and configuring the URL and port for your Frigate server, simply click the Search button. The cameras found will be automatically added. Please be patient, as the first search also imports events from the past day. This may take a little time.

### Equipment

- **Username** and **Password**: required only for HTTP commands (see below).
- **Refresh Rate**: Refresh rate of the camera image, in seconds: the first value applies to the dashboard and control panel, the second to JeeMate. If no value is specified, the general configuration setting is used.
- **Display on the Panel**: Check this box to make the camera visible on the Panel.
- **Position on the panel**: the order in which the camera appears on the panel (1, 2, 3…). Cameras without a position are displayed after the others. When the camera is created, the position defaults to the order defined in Frigate (**``ui -> order``**).
- **Video stream**: Enter a different stream than the default one if the default is not suitable (rtsp://Frigate_URL:8554/Camera_Name)
- **Number of presets**: the number of PTZ presets to import, if you want a number different from the global setting (maximum of 10).
- **Snapshot Quality**: Compression quality of uploaded images, ranging from 1 to 100 (default is 70). The lower the value, the smaller the files. Applies to snapshots, thumbnails, and screenshots.
- **Snapshot height**: maximum height of images, in pixels. A taller image is scaled down while maintaining its aspect ratio. If no value is specified, the original size is retained. Does not apply to thumbnails.
- **Convert to WEBP**: Images are saved in the WebP format, which is smaller than JPEG.
- **Dashboard Template** and **Panel Template**: Display only the camera image on the dashboard or panel. Buttons, PTZ commands, and detection icons are hidden; clicking on the image always opens the enlarged window with the actions.

Quality, height, and format apply only to images uploaded after they have been edited.

On the right are the few settings available for the display.
Refresh the image based on your configuration.

- Bbox
- timestamp: the date; this will also appear on the snapshot if this box is checked.
- zones
- mask: the area will be masked
- motion: the area is outlined in red
- region: the area with a green outline

### Commands and Information
##### All cameras
Information about the camera's latest event: camera, label, score, top score, zones, ID, type, timestamp, duration, clip available, snapshot available, snapshot URL, clip URL, and thumbnail URL. And the camera's statistics.

The **LABEL** information corresponds to the object that triggered the detection (person, vehicle, cat, dog, etc.)

- **RTSP**: the link to the camera's video stream (see the Video Stream section).
- **SNAPSHOT LIVE**: The link to the camera's live feed, for plugins that display a camera image.

##### MQTT
- **Detection in progress**: As soon as Frigate detects a change, it switches to 1 (clouds, light, person, etc.)
- **Detection xxx**: For each camera, a status will be added indicating whether active detection is in progress for each configured object. For example, if you have a camera monitoring a person, a vehicle, a cow, etc., you’ll have three statuses: person, cow, vehicle. If you check “visible,” the icon will appear on the widget when detection is active. The icon can be customized in the command settings. If an object is considered static, the detection count resets to 0.
- **All Detection**: If a moving object is detected, the command is set to 1. When Frigate no longer detects movement or the object is stationary, the command is reset to 0. If the all command is set to 0, then the other detection commands will be forced to 0.
- **Detection/Recording/Audio Stream Status** (Frigate 0.18 or later, hidden by default): online, offline, or disabled for each camera stream. Frigate restarts an offline stream, so the value may alternate between offline and online: wait until it remains stable before taking action, for example, by using a duration condition in the scenario.

##### Acknowledgments
If facial recognition, license plate recognition, classification models, or generative AI are enabled in Frigate, the camera receives the result of the latest recognition via MQTT. Commands are generated based on the first result.
- **Recognition - Type**: face, license plate recognition (LPR), classification, or description
- **Recognition - Name** and **Recognition - Score**: the recognized person or license plate, or the classification model, along with the score as a percentage
- **Recognition - License Plate**: the license plate was read
- **Recognition - Label** and **Recognition - Attributes**: the result of an object classification model
- **Recognition - Description**: the description generated by AI
- **Recognition - State xxx**: the current state of a state classification model configured for the camera

### Action Commands
- **Create an event**: see the Events page.
- **Snapshot**: status, snapshot (see Creating a Snapshot).
- **(Config) Camera**: status, enable, disable, toggle. These commands modify Frigate's configuration file: a server restart is required for the changes to take effect.

To use the following action commands, you must use MQTT. Otherwise, the commands will not be created. Please refer to the Frigate documentation for instructions on configuring your MQTT server. Each command has a status and supports the "on," "off," and "toggle" commands.

- **Detect**: object detection
- **Snapshot**: event snapshots
- **Recording**: recording
- **Motion**: motion detection (the OFF setting is only available if "detect" is also set to OFF)
- **enabled**: Enables or disables the camera immediately, without modifying the configuration file. Starting with Frigate 0.18, this setting is retained when Frigate restarts; prior to that, the camera reverts to its default configuration.
- **review_alerts** and **review_detections**: alerts and detections related to camera activity, until Frigate is restarted. The plugin receives new events in real time based on camera activity; if there are no alerts or detections, it stops receiving them.
- **review_descriptions** and **object_descriptions**: descriptions generated by generative AI of monitored activities and objects, until Frigate restarts
- **Notifications**: Frigate notifications for the camera (not Jeedom notifications)
- **improve_contrast**: contrast enhancement for motion detection

PTZ, preset, and audio commands are created only if your Frigate server configuration contains the necessary information.
- **PTZ**: left, right, up, down, stop, zoom in, zoom out
- **Audio**: status, on, off, toggle
- **Preset**: the action that allows you to position your camera at a specific point.

### HTTP Commands
In a camera's **PTZ & HTTP** tab, the **Add an HTTP Command** button creates an action command that calls the specified URL—for example, to control a camera feature that Frigate does not support.

In the URL, **``#user#``** and **``#password#``** are replaced by the device's username and password, for example:
**``http://192.168.1.50/cgi-bin/api.cgi?cmd=Snap&user=#user#&password=#password#``**

The request also uses Digest authentication with this username and password. The camera's response is recorded in the **HTTP Status** command. The password is masked in the logs.

HTTP commands are created hidden by default. Once made visible, they appear in the widget's drop-down list of actions, along with the presets. The pencil icon next to the command allows you to edit its URL.

### Event-Triggered Action(s)
Event-based actions are available for **Events** devices and for each **camera** device.
Actions configured on the **Events** device will be executed by events from all cameras **unless those cameras have their own configured and enabled actions.**
If you want to group common actions under the "Events" device and then add actions for each camera, be sure to check the "Allow actions" box on the "Events" device.

<U>Action sequence</U>:


![Performing an action](../images/frigate_Doc_ActionsEvents.png)
#### Terms and Conditions
Specify here under what circumstances the actions **SHOULD NOT** be executed.

For example, you configure the condition as follows:
**#[Home][Home Mode][Mode]# == "present"**
Actions will be executed only if the mode is anything other than "present."


#### Actions
Here, you can specify the actions to be taken whenever a new event occurs.

A checkbox allows you to disable the general condition check.

<u>LABEL</u>:
**As a reminder, the tag is what triggers detection (person, vehicle, animal, etc.)**
In the **label** field, simply enter the label(s) for which you want the action to be performed.
If this field is **empty** or if you enter **all**, the action will be executed for all new events.
You can specify multiple labels by separating them with commas.
Uppercase letters and accents are ignored, so if you enter "Bike" or "bike," both will be considered the same.

<U>TYPE</U>:
**With** MQTT, they can be of the **new**, **update**, and **end** types.
**Without** MQTT, it will always be of the **end** type.
In the **type** field, simply specify the type for which you want the action to be performed.
You can enter multiple items by separating them with commas.
If no type is specified, the action will be executed only for events of type **end**.
Capital letters and accents are ignored, so if you enter "update" or "UPDATE," both will be treated as identical.

<u>ZONES</u>:

In the **input zone** field, simply specify the zone(s) for which you want the action to be performed.
You can specify multiple zones by separating them with commas.

The **exit zone** field allows you to control the direction of detection. This only works when an entry zone has been defined. If the entry zone is triggered before the exit zone, the action will be executed.

Uppercase letters and accents are ignored, so if you enter "Allée" or "allee," both will be considered the same.

<U>TERMS OF THE ACTION</U>:
Specify here under what circumstances the actions **MUST** be executed.

For example, you configure the condition as follows:
**#[Home][Home Mode][Mode]# == "absent"**
Actions will only be executed if the mode is set to "Away."

If no condition is specified, the action will be performed.

<u>GOOD TO KNOW</u>:
- An action that uses **#clip#** or **#clip_path#** is executed only if the clip is available. Similarly, an action that uses **#snapshot#** or **#snapshot_path#** is executed only if the snapshot is available.
- An event that began more than 3 hours ago does not trigger any action, for example, when retrieving past events.

<u>Variables available for conditions:</u>
- **#camera#**: the camera's name
- **#score#**: score as a percentage -> 82%
- **#top_score#**: the maximum score as a percentage -> 92%

<U>Variables available for actions:</U>
A list of variables is available to customize actions; these variables are replaced with their values when the action is executed.
- **#time#**: the current time in 12:00 format
- **#event_id#**: the Frigate ID for the event
- **#type#**: the event type: new, update, or end
- **#camera#**: the camera's name
- **#cameraId#**: the camera ID (for example, a deep link to the camera's page in the JeeMate app)
- **#score#**: score as a percentage -> 82%
- **#has_clip#**: text 0 or 1
- **#has_snapshot#**: text 0 or 1
- **#top_score#**: the maximum score as a percentage -> 92%
- **#zones#**: text, zones separated by commas
- **#description#**: the event description generated by genAI (you must, of course, have it enabled on the Frigate server)
- **#sublabel#**: the label assigned by a Frigate object classification model
- **#attributes#**: attributes assigned by a Frigate object classification model
- **#snapshot#**: link to image file
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#snapshot_path#**: path to image file
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#clip#**: link to MP4 file
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#clip_path#**: path to the MP4 file
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#thumbnail#**: link to image file
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#thumbnail_path#**: path to image file
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#preview#**: link to the preview file
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#preview_path#**: path to the preview file
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#label#**: text
- **#start#**: start time
- **#end#**: end time
- **#duration#**: duration of the event
- **#jeemate#**: see explanation below



### Examples of notifications:
#### JeeMate Plugin
- **snapshot**: in the title field: **``title=your title;;bigPicture=#snapshot#``**
- **preview**: in the title field: **``title=your title;;bigPicture=#preview#``**
- **thumbnail**: in the title field: **``title=your title;;bigPicture=#thumbnail#``**
- **clip**: in the title field: **``title=your title;;bigPicture=#clip#``**

For automatic notifications, add frigate=#jeemate#, available with the upcoming v3 of JeeMate

- **snapshot**: in the title field: **``title=your title;;bigPicture=#snapshot#;;frigate=#jeemate#``**
- **clip**: in the title field: **``title=your title;;bigPicture=#clip#;;frigate=#jeemate#``**

#### Telegram Plugin
Test the two snapshot commands. Depending on your configuration, one of them may not work.
- **snapshot**: in the options field: **``title=your title | snapshot=#snapshot#``**
- **snapshot**: in the options field: **``title=your title | file=#snapshot_path#``**
- **clip**: in the options field: **``title=your title | file=#clip_path#``**
- **preview**: in the message field: **``#preview#``**

#### Mobile Plugin v2
- **snapshot**: in the message field: **``your message | file=#snapshot_path#``**
- **clip**: no idea

#### JeedomConnect Plugin
- **snapshot**: in the title field: **``title=your title | files=#snapshot_path#``**
- **clip**: in the title field: **``title=your title | files=#clip_path#``**

#### NTFY Plugin
- **snapshot**: in the options field: **``Title:your title;Attach:#snapshot#``**
- **clip**: in the options field: **``Title:your title;Attach:#clip#``**

# <u>Events Page</u>

### Event:
![event planning](../images/frigate_Doc_Evenement.png)
1. - Add the event to your favorites
2. - link to the camera
3. - View the snapshot (just click the icon)
   - Download the snapshot (double-click the icon)
4. - Watch the video (just click the icon)
   - Download the clip (double-click the icon)
5. - Delete the event
6. - Event description (if genAI is enabled)

**WARNING**: The "**delete**" button deletes the event from the Jeedom database as well as from your Frigate server. Under no circumstances will I be held responsible for your misuse of this button. However, a confirmation pop-up will appear.

Bookmarked events are not deleted.


![events page](../images/frigate_Doc_Evenements.png)

1. - **Delete all visible events**
**WARNING**: The "**Delete all visible events**" button will do exactly what it says, so be sure to apply the correct filters before deleting: there is no going back—a confirmation pop-up will appear. The deletion is performed in the Jeedom database as well as on your Frigate server.

2. - **Creating a Manual Event**
In the Frigate plugin's general settings, you can specify the default values for manually created events.
On the **Events** page, you'll find a button that lets you create a new event.
For each camera, an action command will also allow you to create an event.
This command is a message-type command. If you leave it blank, the default settings will be used (this will always be the case when using the widget).
title: **``Enter the label``**
message: **``score=80 | video=1 | duration=20 | pre_capture=5``**
**pre_capture** (Frigate 0.18 or later): number of seconds recorded before the event is created. Without this setting, Frigate uses the camera's pre-recording.
When setting the clip duration, keep in mind that Frigate adds 5 seconds before and after the video by default, so if you set it to 20 seconds, you’ll end up with a 30-second video.
Be careful with manually created events: if in your Frigate configuration under **``record -> retain -> mode``** you have **``motion``** selected, clips will only be available if motion is detected. Set it to **``all``** if you want to capture everything.

3. - **Filter events**
Display only events from one or more cameras, only from a specific label or event type, only events from the current week or year, etc...

# Creating a snapshot

Don't want to create an event manually, but want to take a snapshot from the camera? You can create an action on the camera that will capture an image from the camera.

There are two commands in the camera actions:
- Take a picture (action)
- Image URL (info)

Each screenshot is also logged as an event with the label **screenshot**, which can be viewed on the Events page. Screenshots are deleted according to the same rules as events (age and size of the data folder): bookmark the ones you want to keep.

The URL is in the format **``/plugins/frigate/data/snapshots/id_snapshot.jpg``** to be compatible with as many communication plugins as possible.

For example, if you want a full URL, you can add the following to the "Configuration, Calculation, and Rounding" section of the info command:
**``str_replace('"','',"https://monjeedom.eu.jeedom.link"#value#)``**

Or, for those who need the path:

**``str_replace('"','',"/var/www/html"#value#)``**

# <u>Frigate Configuration</u>

**WARNING**: Modifying the Frigate server configuration is at your own risk! No support will be provided!

# <u>Frigate Logs</u>
View all logs on your Frigate server

# <u>Cron</u>
**If you're not using MQTT**: A regular cron job allows you to retrieve the latest events and thus execute the associated actions.

**If you're using MQTT**: All new events are received automatically; a hourly or daily cron job is sufficient—it allows you to update the event information.

In any case, leave at least one cron job active, because the system will check each time to see if the backed-up files correspond to an event; if they do not, they will be deleted.

cronDaily is the only one that checks the version of your Frigate server: if an update is available, you'll receive a message.

**My advice:**
Without MQTT: cron or cron5 (depending on system performance) + cronDaily
With MQTT: cronDaily

***In any case, if a cron job is currently running, the next one will not be triggered; in MQTT, cron jobs 1, 5, 10, and 15 are disabled.***

# <u>Widget</u>
Here you'll find the camera view and the checked buttons:
- Clicking on the image opens a larger window displaying actions, PTZ commands, and presets;
- the recording, snapshot, detection, audio, and motion buttons, as well as event creation and capture;
- The drop-down list of actions includes the visible PTZ presets and HTTP commands;
- The wrench icon opens the AI panel: camera activation, alerts and activity detection, descriptions generated by generative AI;
- An icon displays the camera's events on the Events page;
- Icons for objects currently being detected, for the **Detection xxx** commands that are visible.

A button appears only if its commands are visible.

# <u>Video stream</u>
### setup
The Frigate plugin **does not include a video player**; this configuration is intended for compatible plugins.

The URL of the video stream saved in the plugin is that of your Frigate server, not that of the camera.

1. **Frigate RTSP stream**:
   - **Benefits**: Frigate can centralize streams from multiple cameras, reducing the number of direct connections to each camera. This can improve stability and network resource management.
   - **Disadvantages**: Setup can be more complex, especially if you have multiple cameras with different settings.

2. **RTSP stream from the camera**:
   - **Advantages**: Using the camera's RTSP stream directly can be easier to set up, especially if you have only one camera or if you don't want to use intermediary software.
   - **Drawbacks**: Each device will connect directly to the camera, which can increase the load on the network and on the camera itself.

In summary, if you have multiple cameras and want centralized management, Frigate’s RTSP stream might be a better option. If you prefer a simpler, more straightforward solution, using the camera’s RTSP stream might be sufficient.

### With JeeMate
If your Frigate setup includes multiple video streams per camera, you’ll need to specify which one you want to use in the video stream field for your device; the same applies if you prefer to use the camera’s original stream.

Frigate configuration with a single stream; in this case, I don't need to specify the stream—the default one will work.

```yaml
frigate1:
  ffmpeg:
    inputs:
      - path: rtsp://127.0.0.1:8554/frigate1
```

Frigate configuration with multiple feeds: Enter the URL of the desired feed on your device's page; the default feed will not work. Replace 127.0.0.1 with the Frigate server's IP address.

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

***Please note: under no circumstances should you change the configuration on Frigate***

After each change to the feed URL in the Frigate plugin, you'll need to save the changes in the JeeMate plugin as well, then perform a full sync in the app.

# <u>Panel</u>
Don't forget to enable the "Panel" page in the general settings, and then check the "Panel" box for each camera.

- camera viewing.
- events page

# <u> FAQ </u>

### The plugin is properly configured for MQTT, but no action is taken
The "frigate/reviews" topic corresponds to review items (periods of detected activity) that are generated after objects are detected and recorded. This review system relies heavily on the recording function to operate:

Frigate organizes review items as time intervals that group together multiple detections

If recording is disabled (record.enabled: false), no video segments are stored, and therefore the platform does not create any review items → nothing is published in frigate/reviews.

To work, the plugin therefore requires:

```yaml
record:
  enabled: true
```

### Sample configuration file
Please note that this is my file and my settings, and that it works for my specific situation. It’s up to you to adapt it or compare it with your own if any of the plugin’s features aren’t working for you.

I cannot be held responsible for any malfunctions caused by this configuration; therefore, you must adapt the configuration to your own server and your specific needs.

I've added comments to help you.

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


