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

        $stmt = $conn->prepare("INSERT INTO dashboards (user_id, name, layout) VALUES (?, ?, JSON_OBJECT())");
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
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dashboard ID is required']);
            exit();
        }

        // Convert layout to a proper JSON object if it's empty
        $layout = isset($data['layout']) ? $data['layout'] : new stdClass();
        
        // Prepare the layout JSON
        $layout_json = json_encode($layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid layout format: ' . json_last_error_msg()]);
            exit();
        }

        $stmt = $conn->prepare("UPDATE dashboards SET layout = CAST(? AS JSON) WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $layout_json, $data['id'], $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Fetch the updated layout to confirm it was saved correctly
            $verify_stmt = $conn->prepare("SELECT layout FROM dashboards WHERE id = ? AND user_id = ?");
            $verify_stmt->bind_param("ii", $data['id'], $_SESSION['user_id']);
            $verify_stmt->execute();
            $result = $verify_stmt->get_result();
            $updated = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'message' => 'Dashboard updated successfully',
                'layout' => json_decode($updated['layout'])
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error updating dashboard: ' . $conn->error]);
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

        $stmt = $conn->prepare("SELECT id, name, layout, created_at, updated_at FROM dashboards WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $_GET['id'], $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard = $result->fetch_assoc();

        if ($dashboard) {
            // Ensure layout is properly decoded
            $dashboard['layout'] = json_decode($dashboard['layout']);
            
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