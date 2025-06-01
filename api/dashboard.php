<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $name = $conn->real_escape_string($_POST['name']);
        $sql = "INSERT INTO dashboards (user_id, name, layout) VALUES (" . $_SESSION['user_id'] . ", '$name', '{}')";
        
        if ($conn->query($sql)) {
            echo json_encode([
                'success' => true,
                'dashboard_id' => $conn->insert_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error creating dashboard']);
        }
        break;

    case 'DELETE':
        $id = $conn->real_escape_string($_GET['id']);
        $sql = "DELETE FROM dashboards WHERE id = $id AND user_id = " . $_SESSION['user_id'];
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error deleting dashboard']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 