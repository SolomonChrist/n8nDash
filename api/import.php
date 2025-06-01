<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    $dashboard_id = (int)$_POST['dashboard_id'];
    $json_data = json_decode($_POST['json'], true);

    if (!$json_data || !isset($json_data['name']) || !isset($json_data['layout'])) {
        throw new Exception('Invalid dashboard format');
    }

    // Verify dashboard belongs to user
    $sql = "SELECT id FROM dashboards WHERE id = $dashboard_id AND user_id = " . $_SESSION['user_id'];
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        throw new Exception('Dashboard not found');
    }

    // Update dashboard with imported data
    $name = $conn->real_escape_string($json_data['name']);
    $layout_json = $conn->real_escape_string(json_encode($json_data['layout']));
    
    $sql = "UPDATE dashboards SET name = '$name', layout = '$layout_json' WHERE id = $dashboard_id";
    
    if (!$conn->query($sql)) {
        throw new Exception('Error updating dashboard');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 