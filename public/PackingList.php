<?php
// public/PackingList.php

// Prevent caching for mobile devices
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Generate cache busting timestamp
$cache_bust = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Packing List - Travel PhysX CNH</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo $cache_bust; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <h1>Packing List</h1>
        <h2>Hue - Danang - Hoian Adventure</h2>
    </header>

    <a href="index.php" class="back-button">‹</a>

    <main class="main-content">
        <div class="container">
            <h3>Coming Soon</h3>
            <p>Packing list page is under construction.</p>
        </div>
    </main>
</body>
</html>
