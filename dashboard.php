<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Connect to DB
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare statement for getting dashboards
$stmt = $conn->prepare("SELECT * FROM dashboards WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$dashboards = $result->fetch_all(MYSQLI_ASSOC);

// Get current dashboard if an ID is specified
$current_dashboard = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM dashboards WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $_GET['id'], $_SESSION['user_id']);
    $stmt->execute();
    $current_dashboard = $stmt->get_result()->fetch_assoc();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="n8nDash - A professional dashboard for managing and triggering n8n automations. Create custom dashboards with widgets that integrate with n8n workflows through webhooks.">
    <meta name="keywords" content="n8n, automation, dashboard, webhooks, workflow automation, n8n dashboard">
    <meta name="author" content="Solomon Christ">
    <meta property="og:title" content="n8nDash - Professional n8n Dashboard">
    <meta property="og:description" content="Create custom dashboards with widgets that integrate with n8n workflows through webhooks.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://github.com/SolomonChrist/n8nDash">
    
    <title>n8nDash - Professional n8n Dashboard</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/favicon.png">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom styles -->
    <style>
        .grid-container {
            display: grid;
            grid-template-columns: repeat(160, 1fr);
            grid-template-rows: repeat(90, 1fr);
            gap: 1px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            height: calc(100vh - 200px);
            overflow: auto;
            margin-bottom: 60px;
        }
        .widget {
            background-color: white;
            border: 1px solid #dee2e6;
            padding: 10px;
            position: relative;
            transition: background-color 0.3s ease;
        }
        .widget.needs-update {
            background-color: #ffe6e6;
        }
        .widget.updated {
            background-color: #e6ffe6;
        }
        .widget-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
        }
        .widget-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }
        .widget-controls {
            display: flex;
            gap: 5px;
        }
        .widget-instructions {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 10px;
            padding: 5px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .widget-content {
            position: relative;
        }
        .n8n-trigger-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            padding: 2px 6px;
            font-size: 0.75rem;
        }
        .rss-feed-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .rss-feed-item {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .rss-feed-item:last-child {
            border-bottom: none;
        }
        .update-all-btn {
            margin-left: 10px;
        }
        .sidebar {
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            width: 280px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            overflow-y: auto;
            z-index: 1030;
            display: flex;
            flex-direction: column;
        }
        .main-content {
            margin-left: 280px;
            padding: 20px;
            min-height: calc(100vh - 60px);
            width: calc(100% - 280px);
        }
        .logo-area {
            height: 60px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin-bottom: 20px;
            padding: 10px;
        }
        .logo-area img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .logo-preview {
            max-width: 200px;
            max-height: 60px;
            margin: 10px 0;
        }
        .footer {
            position: fixed;
            bottom: 0;
            right: 0;
            left: 280px;
            background-color: white;
            padding: 10px 20px;
            border-top: 1px solid var(--border-color);
            z-index: 1029;
        }
        .dashboard-list {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 80px;
        }
        /* Professional styling enhancements */
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-text: #f8fafc;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        .sidebar {
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
        }

        .sidebar .btn {
            color: var(--sidebar-text);
            border-color: rgba(255, 255, 255, 0.2);
            margin-bottom: 0.75rem;
            width: 100%;
            text-align: left;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .sidebar .btn:hover {
            background: var(--sidebar-hover);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .sidebar .btn-outline-primary {
            border-color: rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .btn-outline-primary:hover {
            background: var(--sidebar-hover);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .sidebar .list-group-item {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--sidebar-text);
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
        }

        .sidebar .list-group-item:hover {
            background: var(--sidebar-hover);
        }

        .sidebar .list-group-item.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .sidebar-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 280px;
            padding: 20px;
            background: var(--sidebar-bg);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1031;
        }

        .user-menu {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
        }

        .user-menu .user-email {
            font-size: 0.875rem;
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }

        .widget {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .widget:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .widget.needs-update {
            background-color: rgba(255, 230, 230, 0.5);
            border-left: 3px solid var(--danger-color);
        }

        .widget.updated {
            background-color: rgba(230, 255, 230, 0.5);
            border-left: 3px solid var(--success-color);
        }

        .widget-header {
            background: rgba(0, 0, 0, 0.02);
            padding: 10px;
            border-radius: 8px 8px 0 0;
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: darken(var(--primary-color), 10%);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--secondary-color);
        }

        .empty-state img {
            width: 200px;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .tooltip-inner {
            max-width: 200px;
            padding: 8px 12px;
            background-color: var(--secondary-color);
            border-radius: 4px;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            background: var(--light-bg);
            border-radius: 12px 12px 0 0;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid var(--border-color);
            padding: 8px 12px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(101, 99, 255, 0.25);
        }

        .footer {
            background: white;
            box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Add animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .widget {
            animation: fadeIn 0.3s ease;
        }

        /* Empty state illustration for no widgets */
        .empty-dashboard {
            text-align: center;
            padding: 40px;
            color: var(--secondary-color);
        }

        .empty-dashboard img {
            width: 300px;
            margin-bottom: 20px;
        }

        /* Status badge */
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .status-badge.warning {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }

        /* Ensure save button and add widget button are properly aligned */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-right: 20px;
        }

        .dashboard-header .btn-group {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-area">
            <?php if (isset($_SESSION['logo_path'])): ?>
                <img src="<?php echo htmlspecialchars($_SESSION['logo_path']); ?>" alt="Company Logo">
            <?php endif; ?>
        </div>
        
        <!-- User Menu -->
        <div class="user-menu">
            <div class="user-email"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></div>
            <button class="btn btn-outline-light btn-sm" onclick="showUserSettings()">
                ⚙️ Settings
            </button>
        </div>

        <a href="dashboard.php" class="btn btn-outline-light">
            🏠 Home
        </a>
        <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#newDashboardModal">
            ➕ New Dashboard
        </button>
        
        <h5 class="mt-4 mb-3 text-light">My Dashboards</h5>
        <div class="list-group dashboard-list mb-3">
            <?php foreach ($dashboards as $dashboard): ?>
                <a href="?id=<?php echo $dashboard['id']; ?>" 
                   class="list-group-item list-group-item-action <?php echo ($current_dashboard && $dashboard['id'] == $current_dashboard['id']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($dashboard['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="sidebar-footer">
            <a href="logout.php" class="btn btn-danger w-100">
                🚪 Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <?php if ($current_dashboard): ?>
            <div class="dashboard-header">
                <h2><?php echo htmlspecialchars($current_dashboard['name']); ?></h2>
                <div class="btn-group">
                    <?php if ($current_dashboard): ?>
                        <button class="btn btn-secondary" onclick="downloadDashboard()">
                            ⬇️ Export Dashboard
                        </button>
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importDashboardModal">
                            ⬆️ Import Dashboard
                        </button>
                    <?php endif; ?>
                    <button id="saveButton" class="btn btn-success" onclick="saveLayout(true)">
                        💾 Save Dashboard
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newWidgetModal">
                        Add Widget
                    </button>
                </div>
            </div>
            <div id="saveStatus" class="alert alert-success d-none position-fixed top-0 end-0 m-3" style="z-index: 1050;">
                Changes saved successfully!
            </div>
            <div class="grid-container" id="gridContainer"></div>
        <?php else: ?>
            <div class="text-center mt-5">
                <h3>Welcome to n8nDash</h3>
                <p>Your Dashboards:</p>
                <div class="row row-cols-1 row-cols-md-3 g-4 mt-3">
                    <?php foreach ($dashboards as $dashboard): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($dashboard['name']); ?></h5>
                                    <p class="card-text">
                                        Created: <?php echo date('M j, Y', strtotime($dashboard['created_at'])); ?>
                                    </p>
                                    <a href="?id=<?php echo $dashboard['id']; ?>" class="btn btn-primary">Open Dashboard</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body text-center d-flex align-items-center justify-content-center">
                                <button class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#newDashboardModal">
                                    ➕ Create New Dashboard
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <div id="statusMessage"></div>
        <div>
            Powered by <a href="https://github.com/SolomonChrist/n8nDash" target="_blank">n8nDash</a> v1.0.0 | 
            By <a href="https://www.linkedin.com/in/solomonchrist0/" target="_blank">Solomon Christ</a> | 
            Built for <a href="https://n8n.io" target="_blank">n8n</a> | 
            Learn more about <a href="https://www.skool.com/learn-automation/about" target="_blank">AI + Automation</a>
        </div>
    </div>

    <!-- Add loading overlay -->
    <div id="loadingOverlay" class="loading-overlay d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="newDashboardModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Dashboard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newDashboardForm">
                        <div class="mb-3">
                            <label class="form-label">Dashboard Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="createDashboard()">Create</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newWidgetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Widget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newWidgetForm">
                        <div class="mb-3">
                            <label class="form-label">Widget Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Size (columns x rows)</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" name="width" min="1" max="160" required>
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="height" min="1" max="90" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Widget Type</label>
                            <select class="form-select" name="widgetType">
                                <option value="n8n">n8n</option>
                                <option value="rss">RSS</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instructions</label>
                            <textarea class="form-control" name="instructions"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">n8n Webhook URL</label>
                            <input type="url" class="form-control" name="webhookUrl">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">RSS Feed URL</label>
                            <input type="url" class="form-control" name="feedUrl">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Count</label>
                            <input type="number" class="form-control" name="itemCount" min="1" max="100">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="createWidget()">Create</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importDashboardModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Dashboard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="importDashboardForm">
                        <div class="mb-3">
                            <label class="form-label">Dashboard JSON</label>
                            <textarea class="form-control" name="json" rows="10" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="importDashboard()">Import</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add drag-and-drop library -->
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Widget types enum
        const WidgetType = {
            N8N: 'n8n',
            RSS: 'rss'
        };

        // Initialize dashboard layout
        const gridContainer = document.getElementById('gridContainer');
        let currentLayout = {};

        <?php if ($current_dashboard): ?>
            try {
                const layoutData = <?php 
                    $layout = $current_dashboard['layout'] ?? "{}";
                    if (is_string($layout)) {
                        echo $layout;
                    } else {
                        echo json_encode($layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }
                ?>;
                
                // Convert layout to object if it's an array
                if (Array.isArray(layoutData)) {
                    layoutData.forEach(widget => {
                        if (widget.id) {
                            currentLayout[widget.id] = widget;
                        }
                    });
                } else {
                    currentLayout = layoutData || {};
                }
                
                renderLayout();
                initializeDragAndDrop();
            } catch (e) {
                console.error('Error parsing layout:', e);
                showError('Error loading dashboard layout: ' + e.message);
                currentLayout = {};
            }
        <?php endif; ?>

        // Create new dashboard
        function createDashboard() {
            const form = document.getElementById('newDashboardForm');
            const formData = new FormData(form);

            fetch('api/dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `dashboard.php?id=${data.dashboard_id}`;
                } else {
                    alert(data.error || 'Error creating dashboard');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating dashboard');
            });
        }

        // Add to your JavaScript section:
        let hasUnsavedChanges = false;
        let autoSaveTimeout = null;

        // Initialize keyboard shortcuts and autosave
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    saveLayout(true);
                }
            });

            // Add beforeunload event listener
            window.addEventListener('beforeunload', function(e) {
                if (hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                    return e.returnValue;
                }
            });

            // Update save button state
            updateSaveButtonState();
        });

        // Function to update save button state
        function updateSaveButtonState() {
            const saveButton = document.getElementById('saveButton');
            if (saveButton) {
                if (hasUnsavedChanges) {
                    saveButton.classList.add('btn-warning');
                    saveButton.innerHTML = '💾 Save Changes*';
                } else {
                    saveButton.classList.remove('btn-warning');
                    saveButton.innerHTML = '💾 Save Dashboard';
                }
            }
        }

        // Function to show save status
        function showSaveStatus(success = true) {
            const saveStatus = document.getElementById('saveStatus');
            saveStatus.textContent = success ? 'Changes saved successfully!' : 'Failed to save changes';
            saveStatus.className = `alert alert-${success ? 'success' : 'danger'} position-fixed top-0 end-0 m-3`;
            saveStatus.style.display = 'block';
            
            setTimeout(() => {
                saveStatus.style.display = 'none';
            }, 2000);
        }

        // Create new widget
        function createWidget() {
            const form = document.getElementById('newWidgetForm');
            const formData = new FormData(form);
            const widgetType = formData.get('widgetType');
            
            const widget = {
                id: Date.now(),
                title: formData.get('title'),
                type: widgetType,
                instructions: formData.get('instructions'),
                width: parseInt(formData.get('width')),
                height: parseInt(formData.get('height')),
                position: findEmptySpace(parseInt(formData.get('width')), parseInt(formData.get('height'))),
                needsUpdate: true,
                config: {}
            };

            if (widgetType === WidgetType.N8N) {
                widget.config = {
                    webhookUrl: formData.get('webhookUrl'),
                    httpMethod: formData.get('httpMethod') || 'GET',
                    variables: formData.get('variables') ? JSON.parse(formData.get('variables')) : {}
                };
            } else if (widgetType === WidgetType.RSS) {
                widget.config = {
                    feedUrl: formData.get('feedUrl'),
                    itemCount: parseInt(formData.get('itemCount'))
                };
            }

            currentLayout[widget.id] = widget;
            hasUnsavedChanges = true;
            updateSaveButtonState();
            
            // Save immediately when creating a new widget
            saveLayout(true).then(() => {
                renderLayout();
                initializeDragAndDrop();
                bootstrap.Modal.getInstance(document.getElementById('newWidgetModal')).hide();
                form.reset();
            }).catch(error => {
                showError('Failed to save widget: ' + error.message);
            });
        }

        // Find empty space for new widget
        function findEmptySpace(width, height) {
            const occupied = new Set();
            
            // Mark occupied spaces
            Object.values(currentLayout).forEach(widget => {
                for (let x = widget.position.x; x < widget.position.x + widget.width; x++) {
                    for (let y = widget.position.y; y < widget.position.y + widget.height; y++) {
                        occupied.add(`${x},${y}`);
                    }
                }
            });

            // Find first available space
            for (let y = 0; y < 90; y++) {
                for (let x = 0; x < 160; x++) {
                    let fits = true;
                    for (let dx = 0; dx < width; dx++) {
                        for (let dy = 0; dy < height; dy++) {
                            if (occupied.has(`${x + dx},${y + dy}`)) {
                                fits = false;
                                break;
                            }
                        }
                        if (!fits) break;
                    }
                    if (fits) {
                        return { x, y };
                    }
                }
            }
            return { x: 0, y: 0 }; // Fallback
        }

        // Render dashboard layout
        function renderLayout() {
            gridContainer.innerHTML = '';
            
            // Remove existing "Update All" button if it exists
            const existingUpdateBtn = document.querySelector('.update-all-btn');
            if (existingUpdateBtn) {
                existingUpdateBtn.remove();
            }
            
            // Add "Update All" button if there are n8n widgets
            const hasN8nWidgets = Object.values(currentLayout).some(w => w.type === WidgetType.N8N);
            if (hasN8nWidgets) {
                const updateAllBtn = document.createElement('button');
                updateAllBtn.className = 'btn btn-primary update-all-btn';
                updateAllBtn.innerHTML = '🔄 Update All Widgets';
                updateAllBtn.onclick = updateAllN8nWidgets;
                document.querySelector('.main-content > div:first-child').appendChild(updateAllBtn);
            }
            
            Object.values(currentLayout).forEach(widget => {
                const elem = document.createElement('div');
                elem.className = `widget ${widget.needsUpdate ? 'needs-update' : 'updated'}`;
                elem.setAttribute('data-widget-id', widget.id);
                elem.style.gridColumn = `${widget.position.x + 1} / span ${widget.width}`;
                elem.style.gridRow = `${widget.position.y + 1} / span ${widget.height}`;
                
                // Reset any transform that might have been applied during dragging
                elem.style.transform = '';
                elem.removeAttribute('data-x');
                elem.removeAttribute('data-y');
                
                // Widget header
                elem.innerHTML = `
                    <div class="widget-header">
                        <h5 class="widget-title">${widget.title}</h5>
                        <div class="widget-controls">
                            ${widget.type === WidgetType.N8N ? 
                              `<button class="btn btn-sm btn-outline-primary" onclick="triggerN8nWidget(${widget.id})">🔄</button>` : ''}
                            <button class="btn btn-sm btn-outline-secondary" onclick="editWidget(${widget.id})">✏️</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteWidget(${widget.id})">×</button>
                        </div>
                    </div>
                `;

                // Widget instructions if present
                if (widget.instructions) {
                    elem.innerHTML += `
                        <div class="widget-instructions">
                            ${widget.instructions}
                        </div>
                    `;
                }

                // Widget content based on type
                elem.innerHTML += `<div class="widget-content" id="widget-content-${widget.id}"></div>`;
                
                gridContainer.appendChild(elem);
                
                // Initialize widget content
                updateWidgetContent(widget);
            });
        }

        // Update widget content based on type
        async function updateWidgetContent(widget) {
            const contentElem = document.getElementById(`widget-content-${widget.id}`);
            
            switch(widget.type) {
                case WidgetType.N8N:
                    if (widget.needsUpdate) {
                        contentElem.innerHTML = '<div class="alert alert-warning">Data needs update</div>';
                    } else {
                        contentElem.innerHTML = '<div class="alert alert-success">Data is current</div>';
                    }
                    break;
                    
                case WidgetType.RSS:
                    try {
                        const response = await fetch(`api/rss.php?url=${encodeURIComponent(widget.config.feedUrl)}&count=${widget.config.itemCount}`);
                        const data = await response.json();
                        
                        if (data.success) {
                            contentElem.innerHTML = `
                                <ul class="rss-feed-list">
                                    ${data.items.map(item => `
                                        <li class="rss-feed-item">
                                            <a href="${item.link}" target="_blank">${item.title}</a>
                                        </li>
                                    `).join('')}
                                </ul>
                            `;
                        } else {
                            contentElem.innerHTML = '<div class="alert alert-danger">Error loading RSS feed</div>';
                        }
                    } catch (error) {
                        contentElem.innerHTML = '<div class="alert alert-danger">Error loading RSS feed</div>';
                    }
                    break;
            }
        }

        // Update all n8n widgets
        async function updateAllN8nWidgets() {
            const n8nWidgets = Object.values(currentLayout).filter(w => w.type === WidgetType.N8N);
            
            for (const widget of n8nWidgets) {
                await triggerN8nWidget(widget.id);
            }
        }

        // Trigger specific n8n widget
        async function triggerN8nWidget(id) {
            const widget = currentLayout[id];
            if (!widget.config.webhookUrl) {
                showError('No webhook URL configured');
                return;
            }

            try {
                let url = widget.config.webhookUrl;
                const method = widget.config.httpMethod || 'GET';
                const variables = widget.config.variables || {};

                // For GET requests, append variables to URL
                if (method === 'GET' && Object.keys(variables).length > 0) {
                    const params = new URLSearchParams(variables);
                    url += (url.includes('?') ? '&' : '?') + params.toString();
                }

                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    }
                };

                // For POST requests, add variables to body
                if (method === 'POST' && Object.keys(variables).length > 0) {
                    options.body = JSON.stringify(variables);
                }

                const response = await fetch(url, options);
                
                if (response.ok) {
                    widget.needsUpdate = false;
                    saveLayout(false);
                    renderLayout();
                    showSuccess('Webhook triggered successfully');
                } else {
                    const errorText = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
                }
            } catch (error) {
                console.error('Error:', error);
                showError(`Error triggering webhook: ${error.message}`);
            }
        }

        // Initialize drag and drop
        function initializeDragAndDrop() {
            interact('.widget').draggable({
                inertia: false, // Disable inertia for more precise positioning
                modifiers: [
                    interact.modifiers.snap({
                        targets: [
                            interact.snappers.grid({
                                x: gridContainer.clientWidth / 160,
                                y: gridContainer.clientHeight / 90
                            })
                        ],
                        range: Infinity,
                        relativePoints: [{ x: 0, y: 0 }]
                    }),
                    interact.modifiers.restrict({
                        restriction: 'parent',
                        elementRect: { top: 0, left: 0, bottom: 1, right: 1 }
                    })
                ],
                listeners: {
                    move: dragMoveListener,
                    end: dragEndListener
                }
            });
        }

        function dragMoveListener(event) {
            const target = event.target;
            const widget = currentLayout[target.getAttribute('data-widget-id')];
            
            // Calculate grid position
            const gridX = Math.round(event.dx / (gridContainer.clientWidth / 160));
            const gridY = Math.round(event.dy / (gridContainer.clientHeight / 90));
            
            // Update widget position
            widget.position.x = Math.max(0, Math.min(widget.position.x + gridX, 160 - widget.width));
            widget.position.y = Math.max(0, Math.min(widget.position.y + gridY, 90 - widget.height));
            
            // Update element position
            target.style.gridColumn = `${widget.position.x + 1} / span ${widget.width}`;
            target.style.gridRow = `${widget.position.y + 1} / span ${widget.height}`;
        }

        function dragEndListener(event) {
            const target = event.target;
            const widgetId = target.getAttribute('data-widget-id');
            const widget = currentLayout[widgetId];
            
            hasUnsavedChanges = true;
            updateSaveButtonState();
            
            // Clear existing timeout
            if (autoSaveTimeout) {
                clearTimeout(autoSaveTimeout);
            }
            
            // Set new timeout for autosave
            autoSaveTimeout = setTimeout(() => {
                saveLayout(false);
            }, 2000);
        }

        // Save layout to server
        async function saveLayout(showFeedback = false) {
            const dashboardId = <?php echo $current_dashboard ? $current_dashboard['id'] : 'null'; ?>;
            if (!dashboardId) {
                const error = new Error('No dashboard selected');
                showError(error.message);
                throw error;
            }

            try {
                const response = await fetch('api/dashboard.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: dashboardId,
                        layout: currentLayout
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    hasUnsavedChanges = false;
                    updateSaveButtonState();
                    if (showFeedback) {
                        showSuccess('Dashboard saved successfully');
                    }
                } else {
                    throw new Error(data.error || 'Failed to save dashboard');
                }
            } catch (error) {
                console.error('Error:', error);
                hasUnsavedChanges = true;
                updateSaveButtonState();
                showError('Error saving dashboard: ' + error.message);
                throw error;
            }
        }

        // Delete widget
        function deleteWidget(id) {
            if (confirm('Are you sure you want to delete this widget?')) {
                delete currentLayout[id];
                hasUnsavedChanges = true;
                updateSaveButtonState();
                saveLayout(false); // Autosave
                renderLayout();
            }
        }

        // Export dashboard
        function downloadDashboard() {
            const data = {
                name: <?php echo $current_dashboard ? json_encode($current_dashboard['name']) : '""'; ?>,
                layout: Object.values(currentLayout)
            };
            
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `dashboard_${<?php echo $current_dashboard ? $current_dashboard['id'] : '0'; ?>}_${Date.now()}.json`;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Import dashboard
        function importDashboard() {
            const form = document.getElementById('importDashboardForm');
            const jsonText = form.elements.json.value;

            try {
                const data = JSON.parse(jsonText);
                if (!data.layout || !Array.isArray(data.layout)) {
                    throw new Error('Invalid dashboard format: layout must be an array');
                }

                // Convert array to object with widget IDs as keys
                const newLayout = {};
                data.layout.forEach(widget => {
                    if (!widget.id) {
                        widget.id = Date.now() + Math.random().toString(36).substr(2, 9);
                    }
                    newLayout[widget.id] = widget;
                });

                currentLayout = newLayout;
                hasUnsavedChanges = true;
                updateSaveButtonState();
                
                saveLayout(true).then(() => {
                    renderLayout();
                    bootstrap.Modal.getInstance(document.getElementById('importDashboardModal')).hide();
                    form.reset();
                    showSuccess('Dashboard imported successfully');
                }).catch(error => {
                    showError('Failed to import dashboard: ' + error.message);
                });
            } catch (e) {
                showError('Invalid dashboard format: ' + e.message);
            }
        }

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Show/hide loading overlay
        function toggleLoading(show) {
            document.getElementById('loadingOverlay').classList.toggle('d-none', !show);
        }

        // Enhanced error handling
        function showError(message) {
            const statusMessage = document.getElementById('statusMessage');
            statusMessage.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            setTimeout(() => {
                statusMessage.innerHTML = '';
            }, 5000);
        }

        // Show success message
        function showSuccess(message) {
            const statusMessage = document.getElementById('statusMessage');
            statusMessage.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            setTimeout(() => {
                statusMessage.innerHTML = '';
            }, 3000);
        }

        // Edit widget
        function editWidget(id) {
            const widget = currentLayout[id];
            const form = document.getElementById('editWidgetForm');
            
            // Set widget ID
            form.elements.widgetId.value = id;
            
            // Set general properties
            form.elements.title.value = widget.title;
            form.elements.width.value = widget.width;
            form.elements.height.value = widget.height;
            form.elements.instructions.value = widget.instructions || '';
            
            // Show/hide and set type-specific properties
            const n8nProperties = form.querySelector('.n8n-properties');
            const rssProperties = form.querySelector('.rss-properties');
            
            if (widget.type === WidgetType.N8N) {
                n8nProperties.style.display = 'block';
                rssProperties.style.display = 'none';
                form.elements.webhookUrl.value = widget.config.webhookUrl || '';
                form.elements.httpMethod.value = widget.config.httpMethod || 'GET';
                form.elements.variables.value = widget.config.variables ? 
                    JSON.stringify(widget.config.variables, null, 2) : '';
            } else if (widget.type === WidgetType.RSS) {
                n8nProperties.style.display = 'none';
                rssProperties.style.display = 'block';
                form.elements.feedUrl.value = widget.config.feedUrl || '';
                form.elements.itemCount.value = widget.config.itemCount || 5;
            }
            
            new bootstrap.Modal(document.getElementById('editWidgetModal')).show();
        }

        function updateWidget() {
            const form = document.getElementById('editWidgetForm');
            const widgetId = form.elements.widgetId.value;
            const widget = currentLayout[widgetId];
            
            // Update general properties
            widget.title = form.elements.title.value;
            widget.width = parseInt(form.elements.width.value);
            widget.height = parseInt(form.elements.height.value);
            widget.instructions = form.elements.instructions.value;
            
            // Update type-specific properties
            if (widget.type === WidgetType.N8N) {
                try {
                    widget.config = {
                        webhookUrl: form.elements.webhookUrl.value,
                        httpMethod: form.elements.httpMethod.value,
                        variables: form.elements.variables.value ? 
                            JSON.parse(form.elements.variables.value) : {}
                    };
                } catch (error) {
                    showError('Invalid JSON in variables field');
                    return;
                }
            } else if (widget.type === WidgetType.RSS) {
                widget.config = {
                    feedUrl: form.elements.feedUrl.value,
                    itemCount: parseInt(form.elements.itemCount.value)
                };
            }
            
            // Save changes
            hasUnsavedChanges = true;
            updateSaveButtonState();
            saveLayout(true).then(() => {
                renderLayout();
                bootstrap.Modal.getInstance(document.getElementById('editWidgetModal')).hide();
            }).catch(error => {
                showError('Failed to save widget: ' + error.message);
            });
        }

        // Add this JavaScript to handle showing/hiding properties in the new widget modal
        document.addEventListener('DOMContentLoaded', function() {
            const newWidgetForm = document.getElementById('newWidgetForm');
            if (newWidgetForm) {
                const typeSelect = newWidgetForm.elements.widgetType;
                const n8nFields = newWidgetForm.querySelector('.n8n-properties');
                const rssFields = newWidgetForm.querySelector('.rss-properties');

                typeSelect.addEventListener('change', function() {
                    if (this.value === WidgetType.N8N) {
                        n8nFields.style.display = 'block';
                        rssFields.style.display = 'none';
                    } else if (this.value === WidgetType.RSS) {
                        n8nFields.style.display = 'none';
                        rssFields.style.display = 'block';
                    }
                });

                // Trigger initial state
                typeSelect.dispatchEvent(new Event('change'));
            }
        });

        // Show user settings modal
        function showUserSettings() {
            new bootstrap.Modal(document.getElementById('userSettingsModal')).show();
        }

        // Update user settings
        function updateUserSettings() {
            const form = document.getElementById('userSettingsForm');
            const formData = new FormData(form);

            if (formData.get('newPassword') !== formData.get('confirmPassword')) {
                showError('New passwords do not match');
                return;
            }

            fetch('api/user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Settings updated successfully');
                    bootstrap.Modal.getInstance(document.getElementById('userSettingsModal')).hide();
                    form.reset();
                } else {
                    showError(data.error || 'Error updating settings');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error updating settings');
            });
        }

        function previewLogo(input) {
            const preview = document.getElementById('logoPreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Check file size (1MB limit)
                if (file.size > 1024 * 1024) {
                    showError('Logo file size must be less than 1MB');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '200px';
                    img.style.maxHeight = '60px';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        }

        function saveSettings() {
            const activeTab = document.querySelector('.nav-tabs .active').getAttribute('href');
            
            if (activeTab === '#passwordTab') {
                updateUserSettings();
            } else if (activeTab === '#logoTab') {
                updateLogo();
            }
        }

        function updateLogo() {
            const form = document.getElementById('logoSettingsForm');
            const formData = new FormData(form);
            
            const fileInput = form.elements.logo;
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                if (file.size > 1024 * 1024) {
                    showError('Logo file size must be less than 1MB');
                    return;
                }
            } else {
                showError('Please select a logo file');
                return;
            }

            fetch('api/upload_logo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showSuccess('Logo updated successfully');
                    // Update logo in sidebar
                    const logoArea = document.querySelector('.logo-area');
                    logoArea.innerHTML = `<img src="${data.logo_path}" alt="Company Logo">`;
                    // Update session path
                    window.sessionStorage.setItem('logo_path', data.logo_path);
                    bootstrap.Modal.getInstance(document.getElementById('userSettingsModal')).hide();
                } else {
                    throw new Error(data.error || 'Error updating logo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error updating logo: ' + error.message);
            });
        }
    </script>

    <!-- Add this new modal before the closing </body> tag -->
    <div class="modal fade" id="editWidgetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Widget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editWidgetForm">
                        <input type="hidden" name="widgetId">
                        <!-- General Properties -->
                        <div class="mb-3">
                            <label class="form-label">Widget Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Size (columns x rows)</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" name="width" min="1" max="160" required>
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="height" min="1" max="90" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instructions</label>
                            <textarea class="form-control" name="instructions"></textarea>
                        </div>
                        <!-- n8n Specific Properties -->
                        <div class="n8n-properties" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">n8n Webhook URL</label>
                                <input type="url" class="form-control" name="webhookUrl">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">HTTP Method</label>
                                <select class="form-select" name="httpMethod">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Variables (JSON format)</label>
                                <textarea class="form-control" name="variables" placeholder='{"key": "value"}'></textarea>
                                <small class="text-muted">Leave empty for no variables</small>
                            </div>
                        </div>
                        <!-- RSS Specific Properties -->
                        <div class="rss-properties" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">RSS Feed URL</label>
                                <input type="url" class="form-control" name="feedUrl">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Item Count</label>
                                <input type="number" class="form-control" name="itemCount" min="1" max="100">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateWidget()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Settings Modal -->
    <div class="modal fade" id="userSettingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#passwordTab">Password</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#logoTab">Logo</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="passwordTab">
                            <form id="userSettingsForm">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="currentPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="newPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirmPassword" required>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="logoTab">
                            <form id="logoSettingsForm">
                                <div class="mb-3">
                                    <label class="form-label">Upload Logo</label>
                                    <input type="file" class="form-control" name="logo" accept="image/*" onchange="previewLogo(this)">
                                    <small class="text-muted">Recommended size: 200x60px. Max file size: 1MB</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Preview</label>
                                    <div id="logoPreview" class="logo-preview">
                                        <?php if (isset($_SESSION['logo_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($_SESSION['logo_path']); ?>" alt="Current logo">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveSettings()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
