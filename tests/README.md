# MQTT activity regression

Run without Jeedom, a database, broker or external dependencies:

```sh
php tests/mqtt-active.php
```

The test loads the complete plugin class in a disposable directory with in-memory
Jeedom doubles. It invokes the real camera MQTT handler with the nested payloads
provided by MQTT Manager and checks camera and global Events commands.

It covers stationary-object presence arriving after zero activity, total-count
messages arriving before or after positive activity, snapshots, invalid payloads,
multiple active objects, person/car independence, multi-camera aggregation,
disabled cameras and the existing `all/active = 0` reset behaviour.

This is an isolated handler regression test. MQTT Manager delivery, Jeedom's
database and scenario engine, widget rendering and live Frigate detection still
require an integration check on a test installation.
