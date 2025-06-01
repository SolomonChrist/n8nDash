<?php
// Set error reporting for installation
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to validate database connection
function testDatabaseConnection($host, $user, $pass) {
    try {
        $conn = new mysqli($host, $user, $pass);
        if ($conn->connect_error) {
            return ['success' => false, 'message' => "Connection failed: " . $conn->connect_error];
        }
        return ['success' => true, 'connection' => $conn];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Connection failed: " . $e->getMessage()];
    }
}

// Function to create config file
function createConfigFile($host, $user, $pass, $dbname) {
    $config = <<<EOT
<?php
define('DB_HOST', '$host');
define('DB_USER', '$user');
define('DB_PASS', '$pass');
define('DB_NAME', '$dbname');
EOT;
    
    return file_put_contents('../config/database.php', $config) !== false;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {	
	
    $host = $_POST['db_host'] ?? 'localhost';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';
    $dbname = $_POST['db_name'] ?? 'n8n_dashboard';

    // Test connection
    $result = testDatabaseConnection($host, $user, $pass);
	
    if ($result['success']) {
        $conn = $result['connection'];
        
		// Select the database
        if (!$conn->select_db($dbname)) {
            die("Could not select database '$dbname': " . $conn->error);
        }
		
        // Create config file
        if (!createConfigFile($host, $user, $pass, $dbname)) {
            die("Error: Could not create config file");
        }
		
        // Read and execute SQL file
        $sql = file_get_contents('init.sql');
		
        $sql = str_replace('$2y$10$YourDefaultHashHere', password_hash('password', PASSWORD_DEFAULT), $sql);

        if ($conn->multi_query($sql)) {
			do {
				// If there is a result set (SELECT, SHOW, etc.)
				if ($result = $conn->store_result()) {
					$result->free();
				}
			} while ($conn->more_results() && $conn->next_result());

			// Installation complete
			header('Location: ../index.php');
			exit;
		} else {
			die("❌ Error executing SQL: " . $conn->error);
		}
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>n8nDash Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">n8nDash Installation</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="db_host" class="form-label">Database Host</label>
                                <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                            </div>
                            <div class="mb-3">
                                <label for="db_user" class="form-label">Database Username</label>
                                <input type="text" class="form-control" id="db_user" name="db_user" required>
                            </div>
                            <div class="mb-3">
                                <label for="db_pass" class="form-label">Database Password</label>
                                <input type="password" class="form-control" id="db_pass" name="db_pass">
                            </div>
                            <div class="mb-3">
                                <label for="db_name" class="form-label">Database Name</label>
                                <input type="text" class="form-control" id="db_name" name="db_name" value="n8n_dashboard" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Install</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 