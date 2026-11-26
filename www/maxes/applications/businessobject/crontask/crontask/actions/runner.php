<?php
// runner.php (Versi 2.0 - Scalable)

if (php_sapi_name() !== 'cli') {
    die("Access Denied. Script is only for command line.");
}

// Muat semua class yang mungkin dibutuhkan oleh cron
require_once __DIR__ . '/actions.php';
$options = getopt('', ['task:']);

if (!isset($options['task'])) {
    echo "ERROR: Argumen --task is required.\n";
    echo "Example: php runner.php --task=ClassName::methodName\n";
    exit(1);
}

$taskFullName = $options['task']; // Contoh: "CronTasks::processPMLeadDayAlerts"

// Pisahkan nama class dan nama method
$parts = explode('::', $taskFullName);
if (count($parts) !== 2) {
    echo "ERROR: Format --task is not valid. Use Format ClassName::methodName.\n";
    exit(1);
}

$className = $parts[0];
$methodName = $parts[1];

try {
    // Cek apakah class-nya ada
    if (!class_exists($className)) {
        echo "ERROR: Class '{$className}' not found.\n";
        exit(1);
    }

    // Cek apakah method-nya ada di dalam class tersebut
    if (!method_exists($className, $methodName)) {
        echo "ERROR: Method '{$methodName}' not found on Task '{$className}'.\n";
        exit(1);
    }

    echo "========================================\n";
    echo "Starting Task: {$taskFullName}...\n";
    echo "========================================\n\n";

    // Panggil method statis secara dinamis
    call_user_func([$className, $methodName]);

    echo "\n\n========================================\n";
    echo "Task {$taskFullName} Complete.\n";
    echo "========================================\n";
    exit(0);

} catch (Exception $e) {
    echo "!!! Error persist when running the task !!!\n";
    echo "Pesan Error: " . $e->getMessage() . "\n";
    exit(1);
}