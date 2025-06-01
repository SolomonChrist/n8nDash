<?php
session_start();
require_once '../config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Validate input
if (!isset($_GET['url']) || !filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid RSS feed URL']);
    exit();
}

$url = $_GET['url'];
$count = isset($_GET['count']) ? min(max(1, intval($_GET['count'])), 100) : 10;

// Load and parse RSS feed
try {
    $rss = simplexml_load_file($url);
    if ($rss === false) {
        throw new Exception('Failed to parse RSS feed');
    }

    $items = [];
    $i = 0;
    
    // Handle different RSS formats
    $feedItems = $rss->channel->item ?? $rss->entry ?? [];
    
    foreach ($feedItems as $item) {
        if ($i >= $count) break;
        
        $items[] = [
            'title' => (string)($item->title ?? ''),
            'link' => (string)($item->link['href'] ?? $item->link ?? ''),
            'description' => (string)($item->description ?? $item->summary ?? ''),
            'pubDate' => (string)($item->pubDate ?? $item->published ?? '')
        ];
        
        $i++;
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error fetching RSS feed: ' . $e->getMessage()
    ]);
} 