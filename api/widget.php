<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $dashboard_id = (int)$_POST['dashboard_id'];
        
        // Verify dashboard belongs to user
        $sql = "SELECT layout FROM dashboards WHERE id = $dashboard_id AND user_id = " . $_SESSION['user_id'];
        $result = $conn->query($sql);
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Dashboard not found']);
            exit();
        }

        $dashboard = $result->fetch_assoc();
        $layout = json_decode($dashboard['layout'], true) ?: [];

        // Create new widget
        $widget = [
            'id' => uniqid(),
            'title' => $_POST['title'],
            'width' => (int)$_POST['width'],
            'height' => (int)$_POST['height'],
            'webhookUrl' => $_POST['webhookUrl'],
            'inputs' => []
        ];

        // Process input types
        $inputTypes = $_POST['inputType'];
        if (!is_array($inputTypes)) {
            $inputTypes = [$inputTypes];
        }

        foreach ($inputTypes as $type) {
            switch ($type) {
                case 'text':
                    $widget['inputs'][] = [
                        'type' => 'text',
                        'label' => 'Enter text'
                    ];
                    break;
                case 'label':
                    $widget['inputs'][] = [
                        'type' => 'label',
                        'text' => 'Label text'
                    ];
                    break;
                case 'button':
                    $widget['inputs'][] = [
                        'type' => 'button',
                        'label' => 'Trigger'
                    ];
                    break;
            }
        }

        // Add widget to layout
        $layout[$widget['id']] = $widget;

        // Update dashboard
        $layout_json = $conn->real_escape_string(json_encode($layout));
        $sql = "UPDATE dashboards SET layout = '$layout_json' WHERE id = $dashboard_id";
        
        if ($conn->query($sql)) {
            echo json_encode([
                'success' => true,
                'widget' => $widget
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error creating widget']);
        }
        break;

    case 'DELETE':
        $dashboard_id = (int)$_GET['dashboard_id'];
        $widget_id = $_GET['widget_id'];

        // Verify dashboard belongs to user
        $sql = "SELECT layout FROM dashboards WHERE id = $dashboard_id AND user_id = " . $_SESSION['user_id'];
        $result = $conn->query($sql);
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Dashboard not found']);
            exit();
        }

        $dashboard = $result->fetch_assoc();
        $layout = json_decode($dashboard['layout'], true) ?: [];

        // Remove widget
        unset($layout[$widget_id]);

        // Update dashboard
        $layout_json = $conn->real_escape_string(json_encode($layout));
        $sql = "UPDATE dashboards SET layout = '$layout_json' WHERE id = $dashboard_id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error deleting widget']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 