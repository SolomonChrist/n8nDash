<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get user's dashboards
$sql = "SELECT * FROM dashboards WHERE user_id = " . $_SESSION['user_id'];
$dashboards = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Get current dashboard
$current_dashboard_id = $_GET['id'] ?? ($dashboards[0]['id'] ?? null);
$current_dashboard = null;

if ($current_dashboard_id) {
    $sql = "SELECT * FROM dashboards WHERE id = $current_dashboard_id AND user_id = " . $_SESSION['user_id'];
    $current_dashboard = $conn->query($sql)->fetch_assoc();
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
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-area"></div>
        <h5 class="mb-3">Dashboards</h5>
        <div class="list-group mb-3">
            <?php foreach ($dashboards as $dashboard): ?>
                <a href="?id=<?php echo $dashboard['id']; ?>" 
                   class="list-group-item list-group-item-action <?php echo $dashboard['id'] == $current_dashboard_id ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($dashboard['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#newDashboardModal">
            New Dashboard
        </button>
        <?php if ($current_dashboard): ?>
            <button class="btn btn-secondary w-100 mb-2" onclick="downloadDashboard()">Download Dashboard</button>
            <button class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#importDashboardModal">
                Import Dashboard
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
                <p>Create your first dashboard to get started!</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <div id="statusMessage"></div>
        <div>
            Powered by <a href="https://n8ndash.com" target="_blank">n8nDash</a> | 
            Learn more about <a href="https://skool.com" target="_blank">AI + Automation</a>
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
        <?php if ($current_dashboard): ?>
            const currentDashboard = <?php echo $current_dashboard['layout'] ?: '{}'; ?>;
        <?php endif; ?>

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
                    window.location.href = `?id=${data.dashboard_id}`;
                } else {
                    alert(data.error);
                }
            });
        }

        function createWidget() {
            const form = document.getElementById('newWidgetForm');
            const formData = new FormData(form);
            formData.append('dashboard_id', '<?php echo $current_dashboard_id; ?>');

            fetch('api/widget.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error);
                }
            });
        }

        function downloadDashboard() {
            const dashboardData = {
                name: '<?php echo $current_dashboard['name']; ?>',
                layout: currentDashboard
            };
            
            const blob = new Blob([JSON.stringify(dashboardData, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${dashboardData.name.toLowerCase().replace(/\s+/g, '-')}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function importDashboard() {
            const form = document.getElementById('importDashboardForm');
            const formData = new FormData(form);
            formData.append('dashboard_id', '<?php echo $current_dashboard_id; ?>');

            fetch('api/import.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error);
                }
            });
        }

        // Initialize grid with widgets
        if (typeof currentDashboard !== 'undefined') {
            const container = document.getElementById('gridContainer');
            Object.entries(currentDashboard).forEach(([id, widget]) => {
                const widgetElement = document.createElement('div');
                widgetElement.className = 'widget';
                widgetElement.style.gridColumn = `span ${widget.width}`;
                widgetElement.style.gridRow = `span ${widget.height}`;
                widgetElement.innerHTML = `
                    <h5>${widget.title}</h5>
                    ${widget.inputs.map(input => {
                        switch (input.type) {
                            case 'text':
                                return `<input type="text" class="form-control mb-2" placeholder="${input.label}">`;
                            case 'label':
                                return `<div class="mb-2">${input.text}</div>`;
                            case 'button':
                                return `<button class="btn btn-primary mb-2" onclick="triggerWebhook('${widget.webhookUrl}')">${input.label}</button>`;
                        }
                    }).join('')}
                `;
                container.appendChild(widgetElement);
            });
        }

        async function triggerWebhook(url) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                document.getElementById('statusMessage').textContent = 'Webhook triggered successfully';
                setTimeout(() => {
                    document.getElementById('statusMessage').textContent = '';
                }, 3000);
            } catch (error) {
                document.getElementById('statusMessage').textContent = 'Error triggering webhook';
            }
        }
    </script>
</body>
</html> 