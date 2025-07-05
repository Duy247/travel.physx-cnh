<?php
// Include security middleware and password manager
require_once __DIR__ . '/includes/admin_security.php';
require_once __DIR__ . '/includes/password_manager.php';

// Check if the script is accessed directly
if (count(get_included_files()) <= 1) {
    // Direct access - require password
    session_start();

    // Check if user is already authenticated
    $isAuthenticated = false;
    if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
        $isAuthenticated = true;
    }

    // Process login attempt
    if (isset($_POST['password'])) {
        if (isValidPassword($_POST['password'])) {
            $_SESSION['admin_authenticated'] = true;
            $isAuthenticated = true;
        } else {
            $error = "Password incorrect. Please try again.";
        }
    }

    // Process logout
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Only continue if authenticated
    if (!$isAuthenticated) {
        // Show login form
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Authentication</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0b;
            color: #e4e4e7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            background: rgba(24, 24, 27, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #f4f4f5;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        input[type="password"] {
            width: 100%;
            padding: 1rem;
            background: rgba(39, 39, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #f4f4f5;
            font-size: 1rem;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .password-hint {
            display: block;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            color: #a1a1aa;
        }
        button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }
        .error-message {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1><i class="fas fa-lock"></i> Admin Access</h1>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required autofocus>
                <span class="password-hint">Hint: Today's date in dd/MM/yyyy format or custom password</span>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
        <?php
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Backend</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0b;
            color: #e4e4e7;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            gap: 1rem
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .header h1 {
            position: relative;
            font-size: 2.5rem;
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }
        
        .header p {
            position: relative;
            margin: 0.5rem 0 0;
            opacity: 0.8;
        }
        
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 0.5rem 1rem;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.3);
        }
        
        .files-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .file-card {
            background: rgba(24, 24, 27, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .file-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            background: rgba(39, 39, 42, 0.8);
        }
        
        .file-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #f4f4f5;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .file-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .btn-edit {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .btn-edit:hover {
            background: rgba(59, 130, 246, 0.3);
        }
        
        .editor-container {
            background: rgba(24, 24, 27, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .editor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .editor-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: #f4f4f5;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        textarea {
            width: 90%;
            height: 500px;
            background: rgba(39, 39, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #f4f4f5;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.9rem;
            line-height: 1.5;
            resize: vertical;
        }
        
        textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .btn-save {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }
        
        .btn-cancel {
            background: rgba(82, 82, 91, 0.2);
            color: #e4e4e7;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-cancel:hover {
            background: rgba(82, 82, 91, 0.3);
        }
        
        .message {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
        }
        
        .success-message {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .refresh-btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .setting-btn{
                        padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="header">
            <h1><i class="fas fa-shield-alt"></i> Admin Dashboard</h1>
            <p>Manage JSON data files for the travel application</p>
            <a href="?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </header>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="refresh-btn"><i class="fas fa-sync-alt"></i> Refresh</a>
            <a href="?password_settings=1" class="setting-btn" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px;">
                <i class="fas fa-key"></i> Password Settings
            </a>
        </div>
        
        <?php
        // Handle messages
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $status = $_GET['status'];
            $message = htmlspecialchars($_GET['message']);
            $messageClass = $status === 'success' ? 'success-message' : 'error-message';
            echo "<div class='message {$messageClass}'>{$message}</div>";
        }
        
        // Handle file editing
        if (isset($_POST['save_file']) && isset($_POST['file_path']) && isset($_POST['file_content'])) {
            $filePath = $_POST['file_path'];
            $content = $_POST['file_content'];
            
            // Validate if the file is in the allowed directory
            $baseDataDir = __DIR__ . '/public/data/';
            
            if (isPathAllowed($filePath, $baseDataDir) && pathinfo($filePath, PATHINFO_EXTENSION) === 'json') {
                // Save the file
                list($success, $message) = saveJsonFile($filePath, $content);
                
                $status = $success ? 'success' : 'error';
                $redirectUrl = $_SERVER['PHP_SELF'] . '?status=' . $status . '&message=' . urlencode($message);
                header("Location: $redirectUrl");
                exit;
            } else {
                $redirectUrl = $_SERVER['PHP_SELF'] . '?status=error&message=' . urlencode('Invalid file path or file type!');
                header("Location: $redirectUrl");
                exit;
            }
        }
        
        // Handle password settings
        if (isset($_GET['password_settings'])) {
            // Check if form is submitted
            if (isset($_POST['set_password'])) {
                $newPassword = trim($_POST['new_password']);
                if (empty($newPassword)) {
                    echo '<div class="message error-message">Password cannot be empty</div>';
                } else {
                    list($success, $message) = saveCustomPassword($newPassword);
                    $messageClass = $success ? 'success-message' : 'error-message';
                    echo "<div class='message {$messageClass}'>{$message}</div>";
                }
            }
            
            // Get current custom password
            $customPassword = getCustomPassword();
            $currentPasswordType = $customPassword ? 'Custom password' : 'Default date-based password (' . getTodayPassword() . ')';
            
            echo <<<HTML
            <div class="editor-container">
                <div class="editor-header">
                    <h2 class="editor-title"><i class="fas fa-key"></i> Password</h2>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <p style="margin-bottom: 1rem;">Current password type: <strong>{$currentPasswordType}</strong></p>
                    <p>The default password is the current date in format dd/MM/yyyy (e.g., 05/07/2025).<br>
                    You can set a custom password below that will override the default date-based password.</p>
                </div>
                
                <form method="post" action="?password_settings=1">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Set Password</label>
                        <input type="text" name="new_password" placeholder="Enter new password" 
                               style="width: 100%; padding: 1rem; background: rgba(39, 39, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.2); 
                                      border-radius: 12px; color: #f4f4f5; font-size: 1rem; margin-bottom: 1rem;">
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" name="set_password" class="btn btn-save">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <a href="{$_SERVER['PHP_SELF']}" class="btn btn-cancel">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
            HTML;
        }
        // Handle file editing interface
        else if (isset($_GET['edit']) && !empty($_GET['edit'])) {
            $editFile = $_GET['edit'];
            $baseDataDir = __DIR__ . '/public/data/';
            $filePath = $baseDataDir . $editFile;
            
            // Validate if the file is in the allowed directory
            if (isPathAllowed($filePath, $baseDataDir) && pathinfo($filePath, PATHINFO_EXTENSION) === 'json' && file_exists($filePath)) {
                $fileContent = file_get_contents($filePath);
                
                // Format JSON for better readability
                $fileContent = prettyPrintJson($fileContent);
                
                echo <<<HTML
                <div class="editor-container">
                    <div class="editor-header">
                        <h2 class="editor-title"><i class="fas fa-edit"></i> Editing: {$editFile}</h2>
                    </div>
                    
                    <form method="post" action="">
                        <input type="hidden" name="file_path" value="{$filePath}">
                        <textarea name="file_content" id="json-editor">{$fileContent}</textarea>
                        <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                            <button type="submit" name="save_file" class="btn btn-save">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <a href="{$_SERVER['PHP_SELF']}" class="btn btn-cancel">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
                HTML;
            } else {
                echo '<div class="message error-message">Invalid file path or file type!</div>';
            }
        } else {
            // Show the file list
            echo '<div class="files-container">';
            
            // Get all JSON files in data directory
            $dataDir = __DIR__ . '/public/data/';
            $jsonFiles = getAllJsonFiles($dataDir);
            
            if (empty($jsonFiles)) {
                echo '<p>No JSON files found in the data directory.</p>';
            } else {
                foreach ($jsonFiles as $file) {
                    $relativePath = str_replace($dataDir, '', $file);
                    $fileInfo = pathinfo($file);
                    $fileName = $fileInfo['basename'];
                    $fileSize = filesize($file);
                    $fileSizeFormatted = $fileSize < 1024 ? $fileSize . ' B' : 
                                         ($fileSize < 1024*1024 ? round($fileSize/1024, 1) . ' KB' : 
                                         round($fileSize/(1024*1024), 1) . ' MB');
                    
                    echo <<<HTML
                    <div class="file-card">
                        <div class="file-name">
                            <i class="fas fa-file-code"></i> {$relativePath}
                        </div>
                        <div style="color: #a1a1aa; font-size: 0.85rem;">
                            <span>Size: {$fileSizeFormatted}</span>
                            <span style="float: right;">Modified: 
                                {$fileName} {$fileSize}
                            </span>
                        </div>
                        <div class="file-actions">
                            <a href="?edit={$relativePath}" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                    HTML;
                }
            }
            
            echo '</div>';
        }
        ?>
    </div>

    <script>
        // Check for JSON syntax errors on save
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const jsonEditor = document.getElementById('json-editor');
            
            if (form && jsonEditor) {
                form.addEventListener('submit', function(e) {
                    try {
                        JSON.parse(jsonEditor.value);
                    } catch (error) {
                        e.preventDefault();
                        alert('Invalid JSON: ' + error.message);
                    }
                });
            }
        });
    </script>
</body>
</html>
