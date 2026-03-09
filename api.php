<?php
header('Content-Type: application/json');

$dataFile = 'events.json';

// Initialize events file if it doesn't exist
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

$events = json_decode(file_get_contents($dataFile), true) ?? [];

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET: list events
if ($method === 'GET') {
    echo json_encode(['status' => 'success', 'data' => $events]);
    exit;
}

// Handle POST: add or update event
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check for add vs update
    if (isset($input['id']) && !empty($input['id'])) {
        // Update
        $updated = false;
        foreach ($events as &$event) {
            if ($event['id'] === $input['id']) {
                $event['title'] = $input['title'] ?? $event['title'];
                $event['date'] = $input['date'] ?? $event['date'];
                $event['time'] = $input['time'] ?? $event['time'];
                $event['description'] = $input['description'] ?? $event['description'];
                $event['status'] = $input['status'] ?? ($event['status'] ?? 'upcoming');
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Event not found']);
            exit;
        }
    } else {
        // Add
        $newEvent = [
            'id' => uniqid(),
            'title' => $input['title'] ?? '',
            'date' => $input['date'] ?? '',
            'time' => $input['time'] ?? '',
            'description' => $input['description'] ?? '',
            'status' => $input['status'] ?? 'upcoming'
        ];
        $events[] = $newEvent;
    }
    
    file_put_contents($dataFile, json_encode($events, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success']);
    exit;
}

// Handle DELETE: remove event
// Note: sometimes browsers send DELETE without body, so we can also check query string
if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? $_GET['id'] ?? null;
    
    if ($id) {
        $events = array_filter($events, function($e) use ($id) {
            return $e['id'] !== $id;
        });
        // re-index
        $events = array_values($events);
        file_put_contents($dataFile, json_encode($events, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
