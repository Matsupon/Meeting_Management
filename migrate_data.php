<?php
// Run this from the php_calendar_app directory:
// php migrate_data.php

$jsonFile = __DIR__ . '/events.json';

if (!file_exists($jsonFile)) {
    echo "events.json not found, nothing to migrate.\n";
    exit(0);
}

$events = json_decode(file_get_contents($jsonFile), true);
if (empty($events)) {
    echo "No events found in events.json.\n";
    exit(0);
}

$db = new PDO('sqlite:' . __DIR__ . '/backend/database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->prepare(
    "INSERT OR IGNORE INTO events (id, title, date, time, description, status, created_at, updated_at)
     VALUES (:id, :title, :date, :time, :description, :status, :created_at, :updated_at)"
);

$now = date('Y-m-d H:i:s');
$imported = 0;

foreach ($events as $e) {
    $stmt->execute([
        ':id'          => $e['id'],
        ':title'       => $e['title']       ?? '',
        ':date'        => $e['date']        ?? '',
        ':time'        => $e['time']        ?? '',
        ':description' => $e['description'] ?? '',
        ':status'      => $e['status']      ?? 'upcoming',
        ':created_at'  => $now,
        ':updated_at'  => $now,
    ]);
    $imported++;
    echo "Imported: {$e['title']}\n";
}

echo "\nDone! $imported event(s) migrated to SQLite.\n";
