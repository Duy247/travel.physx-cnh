<?php
/**
 * Travel PhysX CNH
 * Entry point for the application
 */

// Display basic information
echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Travel PhysX CNH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }";
echo "        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "        h1 { color: #333; text-align: center; }";
echo "        .info { background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <h1>Welcome to Travel PhysX CNH</h1>";
echo "        <div class='info'>";
echo "            <h3>System Information:</h3>";
echo "            <p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "            <p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "            <p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "        </div>";
echo "        <div class='info'>";
echo "            <h3>Application Status:</h3>";
echo "            <p>✅ Application is running successfully!</p>";
echo "            <p>📁 Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "        </div>";
echo "    </div>";
echo "</body>";
echo "</html>";
