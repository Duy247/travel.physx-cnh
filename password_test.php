<?php
// Test password validation
require_once __DIR__ . '/includes/admin_security.php';
require_once __DIR__ . '/includes/password_manager.php';

echo "Today's password according to system: " . getTodayPassword() . "\n";

$testPasswords = [
    '06/07/2025',
    '6/7/2025',
    ' 06/07/2025 ', // with spaces
];

foreach ($testPasswords as $password) {
    echo "Testing password: '{$password}' - " . 
        (isValidPassword($password) ? "VALID" : "INVALID") . "\n";
}

echo "\nCustom password check:\n";
echo "Custom password set: " . (getCustomPassword() !== false ? "YES" : "NO") . "\n";
if ($custom = getCustomPassword()) {
    echo "Custom password is: " . $custom . "\n";
}
?>
