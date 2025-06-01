<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Start session
session_start();

require_once 'config/database.php';

// Debug information
error_log("Session data: " . print_r($_SESSION, true));

// Modified login check - allow access if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in, redirecting to index.php");
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Validate passwords match
        if ($newPassword !== $confirmPassword) {
            throw new Exception('Passwords do not match');
        }

        // Validate password strength
        if (strlen($newPassword) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        // Update password and first login status
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, is_first_login = 0 WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("si", $hashedPassword, $_SESSION['user_id']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update password: " . $stmt->error);
        }

        // Clear session
        session_destroy();
        
        // Set success message in cookie
        setcookie('login_message', 'Password changed successfully. Please log in with your new password.', time() + 30, '/');
        
        // Redirect to login page
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Password change error: " . $e->getMessage());
    }
}

// Get user data to verify first login status
try {
    $stmt = $conn->prepare("SELECT is_first_login FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("i", $_SESSION['user_id']);
    if (!$stmt->execute()) {
        throw new Exception("Failed to fetch user data: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        error_log("User not found in database");
        session_destroy();
        header('Location: index.php');
        exit();
    }

    $_SESSION['is_first_login'] = $user['is_first_login'];
    error_log("User first login status: " . $user['is_first_login']);

} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $error = "System error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - n8nDash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Change Password</h2>
                        <p class="text-muted mb-4">
                            <?php if (isset($_SESSION['is_first_login']) && $_SESSION['is_first_login']): ?>
                                Please change your password to continue.
                            <?php else: ?>
                                Update your password below.
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                                <div class="mt-3">
                                    <a href="index.php" class="btn btn-primary">Back to Login</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required 
                                           minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                           title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters">
                                    <div class="form-text">
                                        Password must be at least 8 characters long and include uppercase, lowercase, and numbers.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                    <?php if (!$_SESSION['is_first_login']): ?>
                                        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
ob_end_flush();
?> 