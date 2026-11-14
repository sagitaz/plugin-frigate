<?php
// Standalone regression test: php tests/mqtt-active.php
// Loads the unmodified plugin class in a disposable Jeedom layout, with in-memory doubles.
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function cleanComponanteName($name) { return $name; }
class log { public static function add($channel, $level, $message) {} }
class config {
    public static function byKey($key, $plugin = '', $default = '') {
        return $key === 'frigate_version' ? '0.18.2' : $default;
    }
}
class eqLogic {
    public static $equipment = [];
    private $id;
    private $logicalId;
    private $enabled;
    public function __construct($id, $logicalId, $enabled = true) {
        $this->id = $id;
        $this->logicalId = $logicalId;
        $this->enabled = $enabled;
        self::$equipment[$id] = $this;
    }
    public function getId() { return $this->id; }
    public function getLogicalId() { return $this->logicalId; }
    public function getHumanName() { return $this->logicalId; }
    public function getConfiguration($key) { return $key === 'objects' ? ['car', 'person', 'cat'] : null; }
    public function getCmd($type, $logicalId) {
        foreach (cmd::byEqLogicId($this->id, $type) as $command) {
            if ($command->getLogicalId() === $logicalId) { return $command; }
        }
        return null;
    }
    public static function byLogicalId($logicalId, $type) {
        foreach (self::$equipment as $equipment) {
            if ($equipment->logicalId === $logicalId) { return $equipment; }
        }
        return null;
    }
    public static function byType($type, $enabledOnly = false) {
        return array_filter(self::$equipment, function ($equipment) use ($enabledOnly) {
            return !$enabledOnly || $equipment->enabled;
        });
    }
}
class cmd {
    public static $commands = [];
    public static $events = [];
    private $attributes = [];
    private $value = null;
    public function __call($method, $arguments) {
        if (strpos($method, 'set') === 0) { $this->attributes[substr($method, 3)] = $arguments[0]; }
        elseif (strpos($method, 'get') === 0) { return $this->attributes[substr($method, 3)] ?? null; }
    }
    public function execCmd() { return $this->value; }
    public function event($value) {
        $this->value = $value;
        self::$events[] = [$this->getEqLogic_id(), $this->getLogicalId(), $value];
    }
    public function save() {
        self::$commands[$this->getEqLogic_id() . ':' . $this->getLogicalId()] = $this;
    }
    public static function byEqLogicId($id, $type = 'info') {
        return array_filter(self::$commands, function ($command) use ($id, $type) {
            return $command->getEqLogic_id() === $id && $command->getType() === $type;
        });
    }
    public static function byEqLogicIdCmdName($id, $name) {
        foreach (self::byEqLogicId($id) as $command) {
            if ($command->getName() === $name) { return $command; }
        }
        return null;
    }
}

// The real class expects Jeedom four directories above it. No production includes are executed.
$root = sys_get_temp_dir() . '/frigate-mqtt-test-' . bin2hex(random_bytes(8));
$classDir = $root . '/plugins/frigate/core/class';
mkdir($classDir, 0700, true);
mkdir($root . '/core/php', 0700, true);
$files = [$classDir . '/frigate.class.php', $classDir . '/frigate_events.class.php', $root . '/core/php/core.inc.php'];
register_shutdown_function(function () use ($files, $root) {
    foreach ($files as $file) { if (is_file($file)) { unlink($file); } }
    foreach (['/plugins/frigate/core/class', '/plugins/frigate/core', '/plugins/frigate', '/plugins', '/core/php', '/core', ''] as $suffix) {
        if (is_dir($root . $suffix)) { rmdir($root . $suffix); }
    }
});
copy(dirname(__DIR__) . '/core/class/frigate.class.php', $files[0]);
file_put_contents($files[1], '<?php');
file_put_contents($files[2], '<?php');
require $files[0];

