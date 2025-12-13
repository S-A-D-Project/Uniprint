<?php
/**
 * Simple Pusher Event Trigger for Demo
 * This script allows the demo to trigger real Pusher events
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

// Validate required fields
$required = ['channel', 'event', 'data'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

// Pusher configuration (from .env)
$pusher_config = [
    'app_id' => '2077111',
    'key' => 'f7ca062b8f895c3f2497',
    'secret' => '9829cf3fa2e92e92ab08',
    'cluster' => 'ap1'
];

try {
    // Simple Pusher HTTP API call
    $channel = $input['channel'];
    $event = $input['event'];
    $data = json_encode($input['data']);
    
    // Create auth string
    $timestamp = time();
    $body_md5 = md5($data);
    
    $string_to_sign = "POST\n/apps/{$pusher_config['app_id']}/events\nauth_key={$pusher_config['key']}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$body_md5}";
    
    $auth_signature = hash_hmac('sha256', $string_to_sign, $pusher_config['secret']);
    
    // Prepare the request
    $url = "https://api-{$pusher_config['cluster']}.pusherapp.com/apps/{$pusher_config['app_id']}/events";
    
    $post_data = json_encode([
        'name' => $event,
        'channel' => $channel,
        'data' => $data
    ]);
    
    $query_params = http_build_query([
        'auth_key' => $pusher_config['key'],
        'auth_timestamp' => $timestamp,
        'auth_version' => '1.0',
        'body_md5' => $body_md5,
        'auth_signature' => $auth_signature
    ]);
    
    // Make the request
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($post_data)
            ],
            'content' => $post_data
        ]
    ]);
    
    $response = file_get_contents($url . '?' . $query_params, false, $context);
    
    if ($response === false) {
        throw new Exception('Failed to trigger Pusher event');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Event triggered successfully',
        'channel' => $channel,
        'event' => $event
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to trigger event: ' . $e->getMessage()
    ]);
}
?>
