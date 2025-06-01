<?php
session_start();
require_once '../config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        // Create new dashboard
        if (!isset($_POST['name']) || trim($_POST['name']) === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Dashboard name is required']);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO dashboards (user_id, name, layout) VALUES (?, ?, '{}')");
        $name = trim($_POST['name']);
        $stmt->bind_param("is", $_SESSION['user_id'], $name);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'dashboard_id' => $conn->insert_id,
                'message' => 'Dashboard created successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error creating dashboard']);
        }
        break;

    case 'PUT':
        // Update dashboard layout (for widget management)
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || !isset($data['layout'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dashboard ID and layout are required']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE dashboards SET layout = ? WHERE id = ? AND user_id = ?");
        $layout = json_encode($data['layout']);
        $stmt->bind_param("sii", $layout, $data['id'], $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Dashboard updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error updating dashboard']);
        }
        break;

    case 'DELETE':
        // Delete dashboard
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dashboard ID is required']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM dashboards WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $_GET['id'], $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Dashboard deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error deleting dashboard']);
        }
        break;

    case 'GET':
        // Get dashboard details
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dashboard ID is required']);
            exit();
        }

        $stmt = $conn->prepare("SELECT * FROM dashboards WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $_GET['id'], $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard = $result->fetch_assoc();

        if ($dashboard) {
            echo json_encode([
                'success' => true,
                'dashboard' => $dashboard
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Dashboard not found']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 