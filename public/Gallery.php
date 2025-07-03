<?php
// public/Gallery.php

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
    <title>Gallery</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #262626;
            margin: 0;
            padding: 10px;
        }
        h1 {
            text-align: center;
            color: #dadada;
            font-size: 24px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 10px;
        }
        .gallery-container {
            width: 100%;
            height: 600px;
            border: 2px solid #7c7272;
            box-sizing: border-box;
            border-radius: 10px;
            overflow: hidden;
        }
        .gallery {
            width: 100%;
            height: 100%;
            margin:0px;
            border: none;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #a68af9;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <h1>Thư Viện Ảnh</h1>   
    
    <div class="gallery-container">
        <iframe class="gallery" src="https://drive.google.com/embeddedfolderview?id=17xlDHqSfzBmxkAbckbkeMJTPUYMVJT_1#grid" frameborder="0"></iframe>
    </div>
    
    <a href="Main.html" class="back-link">Về Trang Chủ</a>
</body>
</html>