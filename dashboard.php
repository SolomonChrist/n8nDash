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
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
            padding: 20px;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .logo-area {
            height: 60px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin-bottom: 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            right: 0;
            left: 250px;
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
        }
        .dashboard-list {
            max-height: calc(100vh - 250px);
            overflow-y: auto;
        }
        /* Professional styling enhancements */
        :root {
            --primary-color: #6563FF;
            --secondary-color: #3F3D56;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--secondary-color) 0%, #2D2B40 100%);
            color: white;
        }

        .sidebar .list-group-item {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .sidebar .list-group-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .list-group-item.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
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
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-area"></div>
        <a href="dashboard.php" class="btn btn-outline-primary w-100 mb-3">
            🏠 Home
        </a>
        <button class="btn btn-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#newDashboardModal">
            ➕ New Dashboard
        </button>
        <h5 class="mb-3">My Dashboards</h5>
        <div class="list-group dashboard-list mb-3">
            <?php foreach ($dashboards as $dashboard): ?>
                <a href="?id=<?php echo $dashboard['id']; ?>" 
                   class="list-group-item list-group-item-action <?php echo ($current_dashboard && $dashboard['id'] == $current_dashboard['id']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($dashboard['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if ($current_dashboard): ?>
            <button class="btn btn-secondary w-100 mb-2" onclick="downloadDashboard()">
                ⬇️ Export Dashboard
            </button>
            <button class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#importDashboardModal">
                ⬆️ Import Dashboard
            </button>
        <?php endif; ?>
    </div>

    <div class="main-content">
        <?php if ($current_dashboard): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?php echo htmlspecialchars($current_dashboard['name']); ?></h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newWidgetModal">
                    Add Widget
                </button>
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
                currentLayout = JSON.parse('<?php echo addslashes($current_dashboard['layout'] ?? "{}"); ?>');
                renderLayout();
                initializeDragAndDrop();
            } catch (e) {
                console.error('Error parsing layout:', e);
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

        // Create new widget
        function createWidget() {
            const form = document.getElementById('newWidgetForm');
            const formData = new FormData(form);
            
            const widget = {
                id: Date.now(),
                title: formData.get('title'),
                type: formData.get('widgetType'),
                instructions: formData.get('instructions'),
                width: parseInt(formData.get('width')),
                height: parseInt(formData.get('height')),
                position: findEmptySpace(parseInt(formData.get('width')), parseInt(formData.get('height'))),
                needsUpdate: true,
                config: {}
            };

            // Add type-specific configuration
            switch(widget.type) {
                case WidgetType.N8N:
                    widget.config.webhookUrl = formData.get('webhookUrl');
                    break;
                case WidgetType.RSS:
                    widget.config.feedUrl = formData.get('feedUrl');
                    widget.config.itemCount = parseInt(formData.get('itemCount'));
                    break;
            }

            currentLayout[widget.id] = widget;
            saveLayout();
            renderLayout();
            initializeDragAndDrop();
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('newWidgetModal')).hide();
            form.reset();
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
                
                // Widget header
                elem.innerHTML = `
                    <div class="widget-header">
                        <h5 class="widget-title">${widget.title}</h5>
                        <div class="widget-controls">
                            ${widget.type === WidgetType.N8N ? 
                              `<button class="btn btn-sm btn-outline-primary" onclick="triggerN8nWidget(${widget.id})">🔄</button>` : ''}
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
                alert('No webhook URL configured');
                return;
            }

            try {
                const response = await fetch(widget.config.webhookUrl);
                if (response.ok) {
                    widget.needsUpdate = false;
                    saveLayout();
                    renderLayout();
                    alert('Webhook triggered successfully');
                } else {
                    throw new Error('Webhook request failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error triggering webhook');
            }
        }

        // Initialize drag and drop
        function initializeDragAndDrop() {
            interact('.widget').draggable({
                inertia: true,
                modifiers: [
                    interact.modifiers.snap({
                        targets: [
                            interact.createSnapGrid({ x: 10, y: 10 })
                        ],
                        range: Infinity,
                        relativePoints: [ { x: 0, y: 0 } ]
                    }),
                    interact.modifiers.restrict({
                        restriction: 'parent',
                        elementRect: { top: 0, left: 0, bottom: 1, right: 1 }
                    })
                ],
                autoScroll: true,
                listeners: {
                    move: dragMoveListener,
                    end: dragEndListener
                }
            });
        }

        function dragMoveListener(event) {
            const target = event.target;
            const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
            const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

            target.style.transform = `translate(${x}px, ${y}px)`;
            target.setAttribute('data-x', x);
            target.setAttribute('data-y', y);
        }

        function dragEndListener(event) {
            const target = event.target;
            const widgetId = target.getAttribute('data-widget-id');
            const widget = currentLayout[widgetId];
            
            // Calculate new grid position
            const x = Math.round(parseFloat(target.getAttribute('data-x')) / 10);
            const y = Math.round(parseFloat(target.getAttribute('data-y')) / 10);
            
            widget.position = { x, y };
            saveLayout();
            renderLayout(); // Re-render to snap to grid
        }

        // Save layout to server
        function saveLayout() {
            fetch('api/dashboard.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: <?php echo $current_dashboard ? $current_dashboard['id'] : 'null'; ?>,
                    layout: currentLayout
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.error || 'Error saving layout');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving layout');
            });
        }

        // Delete widget
        function deleteWidget(id) {
            if (confirm('Are you sure you want to delete this widget?')) {
                delete currentLayout[id];
                saveLayout();
                renderLayout();
            }
        }

        // Export dashboard
        function downloadDashboard() {
            const data = {
                name: <?php echo $current_dashboard ? json_encode($current_dashboard['name']) : '""'; ?>,
                layout: currentLayout
            };
            
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'dashboard.json';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Import dashboard
        function importDashboard() {
            const form = document.getElementById('importDashboardForm');
            const jsonText = form.elements.json.value;

            try {
                const data = JSON.parse(jsonText);
                currentLayout = data.layout || {};
                saveLayout();
                renderLayout();
                bootstrap.Modal.getInstance(document.getElementById('importDashboardModal')).hide();
                form.reset();
            } catch (e) {
                alert('Invalid JSON format');
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

        // Enhance existing functions with loading states and better feedback
        async function createDashboard() {
            toggleLoading(true);
            try {
                // ... existing createDashboard code ...
            } catch (error) {
                showError('Failed to create dashboard. Please try again.');
            } finally {
                toggleLoading(false);
            }
        }
    </script>
</body>
</html> 
