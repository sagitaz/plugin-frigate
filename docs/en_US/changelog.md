# Frigate plugin changelog

>**IMPORTANT**
>
>If there is no information about the update, it means that the update only involves documentation, translations, or text.

# May 11, 2026 Beta & Stable 1.5.5
- Correcting ERROR logs

# May 6, 2026 Beta & Stable 1.5.4
- New button to download the list of events (useful for developer debugging)

# April 19, 2026 Beta 1.5.3
- Fix for the curl command to enable and disable cameras via the API

# April 13, 2026 Beta 1.5.2
- Dashboard widget correction

# April 9, 2026 Beta 1.5.1
- Improvements to the event cleanup process
- Fix for major update via cron using HTTP

# April 5, 2026 Stable 1.5.0
- See below for information on the beta versions.

# March 21, 2026 Beta 1.4.97
- Added MQTT command to activate cameras
- Disabling the cron job for deactivated cameras

# March 21, 2026 Beta 1.4.96
- Clearing the event list

# March 16, 2026 Beta 1.4.95
- Cleaning up command names in the same way as Jeedom
- Correction to the "sub_label" classification—I had added an "s" (sub_labels)

# March 15, 2026 Beta 1.4.94
- Feature to sort commands (click on the name or ID)

# March 15, 2026 Beta 1.4.93
- Creating commands for information on classification statuses
- Adding on/off buttons to the panel

# March 10, 2026 Beta 1.4.9
- Added new commands for facial recognition
- Added new commands for license plate recognition
- Added new genAI commands
- Bug fixes for Debian 12 and Debian 13

# November 9, 2025 Beta 1.4.7
- Modifying the "data" column in the database
- Adding logs
- minor bug fixes

# November 1, 2025 Beta 1.4.6
- Added commands for face recognition
- Added commands for license plate recognition

# 10/30/2025 Beta 1.4.5
- Add position to panel (taken into account if a Frigate setting exists)
- Added settings for image quality and height
- Option to convert screenshots to WebP
- Checkbox to hide buttons on dashboard and panel templates
- The dashboard widget (and design) can be freely adjusted in height and width
- The widget panel has a fixed height and width (435px and 315px)
- Bug fixes (thanks @t0urista)

# September 22, 2025 Stable 1.4.2
- PHP warning correction
- Correction logs

# July 12, 2025 Stable 1.4.0
- Added FAQs to the documentation

# July 5, 2025 Beta 1.3.6
- Added download option on double-click.

# July 4, 2025 Beta 1.3.5
- Fix JS error on the dashboard.
- Fixed the plural form for events spanning multiple months.
- Fixed column width on the events pages. (Thanks, @vegeta0911)
- Aesthetic events pop-up.
- Added the ability to download screenshots and videos.

# June 8, 2025 Stable 1.3.3
- Comparison of events prior to recovery
- Recovery management: 0 days.

# June 4, 2025 Stable 1.3.2
- Add the variables #camera#, #score#, and #top_score# to the conditions

# May 27, 2025 Stable 1.3.1
- Fix for new installations (bug in version 1.3.0)
- Hiding an item on the dashboard does not hide it on the panel.

# May 23, 2025 Stable 1.3.0
- Jeedom version 4.4 or higher
- Debian version 11 or higher

# May 8, 2025 Stable 1.2.9
- Error correction if the server is not connected.

# April 9, 2025 Stable 1.2.5
- Added "Uptime Info" command
- Added "uptimeDate" command

