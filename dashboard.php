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
    <title>n8nDash - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            Powered by <a href="https://github.com/SolomonChrist/n8nDash" target="_blank">n8nDash on Github</a> | 
            Learn more about <a href="https://www.skool.com/learn-automation/about" target="_blank">AI + Automation</a>
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
                            <label class="form-label">Input Type</label>
                            <select class="form-select" name="inputType" multiple>
                                <option value="text">Text Input</option>
                                <option value="label">Label</option>
                                <option value="button">Button</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">n8n Webhook URL</label>
                            <input type="url" class="form-control" name="webhookUrl">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize dashboard layout
        const gridContainer = document.getElementById('gridContainer');
        let currentLayout = {};

        <?php if ($current_dashboard): ?>
            try {
                currentLayout = JSON.parse('<?php echo addslashes($current_dashboard['layout'] ?? "{}"); ?>');
                renderLayout();
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
                id: Date.now(), // Temporary ID
                title: formData.get('title'),
                type: Array.from(form.elements.inputType.selectedOptions).map(opt => opt.value),
                width: parseInt(formData.get('width')),
                height: parseInt(formData.get('height')),
                webhookUrl: formData.get('webhookUrl'),
                position: findEmptySpace(parseInt(formData.get('width')), parseInt(formData.get('height')))
            };

            currentLayout[widget.id] = widget;
            saveLayout();
            renderLayout();
            
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
            
            Object.values(currentLayout).forEach(widget => {
                const elem = document.createElement('div');
                elem.className = 'widget';
                elem.style.gridColumn = `${widget.position.x + 1} / span ${widget.width}`;
                elem.style.gridRow = `${widget.position.y + 1} / span ${widget.height}`;
                
                // Widget header
                elem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="m-0">${widget.title}</h5>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteWidget(${widget.id})">×</button>
                    </div>
                `;

                // Widget content based on type
                if (widget.type.includes('button')) {
                    elem.innerHTML += `
                        <button class="btn btn-primary w-100" onclick="triggerWebhook(${widget.id})">
                            Trigger
                        </button>
                    `;
                }
                if (widget.type.includes('text')) {
                    elem.innerHTML += `
                        <input type="text" class="form-control mb-2" placeholder="Enter value">
                    `;
                }
                if (widget.type.includes('label')) {
                    elem.innerHTML += `
                        <div class="alert alert-info mb-0">
                            Status: Ready
                        </div>
                    `;
                }

                gridContainer.appendChild(elem);
            });
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

        // Trigger webhook
        function triggerWebhook(id) {
            const widget = currentLayout[id];
            if (!widget.webhookUrl) {
                alert('No webhook URL configured');
                return;
            }

            fetch(widget.webhookUrl)
                .then(response => {
                    alert('Webhook triggered successfully');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error triggering webhook');
                });
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
    </script>
</body>
</html> 