$process = new ReflectionMethod('frigate', 'processCameraData');
if (PHP_VERSION_ID < 80100) {
    $process->setAccessible(true);
}$checks = 0;
function same($expected, $actual, $message) {
    global $checks;
    $checks++;
    if ($expected !== $actual) {
        throw new RuntimeException($message . ': expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}
function fixture() {
    eqLogic::$equipment = [];
    cmd::$commands = cmd::$events = [];
    $events = new eqLogic(1, 'eqFrigateEvents');
    $camera = new eqLogic(2, 'eqFrigateCamera_driveway');
    return [$events, $camera];
}
function receive($camera, $data) {
    global $process;
    $process->invoke(null, $camera, 'driveway', $data);
}
function state($equipment, $object) {
    $command = $equipment->getCmd('info', 'info_detect_' . $object);
    return is_object($command) ? $command->execCmd() : null;
}

// The reported regression, including all/active and the later scalar total.
[$events, $camera] = fixture();
receive($camera, ['car' => ['active' => '1'], 'all' => ['active' => '1']]);
same(1, state($camera, 'car'), 'moving car');
same(1, state($events, 'car'), 'Events moving car');
receive($camera, ['car' => ['active' => '0'], 'all' => ['active' => '0']]);
$before = count(cmd::$events);
receive($camera, ['car' => '1', 'all' => '1']);
same(0, state($camera, 'car'), 'parked car must not reactivate camera');
same(0, state($events, 'car'), 'parked car must not reactivate Events');
same(0, state($camera, 'all'), 'parked car must not reactivate all');
same(0, state($events, 'all'), 'parked car must not reactivate Events all');
same($before, count(cmd::$events), 'presence must emit no command event');

// A stale zero total must not cancel a positive /active, in either arrival order.
foreach ([['car' => '0'], ['all' => '0']] as $total) {
    [$events, $camera] = fixture();
    receive($camera, $total);
    receive($camera, ['car' => ['active' => 2], 'all' => ['active' => 2]]);
    $before = count(cmd::$events);
    receive($camera, $total);
    same(1, state($camera, 'car'), 'scalar zero must not clear active car');
    same(1, state($events, 'car'), 'scalar zero must not clear active Events');
    same(1, state($camera, 'all'), 'scalar zero must not clear active all');
    same($before, count(cmd::$events), 'scalar zero must emit no event');
}

// Snapshot and malformed messages must not create or alter detection commands.
[$events, $camera] = fixture();
foreach ([0, '0', 1, '1', null, false, true, '', 'ON', [], ['snapshot' => 'jpeg'], ['active' => null], ['active' => 'ON']] as $ignored) {
    receive($camera, ['car' => $ignored, 'all' => $ignored]);
    same([], cmd::$events, 'non-active or non-numeric payload must be ignored');
}
foreach ([0, '0', 1, '1', 3, '3'] as $count) {
    receive($camera, ['car' => ['active' => $count, 'snapshot' => 'jpeg']]);
    same($count > 0 ? 1 : 0, state($camera, 'car'), 'numeric activity is binary');
}

// Person-before-car does not affect independent object activity.
[$events, $camera] = fixture();
receive($camera, ['person' => ['active' => '1']]);
receive($camera, ['car' => ['active' => '1']]);
receive($camera, ['person' => ['active' => '0']]);
same(1, state($camera, 'car'), 'person ending must not clear car');
same(1, state($events, 'car'), 'Events car independent of person');

// Global Events stays active until all enabled cameras are inactive.
[$events, $camera] = fixture();
$other = new eqLogic(3, 'eqFrigateCamera_garage');
receive($camera, ['car' => ['active' => 1], 'all' => ['active' => 1]]);
receive($other, ['car' => ['active' => 1], 'all' => ['active' => 1]]);
receive($camera, ['car' => ['active' => 0], 'all' => ['active' => 0]]);
same(1, state($events, 'car'), 'second camera keeps global car active');
same(1, state($events, 'all'), 'second camera keeps global all active');
receive($other, ['car' => ['active' => 0], 'all' => ['active' => 0]]);
same(0, state($events, 'car'), 'last camera clears global car');
same(0, state($events, 'all'), 'last camera clears global all');

// A disabled camera with stale detection must be excluded from aggregation.
$disabled = new eqLogic(4, 'eqFrigateCamera_disabled', false);
receive($disabled, ['car' => ['active' => 1], 'all' => ['active' => 1]]);
receive($camera, ['car' => ['active' => 0], 'all' => ['active' => 0]]);
same(0, state($events, 'car'), 'disabled camera ignored for car');
same(0, state($events, 'all'), 'disabled camera ignored for all');

// all/active = 0 keeps the existing per-object reset behaviour.
receive($camera, ['car' => ['active' => 1], 'cat' => ['active' => 1]]);
receive($camera, ['all' => ['active' => 0]]);
same(0, state($camera, 'car'), 'all/active clears car');
same(0, state($camera, 'cat'), 'all/active clears cat');
same(0, state($events, 'car'), 'all/active clears global car');
same(0, state($events, 'cat'), 'all/active clears global cat');

echo "PASS: $checks MQTT activity regression checks\n";