# April 4, 2025 Stable 1.2.4
- Added "info" command (be sure to test the plugin's actions anyway)

# April 2, 2025 Stable 1.2.3
- Managing the genAI description
- Set actions based on conditions

# March 21, 2025 Beta 1.2.2
- Add a condition for actions

# March 20, 2025 Beta 1.2.1
- Added a "Allow Actions" checkbox for device events

# March 18, 2025 Stable 1.2.0
- Update including all previous fixes.

# March 18, 2025 Beta 1.2.0
- Path Snapshot Correction

# February 27, 2025 Beta 1.1.9
- Added CPU and storage stats

# February 23, 2025 Beta 1.1.8
- Added frigateActions and frigateMQTT logs
- Fixes for variable snapshots in the `update` and `new` types
- Image URL correction (see documentation if you need to change it)

# February 19, 2025 Beta 1.1.7
- Output Zone Management
- Fix for displaying the Frigate configuration file > 0.15

# January 10, 2025 Beta 1.1.6
- Fixing the cronDaily MySQL error

# 11/11/2024 Stable 1.1.5
- Clean up the URL

# 10/23/2024 Beta 1.1.3
- Adding the zone to the actions
- Fixing Action Execution

# 10/07/2024 Stable 1.1.2
- Checking the status of the Frigate server before running cron jobs

# October 7, 2024 Beta 1.1.1
- Added binary commands for detected objects

# 10/05/2024 Stable 1.1.0
- View details of previous updates.

# October 4, 2024 Beta 1.0.6
- Fix for audio value change
- Update the status only if it differs from the last one

# October 2, 2024 Beta 1.0.5
- Fixed an error when creating audio commands
- Fixed an error when creating MQTT commands (value reset to 1)

# October 1, 2024 Beta 1.0.4
- Option to include or exclude data from the Jeedom backup
- Added PTZ pause
- Added server status and server availability commands
- Automatically save the Bbox to snapshots
- Cron Optimization
- Fixed the `file_get_content` error when files do not exist
- Date filter fix (Firefox)
- Option to display cameras on the panel

# September 21, 2024 Beta 1.0.3
- Option for RTSP streams (see documentation)
- Force the generic type of the URL snapshot (search for or save each device)
- Fix for page events thumbnail (clip, preview, nothing)

# September 17, 2024 Beta 1.0.2
- Adding the #preview# variable to notifications
- On the events page, there will be a preview when you hover over it, plus the clip (which is smaller).
- The filters are saved so they can be applied the next time you open the events page.

# September 16, 2024 Beta 1.0.1
- Fixing the preset selector on the widget
- Fixes for various JavaScript errors
- Added configuration for an external link to access Frigate
- If MQTT is used, cron jobs set to run less than 30 minutes apart will not be triggered
- No "Log" commands will be selected for new installations (for existing installations, be sure to uncheck them)
- Added a wait before retrieving snapshots (check it out!)
- You can edit the names of preset and HTTP commands
- Fixed the condition exception checkbox, which was previously applied only to the first action

# September 14, 2024 Stable 1.0.0
- Everything included in previous beta versions.

# September 14, 2024 Beta 0.9.7
- HTTP_ERROR and JS error fixes
- Button to edit the HTTP command URL
- Panel Enhancement
- Variables #user# and #password#, if necessary, in HTTP commands
- Reorganization of information and action commands
- Setup for automatic integration into JeeMate v3
- checkbox to ignore the condition for triggering actions

# September 13, 2024 Beta 0.9.6
- PTZ Command Fixes
- Addition of PTZ buttons to the widget
- Add a button to create HTTP commands (username and password must be entered on the camera page)

# September 11, 2024 Beta 0.9.5
- Changes to command creation.
- Audio commands (status, on, off, and toggle) are available if included in your setup.
- Check the Frigate version once a day (if cronDaily is enabled).
- Correct if the name already exists elsewhere (hidden or capitalized)
- Changes to the Dashboard and Mobile Widgets
- Creating PTZ preset commands (configuration required)

# September 6, 2024 Beta 0.9.4
- Added "Create Screenshot" command (see documentation)
- Add Panel

# September 5, 2024 Beta 0.9.3
- Added the mask to the camera view.
- Updating snapshots during end-of-day processing.
- Various changes and improvements to the Events page.
- Faster event handling on `createEvent` if MQTT is not installed.
- Translations

# August 19, 2024 Beta 0.9.2
- Correction of "keyword" actions of type "keyword".
- Fixed the "type" filter when executing actions.
- Fixed accents in event creation.

# August 17, 2024 Beta 0.9.1
- Translation into English, German, Spanish, Italian, and Portuguese. Thanks, @mips
- Fixed the execution of actions.
- New handling for receiving MQTT events (Frigate 0.14).
- Fix for creating a manual event.
- Improvements to the events page.

# August 10, 2024 Beta 0.9.0
- Added a button and options for creating events.
- Fixes for the "cron isFavorite" error.
- Add an editor for the configuration file (any changes are at your own risk; be sure to read the official Frigate documentation carefully and back up the configuration first).
- Retrieving logs from the Frigate server.
- Changes to how folders and events are cleaned up.
- Lots of other changes.

# July 26, 2024 Beta 0.8.2
- corrections, thumbnail recovery
- Added a button to access camera events on the widget
- Minor corrections

# July 26, 2024 Beta 0.8.1
- corrections, recovery of clips and snapshots
- changing the color of widget buttons
- The "data" folder is no longer included in Jeedom backups

# July 22, 2024 Beta 0.8.0
- Add the variables #thumbnail_path# and #thumbnail#
- Add MQTT2 dependency
- Added dashboard and mobile widgets
- Add to Favorites
- Added "Restart" commands (statistics equipment)
- Adding an execution condition to an action
- Creating the "detect," "snapshot," and "recording" commands (start, stop, toggle)
- Button available for creating PTZ commands
- Configuring the refresh interval
- Configuring the maximum size of the folder for storing snapshots and clips
- Modify snapshot view
- Added debug button (config file)
- Add Discord button
- Add Frigate server button
- Several minor fixes

# June 22, 2024 Beta 0.7.5
- Added variables #time#, #event_id#, #snapshot_path#, and #clip_path#
- Added a button to delete all events (see documentation)
- Add a confirmation pop-up before deletion

# June 20, 2024 Beta 0.7.0
- Bug fix for creating devices
- Bug fixes on the Events page display
- Cron bug fixes
- Added filtering options to the Events page
- Added a link on the events page to go to the camera and a link from the camera to go to the events page.
- Add a label field to actions (which can be empty, "all," or the label name) to trigger the action only for a specific label.

# June 17, 2024 Beta 0.6.0
- Adding logs
- Added command events to the equipment to activate the cron job
- To modify the cron configuration, use the Jeedom checkboxes.
- Added options to the events page (thanks @noodom)
- Default Room Configuration

# June 15, 2024 Beta 0.5.0
- first beta version
